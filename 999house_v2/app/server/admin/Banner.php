<?php
namespace app\server\admin;

use app\common\traits\TraitInstance;
use app\common\base\ServerBase;
use app\common\MyConst;
use app\common\traits\TraitEstates;
use think\App;
use think\Db;
use think\Exception;

class Banner extends ServerBase
{
    use TraitEstates;

    /**
     * 获取设置的广告位置
     */
    public function getPlaceList($search = []){
        $where = [];
        if(!in_array($search['type'],['0','1'])){
            unset($search['type']);
        }
        if(isset($search['type'])){//状态
            $where[]=  ['type','=', $search['type']];
        }

        $order = ['id'=>'desc'];
        $list = $this->db->name("banner_img_place")->where($where)->order($order)->select()->toArray();
        if(empty($list)){
            $result['list'] = [];
        }else{
            $result['list'] =   $list;
        }
        return $this->responseOk($result);
    }
    //添加操作
    public function placeAdd($data)
    {
        try{
            $data['place'] = trim_all($data['place']);
            if(empty($data['place'])){
                throw new Exception('该标识已经存在');
            }

            $has = $this->db->name("banner_img_place")->where([
                'place' => $data['place'],
            ])->value('id');
            if(!empty($has)){
                throw new Exception('该标识已经存在');
            }

            $id = $this->db->name("banner_img_place")->insertGetId([
                'type'=> $data['type'],
                'desc'=> $data['desc'],
                'place'=> $data['place'],
                'update_time'=> 0,
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
    public function placeEdit($id,$data){
        try{
            $this->db->startTrans();
            $id = intval($id);
            if(empty($id)){
                throw new Exception('缺少设置参数');
            }
            unset($data['id']);//不可变更id

            if(isset($data['place'])){
                $data['place'] = trim_all($data['place']);
                $has = $this->db->name("banner_img_place")->where([
                    'place' => $data['place'],
                ])->value('id');
                if(!empty($has)&&$has!=$id){
                    throw new Exception('该标识已经存在');
                }
            }
            $data['update_time'] = time();
            $rs = $this->db->name('banner_img_place')->where(['id'=>$id])->update($data);
            if(empty($rs)){
                throw new Exception('操作失败');
            }
            if(isset($data['place'])){
                $this->db->name('banner_img')->where(['place_id'=>$id])->update([
                   'place'=> $data['place']
                ]);
            }

            $this->db->commit();
            return $this->responseOk();
        }catch (Exception $e){
            $this->db->rollback();
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }
    public function placeDel($id){
        try{
            $this->db->startTrans();
            $res = $this->db->name("banner_img_place")->where("id",$id)->delete();
            if($res){
                $this->db->name("banner_img")->where("place_id",$id)->delete();
            }else{
                throw new Exception('删除失败');
            }

            $this->db->commit();
            return $this->responseOk($res);
        }catch (Exception $e){
            $this->db->rollback();
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    //显示所有
    public function getList($search = []){
        $where = [];
        if(!in_array($search['status'],['0','1'])){
            unset($search['status']);
        }
        if(isset($search['status'])){//状态
            $where[]=  ['status','=', $search['status']];
        }

        if(!empty($search['place'])){//使用位置
            $where[]=  ['place','=', trim_all($search['place'])];
        }
        if(!empty($search['place_id'])){//使用位置
            $where[]=  ['place_id','=', ($search['place_id'])];
        }
        if(!empty($search['href'])){//跳转地址
            $where[]=  ['href','=', $search['href']];
        }
        $order = ['id'=>'desc'];
        if(!empty($search['sort'])){//排序
            $order = ['sort'=>$search['sort'],'id'=>'desc'];
        }
        if(!empty($search['start_time'])){
            $where[]=  ['start_time','>=', $search['start_time']];
        }
        if(!empty($search['end_time'])){
            $where[]=  ['end_time','<=', $search['end_time']];
        }

        $list = $this->db->name("banner_img")->where($where)->order($order)->select()->toArray();
        if(empty($list)){
            $result['list'] = [];
        }else{

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

            $id = $this->db->name("banner_img")->insertGetId([
                'cover'=> $data['cover'],
                'status'=> $data['status'],
                'sort'=> $data['sort'],
                'place'=> $data['place'],
                'place_id'=> $data['place_id'],
                'forid'=> $data["forid"],
                'forname'=> $data["forname"],
                'is_propert_news'=> $data["is_propert_news"] ? 1:0,
                'href'=> $data['href'],
                'title'=> $data["title"],
                'start_time'=> $data['start_time'],
                'end_time'=> $data['end_time'],
                'region_no'=> $data["region_no"],
                'update_time'=> 0,
                'read_num'=> $data["read_num"],
                'type' => $data["type"],
                'align' => $data["align"],
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
                $has=$this->db->name('banner_img')
                    ->field('cover')->where([
                        ['id','=',$id],
                    ])->find();
            }

            if(isset($data['place'])){
                $data['place'] = trim_all($data['place']);
            }
            $data['update_time'] = time();
            $rs = $this->db->name('banner_img')->where(['id'=>$id])->update($data);
            if(empty($rs)){
                throw new Exception('操作失败');
            }

//            if(!empty($has['cover'])&&!empty($data['cover'])&&$has['cover']!=$data['cover']){
//                //删除旧的图片
//                $this->delFile($has['cover']);
//            }

            return $this->responseOk();
        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }


    public function del($id)
    {
        try{
            //        $fileName 为图片路径，从数据库中获取
            $fileName = $this->db->name("banner_img")->where("id",$id)->value("cover");
            /*$fileName = ltrim($fileName,'/');   //去除第一个”/“ 否者找不到文件
            if(file_exists($fileName))
            { //检查图片文件是否存在
                $tf = unlink($fileName);
            }*/
            $res = $this->db->name("banner_img")->where("id",$id)->delete();
            if($res){
                //删除旧的图片
                $this->delFile($fileName);

                return $this->responseOk($res);
            }else{
                throw new Exception('图片删除失败');
            }

        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 获取文章列表
     */
    public function getNewsAdvlist($start,$flag,$ad_flag){
        $list =  $this->db->name('banner_img')
            ->where('place','=',$ad_flag)
            ->where('status','=',1)
            ->where('start_time','<=',time())
            ->where('end_time','>=',time())
            ->order('sort desc,id desc')
            ->limit($start,$flag)->select()->toArray();
//        echo $this->db->getLastSql();
        return $list;

    }

    /**
     * 获取最小id
     */
   public function getMinId(){
        return $this->db->name('banner_img')->min('id')??1;
   }
   /**
    * 获取最大id
    */
   public function getMAxId(){
       return $this->db->name('banner_img')->Max('id');
   }

    /**
     * 根据位置标识获取一天广告
     */
    public function getRandNewsAdvInfoByFlag($flag){
        $info =  $this->db->name('banner_img')
            ->where('place','=',$flag)
            ->where('status','=',1)
            ->where('start_time','<=',time())
            ->where('end_time','>=',time())
            ->where('id','>=','')
            ->orderRand()
            ->find();
//        echo $this->db->getLastSql();
        return $info;

    }

    /**
     * 获取广告-楼盘适用版
     */
    public function getAdvServer($params)
    {
        try {
            $userId = $params['user_id'] ?? 0;
            $num = $params['num'] ?? '6';// 间隔条数(每几条插一条广告)
            $count = $params['count'] ?? 0;// 当前列表条数
            $flag = intval($count/$num);// 本次需要拉取的广告数量
            $place = $params['place'] ?? 'h5_estates_list';// 广告位置
            $type = $params['type'] ?? 'estates';// 广告位置
            $advLsit = [];
            if($flag) {
                $banner = new Banner();

                switch($type) {
                    case 'estates':
                        $key = MyConst::ESTATES_ADV_READING_FLAG;
                        break;
                    default:
                        return $this->responseFail('类型错误');
                        break;
                }

                $redis = $this->getReids();
                $start = $redis->hGet($key, $userId) ?? 0;
                $dataAdv = [
                    'where' => [
                        ['place', '=', $place],
                        ['status', '=', 1],
//                        ['start_time', '<=', time()],
//                        ['end_time', '>=', time()],
                    ],
                    'order' => 'sort desc, id desc',
                    'start' => $start,
                    'end' => $flag,
                ];
                $advRes = $banner->getAdvListByParams($dataAdv);
                if(!empty($advRes['code']) && 1 == $advRes['code']) {
                    $advLsit = $advRes['result'];
                }

                $start = $start + $flag;
                // 广告数量不足，从头补足
                $advCount = count( $advLsit);
                if($advCount < $flag){
                    $start = $dataAdv['start'] = 0;
                    $dataAdv['end'] = $flag - $advCount;
                    $advResNew = $banner->getAdvListByParams($dataAdv);

                    if(!empty($advResNew['code']) && 1 == $advResNew['code']) {
                        $advListNew = $advResNew['result'];
                    }

                    $start += $flag - $advCount;
                }
                $redis->hSet($key, $userId, $start);// 记录位置
                // 合并
                $advLsit  = array_merge($advLsit, $advListNew ?? []);

                //判断广告类型

                if(!empty($advLsit)){
                    //todo 广告处理-如果有过期的，自动加到过期里面
                    $advLsit = $this->adExpired($advLsit);
                    $advLsit  = $this->getAdlist($advLsit);
                }
            }
            return $this->responseOk($advLsit);
        } catch(Exception $e) {
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 根据位置标识获取广告-楼盘适用版
     */
    public function getAdvListByParams($params)
    {
        try {
            // 条件
            $where = $params['where'] ?? [];
            // 字段
            $fields = $params['fields'] ?? "*";
            // 排序
            $order = $params['order'] ?? [];
            // 区间
            $start = $params['start'] ?? 0;
            $end = $params['end'] ?? 0;
            // 联表
            $join = $params['join'] ?? [];
            // 分组
            $group = $params['group'] ?? "";

            $myDB = $this->db->name('banner_img')->alias('bi');

            // 条件
            if(!empty($where)) {
                $myDB->where($where);
            }
            // 联表
            if(!empty($join)) {
                foreach($join as $v) {
                    if(!empty($v['table']) && !empty($v['cond'])) {
                        $type = $v['type'] ?? 'left';
                        $myDB->join($v['table'], $v['cond'], $type);
                    }
                }
            }
            // 字段
            $myDB->field($fields);
            // 排序
            if(!empty($order)) {
                $myDB->order($order);
            }
            // 分组
            if(!empty($group)) {
                $myDB->group($group);
            }
            // 区间条数
            $myDB->limit($start, $end);

            $list = $myDB->select()->toArray();
            if(empty($list)) {
                $list = [];
            }
            $result = $list;

            return $this->responseOk($result);
        } catch(Exception $e) {
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 判断广告类型放回前端格式
     * @param $data
     */
    private function getAdlist($data){
        if(empty($data) ) {
            return [];
        }
        $arr =  array();
        foreach ($data as $k=> $v) {
            $arr[$k]['id']      = $v['id'];
            if($v['type'] == 2) { //类型为2 的时候是楼盘广告带图的
                $arr[$k]['type']    = 4; // 楼盘带图广告
                $res = $this->db->name("estates_new")->alias('en')
                                ->leftJoin('estates_has_tag eht','en.id = eht.estate_id AND eht.type = 1')
                                ->field('en.logo,en.detail_cover,	en.id,en.NAME,en.list_cover,en.price,en.price_total,en.city_str,en.area_str,en.city,en.area,en.business_area_str,en.sale_status,en.built_area,en.house_purpose,en.discount,GROUP_CONCAT( tag_id ) AS feature_tag')
                                ->where('en.id','=',$v['forid'])->find();
                $sellingPoint = $this->dealSellingPoint($res);
                $arr[$k]['info']['lab']  = $sellingPoint;
                $arr[$k]['info']['name']  = $res['NAME'];
                $arr[$k]['info']['price']  = (int)$res['price'];
                $arr[$k]['info']['sale_status']  = $res['sale_status'];
                $arr[$k]['info']['tip']  = !empty($res['feature_tag']) ? array_splice(explode(',', $res['feature_tag']), 0, 2) : [];
                $arr[$k]['info']['house_purpose']  = !empty($res['house_purpose']) ? explode(',', $res['house_purpose']) : [];
                $arr[$k]['info']['area']  = $res['built_area'];
                $arr[$k]['info']['site']  = $res['area_str'].$res['business_area_str'];
                $arr[$k]['info']['estate_id']  = $res['id'];
                $arr[$k]['info']['cover']  = !empty($res['logo']) && !empty($res['detail_cover']) ? 1:0;
              //  $arr[$k]['info']['img']  = array_column(json_decode( $arr[$k]['info']['img'],true),'url') ?? [];
            }else if ($v['type'] == 0){
                $arr[$k]['type']    = 2; //广告有图
            }else{
                $arr[$k]['type']    = 3; //广告视频
            }
            $arr[$k]['title']   = $v['title'] ?? '';
            $arr[$k]['href']    = $v['href'] ?? '';
            if($arr[$k]['type'] == 3){
                $img_url  =  trim($v['cover_path'],'"');
            }else{
                $img_url            = array_column(json_decode($v['cover_path'],true),'url');
            }
            $arr[$k]['img']     =  $img_url;
        }

        return $arr;
    }



    /**
     * 广告显示处理
     * @param $data 数据
     */
    public function adExpired($data){
        $time = time();
        $changeId = [];
        foreach ($data as $key => $value){
            switch ($value['put_type']){
                case 1://时间
                    if($value['end_time'] <= $time){
                        $changeId[] = $value['id'];
                        unset($data[$key]);
                    }elseif($value['start_time']>$time){
                        unset($data[$key]);
                    }
                    break;
                case 2: //点击
                    if($value['click_on_upper'] >= $value['click_on']){
                        $changeId[] = $value['id'];
                        unset($data[$key]);
                    }
                    break;
                case 3: //浏览
                    if($value['read_num_upper'] >= $value['read_num']){
                        $changeId[] = $value['id'];
                        unset($data[$key]);
                    }
                    break;

            }
        }

        if(!empty($changeId)){
            $where = [
                ['id','IN',$changeId],
                ['status','=',1]
            ];

            //插入消息通知
            $res = $this->db->name('banner_img')->where($where)->field('id,title')->select()->toArray();

            if($res){
                $insert = [];
                foreach ($res as $key => $v){
                    $insert[] = [
                        'banner_id' => $v['id'],
                        'name' => $v['name'],
                        'type' => 1,
                        'create_time'=>$time,
                        'update_time'=>$time
                    ];
                }

                if(!empty($insert)){
                    $this->db->name('notification')->insertAll($insert);
                }
            }

            //修改用户状态
            $this->db->name('banner_img')->where($where)->update([
                'status' => 0
            ]);
        }

        return $data;

    }

    /**
     * 点击量
     * @param $params
     * @return array
     */
    public function clickOnAd($params){
        try {
            $info = $this->db->name('banner_img')->where('id',$params['id'])->find();
            if($info){
                $this->db->name('banner_img')->where('id',$params['id'])->setInc('setInc',1);
            }
            return $this->responseOk();
        }catch (\Exception $exception){
            return $this->responseFail($exception->getMessage());
        }
    }


}