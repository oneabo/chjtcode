<?php
namespace app\server\admin;

use app\common\traits\TraitInstance;
use app\common\base\ServerBase;
use think\Db;
use think\Exception;

class InformationVideo extends ServerBase
{
    /**
     * 视频修改
     * @param $data
     * @return bool
     */
    public function edit($data){
        $res =   $this->db->name('information_video')->where('id','=',$data['id'])->update($data);
        return  $res===false ? false: true;

    }

    /**
     * 文章新增
     * @param $data
     * @return bool
     */
    public function add($data){
        $res =   $this->db->name('information_video')->insert($data,true);
        return  $res===false ? false: $res;

    }

    /**
     * 获取视频列表
     * @param $search
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getListVoide($search){
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

        if( !empty($search['name']) ){
            $where[]=  ['a.name','like', "%".$search['name']."%"];
        }

        if(!empty($search['region_no'])){
            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$search['region_no']}, city_list)")];
        }

        if(!empty($search['forid'])){
            $where[] = ['','=',$search['forid']]; //单id todo 到时候要做成多选 等前端有空的时候
//            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$search['forid']}, forid)")];
        }

        $order = ['a.sort'=>'desc','a.id'=>'desc'];
        $list = $this->db->name("information_video")->alias('a')
//            ->leftJoin('video_cloumn ac','a.id=ac.article_id') //todo 现在改用标签不用栏目
            ->leftJoin('video_tag ac','a.id=ac.article_id')
            ->where($where)
            ->field('a.*,a.status as status1')
            ->order($order)
            ->group('a.id')
            ->paginate(10);
//        echo $this->db->getLastSql();
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

    public function getNewsInfo($id){
        $info  = $this->db->name('information_video')
            ->where('id',$id)->find();

        return $info;
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
        $res  =  $this->db->name('information_video')
            ->where('id','=',$id)
            ->update([$column_key=>$column_val]);
        return $res ===false ? false:true;
    }

    /**
     * 删除新闻
     * @param $id
     * @param $p_cate_id
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DbException
     */
    public function delVideo($id,$p_cate_id){
        $res  = $this->db->name('article')
            ->where('id',$id)->delete();

        return $res === false ? false:true;
    }

    public function getVoideList($where){
        $field = 'a.id,ad.account,a.title,a.name,a.resource_type,a.keyword,a.order_type,a.is_original,a.is_top,
        a.is_index,a.lable,a.source_id,a.num_thumbup,a.num_share,a.num_collect,a.num_read,a.num_read_real,
        a.num_collect_real,a.num_share_real,a.num_thumbup_real,a.release_time,a.update_time,a.top_time,a.sort,
        a.status,a.region_no,a.forid,a.video_url,a.is_propert_news,a.lable_string,a.video_path,ad.head_ico_path,cover_url';


        $list  = $this->db->name('information_video')->alias('a')
            ->leftJoin('video_cloumn c','a.id = c.article_id')
//            ->leftJoin('video_tag c','a.id = c.article_id') // todo 前端重新连接
//            ->leftJoin('article_tag_bind_cloumn tc','tc.tag_id = c.tag_id and tc.type = 2')
            ->leftJoin('admin ad',' a.source_id=ad.id')
            ->where('a.status','=',1)
            ->field($field);
        $order = [];

        if( !empty( $where['cate_id'] ) ){
            $list = $list->where('c.column_id','=',$where['cate_id'] );
        }
        if(!empty($where['id'])){
            $list = $list->where('a.id','=',$where['id']);
        }

        if(!empty($where['is_propert_news']) || $where['is_propert_news'] === 0) {
            $list = $list->where('a.is_propert_news','=',$where['is_propert_news'] );
        }
        if( !empty($where['name']) ) {
            $list = $list->whereLike('a.name',"%{$where['name']}%" );
        }
        if( !empty($where['city_no']) ) {
            $list = $list->whereFindInSet('a.city_list',"{$where['city_no']}" );
        }
        if(!empty($where['resource_type'])){
            $list = $list->where('a.resource_type','=',$where['resource_type'] );
        }
        if($where['is_top'] == 1){
            $order = ['a.is_top'=>'desc','a.sort'=>'desc','a.id'=>'desc'];
//            $where[] = ['a.top_time','>=',time()];
        }else{
            $order = ['a.sort'=>'desc','a.id'=>'desc'];
        }
        if($where['is_index']  ==1){
            $list->where('a.is_index','=',1);
        }
        if($where['next']){
            $list->where('a.id','>',$where['next']);
        }
        if($where['up'] ) {
            $list->where('a.id','<',$where['up']);
        }
        $list = $list->order($order)->paginate($where['pageSize'])->toArray();

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


}