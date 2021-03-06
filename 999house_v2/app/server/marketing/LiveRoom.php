<?php
namespace app\server\marketing;

use app\common\base\ServerBase;
use think\Db;
use think\Exception;

class LiveRoom extends ServerBase
{
    //显示所有
    public function getList($search = [], $field='l.*,es.name as forname',$pageSize = 50){
        $where = [];
        if(is_array($search['status'])) {
            if(!empty($search['status'])) {
                $where[]=  ['l.status', 'in', $search['status']];
            }
        } else {
            if(!in_array($search['status'],['0','1','2','3'])){
                unset($search['status']);
            }
            if(isset($search['status'])){//状态
                $where[]=  ['l.status','=', $search['status']];
            }
        }

        if(!empty($search['room_name'])){
            $where[]=  ['l.room_name','like', '%'.$search['room_name'].'%'];
        }
        if(!empty($search['room_url'])){//直播间地址
            $where[]=  ['l.room_url','=', $search['room_url']];
        }
        $order = ['l.id'=>'desc'];
        if(!empty($search['sort'])){//排序
            $order = ['l.sort'=>$search['sort'],'id'=>'desc'];
        }
        if(!empty($search['start_time'])){
            $where[]=  ['l.create_time','>=', $search['start_time']];
        }
        if(!empty($search['end_time'])){
            $where[]=  ['l.create_time','<=', $search['end_time']];
        }

        if(!empty($search['region_no'])){
            if(is_array($search['region_no'])) {
                $where[]=  ['l.region_no','in', $search['region_no']];
            } else {
                $where[]=  ['l.region_no','=', $search['region_no']];
            }
        }

        if(!empty($search['ids'])) {
            $where[]=  ['l.id','in', $search['ids']];
        }

        if(!empty($search['estates_id'])) {
            $where[]=  ['l.forid','in', $search['estates_id']];
        }

        $list = $this->db->name("live_room")->alias('l')->join('estates_new es','es.id=l.forid')->field($field)->where($where)->order($order)->paginate($pageSize);
        if($list->isEmpty()){
            $result['list'] = [];
        }else{
            $result['total'] = $list->total();
            $result['last_page'] = $list->lastPage();
            $result['current_page'] = $list->currentPage();
            $result['list'] =$list->items();
        }
        return $this->responseOk($result);
    }

    //添加操作
    public function add($data)
    {
        try{

            $id = $this->db->name("live_room")->insertGetId([
                'room_name' => $data['room_name'],
                'cover'=> $data['cover'],
                'share_img' => $data["share_img"],
                'start_time'=> $data["start_time"],
                'end_time'=> $data["end_time"],
                'status' => intval($data["status"]),
                "room_url"=> $data["room_url"],
                'sort' => intval($data["sort"]),
                'desc' => $data["desc"],
                'forid' => intval($data["forid"]),
                'region_no' => $data["region_no"],
                'update_time'=> 0,
                'create_time'=> time()
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
                $has=$this->db->name('live_room')
                    ->field('cover')->where([
                        ['id','=',$id],
                    ])->find();
            }

            $data['update_time'] = time();
            $rs = $this->db->name('live_room')->where(['id'=>$id])->update($data);
            if(empty($rs)){
                throw new Exception('操作失败');
            }

            if(!empty($has['cover'])&&!empty($data['cover'])&&$has['cover']!=$data['cover']){
                //删除旧的图片
                $this->delFile($has['cover']);
            }

            return $this->responseOk();
        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }


    public function del($id)
    {
        try{
            $res = $this->db->name("live_room")->where("id",$id)->delete();
            if($res){

                return $this->responseOk($res);
            }else{
                throw new Exception('操作失败');
            }

        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }




}