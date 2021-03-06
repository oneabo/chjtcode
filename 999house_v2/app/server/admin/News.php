<?php
namespace app\server\admin;

use app\common\traits\TraitInstance;
use app\common\base\ServerBase;
use Co\WaitGroup;
use think\facade\Db;
use think\Exception;
use  \app\common\traits\News as news1;
class News extends ServerBase
{

    /**
     * 获取分类列表
     */
    use news1;
    public function getCategoryList($search = []){
        $where = [];
        if(!in_array($search['status'],['0','1'])){
            unset($search['status']);
        }
        if(isset($search['status'])){//状态
            $where[]=  ['c.status','=', $search['status']];
        }


        if(!in_array($search['has_comment'],['0','1'])){
            unset($search['has_comment']);
        }
        if(isset($search['has_comment'])){//状态
            $where[]=  ['c.has_comment','=', $search['has_comment']];
        }

        if( isset($search['name']) ){
            $where[]=  ['c.title','like', "%".$search['name']."%"];
        }
        if( isset($search['pid']) ){
            $where[]=  ['c.pid','=', $search['pid']];
        }
        $list = $this->db->name("column")->alias('c')->leftJoin('column c1','c.pid=c1.id')
                         ->field('c.*,c1.title as p_title')
                         ->where($where)
                         ->where('c.pid','in',[9,13])
                         ->select()
                         ->toArray();
//        echo $this->db->getLastSql();
        if(empty($list)){
            $result['list'] = [];
        }else{
            $result['list'] =   $list;
        }
        return $this->responseOk($result);
    }

    /**
     * 状态修改
     * @param $id
     * @param $status
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function categoryEnable($id,$status){
        $res  =  $this->db->name('column')->where('id','in',$id)->update(['status'=>$status]);
        return  $res === false ? false :true;
    }

    /**
     * @param $id
     * @param $has_comment
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function commentEnable($id,$has_comment){
        $res  =  $this->db->name('column')->where('id','in',$id)->update(['has_comment' => $has_comment]);
        return  $res === false ? false :true;
    }

    /**
     * 排序需改
     * @param $id
     * @param $sort
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function categoryChangeSort($id,$sort) {
        $res  =  $this->db->name('column')->where('id','=',$id)->update(['sort' => $sort]);
        return  $res === false ? false :true;
    }

    /**
     *获取顶级模块
     */
    public function getCateList() {
        return $this->db->name('column')->where('pid','=',0)->where('status','=',1)->select()->toArray();
    }

    /**
     * 根据id修改分类
     * @param $data
     * @return array
     */
    public function categoryEdit($data)
    {
        try{
           $res = $this->db->name('column')->where('id','=',$data['id'] )->update($data);
           return  $res !==false ? $this->responseOk() : $this->responseFail();

        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    /**
     * 创建分类
     * @param $data
     * @return array
     */
    public function categoryCreate($data){
        try{
            $res = $this->db->name('column')->insert($data);
            return  $res !==false ? $this->responseOk() : $this->responseFail();
        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }

    }

    /**
     * 根据id获取信息
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo($id){
        $info  = $this->db->name('column')->where('id','=',$id)->find();
        return $info;
    }

    /**
     * 根据pid获取集合
     * @param $pid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSonList($pid){
       $list  =   $this->db->name('article_category')->where('pid','=',$pid)->select();
       return $list;
    }

    /**
     * 根据id删除分类
     * @param $id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function categoryDel($id){
        $list  =   $this->db->name('column')->where('id','=',$id)->delete();
        return $list===false ? false:true;
    }

    /***
     * 获取推荐文章排序
     */
    public function getArticleOrder($cate_id){
        $redis = $this->getReids();
        $key   = "9H:article_order:cate_".$cate_id;
        return  $redis->incr($key);
    }

    /**
     * 文章修改
     * @param $data
     * @return bool
     */
    public function edit($data){
      $res =   $this->db->name('article')->where('id','=',$data['id'])->update($data);
      return  $res===false ? false: true;

    }
    /**
     * 文章新增
     * @param $data
     * @return bool
     */
    public function add($data){
        $res =   $this->db->name('article')->insert($data,true);
        return  $res===false ? false: $res;

    }
    public function getList($search){
        $field = 'a.id,a.title,a.name,a.resource_type,a.keyword,a.order_type,a.is_original,a.is_top,
        a.is_index,a.lable,a.source_id,a.num_thumbup,a.num_share,a.num_collect,a.num_read,a.num_read_real,
        a.num_collect_real,a.num_share_real,a.num_thumbup_real,a.release_time,a.update_time,a.top_time,a.sort,
        a.status,a.region_no,a.forid,a.img_url,a.is_propert_news,a.is_wx_material,a.city_list';
        $where = [];
        if(!in_array($search['status'],['0','1','2'])){
            unset($search['status']);
        }
        if(isset($search['status'])){//状态
            $where[]=  ['a.status','=', $search['status']];
        }

        if(isset($search['cate_id']) && $search['cate_id'] !=='all'){//状态
            $where[]=  ['ac.tag_id','=', $search['cate_id'] ];
        }
        if(!empty( $search['city']) ){
            $where[] = ['a.city_list','like',"%{$search['city']}%"];
        }
        if( !empty($search['name']) ){
            $where[]=  ['a.name','like', "%".$search['name']."%"];
        }

        if(!empty($search['start_date']) && !empty($search['end_date'])){ //时间搜索
            $where[] = ['a.release_time','>=',$search['start_date']];
            $where[] = ['a.release_time','<=',$search['end_date']];
        }
        if(!empty($search['region_no'])){
            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$search['region_no']}, city_list)")];
        }

        if(!empty($search['forid'])){
            $where[] = ['forid','=',$search['forid']]; //单id todo 到时候要做成多选 等前端有空的时候
//            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$search['forid']}, forid)")];
        }

        $order = ['a.release_time'=>'desc','a.sort'=>'desc','a.id'=>'desc'];
        $list = $this->db->name("article")->alias('a')
//            ->leftJoin('article_cloumn ac','a.id=ac.article_id') //todo 以前是栏目，现在改成标签
            ->leftJoin('article_tag_bind ac','a.id=ac.article_id')
            ->field($field.',a.status as status1')
            ->where($where)
            ->order($order)
            ->group('a.id')
            ->paginate(10);
//        var_dump($this->db->getLastSql());
        if($list->isEmpty()){
            $result['list'] = [];
        }else{
            $list = $list->toArray();
            $arrayId = array_column($list['data'],'id');

            $whereId[] = [
                ['atb.article_id','IN',$arrayId]
            ];

            //标签的处理-显示
            $label = $this->db->name('article_tag_bind')->alias('atb')
                ->leftJoin('article_tag at','at.id = atb.tag_id')
                ->leftJoin('article_tag at1','at.pid = at1.id')
                ->where($whereId)
                ->field('atb.article_id,at.name,at1.name as pname')
                ->select()->toArray();
            $lableData = [];
            foreach ($label as $v){
               if(!empty($v['pname'])){
                   $v['lable'] = $v['pname'].'/'.$v['name'];
               }else{
                   $v['lable'] = $v['name'];
               }
                $lableData[$v['article_id']][] = $v['lable'];
            }

            foreach ($list['data'] as $key => &$value){
                if(!empty($lableData[$value['id']])){
                    $value['label_tag'] = $lableData[$value['id']];
                }else{
                    $value['label_tag'] = [];
                }
            }

            $result['total'] = $list['total'];
            $result['last_page'] = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list'] =$list['data'];
        }
        return $this->responseOk($result);
    }



    /**
     * 修改单字段
     * @param $id
     * @param $column_val
     * @param $p_cate_id
     * @param string $column_key
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DbException
     */
    public function setColumnSort($id,$column_val,$column_key='sort'){
        $res  =  $this->db->name('article')
            ->where('id','=',$id)
            ->update([$column_key=>$column_val]);
        return $res ===false ? false:true;
    }

    public function getNewsInfo($id){
        $info  = $this->db->name('article')
                          ->where('id',$id)->find();

        return $info;
    }

    /**
     * 删除新闻
     * @param $id
     * @param $p_cate_id
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DbException
     */
    public function delNews($id){
        $this->db->startTrans();
        $res  = $this->db->name('article')
            ->where('id',$id)->delete();
        $res === false ? false:true;
        if(!$res){
            $this->db->rollback();
            return false;
        }

       $res = $this->db->name('article_attention')->where('article_id',$id)->delete();
         $res === false ? false:true;
        $res === false ? false:true;
        if(!$res){
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return $res;
    }

    /**
     * 根据条件获取 前端 文章列表
     */
    public function getArticleList($where){
        $field = 'a.id,ad.account,a.title,a.name,a.resource_type,a.keyword,a.order_type,a.is_original,a.is_top,
        a.is_index,a.lable,a.source_id,a.num_thumbup,a.num_share,a.num_collect,a.num_read,a.num_read_real,
        a.num_collect_real,a.num_share_real,a.num_thumbup_real,a.release_time,a.update_time,a.top_time,a.sort,
        a.status,a.region_no,a.forid,a.img_url,a.is_propert_news,a.is_wx_material,a.img_path,a.lable_string,ad.head_ico_path';
        $list  = $this->db->name('article')->alias('a')
            ->leftJoin('article_cloumn c','a.id = c.article_id')
//            ->leftJoin('article_tag_bind c','a.id = c.article_id') // todo 前端 重新关联
//            ->leftJoin('article_tag_bind_cloumn tc','tc.tag_id = c.tag_id and tc.type = 1')
            ->leftJoin('admin ad',' a.source_id=ad.id')
            ->field($field);
        $order = [];

        if( !empty( $where['cate_id'] ) ){
             if(is_array($where['cate_id']) ){
                 $list = $list->whereIn('c.column_id',$where['cate_id'] );
             }else{
                 $list = $list->where('c.column_id','=',$where['cate_id'] );
             }

        }
        //去除单个id
        if( !empty($where['not_id']) ){
            $list = $list->where('a.id','<>',$where['not_id']);
        }
        if(!empty($where['is_propert_news'])) {
            $list = $list->where('a.is_propert_news','=',$where['is_propert_news'] );
        }
        if( !empty($where['name']) ) {
            $list = $list->whereLike('a.name',"%{$where['name']}%" );
        }
        if( !empty($where['city_no']) ) {
            $list = $list->whereFindInSet('a.city_list',"{$where['city_no']}" );
        }
        if($where['is_top'] == 1){
            
            $order = ['a.is_top'=>'desc','a.release_time' => 'desc', 'a.sort'=>'desc','a.id'=>'desc'];
//            $where[] = ['a.top_time','>=',time()];
        }else{
            $order = ['a.sort'=>'desc','a.release_time' => 'desc','a.id'=>'desc'];
        }
        if($where['is_index']==1 ){
            $list->where('a.is_index','=',1);
        }
        $list = $list->order($order)
            ->group('a.id')
            ->paginate($where['pageSize'])
            ->toArray();

//        echo $this->db->getLastSql();

        if(empty($list['data'])){
            $result['list'] = [];
        }else{
            $result['total']        = $list['total'];
            $result['last_page']    = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list']         = $list['data'];
        }
        return $result;

    }

    public function getArticleList1($where){
        $field = 'a.id,ad.account,a.title,a.name,a.resource_type,a.keyword,a.order_type,a.is_original,a.is_top,
        a.is_index,a.lable,a.source_id,a.num_thumbup,a.num_share,a.num_collect,a.num_read,a.num_read_real,
        a.num_collect_real,a.num_share_real,a.num_thumbup_real,a.release_time,a.update_time,a.top_time,a.sort,
        a.status,a.region_no,a.forid,a.img_url,a.is_propert_news,a.is_wx_material,a.img_path,a.lable_string,ad.head_ico_path';
        $list  = $this->db->name('article')->alias('a')
            ->leftJoin('article_cloumn c','a.id = c.article_id')
//            ->leftJoin('article_tag_bind c','a.id = c.article_id') // todo 前端 重新关联
//            ->leftJoin('article_tag_bind_cloumn tc','tc.tag_id = c.tag_id and tc.type = 1')
            ->leftJoin('admin ad',' a.source_id=ad.id')
            ->field($field);
        $order = [];

        if( !empty( $where['cate_id'] ) ){
            if(is_array($where['cate_id']) ){
                $list = $list->whereIn('c.column_id',$where['cate_id'] );
            }else{
                $list = $list->where('c.column_id','=',$where['cate_id'] );
            }

        }
        //去除单个id
        if( !empty($where['not_id']) ){
            $list = $list->where('a.id','<>',$where['not_id']);
        }
        if(!empty($where['is_propert_news'])) {
            $list = $list->where('a.is_propert_news','=',$where['is_propert_news'] );
        }
        if( !empty($where['name']) ) {
            $list = $list->whereLike('a.name',"%{$where['name']}%" );
        }
        if( !empty($where['city_no']) ) {
            $list = $list->whereFindInSet('a.city_list',"{$where['city_no']}" );
        }
        if($where['is_top'] == 1){

            $order = ['a.is_top'=>'desc','a.release_time' => 'desc', 'a.sort'=>'desc','a.id'=>'desc'];
//            $where[] = ['a.top_time','>=',time()];
        }else{
            $order = ['a.sort'=>'desc','a.release_time' => 'desc','a.id'=>'desc'];
        }
        if($where['is_index']==1 ){
            $list->where('a.is_index','=',1);
        }
        $list = $list->order($order)
            ->group('a.id')
            ->paginate($where['pageSize'])
            ->toArray();

//        echo $this->db->getLastSql();

        if(empty($list['data'])){
            $result['list'] = [];
        }else{
            $result['total']        = $list['total'];
            $result['last_page']    = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list']         = $list['data'];
        }
        return $result;

    }

    /**
     * 获取热讯排行
     */
    public function getHosList($city_ids){
        $wg   = new WaitGroup();
        $result = [];
        foreach ($city_ids as $k => $v){
            $wg->add();
            go(function () use($v,&$result,$wg){
                $list =  $this->db->name('article')
                    ->whereLike('city_list',"%{$v}%")
                    ->field('id,num_read + num_read_real as num_read,name')
                    ->orderRaw('num_read+num_read_real desc')
                    ->limit(7)
                    ->select() ;
//                echo $this->db->getLastSql();
                $result[$v] = $list->toArray() ?? [];
                $wg->done();
            });

        }

        $wg->wait();

        return $result;
    }

    //批量操作
    public function batchEdit($params){
        try {
            $where = [
                ['id','IN',$params['ids']]
            ];
            switch ($params['type']){
                case 'top': //批量置顶
                    $where[] = ['is_top','=',0];
                    $data = [
                        'is_top' => 1,
                        'top_time' => strtotime($params['top_time'])
                    ];
                    $this->db->name('article')->where($where)->update($data);
                    break;
                case 'closeTop': //取消置顶
                    $where[] = ['is_top','=',1];
                    $data = [
                        'is_top' => 0,
                        'top_time' => 0
                    ];
                    $this->db->name('article')->where($where)->update($data);
                    break;
                case 'delete': //批量删除
                    $this->db->name('article')->where($where)->delete();
                    break;
                case 'display': //显示
                    $data = [
                        'status' => 1
                    ];
                    $this->db->name('article')->where($where)->update($data);
                    break;
                case 'hide': //隐藏
                    $data = [
                        'status' => 0
                    ];
                    $this->db->name('article')->where($where)->update($data);
                    break;
            }

            return $this->responseOk();
        }catch (\Exception $exception){
            return $this->responseFail($exception->getMessage());
        }
    }

    /**
     * @param $status
     * @return array|int
     * 获取指定资讯状态条数
     */
    public function getCountByStatus($data){
        if(!in_array($data['status'],[0,1,2])){
            return 0;
        }
        $count =  $this->db->name('article')->where('status','=',$data['status']);
        if(!empty($data['city_list']) && is_array($data['city_list']) ){
            $where_str ="";
            foreach ($data['city_list'] as $k => $v){

                if(end($data['city_list']) == $v){
                    $where_str.=  " FIND_IN_SET({$v},'city_list')";
                }else{
                    $where_str.= " FIND_IN_SET({$v},'city_list') OR";
                }

            }

            $where[] = ['','exp',Db::Raw($where_str)];
        }
        $count = $count->where($where_str)->count();

        return $count;
    }

    /**
     * 根据条件获取文章列表
     */
    public function getnewsListBywhere($data,$order ='id desc'){
        $list  =  $this->db->name('article')->field('name,num_read+num_read_real as num_read,id,update_time,img_path');
        if(!empty($data['city_list']) && is_array($data['city_list']) ){
            $where_str ="";
            foreach ($data['city_list'] as $k => $v){

                if(end($data['city_list']) == $v){
                    $where_str.=  " FIND_IN_SET({$v},'city_list')";
                }else{
                    $where_str.= " FIND_IN_SET({$v},'city_list') OR";
                }

            }

            $where[] = ['','exp',Db::Raw($where_str)];
        }
        $list = $list->order($order);
        $list = $list->where($where_str)->limit(1,18)->select()->toArray();

        return $list;
    }

    public function getListByIds($ids){
        if(empty($ids)){
            return [];
        }
       return $this->db->name('article')->where('id','in',$ids)->field('name,num_read+num_read_real as num_read ,id')->select()->toArray()  ?? [];

    }

    /**
     * 同步新闻倒旧系统
     * @param $data   新的新闻数据
     * @param $new_id 新的新闻id
     */
    public function addOldNews($data,$new_id,$type){
        $old_data  = $this->newNewstoOldNews($data);
        $data['new_id'] = $new_id;
        //将楼盘id改为旧的楼盘id
        if(!empty($data['forid'])){
            $lod_f_id  = $this->db->name('estates_new')->where('id','=',$data['forid'])->find('old_id');
            $data['property_id'] = $lod_f_id;
        }else{
            $data['property_id'] = 0;
        }

        if($type == 'add'){ //添加
            $data['new_id'] = $new_id;
            Db::connect('old9h')->name('news_copy1')->insert($old_data);
            echo $this->db->getLastSql();
        }else{ //修改
            $info  = $this->db->name('article')->where('id','=',$new_id)->find();
            if(!empty($info['old_id'])){ //旧系统同步过来的新闻 跟新久系统新闻
                Db::connect('old9h')->name('news_copy1')->where('id','=',$info['old_id'])->update($old_data);
            }else{ //新系统新闻查找久系统有没有 有久跟新  没有就插入新闻
                $old_info   =     Db::connect('old9h')->name('news_copy1')->where('new_id','=',$new_id)->whereNotNull('new_id')->find();
                if( !empty($old_info) ){ //久系统有跟新旧系统数据
                    Db::connect('old9h')->name('news_copy1')->where('new_id','=',$old_info['new_id'])->update($old_data);
                }else{ //
                    Db::connect('old9h')->name('news_copy1')->insert($old_data);
                }
            }
        }
    }

}