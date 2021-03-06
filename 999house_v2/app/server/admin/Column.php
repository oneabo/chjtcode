<?php
namespace app\server\admin;


use app\common\base\ServerBase;
use think\Exception;

class Column extends ServerBase
{

    //显示所有
    public function getList($search = []){
        $where = [];
        if(!in_array($search['status'],['0','1'])){
            unset($search['status']);
        }
        if(isset($search['status'])){//状态
            $where[]=  ['c1.status','=', $search['status']];
        }

        if($search['type']<0){
            unset($search['type']);
        }
        if(isset($search['type'])){//类型
            $where[]=  ['c1.type','=', $search['type']];
        }
        if(!empty($search['place'])){//使用位置
            $where[]=  ['c1.place','=', trim_all($search['place'])];
        }
        if(!empty($search['href'])){//跳转地址
            $where[]=  ['c1.href','=', $search['href']];
        }

        if(!empty($search['pid']) || $search['pid'] === '0') {
            $where[] = ['c1.pid', '=', $search['pid']];
        }
        $order = ['c1.id'=>'desc'];
        if(!empty($search['sort'])){//排序
            $order = ['c1.sort'=>$search['sort'],'id'=>'desc'];
        }
        $list = $this->db->name("column")->alias('c1')
            ->leftJoin('column c2','c1.pid=c2.id')
            ->field('c1.*,c2.title as p_title')
            ->where($where)->order($order)->select()->toArray();
        if(empty($list)){
            $result['list'] = [];
        }else{
            foreach ($list as &$value){
                $value['cover'] = getRealStaticPath($value["cover"]);
            }
            unset($value);
            $result['list'] =   $list;
        }
        return $this->responseOk($result);
    }

    //添加操作
    public function add($data)
    {
        try{
            if(isset($data['place'])){
                $data['place'] = trim_all($data['place']);
            }
            $data['title'] = trim_all($data['title']);
            $data['flag_name'] = trim_all($data['title']);

            $id = $this->db->name("column")->insertGetId([
                'cover'=> $data['cover'],
                'type'=> $data['type'],
                'status'=> $data['status'],
                'sort'=> $data['sort'],
                'place'=> $data['place'],
                'href'=> $data['href'],
                'title' => $data['title'],
                'page_title' => $data["page_title"],
                'page_keywords' => $data["page_keywords"],
                'page_desc' => $data["page_desc"],
                'update_time'=> 0
            ]);   //将数据存入并返回自增 ID
            if(empty($id)){
                throw new Exception('操作失败');
            }
            return $this->responseOk($id);
        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    //修改状态
    public function edit($id,$data){
        try{
            $id = intval($id);
            if(empty($id)){
                throw new Exception('缺少设置参数');
            }
            unset($data['id']);//不可变更id
            if(!empty($data['cover'])){
                $has=$this->db->name('column')
                    ->field('cover')->where([
                        ['id','=',$id],
                    ])->find();
            }
            if(isset($data['place'])){
                $data['place'] = trim_all($data['place']);
            }
            if(isset($data['title'])){
                $data['title'] = trim_all($data['title']);
            }

            $data['update_time'] = time();



            /**标签和栏目关系处理**/
           $info = $this->db->name('article_tag_bind_cloumn')->where('cloumn_id',$id)->field('tag_id')->find();

            if(empty($data['tags'])){ //如果标签是空的时候
                if($info){   //标签和栏目关联表中的标签如果存在则全部删除
                    $this->db->name('article_tag_bind_cloumn')->where('cloumn_id',$id)->delete();
                }


            }else{ //如果标签不是空的时候。
                if($data['place'] == 'h5_fx_home' && $id == 13){
                    $type = 2;
                }else{
                    $type = 1;
                }

                $array_all = [];
                $time = time();
                $tags = explode(',',$data['tags']);

                if($info){ //如果存在标签和栏目关系，进行筛选对比重新存入
                    $tagId = $this->db->name('article_tag_bind_cloumn')->where('cloumn_id',$id)->field('tag_id')->select()->toArray();
                    $tagArray = array_column($tagId,'tag_id');
                    $insId = array_diff($tags,$tagArray); //插入
                    $delId = array_diff($tagArray,$tags);
                    foreach ($insId as $v){
                        $array_all[] = [
                            'tag_id' => $v,
                            'cloumn_id' => $id,
                            'create_time' => $time,
                            'update_time' => $time,
                            'type' => $type
                        ];
                    }

                    if(!empty($delId)){
                        $deleteWhere = [
                            ['tag_id','in',$delId]
                        ];
                        $this->db->name('article_tag_bind_cloumn')->where($deleteWhere)->delete();
                    }

                }else{ //如果存在标签和栏目没有关系，直接存入

                    foreach ($tags as $v){
                        $array_all[] = [
                            'tag_id' => $v,
                            'cloumn_id' => $id,
                            'create_time' => $time,
                            'update_time' => $time,
                            'type' => $type
                        ];
                    }

                }

                if(!empty($array_all)){
                    $this->db->name('article_tag_bind_cloumn')->insertAll($array_all);
                }
            }


            $rs = $this->db->name('column')->where(['id'=>$id])->update($data);

            if(empty($rs)){
                throw new Exception('操作失败');
            }

            if(!empty($has['cover'])&&!empty($data['cover'])&&$has['cover']!=$data['cover']){
                //删除旧的图片
                $this->delFile($has['cover']);
            }

            return $this->responseOk();
        }catch (Exception $e){var_dump($e->getMessage());
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }


    public function del($id)
    {
        try{
            //        $fileName 为图片路径，从数据库中获取
            $fileName = $this->db->name("column")->where("id",$id)->value("cover");

            $res = $this->db->name("column")->where("id",$id)->delete();
            if($res){
                //删除旧的图片
                $this->delFile($fileName);

                return $this->responseOk($res);
            }else{
                throw new Exception('操作失败');
            }

        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 获取
     */
    public function getListByPid($pid,$flag=''){

           $list  = $this->db->name('column')
               ->where('pid','=',$pid)
               ->where('status','=',1);

           if(!empty($flag) ) {
               $list  =$list->whereLike('place','%'.$flag.'%');

           }
        $list = $list->select();
           if($list){
               return $this->responseOk($list->toArray());
           }

    }

    /**
     * 根据标识获取顶级类别
     */
    public function getListByPlace($place){
        $list  = $this->db->name('column')
            ->where('status','=',1)
            ->whereLike('place','%'.$place.'%')
            ->where('pid','=',0)
            ->select();
        return $list;
    }



    /**
     *获取所有分类
     */
    public function getCateListAll($pid =[],$field='*') {

        if($pid){
            $where[]     = ['pid','in',$pid];
            $whereor[]   = ['id','in',$pid] ;
        }

        return $this->db->name('Column')->where('status','=',1)
            ->where($where)
            ->whereOr($whereor)
            ->field($field)
            ->order('sort asc,id asc')
            ->select()->toArray();
    }

    /**
     *获取所有标签
     */
    public function getLabelListAll($field = "*"){
        //现在改标签
        return $this->db->name('article_tag')->where('status','=',1)
            ->field($field)
            ->order('id asc')
            ->select()->toArray();
    }

    public function getInfo($id){
        if(empty($id)){
            return [];
        }
        $info  = $this->db->name('Column')->where('id','=',$id)->find();
        if( empty($info) ){
            return  [];
        }
        return $info;
    }
   //获取正在使用的标签
   public function getIsTrueTag(){
        $info  = $this->db->name('column')
            ->where('place','=','h5_fx_home')
            ->where('status','=',1)
            ->where('pid','>',0)
            ->field('tags')
            ->select()->toArray();

        $tagList = [];
        foreach ($info as $k => $v){
            if($k == 0 ){
                $tagList= explode(',',$v['tags']);
            }else{
                $arr  = explode(',',$v['tags']);
                foreach ($arr as $key=>$value){
                    if(!in_array($value,$tagList)){
                        $tagList[] = $value;
                    }
                }
            }

        }

        return $tagList ?? [];


   }


}