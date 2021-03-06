<?php
namespace app\miniwechat\controller;

use app\common\base\UserBaseController;
use app\common\MyConst;
use app\common\traits\TraitEstates;
use app\server\admin\Admin;
use app\server\admin\ArticleTag;
use app\server\admin\Banner;
use app\server\admin\City;
use app\server\admin\CityPriceLog;
use app\server\admin\Column;
use app\server\admin\ConsultingComments;
use app\server\admin\InformationVideo;
use app\server\admin\News;
use app\server\estates\Estatesnew;
use app\websocket\BobingStore;
use Swoole\Coroutine\WaitGroup;
use think\Config;
use think\contract\Arrayable;
use think\initializer\BootService;
use function Co\run;

class NewsController extends UserBaseController
{
    use TraitEstates;
    /**
     * 获取资讯栏目
     */
    public function getColumnList(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getColumnList();
        return ;
        $flag        = $this->request->param('group_flag','h5_fx');
        if(!$flag){
            return $this->error('参数不能为空');
        }
        $server      = new Column();
        $pid_arr     = $server->getListByPlace($flag);
        if( !$pid_arr ){
            return $this->error('暂无栏目');
        }

        $pid =  [];
        foreach ($pid_arr as $k => $v) {
            $pid[] = $v['id'];
        }
//       var_dump($pid);return ;
        $list        = $server->getCateListAll($pid,'id,title as name,pid,cover,href');
        if(empty( $list )) {
            return $this->error('暂无栏目');
        }
        $list = getTree($list);

        if(empty($list) ) {
            return $this->error('暂无栏目');
        }

         $this->success($list);

    }


    /**
     * 获取新闻列表
     */
    public function getNewsList(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getNewsList();

//        $data    = $this->request->post();
//        $user_id = $this->getUserId(false);
//        $is_get_small_video  = $data['is_get_small_video'] ?? 0;//是否获取小视频
//        $where = [
//            'pid'               => $data['pid'],
//            'cate_id'           => $data['cate_id'] ,
//            'is_top'            => $data['is_top'] ?? 1,
//            'is_index'          => $data['is_index'] ?? 0,
//            'page'              => $data['page'] ?? 1,
//            'name'              => $data['name'],
//            'pageSize'          => $data['pageSize'] ?? 12,
//            'is_propert_news'   => $data['is_propert_news'] ??0,
//            'order_type'        => $data['order_type'] ?? 2,
//            'date'              => $data['date'],
//            'city_no'           => $data['city_no']
//        ];
////        $page  =$this->request->param('page');
////        var_dump($where);
//
//        if(empty($data['pid']) || empty($data['city_no']) ) {
//            return $this->error('参数不能为空');
//        }
//        $pid            = $data['pid'];
//        $cate_id        = $data['cate_id'];
//        $news_list      = [];
//        switch ( $pid ){
//            case 9:  $news_list     = $this->getArticleList($where); break;//文章
//
//            case 13:
//                $where['resource_type'] = 2;
////                var_dump($where);
//                $news_list     = $this->getVoideList($where); break;  // 视频
//
//            case 18: $news_list     = $this->getEstatesList(); break; // 房源 todo 调用超哥接口
//
//            case 19:
//                $where['pid']      =9;//取文章新房模块文章
//                $where['cate_id']  =12;//取文章新房模块文章
//                $news_list     = $this->getArticleList($where); break ;// 研究院取 新房列表得房源数据
//
//            default : $this->getArticleList($where);
//        }
//        if( empty($news_list) ){
//            return $this->success([],'暂无文章');
//        }
//        //
//        $num         = 6; //间隔几天插入广告
//        $list_count  = count($news_list['list']);
//        $flag        = intval($list_count/$num);
//        $redis_user_key = empty($user_id) ? $this->request->ip() :$user_id; //如果没有登录 用ip 作为标识
//        //不足三条 不插入广告
//        if($flag == 0 ){
//
//        }else{
//            //取对应的广告条数
//            $banner     = new Banner();
//            //取对应条数
//            $redis      = $this->getReids();
//            $key        = MyConst::NEWS_ADV_READING_FLAG;
//            $start      = $redis->hGet($key,$redis_user_key) ?? 0;
//            $adv_lsit   = $banner->getNewsAdvlist($start,$flag,'h5_news_ad');
//            $start      = $start  + $flag;
//            //广告不足重头开始补齐
//            if(count( $adv_lsit) < $flag){
//                $start = 0;
//                $adv_lsit_new   = $banner->getNewsAdvlist($start,$flag-count($adv_lsit),'h5_news_ad');
//
//                $start      += $flag-count($adv_lsit);
//            }
//            $redis->hSet($key,$redis_user_key,$start);
//
////            var_dump($adv_lsit);
//            $adv_lsit  = array_merge($adv_lsit,$adv_lsit_new ?? []);
//            //判断广告类型
//            if(!empty($adv_lsit)){
//                $adv_lsit  = $this->getAdlist($adv_lsit) ;
//            }
//
//
//        }
//        //获取小视频
//        if(!empty($is_get_small_video)){
//            $where['resource_type'] =3 ;
//            $where['pageSize']  = 4;
//            $where['cate_id'] = 13;
//            $small_voide = $this->getVoideList($where)['list'];
//            $samll =[];
////            var_dump($small_voide);
//            foreach ($small_voide as $k => $v){
//                $samll[$k]['title']     =  $v['title'];
//                $samll[$k]['tip']       =  $v['lable'];
//                $samll[$k]['view']      =  $v['readNum'];
//                $samll[$k]['url']       =  $v['url'];
//                $samll[$k]['id']        =  $v['id'];
//                $samll[$k]['img']       =  $v['img'];
//            }
//            $small_list = [
//                'type' => 6,
//                'list' => $samll
//            ];
//            //整合成小视频格式
//
//         }
//        $news_list['small_voide']  = $small_list ??[];
//        $news_list['ad_lsit']      = $adv_lsit ??[];
//        $this->success($news_list);

//        var_dump($adv_lsit);

//        $data   =    $this->insertAdvertisement($news_list['list'],$adv_lsit,$num);
//        var_dump($data);
//        if($data['code'] == 1){
//            $this->error($data['data']);
//        }else{
//
//        }

    }


    private function getVoideList($where){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getVideoInfo();
        return ;
        $model  =  new  InformationVideo();
        $list   =  $model->getVoideList($where);
        $data   =  $list;
//        var_dump($data);
        unset($data['list']);

        if($list['list']){
            foreach ($list['list'] as $k => $v ){
                $data['list'][$k]['id']             = $v['id'];
                $data['list'][$k]['title']          = $v['name'];
                $data['list'][$k]['type']           = $v['resource_type'] == 2 ? 5:6;
                $data['list'][$k]['hot']            = $v['is_top'];
                $data['list'][$k]['write']          =  $v['is_original'] ?? 0; //todo  原创
                $data['list'][$k]['lable']          = json_decode($v['lable_string'],true) ?? [];
                $data['list'][$k]['num_share']      = $v['num_share'] ?? 0+$v['num_share_real'] ?? 0 ;
                $data['list'][$k]['num_collect']    = $v['num_collect'] ??0 +$v['num_collect_real'] ??0;
                $data['list'][$k]['readNum']         = $v['num_read'] ?? 0 +$v['num_read_real']??0;
                $data['list'][$k]['num_thumbup']     = $v['num_thumbup'] ?? 0 +$v['num_thumbup_real'] ??0;
                $data['list'][$k]['region_no']       = $v['region_no'] ;
                $data['list'][$k]['url']             = $v['video_path'];
                $data['list'][$k]['img']             = $v['cover_url'];
                $data['list'][$k]['author']['name']  = $v['account'] ?? '';
                $data['list'][$k]['author']['head']  = $v['head_ico_path'] ?? '';
                $data['list'][$k]['describe']        = $v['title'] ?? ''; //视频描述
                $data['list'][$k]['release_time']    = date('Y-m-d',$v['release_time']);//发布时间
                $data['list'][$k]['commentNum']      = 0;
                $ids[]  =  $v['id'];
            }

            if($ids){
                $comments  =  (new ConsultingComments())->getCountById($ids,$where['pid']);
//            var_dump($comments->toArray());
                if($comments){
                    foreach ($list['list'] as $ks => $vs){
                        foreach ($comments as $key => $val){
                            if($vs['id'] == $val['article_id']){
                                $data['list'][$ks]['commentNum'] = $val['count'];
                                continue;
                            }
                        }
                    }

                }
            }
        }

        return $data;
    }



    /**
     * 分页获取精彩小视频
     */
    public function getSmallvideo(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getSmallvideo();
        return ;
        $data    = $this->request->post();
        $user_id = $this->getUserId(false);
        $data['is_top'] = intval($data['is_top']);
        $data['is_index'] = intval($data['is_index']);
        $data['page'] = intval($data['page']);
        $data['pageSize'] = intval($data['pageSize']);
        $data['is_propert_news'] = intval($data['is_propert_news']);

        if(empty($data['pageSize'])||$data['pageSize']>5){
            $data['pageSize'] = 5;
        }
        $where = [
            'pid'               => 13,
            'cate_id'           => 13,
            'is_top'            => $data['is_top'],
            'is_index'          => $data['is_index'],
            'page'              => $data['page'] ?? 1,
            'pageSize'          => $data['pageSize'],
            'is_propert_news'   => $data['is_propert_news'],
            'city_no'           => $data['city_no'],
            'order_type'        => 3,
        ];

        if(empty($data['city_no'])){
           return $this->error('请先选择城市');
        }
        $list = $this->getVoideList($where);
        $list = $this->getUserIsFool($user_id,$list);
        if(empty($list)){
            return $this->success([]);
        }

        $this->success($list);

    }

    /**
     *
     * 整合文字是否点赞关注
     */
    private function getUserIsFool($user_id,$list){
        $ids  = [];
        if($list['data'] ){
            foreach ($list['data'] as $k => $v){
                $ids[]  =  $v['id'];
            }
        }

        if($user_id){
            $is_follow  = $this->db->name('article_attention')->alias('aa')
                ->where('aa.article_id','in',$ids)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',2)
                ->select();
        }

        if($user_id){
            $is_like  = $this->db->name('article_fabulous_log')->alias('aa')
                ->where('aa.article_id','in',$ids)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',2)
                ->select();
        }

        if($is_follow){
            foreach ($list['list'] as $ks => $vs){
                foreach ($is_follow as $key => $val){
                    if($vs['id'] == $val['article_id']){
                        $list['list'][$ks]['is_follow'] = 1;
                    }else{
                        $list['list'][$ks]['is_follow'] = 0;
                    }
                }
            }

        }else{
            foreach ($list['list'] as $ks => $vs){
                $list['list'][$ks]['is_follow'] = 0;
            }
        }

        if($is_like){
            foreach ($list['list'] as $ks => $vs){
                foreach ($is_like as $key => $val){
                    if($vs['id'] == $val['article_id']){
                        $list['list'][$ks]['is_thump'] = 1;
                    }else{
                        $list['list'][$ks]['is_thump'] = 0;
                    }
                }
            }

        }else{
            foreach ($list['list'] as $ks => $vs){
                $list['list'][$ks]['is_thump'] = 0;
            }
        }
        return $list ;
    }
    /**
     * 获取文章列表
     */
    private  function getArticleList($where =[]){
        $model  =  new  News();
        $list   =  $model->getArticleList($where);
        $data   =  $list;
//        var_dump($data);
        unset($data['list']);
        if($list['list']){
            $ids  = [];
            foreach ($list['list'] as $k => $v ){
                $data['list'][$k]['id']             = $v['id'];
                $data['list'][$k]['title']           = $v['name'];
                $data['list'][$k]['type']            = $v['order_type'] != 0 ? 1:0;
                $data['list'][$k]['hot']             = $v['is_top'];
                $data['list'][$k]['write']           =  $v['is_original']; //todo  原创
                $data['list'][$k]['author']          = $v['author'];
                $data['list'][$k]['tip']             =   json_decode($v['lable_string'],true) ?? [];
                $data['list'][$k]['num_share']       = ($v['num_share'] ?? 0)+($v['num_share_real'] ?? 0) ;
                $data['list'][$k]['num_collect']     = ($v['num_collect'] ??0) +($v['num_collect_real'] ??0);
                $data['list'][$k]['readNum']         = ($v['num_read'] ?? 0) +($v['num_read_real']??0);
                $data['list'][$k]['num_thumbup']     = ($v['num_thumbup'] ?? 0) +($v['num_thumbup_real'] ??0);
                $data['list'][$k]['region_no']       = $v['region_no'] ;
                $data['list'][$k]['img']             = array_column(json_decode($v['img_path'],true),'url');
                $data['list'][$k]['author']['name']  = '九房网';
                $data['list'][$k]['author']['head']  = $v['head_ico_path'] ?? '';
                $data['list'][$k]['commentNum']      = 0;
                $ids[]  =  $v['id'];
            }
        }
//        var_dump($ids);
        if($ids){
            $comments  =  (new ConsultingComments())->getCountById($ids,$where['pid']);
//            var_dump($comments->toArray());
            if($comments){
                foreach ($list['list'] as $ks => $vs){
                    foreach ($comments as $key => $val){
                        if($vs['id'] == $val['article_id']){
                            $data['list'][$ks]['commentNum'] = $val['count'];
                            continue;
                        }
                    }
                }

            }
        }


        return $data;

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
//            //图片广告
//            if($v['is_propert_news'] ==1){
//                if($v['type'] == 0 ){
//                    $arr[$k]['type']    = 4; // 楼盘带图广告
//                }else{
//
//                }
//            }
            if($v['type'] == 0  && $v['is_propert_news'] ==1) {
                $arr[$k]['type']    = 4; // 楼盘带图广告
                $res = $this->db->name("estates_new")->alias('en')
                                ->leftJoin('estates_has_tag eht','en.id = eht.estate_id AND eht.type = 1')
                                ->field('en.logo,en.detail_cover,en.id,en.NAME,en.list_cover,en.price,en.price_total,en.city_str,en.area_str,en.city,en.area,en.business_area_str,en.sale_status,en.built_area,en.house_purpose,en.discount,GROUP_CONCAT( tag_id ) AS feature_tag')
                                ->where('en.id','=',$v['forid'])->find();
//                var_dump($res);
                $sellingPoint = $this->dealSellingPoint($res);

                $arr[$k]['info']['lab']  = $sellingPoint;
                $arr[$k]['info']['estate_id']  = $res['id'];
                $arr[$k]['info']['name']  = $res['NAME'];
                $arr[$k]['info']['price']  = intval($res['price']);
                $arr[$k]['info']['feature_tag']  =$res['feature_tag'];
                $arr[$k]['info']['sale_status']  = $res['sale_status'];
                $arr[$k]['info']['house_purpose']  = $res['house_purpose'];
                $arr[$k]['info']['area']  = $res['built_area'];
                $arr[$k]['info']['site']  = $res['area_str'].$res['business_area_str'];


            }else if ($v['type'] == 0){
                $arr[$k]['type']    = 2; //广告有图
            }else{
                $arr[$k]['type']    = 3; //广告视频
            }
            $arr[$k]['cover'] = !empty($res['logo']) && !empty($res['detail_cover']) ? 1:0;
//            $arr[$k]['type']    = $v['type'] ==0 ? 2 :3;
            $arr[$k]['title']   = $v['title'] ?? '';
            $arr[$k]['href']    = $v['href'];
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
     *  研究院
     */
    public function getInstituteList(){

        $data = $this->request->post();
        $where = [
            'date'              => $data['date'],
            'city'              => $data['city'] ?? 350200
        ];
        $is_get_chart_list      =  $data['is_chart_list'] ?? null;
        $data   = $where;
        if(empty($data['date'])) {
            $data['date'] = date('Y-m',time());
        }
        $date                 = date('Y-m',strtotime($data['date']));
        $date_start_6         = date('Y-m',strtotime($data['date'].' -6 month'));
        $date_start_12        = date('Y-m',strtotime($data['date'].' -12 month'));
        $date                 = strtotime($date);
        $date_start_6         = strtotime($date_start_6);
        $date_start_12        = strtotime($date_start_12);
        $model          = new CityPriceLog();
        $info           = $model->getInfoByMonth($date,$data['city']);
//        var_dump($info);
        $year_vag     = $model->getVagByCity($date,$data['city']);
        $month         = round($info['price'],2);
        $year_vag      = ($month-$year_vag)/$year_vag;
        $year_vag      = round($year_vag,2) *100;
        $list           = [

                        'info' => [
                            'city_no'           => $info['city_no'],
                            'city_no_name'      => $info['city_no_name'],
                            'price'             => round($info['price'],2),
                            'city_price'        => json_decode($info['city_price'],true),
                            'show_time'         => date('m',$info['show_time']) != date('m',$date) ? date('m',$date) :date('m',$info['show_time']),  //月份
                            'recent_opening'    => intval($info['recent_opening']),
                            'on_sale'           => intval($info['on_sale']),
                            'deal'              => intval($info['deal']),
                            'last_month_rate'   => ($info['last_month_rate']) .'%',
                            'last_month_type'   => $info['last_month_rate'] > 0 ? 1:0,
                            'last_year_rate'    => bcmul($year_vag,1,2).'%',
                            'last_year_type'    =>  $year_vag > 0 ? 1:0,
                        ]
        ];

        if(empty($is_get_chart_list)){
            $this->success($list['info']);
        }

        $char_arr_6            = $model->getListByMonth($date,$date_start_6,$data['city']);
        $char_arr_12           = $model->getListByMonth($date,$date_start_12,$data['city']);

        //6个月数据
        $num  =  6;
        $char_6 = [];
        for($i=0;$i<$num;$i++) {
            $time           = date('Y-m',strtotime($data['date']." -$i month" ));
            $key            = strtotime($time);
            $char_6[$i]['data']     = $time ;
            $char_6[$i]['value']    = floatval($char_arr_6[$key]['price']);

        }

        //12个月数据
        $num  =  12;
        $char_12 = [];
        for($i=0;$i<$num;$i++) {
            $time           = date('Y-m',strtotime($data['date']." -$i month" ));
            $key            = strtotime($time);
            $char_12[$i]['data']     = $time ;
            $char_12[$i]['value']    = floatval($char_arr_12[$key]['price']);

        }

        $list['char_6']  = $char_6;
        $list['char_12']  = $char_12;

       $this->success($list);


    }

    /**
     * 获取文章详情
     */
    public function getArticleInfo(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getArticleInfo();
        return ;
        $id         = $this->request->param('id');
        $cate_id    = $this->request->param('cate_id') ?? 13;
        $user_id    = $this->getUserId(false);
        if(empty($id || empty($cate_id)) ) {
            return $this->success('参数错我');
        }

        $model      = new News();
        $info       = $model->getNewsInfo($id);
        if(empty($info) ){
            return $this->error(['code'=>404,'msg'=>'抱歉，未找到相应数据']);
        }
        //todo 以后多端发布分开取，先去Admin
        $author     = $this->db->name('admin')->where('id',$info['source_id'])->find();
        $where      = [
          'cate_id'     =>$cate_id,
          'pageSize'    =>$this->request->get('pageSize') ?? 10,

        ];
        $where['not_id']  = $id;
        $recommend  = $this->getArticleList($where)['list'];

        $banner     = new Banner();
        $flag       = 'h5_news_info_adv';
        $adv        = $banner->getRandNewsAdvInfoByFlag($flag); // 随机取一条
        if(empty($adv)){
            $flag       = 'h5_news_ad';
            $adv        = $banner->getRandNewsAdvInfoByFlag($flag);

        }

        $comment    = $this->db->name('consulting_comments')
                        ->where('article_id','=',$info['id'] )
                        ->where('status','=',1)
                        ->field('pid,id,user_id,user_name as name,user_avatar as head ,content,like_number as lik,update_time as time')
                        ->order('update_time desc')
                        ->select();

        if(isset($comment)){
            $comment   = getTree($comment);
            foreach ($comment as $k => $v) {
                $comment[$k]['num']     = count($v['children']);
                $comment[$k]['reply']     = $comment[$k]['children'] ?? [];
                $comment[$k]['time']    = date('m-d H:i',$v['time']);
            }
        }else{
            $comment = [];
        }
//        $comment = array_splice($comment,0,3);
        //已经登录看是否收藏
        if($user_id){
            $is_follow  = $this->db->name('article_attention')->alias('aa')
                ->where('aa.article_id','=',$id)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',1)
                ->find();
        }
        if($user_id){
            $is_like  = $this->db->name('article_fabulous_log')->alias('aa')
                ->where('aa.article_id','=',$id)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',1)
                ->find();
        }

        $adv        = $this->getAdlist([$adv]);

        $list = [
                'info' =>[
                    'title'         => $info['name'],
                    'isWrite'       => $info['is_original'],
                    'name'          => $author['account'] ??'',
                    'text'          => htmlspecialchars_decode($info['context']),
                    'head'          => $author['head_ico_path'] ?? '',
                    'num_share'     => ($info['num_share'] ?? 0)+$info['num_share_real'] ?? 0,
                    'num_collect'   => ($info['num_collect'] ??0) +$info['num_collect_real'] ??0,
                    'read'          => ($info['num_read'] ?? 0) +$info['num_read_real']??0,
                    'like'          => ($info['num_thumbup'] ?? 0)  +  ($info['num_thumbup_real'] ?? 0),
                    'time'          => date('Y-m-d',$info['release_time']),
                    'id'            => $info['id'],
                    'likeStatus'    => !empty($is_like) ? 1 : 0,
                    'favorite'      => !empty($is_follow) ? 1 : 0,
                    'source_link'   => $info['source_link'] ?? '',
                ],

                'adv' => $adv[0] ?? [],
                'recommend' => $recommend,
                'replyList'   => $comment ??'',
        ];
        $db = $this->db;
        //异步增加阅读次数
        go(function () use($db,$id){
            $db->name('article')->where('id','=',$id)->inc('num_read_real')->update();
        });

        $this->success($list);
    }

    /**
     * 获取长视频详情
     */
    public function getVideoInfo(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getVideoInfo();
        return ;
        $id         = $this->request->post('id');
        $cate_id    = 13;
        $user_id    = $this->getUserId(false);
        $pageSize   = intval($this->request->get('pageSize'));
        $pageSize   = $pageSize??4;
        if(empty($id || empty($cate_id)) ) {
            return $this->error(['code'=>404,'msg'=>'抱歉，未找到相应数据']);
        }

        $model      = new InformationVideo();
        $info       = $model->getNewsInfo($id);
        if(empty($info['id'] )){
            return $this->error(['code'=>404,'msg'=>'抱歉，未找到相应数据']);
        }

        //todo 以后多端发布分开取，先去Admin
        $author     = $this->db->name('admin')->where('id',$info['source_id'])->find();
        $where      = [
            'cate_id'     => $cate_id,
            'pageSize'    => $this->request->get('pageSize') ?? 4,
            'resource_type'  => 2,
        ];

        //相关推荐
        $recommend  = $this->getVoideList($where)['list'];

//        $banner     = new Banner();
//        $flag       = 'h5_news_info_adv';
//        $adv        = $banner->getNewsAdvInfoByFlag($flag);
//        if(empty($adv)){
//            $flag       = 'h5_news_ad';
//            $adv        = $banner->getNewsAdvInfoByFlag($flag);
//
//        }
        //todo  优化方案
        //评论
        $comment    = $this->db->name('consulting_comments')
            ->where('article_id','=',$info['id'] )
            ->where('status','=',1)
            ->where('cate_pid','=',$cate_id)
            ->field('pid,id,user_id,user_name as name,user_avatar as head ,content,like_number as lik,update_time as time')
            ->order('update_time desc')
            ->select();

        if(isset($comment)){
            $comment   = getTree($comment);
            foreach ($comment as $k => $v) {
                $comment[$k]['num']     = count($v['children']);
                $comment[$k]['reply']     = $comment[$k]['children'] ?? [];
                $comment[$k]['time']    = date('m-d H:i',$v['update_time']);
            }
        }else{
            $comment = [];
        }
//        $comment = array_splice($comment,0,3);
        //已经登录看是否收藏
        if($user_id){
            $is_follow  = $this->db->name('article_attention')->alias('aa')
                ->where('aa.article_id','=',$id)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',2)
                ->find();
        }
        if($user_id){
            $is_like  = $this->db->name('article_fabulous_log')->alias('aa')
                ->where('aa.article_id','=',$id)
                ->where('aa.user_id','=',$user_id)
                ->where('aa.type','=',2)
                ->find();
        }

        $list = [
            'info' =>[
                'title'         => $info['name'],
                'isWrite'       => $info['is_original'],
                'name'          => $author['account'] ??'',
                'head'          => $author['head_ico_path'] ?? '',
                'num_share'     => ($info['num_share'] ?? 0)+$info['num_share_real'] ?? 0,
                'num_collect'   => ($info['num_collect'] ??0) +$info['num_collect_real'] ??0,
                'read'          => ($info['num_read'] ?? 0) +$info['num_read_real']??0,
                'like'          => ($info['num_thumbup'] ?? 0)  +  ($info['num_thumbup_real'] ?? 0),
                'time'          => date('Y-m-d',$info['release_time']),
                'id'            => $info['id'],
                'url'           => $info['video_path'],
                'img'           => $info['cover_url'],
                'likeStatus'    => !empty($is_like) ? 1 : 0,
                'favorite'      => !empty($is_follow) ? 1 : 0
            ],
            'recommend' => $recommend,
            'replyList'   => $comment ??'',
        ];

        $db = $this->db;
        //异步增加阅读次数
        go(function () use($db,$id){
            $db->name('information_video')->where('id','=',$id)->inc('num_read_real')->update();
        });

        $this->success($list);
    }
    /**
     * 获取文字视频评论接口
     */
    public function getComment(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getComment();
        return ;
        $page  = $this->request->get('page');
        $pageSize  = $this->request->get('pageSize');
        $pid  = $this->request->get('pid');
        $id         = $this->request->get('id');
        if(!$id){
            return $this->error('参数错误');
        }

        $comment    = $this->db->name('consulting_comments')
            ->where('article_id','=',$id )
            ->where('status','=',1)
            ->where('cate_pid','=',$pid)
            ->field('pid,id,user_id,user_name as name,user_avatar as head ,content,like_number as lik,from_unixtime(update_time,"%Y-%m-%d %H:%i") as time')
            ->order('id desc')
            ->paginate($pageSize)->toArray();
//        echo $this->db->getLastSql();
//        var_dump($comment);
        if(isset($comment)){

            $comment['data']   = getTree($comment['data']);
            foreach ($comment['data'] as $k => $v) {
                $comment['data'][$k]['reply']     = $v['children'] ?? []; //children
                unset($comment['data'][$k]['children']);
//                $comment['data'][$k]['time']    = date('m-d H:i');
            }
        }else{
            $comment = [];
        }
//        var_dump($comment);
        if(empty($comment['data'])){
            $result['list'] = [];
        }else{
            $result['total']        = $comment['total'];
            $result['last_page']    = $comment['last_page'];
            $result['current_page'] = $comment['current_page'];
            $result['list']         = $comment['data'];
        }
//        $result['list'] = $result;
      $this->success($result);
    }

    /**
     * 添加文章关注
     */
    public function addFollow(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->addFollow();
        return ;
        $id         = intval($this->request->post('id'));
        $pid        = intval($this->request->post('pid'));

        $user_id    = $this->getUserId();

        $type   = null;
        $table  = null;
        $filed  = '';
        switch ($pid){
            case '9' : $type = 1;
                    $table='article';
                    $filed = 'a.img_path as img';
                     break;
            case '13' : $type = 2;
                    $table='information_video';
                    $filed = 'a.cover_url as img';
                     break;
            default : $type = null;$table=null;
        }
        if(empty($id) || empty($pid) || empty($type) || empty($user_id)){
            return $this->error('参数错误');
        }
        $info = $this->db->name('article_attention')->alias('aa')
            ->leftJoin($table.' a','a.id = aa.article_id')
            ->leftJoin('admin m','m.id= a.source_id')
            ->field('a.name,m.account,a.num_collect_real,'.$filed)
            ->where('aa.article_id','=',$id)
            ->where('aa.user_id','=',$user_id)
            ->where('aa.type','=',$type)
            ->find();
        $result =false;
        $is_follow =0;
        //已经关注,删除关注列表
        if(!empty($info)) {
            //减少真实收藏数
//            var_dump($info['num_collect_real']);
            if($info['num_collect_real'] > 0 ){

                $dec_res = $this->db->name($table)->where('id','=',$id)->dec('num_collect_real',1)->update();
            }
            if($dec_res){
                $result =  $this->db->name('article_attention')
                    ->where('article_id','=',$id)
                    ->where('user_id','=',$user_id)
                    ->where('type','=',$type)
                    ->delete();
            }


        }else{
            $info = $this->db->name($table)->alias('a')
                ->leftJoin('admin m','m.id= a.source_id')
                ->field('m.head_ico_path,a.name,m.account,'.$filed)
                ->where('a.id','=',$id)
                ->find();
            //增加真实收藏数

            $inc_res = $this->db->name($table)->where('id','=',$id)->inc('num_collect_real',1)->update();
//            echo $this->db->getLastSql();

            $info['img'] = json_decode($info['img'],true);
            $info['img'] = array_column($info['img'],'url')[0] ?? [];

            $arr =[
                'article_id'         =>  $id,
                'user_id'            =>  $user_id,
                'name'               =>  $info['name'],
                'img'                =>  json_encode($info['img']),
                'author_avatar'      =>  $info['head_ico_path'],
                'author_name'        =>  $info['account'] ?? '',
                'create_time'        =>  time(),
                'update_time'        =>  time(),
                'type'               =>  $type

            ];
            if($inc_res){
                $result =  $this->db->name('article_attention')->insert($arr);
            }

            $is_follow =1;
        }
        $arr = [
            'is_follow' => $is_follow
        ];
        return  $result ===false ? $this->error('失败'):$this->success($arr,'成功');

    }

    /**
     * 文章视频点赞日志表
     */
    public function addFabulous(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->addFabulous();
        return ;
        $id         = intval($this->request->post('id'));
        $pid        = intval($this->request->post('pid'));
        $user_id    = $this->getUserId();

        $type   = null;
        $table  = null;
        $filed  = '';
        switch ($pid){
            case '9' : $type = 1;
                $table='article';
                break;
            case '13' : $type = 2;
                $table='information_video';
                break;
            default : $type = null;$table=null;
        }
        if(empty($id) || empty($pid) || empty($type) || empty($user_id)){
            return $this->error('参数错误');
        }
        $info = $this->db->name('article_fabulous_log')->alias('aa')
            ->where('aa.article_id','=',$id)
            ->where('aa.user_id','=',$user_id)
            ->where('aa.type','=',$type)
            ->find();
        $result =false;
        $is_fabulous = 0;
        //已经关注,删除关注列表
        if(!empty($info)) {
            //减少真实收藏数
            $dec_res = $this->db->name($table)->where('id','=',$id)->dec('num_thumbup_real',1)->update();
            if($dec_res){
                $result =  $this->db->name('article_fabulous_log')
                    ->where('article_id','=',$id)
                    ->where('user_id','=',$user_id)
                    ->where('type','=',$type)
                    ->delete();
            }

        }else{


            $inc_res = $this->db->name($table)->where('id','=',$id)->inc('num_thumbup_real',1)->update();
            $arr =[
                'article_id'         =>  $id,
                'user_id'            =>  $user_id,
                'create_time'        =>  time(),
                'update_time'        =>  time(),
                'type'               =>  $type

            ];
            if($inc_res){
                $result =  $this->db->name('article_fabulous_log')->insert($arr);
            }

            $is_fabulous = 1;
        }
        //返回当前状态 没登录时使用
        $arr = [
            'is_fabulous' => $is_fabulous
        ];
        return  $result ===false ? $this->error('失败'):$this->success($arr,'成功');

    }

    //资讯搜索
    public function newsSearch(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->newsSearch();
        return ;
        $pid            = $this->request->post('pid');
        $search_value   = $this->request->post('search_value');
        $city_no        = $this->request->post('city_no');
        if(empty($pid) || empty($search_value)  || empty($city_no)){
            return $this->error('参数错误');
        }
        $where = [
            'name'              => $search_value,
            'is_propert_news'   => $data['is_propert_news'] ?? 0,
            'pageSize'          => $data['pageSize'] ?? 10,
            'page'              => $data['page'],
            'city_no'           => $city_no,
            'pid'               => $pid
        ];
        switch ( $pid ){
            case 9:
//                $where['cate_id'] = [10,11,13];
                $news_list     = $this->getArticleList($where); break;//文章

            case 13:
                $where['cate_id'] = 13;
                $news_list     = $this->getVoideList($where); break;  // 视频

//            case 18:
//                $where['cate_id'] = 18;
//                $news_list     = $this->getEstatesList(); break; // 房源 todo 调用超哥接口

            default : $this->getArticleList($where);
        }

        $this->success($news_list);

    }


    //资讯综合搜索
    public function ColligateSearchSearch(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->ColligateSearchSearch();
        return ;
//        $pid            = $this->request->post('pid');
        $search_value   = $this->request->post('search_value');
        $pid            = $this->request->post('pid') ?? 0;
        $city_no        = $this->request->post('city_no');
        if(empty($search_value) ){
            return $this->error('请选择关键词');
        }

        if(empty($city_no)){
            return $this->error('请先选择城市');
        }
        $wg  = new WaitGroup();

        $where = [
            'name'              => $search_value,
            'is_propert_news'   => $data['is_propert_news'] ?? 0,
            'pageSize'          => $data['pageSize'] ?? 10,
            'page'              => $data['page'],
            'city_no'           => $city_no,
            'pid'               => 9 //todo 后期改动态
        ];
        $result  = [];

        if($pid ==9){
            $wg->add();
            go(function () use(&$result,$where,$wg){
                $result['article'] = $this->getArticleList($where); //文章列表
                $wg->done();
            });


        }elseif ($pid==13){
            $where['resource_type'] =2; //只查小视频
            $wg->add();

            go(function () use(&$result,$where,$wg){
                $result['voide'] = $this->getVoideList($where); //视频列表
//            echo microtime();
                $wg->done();
            });

        }else{
            $wg->add();
            go(function () use(&$result,$where,$wg){
                $result['article'] = $this->getArticleList($where); //文章列表
                $wg->done();
            });

            ///sdfserwerwerwe
//            $wg->add();
//
//            go(function () use(&$result,$where,$wg){
//
//                $result['voide'] = $this->getVoideList($where); //视频列表
//                $wg->done();
//            });
        }

        $wg->wait();
        $result  = array_merge($result['article'] ?? [],$result['voide'] ?? []);
        $this->success($result);

    }


    public function setRank()
    {
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->setRank();
        return ;
        try {
            $redis = $this->getReids();

            $server = new News();

            $newskey    = MyConst::NEWS_HOS_LIST;// 热讯榜

            // 删除原有榜单
            $redis->del($newskey);
            $where      = ['status' => 1];
            $city_ids   =   [];
            $citys  = (new City())->getSiteCitys($where)['result'];

            foreach ($citys as $k => $v){
                $city_ids[]  = $v['id'];
            }
            $list = $server->getHosList($city_ids);

            foreach ($city_ids as $k => $v){
                $redis->hSet($newskey, $v, json_encode($list[$v]));
            }
        } catch(Exception $e) {
            // 记日志
        }
    }

    /**
     * 根据城市获取资讯热榜
     */
    public function getNewsRank(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getNewsRank();
        return ;
        $city_no   = $this->request->post('city_no');
        $newskey  = MyConst::NEWS_HOS_LIST;
        $redis =  $this->getReids();

        $list =  $redis->hGet($newskey,$city_no);

        if(empty($list)){
            $list = [];
        }else{
            $list = json_decode($list,true);
        }

        $this->success($list);

    }

    /**
     * 获取详情
     */
    public function getShoreInfo(){
        $indexNews  = new \app\index\controller\NewsController($this->app);
        $indexNews->getShoreInfo();
        return ;
        $id         = $this->request->param('id');
        $pid         = $this->request->param('pid');
        $cate_id         = $this->request->param('cate_id');
        $user_id    = $this->userId ?? 0;
        if(!$id || !$cate_id || !$pid){
            return $this->error('参数确缺失');

        }
        $db = $this->db;
        $info = $db->name('article')->alias('a')
                    ->leftJoin('admin m','a.source_id=m.id')
                    ->where('a.id','=',$id)
                    ->field('a.*,m.account,m.head_ico_path as head')
                    ->find();

        $qrUrl = 'http://act.999house.com/9house/pages/discover/news_detail.html?id='.$id.'&share_id='.$user_id.'&pid='.$pid.'&cate_id='.$cate_id;

        $info = [
          'title' => $info['name'],
          'content' => $info['description'],
           'qrUrl'  => $qrUrl,
          'info'   =>[
                'name' => $info['account'],
                'head' => $info['head'] ?? '/9house/static/logo.png',
                'nickName' => '资深楼市分析师、调研专家 金牌房地产顾问',
          ]
        ];
        //增加分享数
        go(function ()use($db,$id){
           $db->name('article')
               ->where('id','=',$id)
               ->inc('num_share_real')->update();
        });
        $this->success($info);

    }

    /**
     *
     * 需要是连续得key
     * @param $data 数据
     * @param $adv 广告
     * @param $num 插入长度
     * @return bool
     */
    private function insertAdvertisement($data,$adv,$num){
        if(!is_array($data) || !is_array($adv) ) {
            return  [
                'code' => 0,
                'msg'  => '给定参数不正确',
                'data' => []
            ];
        }
        $data_count = count($data);
        $adv_count  = count($adv);

        $flag       = intval($data_count/$num);
        if($flag < $adv_count){
            return  [
                'code' => 1,
                'msg'  => '给定广告长度数量不够',
                'data' => $data
            ];
        }
        //给定数组小于 插入步长直接返回
        if($flag ==0 ){
            return  [
                'code' => 1,
                'msg'  => '没到查广告长度，直接返回原来数据',
                'data' => $data
            ];
        } else{
            for($i=1;$i <= $flag;$i++){
                array_splice($data,$i*$num,0,$adv[$i-1]);
                var_dump($data); return ;
            }
        }
//        var_dump($data);

        return  [
            'code' => 1,
            'msg'  => '成功',
            'data' => $data
        ];;

    }

}
