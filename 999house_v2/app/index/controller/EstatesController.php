<?php

namespace app\index\controller;

use app\common\base\UserBaseController;
use app\common\lib\wxapi\co\CoWxPool;
use app\common\MyConst;
use app\common\pool\CoPool\CoMysqlSelectPool;
use app\common\traits\TraitEstates;
use app\server\admin\Agent;
use app\server\admin\Banner;
use app\server\admin\Chat;
use app\server\admin\ConsultingComments;
use app\server\admin\News;
use app\server\admin\SearchWord;
use app\server\admin\Video;
use app\server\admin\CitySite;
use app\server\admin\City;
use app\server\estates\SelectLog;
use app\server\estates\Tag;
use app\server\admin\Subway;
use app\server\estates\BuildingPhotos;
use app\server\estates\Estatesnew;
use app\server\estates\EstatesnewBuilding;
use app\server\estates\EstatesnewHouse;
use app\server\estates\EstatesnewNews;
use app\server\estates\EstatesnewPrice;
use app\server\estates\EstatesnewTime;
use app\server\estates\Tag as estatesTag;
use app\server\marketing\Liveroom;
use app\server\marketing\Subject;
use app\server\user\BrowseRecords;
use Exception;
use QL\QueryList;
use think\Container;
use think\facade\Config;
use think\facade\Db;

class EstatesController extends UserBaseController
{
    use TraitEstates;

    /**
     * 楼盘列表
     */
    public function getEstatesList()
    {
        $params = $this->request->param();
        $time = time();

        $data = [];

        /**
         * 是否需要记录搜索日志（买房方案）
         */
        if(!empty($params['is_log']) && !empty($this->userId)) {
            $this->addSelectLog($params);
        }

        $data['join_already'] = [];// 已连接的表

        /**
         * 参数构造
         */

        // 每页记录数
        $data['page_size'] = $params['page_size'] ?? 0;
        if ($data['page_size'] > 20) {
            $this->error('超出限制');
        }

        // 排序
        $data['order'] = [
            'en.sort' => 'desc',
            'en.id'   => 'desc',
        ];

        // 字段
        $data['fields'] = "en.id, en.name, en.list_cover, en.detail_cover, en.logo, en.price, en.price_total, en.city_str, en.city, en.area_str, en.area, en.business_area_str, en.sale_status, en.built_area, en.house_purpose, en.discount, en.lng, en.lat, GROUP_CONCAT(tag_id) as feature_tag, lr.id as live_room_id, lr.start_time, lr.end_time";

        // 联表
        $data['join'][] = ['table' => 'estates_has_tag eht', 'cond' => '(en.id=eht.estate_id and eht.type=1)', 'type' => 'left'];
        $data['join'][] = ['table' => 'live_room lr', 'cond' => "(en.id=lr.forid and lr.end_time>{$time}) and (lr.status=1 or lr.status=2)", 'type' => 'left'];
        $data['join_already'][] = 'estates_has_tag';
        $data['group'][] = 'en.id';

        // 条件/联表/分组
        $this->buildWhere($params, $data);

        $server = new Estatesnew();

        $res = $server->getListByParams($data);
        // echo $this->db->getLastSql();

        if (empty($res['code'])) {
            $this->error($res);
        }
        if(!empty($data['page_size'])) {// 有分页
            $result = $res['result'];
        } else {// 未分页
            $result['list'] = $res['result'];
        }

        /**
         * 数据处理
         */
        if (!empty($result['list'])) {
            foreach ($result['list'] as $k => $v) {
                $result['list'][$k]['price'] = (int)$v['price'];
                $result['list'][$k]['built_area'] = (int)$v['built_area'];
                $result['list'][$k]['price_total'] = (int)$v['price_total'];
                //判断图片是否存在不存在返回默认图片
//                $result['list'][$k]['list_cover']   = empty(urlIs404($v['price_total']))? 'https://pic4.zhimg.com/v2-588b5a5dd202c4ec9c7f258df5df2f79_r.jpg':$v['price_total'];

                // 建筑用途
                $housePurpose = !empty($v['house_purpose']) ? explode(',', $v['house_purpose']) : [];
                $result['list'][$k]['house_purpose'] = array_slice($housePurpose, 0, 1);// 取出第一个
                // 特色标签
                $featureTag = !empty($v['feature_tag']) ? explode(',', $v['feature_tag']) : [];
                $result['list'][$k]['feature_tag'] = array_slice($featureTag, 0, 2);// 取出前两个
                /**
                 * 卖点
                 */
                $sellingPoint = $this->dealSellingPoint($v);
                unset($result['list'][$k]['discount']);
                $result['list'][$k]['selling_point'] = $sellingPoint;
                // 直播
                if(!empty($v['live_room_id'])) {
                    $liveRoomInfo['id'] = $v['live_room_id'];
                    $info = '';
                    if($v['start_time'] > $time) {
                        $startTime = date('Y-m-d H:i:s', $v['start_time']);
                        $info = "直播预告：即将开始，{$startTime}";
                    } elseif($v['end_time'] > $time) {
                        $endTime = date('Y-m-d H:i:s', $v['end_time']);
                        $info = "直播进行中：{$endTime}结束";
                    }
                    $liveRoomInfo['info'] = $info;
                } else {
                    $liveRoomInfo = [];
                }
                $result['list'][$k]['live_room'] = $liveRoomInfo;
                unset($result['list'][$k]['live_room_id'], $result['list'][$k]['start_time'], $result['list'][$k]['end_time']);
            }

            if(!empty($params['no_adv'])) {// 不需要广告
                $result['adv_list'] = [];
            } else {
                // 广告
                $dataBanner = [
                    'num' => 6,
                    'count' => sizeof($result['list']),
                    'place' => 'h5_estates_list',
                    'user_id' => empty($this->userId) ? $this->request->ip():$this->userId,
                ];
                $advRes = (new Banner())->getAdvServer($dataBanner);
                $advLsit = [];
                if(!empty($advRes['code']) && 1 == $advRes['code']) {
                    $advLsit = $advRes['result'];
                }

                $result['adv_list'] = $advLsit;
            }
        }

        $this->success($result);
    }

    /**
     * 列表上方的专题方块
     */
    public function getSubjectBlock()
    {
        $params = $this->request->param();

        if(empty($params['show_on_list'])) {
            $this->error('缺少必要参数');
        }

        $search['show_on_list'] = $params['show_on_list'];
        $res = (new Subject())->getList($search);
        if(empty($res['code']) || 1 != $res['code']) {
            $this->error($res);
        }
        $res = $res['result']['list'] ?? [];
        
        $list = [];
        if(!empty($res)) {
            foreach($res as $v) {
                $list[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'icon' => !empty($v['icon']) ? explode(',', $v['icon']) : [],
                ];
            }
        }
        $this->success($list);
    }

    /**
     * 详情页
     */
    public function getInfo()
    {
        $params = $this->request->param();

        $time = time();
        
        if(empty($params['id'])) {
            $this->error('缺失必要参数');
        }

        $id = $params['id'];
        $from = $param['from'] ?? 'normal';// 点击来源 normal-正常 search-搜索

        $userId = $this->getUserId(false);// 用户ID
        // $userId = 2;// 用户ID

        $server = new Estatesnew();

        // 房源基本信息
        $where[] = ['id', '=', $id];
        $fields = "id, name, price, price_str, house_purpose, house_type, built_area, province_str, city_str, area_str, business_area_str, street_str, address, discount, city, area, sand_table, detail_cover, sales_telephone, logo, sale_status, lng, lat, built_area,share_desc, opening_time";
        $estates = $server->getInfo($where, $fields);
        if(empty($estates['code']) || 1 != $estates['code']) {
            $this->error($estates);
        }
        $estates = $estates['result'];
        if(!empty($estates)) {
            // 建筑用途
            $housePurpose = !empty($estates['house_purpose']) ? explode(',', $estates['house_purpose']) : [];
            $estates['house_purpose'] = $housePurpose/* array_slice($housePurpose, 0, 1) */;// 取出第一个
            // 数据处理
            $estates['address'] = $estates['province_str'] . $estates['city_str'] . $estates['area_str'] . $estates['business_area_str'] . $estates['street_str'] . $estates['address'];
            $estates['sand_table'] = !empty($estates['sand_table']) ? explode(',', $estates['sand_table']) : [];// 沙盘图
            // 标签整合
            $estates['common_tags'] = [
                'estatesnew_sale_status' => [$estates['sale_status']],
                'house_purpose' => $housePurpose,
                'feature_tag' => [],
            ];
            // 优惠
            $discountInfo = json_decode($estates['discount'], TRUE);
            $discount = [];
            if(!empty($discountInfo)) {
                foreach($discountInfo as $v) {
                    $startTime = strtotime($v['start_time']);
                    $endTime = strtotime($v['end_time']);
                    if($startTime < $time && $endTime > $time) {// 在活动时间范围内
                        $discount[] = $v;
                    }
                }
            }
            $estates['discount'] = $discount;

            // 特色标签
            $whereTags = [
                ['estate_id', '=', $id],
            ];
            $fieldsTags = 'tag_id';
            $tags = $this->db->name('estates_has_tag')->where($whereTags)->field($fieldsTags)->select()->toArray();
            if(!empty($tags)) {
                $tags = array_column($tags, 'tag_id');
                $estates['common_tags']['feature_tag'] = $tags;
                $estates['tags'] = array_splice($tags, 0, 1);
            } else {
                $estates['tags'] = [];
            }

            // 报名信息
            $whereSign = [
                ['forid', '=', $id],
                ['start_time', '<=', time()],
                ['end_time', '>=', time()],
            ];
            $orderSign = ['start_time' => 'asc'];
            $fieldsSign = 'id, name, subname, desc, start_time, end_time, join_num, share_title, share_desc, share_img';
            $sign = $this->db->name('signup')->where($whereSign)->field($fieldsSign)->order($orderSign)->find();
            if(!$sign) {
                $sign = [];
            } else {
                $leftTime = $sign['end_time'] - time();
                $leftDay = (int)($leftTime/86400);
                $sign['left_day'] = $leftDay;
                $sign['start_time'] = date('Y-m-d H:i:s', $sign['start_time']);
                $sign['end_time'] = date('Y-m-d H:i:s', $sign['end_time']);
            }
            $estates['sign_up'] = $sign;
            // 是否报名
            $estates['is_sign'] = 0;
            if(!empty($sign['id']) && !empty($userId)) {
                $whereSignLog = [
                    ['signup_id', '=', $sign['id']],
                    ['user_id', '=', $userId],
                ];
                $signLog = $this->db->name('signup_log')->where($whereSignLog)->count();
                if(!empty($signLog)) {
                    $estates['is_sign'] = 1;
                }
            }
            
            // 楼栋/户型数量
            $countBuilding = $this->db->name('estates_new_building')->where([
                ['estate_id', '=', $id],
                ['is_delete', '=', 0],
            ])->count();
            $countHouse = $this->db->name('estates_new_house')->where([
                ['estate_id', '=', $id],
            ])->count();
            $estates['house_num'] = !empty($countHouse) ? $countHouse : 0;
            $estates['building_num'] = !empty($countBuilding) ? $countBuilding : 0;

            // 开盘时间
            $estates['open_time'] = '';
            $paramsOpen = [
                'where' => [
                    ['estate_id', '=', $id],
                ],
                'order' => ['opening_time' => 'asc'],
                'fields' => 'opening_time',
            ];
            $resOpen = (new EstatesnewTime())->getOne($paramsOpen);
            if(!empty($resOpen['code']) && 1 == $resOpen['code']) {
                $resOpen = $resOpen['result'];
                $openTime = !empty($resOpen['opening_time']) ? date('Y-m-d', $resOpen['opening_time']) : '';
                if(!empty($openTime)) {
                    $estates['open_time'] = $openTime;
                } else {
                    $estates['open_time'] = !empty($estates['opening_time']) ? date('Y-m-d', $estates['opening_time']) : '';// 没有楼栋开盘时间则取楼盘开盘时间-旧库同步数据适用
                }
            }
            //均价去除小数点
            $estates['price'] = intval($estates['price']);

            // 获取关联的淘房师
            $estates['agent'] = $this->getHousekeeper($id,$estates);
            /**
             *  $estates['agent'] = [];
             $paramAgent = [
                'where' => [
                    // ['ae.estate_id', '=', $id],
                    ['', 'exp', Db::Raw("ae.estate_id={$id} or (a.is_default=1 and (a.city_no={$estates['city']} or a.area_no={$estates['area']}))")],
                ],
                'join' => [
                    ['table' => 'estates_agent a', 'cond' => "a.id=ae.agent_id", 'type' => 'right'],
                ],
                'fields' => 'a.id, a.name, a.phone, a.head_img',
            ];
            $agent = (new Agent())->getListByRelation($paramAgent);
            // var_dump($this->db->getLastSql());
            if(!empty($agent['code']) && 1 == $agent['code']) {
                if(!empty($agent['result']['list'])) {
                    $estates['agent'] = $agent['result']['list'];
                }
            }
             * **/


            // 是否关注
            $estates['is_attention'] = 0;

            /**
             * 浏览记录
             */
            if(!empty($userId)) {
                // 是否关注
                $whereAttention = [
                    ['user_id', '=', $userId],
                    ['building_id', '=', $id],
                ];
                $countAttention = $this->db->name('estates_new_attention')->where($whereAttention)->count();
                if(!empty($countAttention)) {
                    $estates['is_attention'] = 1;
                }

                //更新浏览记录
                $res = (new BrowseRecords())->updateEstaes($id,$userId,$from,$estates);
            }
            $estates['share_title'] =    $estates['name'].$estates['price_str'] ?? '';
            $estates['share_desc'] =     $estates['share_desc']?? '';
            $estates['share_ico'] =      $estates['detail_cover']?? '';
        } else {
            $estates = [];
        }
        //判断用户是否有楼盘消息 如果有将消息状态设为已读
        if(!empty( $userId)) {
            $is_msg  = (new Chat())->userIsEStatesMsg($userId,$id);
            if($is_msg >0){
                (new Chat())->SetReadByuserIsEStatesMsg($userId,$id);
            }
        }
        $estates['share_title'] =    $estates['name'].$estates['price_str'] ?? '1111';
        $estates['share_desc'] =     $estates['share_desc']?? '1111';
        $estates['share_ico'] =      $estates['detail_cover']?? '1111';
        $this->success($estates);
    }

    /**
     * 价格历史变化趋势
     */
    public function getPriceHistory()
    {
        $params = $this->request->param();

        if(empty($params['id'])) {
            $this->error('资讯列表缺少必要参数');
        }
        $id = $params['id'];
        $time = time();
        $priceStartTime = strtotime(date("Y-m", strtotime("-6 month")));// 时间范围半年

        $priceParam = [
            'where' => [
                ['estate_id', '=', $id],
                ['type', '=', 1],
                ['create_time', '>=', $priceStartTime],
                ['create_time', '<=', $time],
            ],
            'fields' => "id, month_time,new_price as newPrice",
            'order' => ['month_time'=>'asc'],
        ];
        $priceList = (new EstatesnewPrice())->getList($priceParam);
        $priceList = $priceList['result'];
        $result = [];
        if(empty($priceList['code']) && 1 != $priceList['code']) {
            foreach($priceList as $pk => $pv) {
                $result[date('Y-m', $pv['month_time'])]['date'] = date('Y-m', $pv['month_time']);
                $result[date('Y-m', $pv['month_time'])]['value'] = $pv['newPrice'];
                $result[date('Y-m', $pv['month_time'])]['type']  = '本楼盘房价';
            }
        }
        $result = array_values($result);
        $this->success($result);
    }

    /**
     * 楼盘资讯
     */
    public function getEstateNews()
    {
        $params = $this->request->param();

        if(empty($params['id'])) {
            $this->error('资讯列表缺少必要参数');
        }
        $id = $params['id'];

        $data = [
            'where' => [
                ['estate_id', '=', $id],
            ],
            'fields' => 'id, title, describe, content, cover, create_time',
            'order' => [
                'create_time' => 'desc'
            ],
            'page_size' => $params['page_size'] ?? 0,
        ];

        $news = (new EstatesnewNews())->getListByParam($data);


        if(empty($news['code']) || 1 != $news['code']) {
            $this->error($news);
        }

        $news = $news['result'];
        if(!empty($params['page_size'])) {
            $newsList = $news['list'];
        } else {
            $newsList = $news;
        }

        if(!empty($newsList)) {
            foreach($newsList as &$nv) {
                $nv['create_time'] = date('Y-m-d H:i:s', $nv['create_time']);
                // 图片
                $nv['cover'] = !empty($nv['cover']) ? explode(',', $nv['cover']) : [];
                $nv['img_num'] = sizeof($nv['cover']);
            }
        }

        if(!empty($params['page_size'])) {
            $news['list'] = $newsList;
        } else {
            $news = $newsList;
        }

        $this->success($news);
    }

    /**
     * 房屋测评
     */
    public function getEstateArticle()
    {
        $params = $this->request->param();

        if(empty($params['id'])) {
            $this->error('房屋测评缺少必要参数');
        }
        $id = $params['id'];
        $pageSize = $params['page_size'] ?? 0;

        if($params['dotype']=='assess'){//资讯
            $column_id = 12;
            $lable_id = [25,35];

        }
        if($params['dotype']=='news'){
            $column_id = 12;
            $lable_id = [25,35];
            $whereEvaluation[] = ['','exp',Db::Raw(" !FIND_IN_SET({$lable_id[0] } ,a.lable) and !FIND_IN_SET({$lable_id[1]},a.lable )" )];
        }

        $whereEvaluation = [
            ['ac.column_id', '=', $column_id], // 房屋测评类别ID为20
            ['a.forid', '=', $id], // 关联本楼盘
            ['a.status', '=', 1], // 开启
            ['a.is_propert_news', '=', 1], // 是楼盘文章
        ];

        if( $params['dotype']=='assess' ){ //资讯
            $whereEvaluation[] = ['','exp',Db::Raw(" FIND_IN_SET({$lable_id[0] } ,a.lable) or FIND_IN_SET({$lable_id[1]},a.lable )" )];

        }
        if($params['dotype']=='news'){ //测评
            $whereEvaluation[] = ['','exp',Db::Raw(" !FIND_IN_SET({$lable_id[0] } ,a.lable) and !FIND_IN_SET({$lable_id[1]},a.lable )" )];
        }

//        $fieldsEvaluation = 'a.id, a.name, a.title, a.img_url as img_id, a.img_path';
        $fieldsEvaluation = 'a.id,a.title,a.name,a.resource_type,a.keyword,a.order_type,a.is_original,a.is_top,
        a.is_index,a.lable,a.source_id,a.num_thumbup,a.num_share,a.num_collect,a.num_read,a.num_read_real,
        a.num_collect_real,a.num_share_real,a.num_thumbup_real,a.release_time,a.update_time,a.top_time,a.sort,
        a.status,a.region_no,a.forid,a.img_url,a.is_propert_news,a.is_wx_material,a.img_path,a.lable_string';
        $orderEvaluation = ['a.sort' => 'desc', 'a.create_time' => 'desc'];
        $myDB = $this->db->name('article_cloumn')->alias('ac')
                            ->join('article a', 'ac.article_id=a.id')
                            ->where($whereEvaluation)
                            ->field($fieldsEvaluation)
                            ->order($orderEvaluation);
        if($pageSize) {
            $result = array(
                'list'  =>  [],
                'total' =>  0,
                'last_page' =>  0,
                'current_page'  =>  0
            );

            $list = $myDB->paginate($pageSize)->toArray();


            if(empty($list['data'])){
                $result['list'] = [];
            }else{
                $result['total'] = $list['total'];
                $result['last_page'] = $list['last_page'];
                $result['current_page'] = $list['current_page'];
                $result['list'] =$list['data'];
            }
            $articleList = $result['list'];
        } else {
            $result = $myDB->select()->toArray();
//            echo $this->db->getLastSql();
            if(empty($result)) {
                $result = [];
            }
            $articleList = $result;
        }
        /**
        if(!empty($articleList)) {
            foreach($articleList as &$av) {
                // 图片
                $imgUrls = !empty($av['img_path']) ? json_decode($av['img_path'], TRUE) : [];
                $imgUrls = array_column($imgUrls, 'url');
                $av['img_url'] = !empty($imgUrls) ? $imgUrls : [];
                $av['type'] = !empty($imgUrls) ?1:0;
            }
        }
         * **/
        $data = [];
        if(!empty($articleList)){
            foreach ($articleList as $k => $v ){
                $data[$k]['release_time']    = $this->getTimeLabel($v['release_time']) ;
                $data[$k]['id']              = $v['id'];
                $data[$k]['title']           = $v['name'];
                $data[$k]['type']            = $v['order_type'] != 0 ? 1:0;
                $data[$k]['hot']             = $v['is_top'];
                $data[$k]['write']           = $v['is_original']; //todo  原创
                $data[$k]['author']          = $v['author'];
                $data[$k]['tip']             = json_decode($v['lable_string'],true) ?? [];
                $data[$k]['num_share']       = ($v['num_share'] ?? 0)+($v['num_share_real'] ?? 0) ;
                $data[$k]['num_collect']     = ($v['num_collect'] ??0) +($v['num_collect_real'] ??0);
                $data[$k]['readNum']         = ($v['num_read'] ?? 0) +($v['num_read_real']??0);
                $data[$k]['num_thumbup']     = ($v['num_thumbup'] ?? 0) +($v['num_thumbup_real'] ??0);
                $data[$k]['region_no']       = $v['region_no'] ;
                $data[$k]['img']             = array_column(json_decode($v['img_path'],true),'url');
                $data[$k]['author']['name']  =  '九房网';
                $data[$k]['author']['head']  = $v['head_ico_path'] ?? '';
                $data[$k]['commentNum']      = 0;
                $ids[]  =  $v['id'];
            }

            if($ids){
                $comments  =  (new ConsultingComments())->getCountById($ids,9);
//            var_dump($comments->toArray());
                if($comments){
                    foreach ($articleList as $ks => $vs){
                        foreach ($comments as $key => $val){
                            if($vs['id'] == $val['article_id']){
                                $data[$ks]['commentNum'] = $val['count'];
                                continue;
                            }
                        }
                    }

                }
            }
        }


        if($pageSize) {
            $result['list'] = $data;
        } else {
            $result = $data;
        }

        $this->success($result);
    }

    //todo 新版
    public function getEstateArticle1()
    {
        $params = $this->request->param();

        if(empty($params['id'])) {
            $this->error('房屋测评缺少必要参数');
        }
        $id = $params['id'];
        $pageSize = $params['page_size'] ?? 0;

        if($params['dotype']=='assess'){
            $column_id = 12;
            $lable_id = [25,35];

        }
        if($params['dotype']=='news'){ //资讯去除测评
            $column_id = 12;
            $lable_id = [25,35];
            $whereEvaluation[] = ['','exp',Db::Raw(" !FIND_IN_SET({$lable_id[0] } ,a.lable) and !FIND_IN_SET({$lable_id[1]},a.lable )" )];
        }

        $whereEvaluation = [
            ['ac.column_id', '=', $column_id], // 房屋测评类别ID为20
            ['a.forid', '=', $id], // 关联本楼盘
            ['a.status', '=', 1], // 开启
            ['a.is_propert_news', '=', 1], // 是楼盘文章
        ];

        if( $params['dotype']=='assess' ){
            $whereEvaluation[] = ['','exp',Db::Raw(" FIND_IN_SET({$lable_id[0] } ,a.lable) or FIND_IN_SET({$lable_id[1]},a.lable )" )];

        }
        if($params['dotype']=='news'){ //资讯去除测评
            $whereEvaluation[] = ['','exp',Db::Raw(" !FIND_IN_SET({$lable_id[0] } ,a.lable) and !FIND_IN_SET({$lable_id[1]},a.lable )" )];
        }

        $fieldsEvaluation = 'a.id, a.name, a.title, a.img_url as img_id, a.img_path';
        $orderEvaluation = ['a.sort' => 'desc', 'a.create_time' => 'desc'];
        $myDB = $this->db->name('article_cloumn')->alias('ac')
            ->join('article a', 'ac.article_id=a.id')
            ->where($whereEvaluation)
            ->field($fieldsEvaluation)
            ->order($orderEvaluation);
        if($pageSize) {
            $result = array(
                'list'  =>  [],
                'total' =>  0,
                'last_page' =>  0,
                'current_page'  =>  0
            );

            $list = $myDB->paginate($pageSize)->toArray();

            if(empty($list['data'])){
                $result['list'] = [];
            }else{
                $result['total'] = $list['total'];
                $result['last_page'] = $list['last_page'];
                $result['current_page'] = $list['current_page'];
                $result['list'] =$list['data'];
            }
            $articleList = $result['list'];
        } else {
            $result = $myDB->select()->toArray();
//            echo $this->db->getLastSql();
            if(empty($result)) {
                $result = [];
            }
            $articleList = $result;
        }

        if(!empty($articleList)) {
            foreach($articleList as &$av) {
                // 图片
                $imgUrls = !empty($av['img_path']) ? json_decode($av['img_path'], TRUE) : [];
                $imgUrls = array_column($imgUrls, 'url');
                $av['img_url'] = !empty($imgUrls) ? $imgUrls : [];
                $av['type'] = !empty($imgUrls) ?1:0;
            }
        }

        if($pageSize) {
            $result['list'] = $articleList;
        } else {
            $result = $articleList;
        }

        $this->success($result);
    }

    /**
     * 获取图片资源
     * $resource 资源ID或ID集
     * $type 资源类型 img-图片 video-视频
     * $fields 搜索字段
     * $isDeal 是否进行数据处理
     */
    protected function getResources($resource, $type = 'img', $isDeal = 0, $fields = '')
    {
        if(empty($resource)) {
            return FALSE;
        }
        
        switch($type) {
            case 'img':
                $findFields = 'file_id, file_path';
                $whereFields = 'file_id';
                $tableName = 'upload_file';
                break;
            case 'video':
                $findFields = 'id, dir, name';
                $whereFields = 'id';
                $tableName = 'video_simple';
                break;
            default:
                return FALSE;
                break;
        }
        // 搜索字段
        if(empty($fields)) {
            $fields = $findFields;
        }
        // 传入字符串
        if(is_string($resource)) {
            $where = [
                [$whereFields, '=', $resource],
            ];
            $res = $this->db->name($tableName)->where($where)->field($fields)->find();
            if(empty($res)) {
                return [];
            }
            return $res;
        }
        // 传入数组
        if(is_array($resource)) {
            $where = [
                [$whereFields, 'in', $resource],
            ];
            $res = $this->db->name($tableName)->where($where)->field($fields)->select()->toArray();
            if(empty($res)) {
                return [];
            }
            // 数据处理
            if($isDeal) {
                $tmp = [];
                foreach($resource as $sk => $sv) {
                    foreach($res as $item) {
                        if($item[$whereFields] == $sv) {
                            $tmp[$sv] = $item;
                            break;
                        }
                    }
                }
                $res = $tmp;
            }

            return $res;
        }
        return FALSE;
    }

    /**
     * 获取楼盘相关视频
     */
    public function getVideo()
    {
        $params = $this->request->param();
        if(empty($params['estate_id'])) {
            $this->error('缺失必要参数');
        }
        $estateId = $params['estate_id'];

        $video = $this->videoDataById($estateId);

        $this->success($video);
    }

    protected function videoDataById($estateId)
    {
        $whereVideo = [
            ['is_propert_news', '=', 1],
            ['forid', '=', $estateId],
            ['resource_type', '=', 1],
            ['status', '=', 1],
        ];
        $fieldsVideo = "id, title, name, num_read, video_url, cover";
        $orderVideo = ['sort' => 'desc', 'create_time' => 'desc'];
        $video = (new Video())->getSimpleVideo($whereVideo, $fieldsVideo, $orderVideo, 6);
        if(empty($video['code']) || 1 != $video['code']) {
            $this->error($video);
        }
        if(!empty($video['result'])) {
            $video = $video['result'];
        } else {
            $video = [];
        }
        return $video;
    }


    //返回新房通用常量列表
    public function getConst()
    {
        $rs = [
            'house_purpose'            => MyConst::HOUSE_PURPOSE, //楼盘的建筑用途列表
            'buildingphotos_categorys' => MyConst::BUILDINGPHOTOS_CATEGORYS, //楼盘的相册类型列表
            'orientation'              => MyConst::ORIENTATION, //朝向列表
            'rooms'                    => MyConst::ROOMS, //几居室列表
            'estatesnew_sale_status'   => MyConst::ESTATESNEW_SALE_STATUS, //新房销售状态列表
            'feature_tag'              => (new estatesTag())->getTagList(), //特色标签
        ];
        $this->success($rs);
    }

    //某个新房楼盘的相册
    public function getBuildingPhotosList()
    {
        $data = $this->request->param();
        $data['estate_id'] = intval($data['estate_id']);
        if (empty($data['estate_id'])) {
            return $this->success([]);
        }
        $where = [
            'estate_id' => $data['estate_id'],
        ];

        $category = $data['category'];
        if(!empty($category)) {
            if(!in_array($category, [9])) {// 图片
                $where['category_id'] = (int)$data['category'];
                $rs = (new BuildingPhotos())->getList($where)['result'];
                if(!empty($rs['list'])) {
                    $res = $rs['list'];
                } else {
                    $res = [];
                }
            } else {
                switch($category) {
                    // 视频
                    case 9:
                        $res = $this->videoDataById($data['estate_id']);
                        break;
                    default:
                        $this->error('未知类型');
                        break;
                }
            }
        } else {
            $arr = [];//以类别分组
            // 图片
            $rs = (new BuildingPhotos())->getList($where)['result'];
            if (!empty($rs['list'])) {
                $arr = [];//以类别分组
                foreach ($rs['list'] as $v) {
                    $arr[$v['category_id']][] = $v;
                }
                unset($rs);
            }
            // 视频
            $video = $this->videoDataById($data['estate_id']);
            if(!empty($video)) {
                $arr['9'] = $video;
            }
            $res = $arr;
        }

        $this->success($res);
    }

    /**
     * 某个楼盘的户型图列表
     */
    public function getEstatesnewHouseList()
    {
        $data = $this->request->param();
        $is_group = intval($data['is_group']);//是否执行分组

        $data['estate_id'] = intval($data['estate_id']);
        if(empty($data['estate_id'])){
            $this->success([]);
        }

        $rs = (new EstatesnewHouse())->getList([
            'estate_id' => $data['estate_id']
        ], 'enh.*', 100);
        $res = $rs['result'];
        if (empty($res['list'])) {
            $this->success([]);
        } else {
            $arr = [];
            foreach($res['list'] as &$v) {
                $housePurpose = !empty($v['house_purpose']) ? explode(',', $v['house_purpose']) : [];
                $v['house_purpose'] = array_splice($housePurpose, 0, 1)[0] ?? '';
                // list($v['img_ids'], $v['img_url']) = $this->getImgsIdAndUrl($v['img']);
                $price = intval($v['price']);
                $priceTotal = intval($v['price_total']);
                $v['price'] = $priceTotal;// 总价
                $v['price_ave'] = $price;// 均价
                unset($v['price_total']);
                if($is_group==1){//以几居室分组
                    $arr['group'][$v['rooms']][] =  $v;
                }
            }
            unset($v);
            if ($is_group != 1) {
                $arr = $res['list'];
            } else {
                $arr['all'] = $res['list'];//以几居室分组，追加全部组
            }
            unset($res);

            $this->success($arr);
        }
    }

    /**
     * 获取沙盘图页面的楼栋列表和含有的户型
     */
    public function getEstatesnewBuildingList()
    {
        $data = $this->request->param();
        $data['estate_id'] = intval($data['estate_id']);
        if(empty($data['estate_id'])){
            $this->success([]);
        }

        // 获取楼盘信息
        $where = [
            ['id', '=', $data['estate_id']],
            ['id', '=', $data['estate_id']],
        ];
        $fields = 'sand_table';
        $estate = (new Estatesnew())->getInfo($where, $fields);
        if(empty($estate['code']) || 1 != $estate['code']) {
            $this->error($estate);
        }
        $estate = $estate['result'];
        $sandTale = !empty($estate['sand_table']) ? explode(',', $estate['sand_table']) : [];

        //获取楼栋列表id集合
        $res = (new EstatesnewBuilding())->getList([
            'estate_id' => $data['estate_id'],
            'getHouses' => intval($data['getHouses']),//是否取户型
            'getTime' => 1,//是否取开盘时间
        ], 'enb.*, min(ent.opening_time) as open_time');
        if(empty($res['code']) || 1 != $res['code']) {
            $this->error($res);
        }
        $res = $res['result'];

        $result = [
            'banner' => $sandTale,
            'list' => $res['list'],
        ];

        $this->success($result);
    }

    /**
     * 获取更多的楼盘信息
     */
    public function getMoreInfo(){
        $data = $this->request->param();
        $data['estate_id'] = intval($data['estate_id']);
        if(empty($data['estate_id'])){
            $this->success([]);
        }

        $info = (new Estatesnew())->getInfo([
            ['id','=', $data['estate_id']]
        ]);
        if(!empty($info['result'])){
            $info = $info['result'];
            // 区域
            $areaInfo = '';
            if(!empty($info['city_str'])) {
                $areaInfo .= $info['city_str'];
            }
            if(!empty($info['area_str'])) {
                $areaInfo .= $info['area_str'];
            }
            if(!empty($info['business_area_str'])) {
                $areaInfo .= $info['area_sbusiness_area_strtr'];
            }
            if(!empty($info['street_str'])) {
                $areaInfo .= $info['street_str'];
            }
            $info['areaInfo'] = $areaInfo;
            // 获取楼栋信息
            $searchBuilding = ['estate_id' => $data['estate_id']];
            $buildings = (new EstatesnewBuilding())->getList($searchBuilding, 'id, name, delivery_time');
            if(empty($buildings['code']) || 1 != $buildings['code']) {
                $this->error($buildings);
            }
            $buildingArr = [];
            $buildings = $buildings['result']['list'] ?? [];
            if(!empty($buildings)) {
                foreach($buildings as $b) {
                    $buildingArr[$b['id']] = $b['name'];
                }
                // 交房时间
                $deliveryTime = array_column($buildings, 'delivery_time');
                $deliveryTime = min($deliveryTime);
            } else {
                // 没有楼栋时，使用楼盘交房时间，旧库同步过来的数据适用
                // $deliveryTime = !empty($info['delivery_time']) ? date('Y-m-d', $info['delivery_time']) : '';
            }
            //获取开盘时间列表
            $start_opens = (new EstatesnewTime())->getList([
                'estate_id' => $data['estate_id'],
                'order' => ['opening_time'=>'asc']
            ],'estate_id,opening_time,building')['result'];
            // 开盘时间处理
            $openTime = [];
            if(!empty($start_opens['list'])) {
                foreach($start_opens['list'] as $v) {
                    $build = [];
                    $building = !empty($v['building']) ? json_decode($v['building'], TRUE) : [];
                    if(!empty($building)) {
                        foreach($building as $b) {
                            if(!empty($buildingArr[$b['id']])) {
                                $build[] = !empty($b['floor']) ? $buildingArr[$b['id']] . ':' . $b['floor'] : $buildingArr[$b['id']];
                            }
                        }
                    }
                    $openTime[] = [
                        'opening_time' => $v['opening_time'],
                        'building' => $build,
                    ];
                }
                $openTime = array_values($openTime);
            } else {
                // 没有楼栋开盘时间，使用楼盘开盘时间，旧库同步过来的数据适用
                if(!empty($info['opening_time'])) {
                    $openTime = [
                        'opening_time' => $info['opening_time'],
                        'building' => '',
                    ];
                }
            }
            $info['sales_license'] = json_decode($info['sales_license'],true);
            $info['start_opens'] = !empty($openTime) ? $openTime : [];
            $info['delivery_time'] = $deliveryTime ?? '';
            // 建筑用途
            $housePurpose = !empty($info['house_purpose']) ? explode(',', $info['house_purpose']) : [];
            $info['house_purpose'] = $housePurpose/* array_slice($housePurpose, 0, 1) */;// 取出第一个
        }else{
            $info = [];
        }
        $this->success($info);
    }


    /**
     * 定时任务-热搜/人气榜
     */
    public function setRank()
    {
        try {
            $redis = $this->getReids();

            $server = new Estatesnew();

            $keyRead    = MyConst::ESTATES_LIST_POPULAR;// 人气榜
            $keySearch  = MyConst::ESTATES_LIST_SEARCH;// 热搜榜
//            $newskey    = MyConst::NEWS_HOS_LIST;// 热讯榜

            // 删除原有榜单
            $redis->del($keyRead);
            $redis->del($keySearch);

            // 地区人气
            $resAreaRead = $server->getEstatesRank('area', 'read');
            if(!empty($resAreaRead)) {
                foreach($resAreaRead as $v) {
                    $redis->hSet($keyRead, $v['area'], $v['ids']);
                }
            }

            // 地区热搜
            $resAreaSearch = $server->getEstatesRank('area', 'search');
            if(!empty($resAreaSearch)) {
                foreach($resAreaSearch as $v) {
                    $redis->hSet($keySearch, $v['area'], $v['ids']);
                }
            }

            // 城市人气
            $resCityRead = $server->getEstatesRank('city', 'read');
            if(!empty($resCityRead)) {
                foreach($resCityRead as $v) {
                    $redis->hSet($keyRead, $v['city'], $v['ids']);
                }
            }

            // 城市热搜
            $resCitySearch = $server->getEstatesRank('city', 'search');
            if(!empty($resCitySearch)) {
                foreach($resCitySearch as $v) {
                    $redis->hSet($keySearch, $v['city'], $v['ids']);
                }
            }
        } catch(Exception $e) {
            // 记日志
        }
    }

    /**
     * 地图汇总
     */
    public function getMapData()
    {
        $params = $this->request->param();

        if(empty($params['type'])) {
            $this->error('缺少必要参数');
        }

        $data = [];

        switch($params['type']) {
            // 区级汇总
            case 'area':
                if(empty($params['city_no'])) {
                    $this->error('缺少城市范围');
                }
                $tableName = 'site_city_area';
                $group = 'area';
                break;
            // 商圈汇总
            case 'bussiness':
                if(empty($params['city_no']) && empty($params['area_no'])) {
                    $this->error('缺少区域范围');
                }
                $tableName = 'site_city_business_area';
                $group = 'business_area';
                break;
            default:
                $this->error('错误类型');
                break;
        }

        // 构建条件
        $this->buildWhere($params, $data);

        $data['where'][] = [$group, '<>', ''];

        $data['fields'] = "count(en.id) as count, {$group}";
        $data['group'][] = $group;

        $server = new Estatesnew();

        $res = $server->getListByParams($data);

        if (empty($res['code'])) {
            $this->error($res);
        }
        $result = $res['result'];

        if(!empty($result)) {
            $regionIds = array_column($result, $group);
            if(!empty($regionIds)) {
                $whereRegion = [
                    ['id', 'in', $regionIds],
                    ['status', '=', 1],
                ];
                $fieldsRegion = 'id, cname, lng, lat';
                $region = $this->db->name($tableName)->where($whereRegion)->field($fieldsRegion)->select()->toArray();
                foreach($result as &$item) {
                    $item['region_name'] = "";
                    $item['lng'] = "";
                    $item['lat'] = "";
                    if(!empty($region)) {
                        foreach($region as $v) {
                            if($item[$group] == $v['id']) {
                                $item['region_name'] = $v['cname'];
                                $item['lng'] = $v['lng'];
                                $item['lat'] = $v['lat'];
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $result = [];
        }

        $this->success($result);
    }

    /**
     * 地图汇总列表
     */
    public function getMapList()
    {
        $params = $this->request->param();

        $data = [];

        if(empty($params['city_no']) && empty($params['area_no']) && empty($params['business_no']) && empty($params['estate_id'])) {
            $this->error('缺少必要参数');
        }

        $data = [];

        // 联表
        $data['join'][] = ['table' => 'estates_has_tag eht', 'cond' => '(en.id=eht.estate_id and eht.type=1)', 'type' => 'left'];
        $data['join_already'][] = 'estates_has_tag';
        $data['group'][] = 'en.id';

        // 构建条件
        $this->buildWhere($params, $data);

        $data['fields'] = "en.id, en.name, en.list_cover, en.detail_cover, en.logo, en.price, en.price_str, en.city_str as city, en.area_str as area, en.business_area_str as business, en.lng, en.lat, en.house_purpose, en.built_area, en.sale_status, GROUP_CONCAT(tag_id) as feature_tag, en.street_str as street, en.address";

        $addWhere = [
            ['en.lng' , '>=', -180],
            ['en.lng' , '<=', 180],
            ['en.lat' , '>=', -85.051128],
            ['en.lat' , '<=', 85.051128],
        ];
        $data['where'] = array_merge($data['where'], $addWhere);

        $server = new Estatesnew();
        

        $res = $server->getListByParams($data);
        // var_dump($this->db->getLastSql());

        if (empty($res['code'])) {
            $this->error($res);
        }
        $result = $res['result'];

        if(!empty($result)) {
            foreach($result as &$v) {
                //美图给默认图
                $v['detail_cover'] = !empty($v['detail_cover']) ? $v['detail_cover'] :'upload/images/admin/admin/lp_detail.png';
                $v['list_cover']   =  !empty($v['list_cover']) ?  $v['list_cover'] : 'upload/images/admin/admin/lp_list.png';
                // 价格
                // $v['price'] = empty($v['price']) ? !empty($v['price_str']) ? $v['price_str'] : '价格待定' : $v['price'] . '元/平';
                $v['price'] = (int)$v['price'];
                unset($v['price_str']);
                // 经纬度结构调整
                $v['lnglat'] = [
                    'lng' => $v['lng'],
                    'lat' => $v['lat'],
                ];
                unset($v['lng']);
                unset($v['lat']);
                // 建筑用途
                $housePurpose = !empty($v['house_purpose']) ? explode(',', $v['house_purpose']) : [];
                $v['house_purpose'] = array_slice($housePurpose, 0, 1);// 取出第一个
                // 标签
                $v['feature_tag']= !empty($v['feature_tag']) ? explode(',', $v['feature_tag']) : [];
                // 是否有封面和logo
                $v['have_cover'] = 0;
                if(!empty($v['detail_cover']) && !empty($v['logo'])) {
                    $v['have_cover'] = 1;
                }

            }
        }

        $this->success($result);
    }

    /**
     * 地图汇总详情
     */
    public function getSimpleInfo()
    {
        $params = $this->request->param();

        if(empty($params['id'])) {
            $this->error('缺少必要参数');
        }

        $id = $params['id'];

        $where[] = ['id', '=', $id];
        $fields = "id, name, list_cover, detail_cover, logo, price, house_purpose,  built_area, city_str as city, area_str as area, business_area_str as business, lng, lat";

        $server = new Estatesnew();
        $estates = $server->getInfo($where, $fields);
        if(empty($estates['code']) || 1 != $estates['code']) {
            $this->error($estates);
        }
        $result = $estates['result'];

        if(!empty($result)) {
            // 建筑用途
            $housePurpose = !empty($result['house_purpose']) ? explode(',', $result['house_purpose']) : [];
            $result['house_purpose'] = array_slice($housePurpose, 0, 1);// 取出第一个
            // 特色标签
            $whereTag = [
                ['estate_id', '=', $id],
                ['type', '=', 1],
            ];
            $fieldsTag = 'tag_id';
            $tags = $this->db->name('estates_has_tag')->where($whereTag)->field($fieldsTag)->select()->toArray();
            if(!empty($tags)) {
                $featureTag = array_column($tags, 'tag_id');
            }
            $result['feature_tag'] = !empty($featureTag) ? $featureTag : [];
            $result['price'] = intval($result['price']);
            // 是否有封面和logo
            $result['have_cover'] = 0;
            if(!empty($result['detail_cover']) && !empty($result['logo'])) {
                $result['have_cover'] = 1;
            }
        }

        $this->success($result);
    }

    /**
     * 获取热搜词汇
     */
    public function getSearchWords()
    {
        $params = $this->request->param();

        if(empty($params['city_no'])) {
            $this->error('缺少必要参数');
        }

        $where = [
            ['city', '=', $params['city_no']],
            ['status', '=', 1],
            ['purpose_type', '=', 1],
        ];
        $fields = 'name, type, bind_id';
        $res = (new SearchWord())->getList($where, $fields);

        if(empty($res['code']) || 1 != $res['code']) {
            $this->error($res);
        }
        $res = $res['result'];

        $words = ['estates' => [], 'tags' => []];
        if(!empty($res)) {
            foreach($res as $v) {
                switch($v['type']) {
                    case 1:
                        $words['estates'][] = ['name' => $v['name'], 'bind_id' => $v['bind_id']];
                        break;
                    case 2:
                        $words['tags'][] = ['name' => $v['name'], 'bind_id' => $v['bind_id']];
                        break;
                    default:
                        break;
                }
            }
        }

        $this->success($words);
    }

    /**
     * 获取筛选条件列表
     */
    public function getSeletList()
    {
        $params = $this->request->param();

        if(empty($params['city_no'])) {
            $this->error('缺少必要参数');
        }

        $conditionList = [
            [
                'title' => '区域',
                'list' => [
                    [
                        'title' => '城区',
                        'list' => [
                            [
                                'id' => 0,
                                'name' => '不限',
                                'list' => [],
                            ],
                        ],
                    ],
                    [
                        'title' => '地铁',
                        'list' => [
                            [
                                'id' => 0,
                                'name' => '不限',
                                'list' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => '价格',
                'list' => [
                    [
                        'title' => '单价',
                        'list' => [
                            [
                                'id' => '不限',
                                'name' => '不限',
                            ],
                        ],
                    ],
                    [
                        'title' => '总价',
                        'list' => [
                            [
                                'id' => '不限',
                                'name' => '不限',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => '户型',
                'list' => [
                    [
                        'id' => 0,
                        'name' => '不限',
                    ],
                ],
            ],
            [
                'title' => '更多',
                'list' => [
                    [
                        'title' => '面积',
                        'type' => 'built_area',
                        'list' => [],
                    ],
                    [
                        'title' => '类型',
                        'type' => 'house_purpose',
                        'list' => [],
                    ],
                    [
                        'title' => '特色',
                        'type' => 'tags',
                        'list' => [],
                    ],
                ],
            ],
        ];

        // 取缓存
        $redis = $this->getReids();
        $key = MyConst::ESTATES_CONDITION . $params['city_no'];
        $redisRes = $redis->get($key);
        $redisRes = !empty($redisRes) ? json_decode($redisRes, TRUE) : [];
        if(!empty($redisRes)) {
            $conditionList = $redisRes;
        } else {
            $isSearchDB = TRUE;// 是否搜索数据库
        }
        // $isSearchDB = TRUE;
        if(!empty($isSearchDB)) {
            // 获取公共配置部分
            $setting = (new CitySite())->setInfo([
                'key' => ['in', ['subway', 'average_area', 'average_price', 'total_price']],
                'region_no' => $params['city_no']
            ])['result'];
            if(!empty($setting['list'])) {
                $newSet = [];
                foreach ($setting['list'] as $value) {
                    $newSet[$value['key']] = $value['val'];
                }
            }

            /**
             * 区域
             */
            // 地区
            $area = (new City())->getSiteAreas([
                'pid' => intval($params['city_no']),
                'status' => 1,
            ])['result'];
            $areaIds = !empty($area) ? array_column($area, 'id') : [];
            if(!empty($areaIds)) {
                $business =  (new City())->getSiteBusinessAreas([
                    'area_no' => $areaIds,
                    'status' => 1,
                ])['result'];
            }
            // 处理结构
            if(!empty($area)) {
                $areaList = [];
                foreach ($area as $ak => $av) {
                    $areaList[$ak] = [
                        'id' => $av['id'],
                        'name' => $av['cname'],
                        'list' => [
                            ['id' => 0, 'name' => '不限'],
                        ],
                    ];
                    if(!empty($business)) {
                        foreach ($business as $bv) {
                            if($av['id'] == $bv['area_no']) {
                                $areaList[$ak]['list'][] = [
                                    'id' => $bv['id'],
                                    'name' => $bv['cname'],
                                ];
                            }
                        }
                    }
                }
                $conditionList['0']['list']['0']['list'] = array_merge($conditionList['0']['list']['0']['list'], $areaList);
            }
            // 地铁
            if(!empty($newSet['subway'])) {
                $sites = (new Subway)->getSubwayList([
                    'region_no' => $params['city_no'],
                    'status' => 1,
                ])['result'];
                $subwayList = [];
                foreach ($newSet['subway'] as $sk => $sv) {
                    $subwayList[$sk] = [
                        'id' => $sv,
                        'name' => "{$sv}号线",
                        'list' => [
                            ['id' => 0, 'name' => '不限'],
                        ],
                    ];
                    if(!empty($sites)) {
                        foreach ($sites as $siteItem) {
                            $subwayArr = !empty($siteItem['subway']) ? explode(',', $siteItem['subway']) : [];
                            if(in_array((int)$sv, $subwayArr)) {
                                $subwayList[$sk]['list'][] = [
                                    'id' => $siteItem['id'],
                                    'name' => $siteItem['name'],
                                ];
                            }
                        }
                    }
                }
                $conditionList['0']['list']['1']['list'] = array_merge($conditionList['0']['list']['1']['list'], $subwayList);
            }

            /**
             * 价格
             */
            if(!empty($newSet['average_price'])) {// 均价
                $unit = "元/㎡";
                $avePriceList = [];
                sort($newSet['average_price']);// 升序排序
                foreach ($newSet['average_price'] as $apk => $apv) {
                    if(0 == $apk) {
                        $avePriceList[] = [
                            'id' => "{$apv}{$unit}以下",
                            'name' => "{$apv}{$unit}以下",
                        ];
                    }
                    if(isset($newSet['average_price'][$apk+1])) {
                        $nextPrice = $newSet['average_price'][$apk+1];
                        $avePriceList[] = [
                            'id' => "{$apv}-{$nextPrice}{$unit}",
                            'name' => "{$apv}-{$nextPrice}{$unit}",
                        ];
                    } else {
                        $avePriceList[] = [
                            'id' => "{$apv}{$unit}以上",
                            'name' => "{$apv}{$unit}以上",
                        ];
                    }
                }
                $conditionList['1']['list']['0']['list'] = array_merge($conditionList['1']['list']['0']['list'], $avePriceList);
            }
            if(!empty($newSet['total_price'])) {// 总价
                $unit = "万元";
                $totalPriceList = [];
                sort($newSet['total_price']);// 升序排序
                foreach ($newSet['total_price'] as $tpk => $tpv) {
                    if(0 == $tpk) {
                        $totalPriceList[] = [
                            'id' => "{$tpv}{$unit}以下",
                            'name' => "{$tpv}{$unit}以下",
                        ];
                    }
                    if(isset($newSet['total_price'][$tpk+1])) {
                        $nextPrice = $newSet['total_price'][$tpk+1];
                        $totalPriceList[] = [
                            'id' => "{$tpv}-{$nextPrice}{$unit}",
                            'name' => "{$tpv}-{$nextPrice}{$unit}",
                        ];
                    } else {
                        $totalPriceList[] = [
                            'id' => "{$tpv}{$unit}以上",
                            'name' => "{$tpv}{$unit}以上",
                        ];
                    }
                }
                $conditionList['1']['list']['1']['list'] = array_merge($conditionList['1']['list']['1']['list'], $totalPriceList);
            }

            /**
             * 户型
             */
            $rooms = MyConst::ROOMS;
            if(!empty($rooms)) {
                $roomList = [];
                foreach ($rooms as $rk => $rv) {
                    $roomList[] = [
                        'id' => $rk,
                        'name' => $rv,
                    ];
                }
                $conditionList['2']['list'] = array_merge($conditionList['2']['list'], $roomList);
            }

            /**
             * 更多
             */
            // 面积
            if(!empty($newSet['average_area'])) {
                $unit = "㎡";
                $aveAreaList = [];
                sort($newSet['average_area']);// 升序排序
                foreach ($newSet['average_area'] as $aak => $aav) {
                    if(0 == $aak) {
                        $aveAreaList[] = [
                            'id' => "{$aav}{$unit}以下",
                            'name' => "{$aav}{$unit}以下",
                        ];
                    }
                    if(isset($newSet['average_area'][$aak+1])) {
                        $nextArea = $newSet['average_area'][$aak+1];
                        $aveAreaList[] = [
                            'id' => "{$aav}-{$nextArea}{$unit}",
                            'name' => "{$aav}-{$nextArea}{$unit}",
                        ];
                    } else {
                        $aveAreaList[] = [
                            'id' => "{$aav}{$unit}以上",
                            'name' => "{$aav}{$unit}以上",
                        ];
                    }
                }
                $conditionList['3']['list']['0']['list'] = array_merge($conditionList['3']['list']['0']['list'], $aveAreaList);
            }
            // 类型
            $purpose = MyConst::HOUSE_PURPOSE;
            if(!empty($purpose)) {
                $purposeList = [];
                foreach ($purpose as $pk => $pv) {
                    $purposeList[] = [
                        'id' => $pk,
                        'name' => $pv,
                    ];
                }
                $conditionList['3']['list']['1']['list'] = array_merge($conditionList['3']['list']['1']['list'], $purposeList);
            }
            // 特色
            $tags = (new Tag())->getList([
                'status' => 1,
                'type' => 1
            ])['result']['list'] ?? [];
            if(!empty($tags)) {
                $tagsList = [];
                foreach ($tags as $tk => $tv) {
                    $tagsList[] = [
                        'id' => $tv['id'],
                        'name' => $tv['name'],
                    ];
                }
                $conditionList['3']['list']['2']['list'] = array_merge($conditionList['3']['list']['2']['list'], $tagsList);
            }

            $redis->setex($key, 3*60*60, json_encode($conditionList, JSON_UNESCAPED_UNICODE));
        }

        $this->success($conditionList);
    }


    /**
     * 猜你喜欢列表
     */
    public function getGuessList()
    {
        $params = $this->request->param();

        $time = time();
        
        $estateId = $params['estate_id'] ?? 0;

        if(!empty($params['subject_id'])) {
            // 有传入活动ID时，直接取活动相关楼盘即可
            $subjectId = $params['subject_id'];
            $serachSubject = [
                'status' => 1,
                'id' => $subjectId,
            ];
            $subject = (new Subject())->getInfo($serachSubject);
            $subject = $subject['result'];

            if(!empty($subject)) {
                // 时间判断 为0时不做判断
                if(!empty($subject['start_time']) && $subject['start_time'] > $time) {
                    $this->success([]);
                }
                if(!empty($subject['end_time']) && $subject['end_time'] < $time) {
                    $this->success([]);
                }

                $estateIds = !empty($subject['config']['estates_new']) ? array_column($subject['config']['estates_new'], 'forid') : [];
                if(!empty($estateIds)) {
                    $paramsEstates = ['estate_id' => $estateIds];
                } else {
                    $this->success([]);
                }
            } else {
                $this->success([]);
            }
        } else {
            if(empty($params['city_no'])) {
                $this->error('缺少必要参数');
            }

            $paramsEstates = ['city_no' => $params['city_no']];

            $userId = $this->userId;

            // 浏览记录获取
            $whereRecord = [
                ['user_id', '=', $userId],
            ];
            $fieldsRecord = 'building_id';
            $record = (new BrowseRecords())->getSimpleList($whereRecord, $fieldsRecord);
            if(empty($record['code']) || 1 != $record['code']) {
                $this->error($record);
            }
            $estateIds = !empty($record['result']) ? array_column($record['result'], 'building_id') : [];

            // $paramsEstates = ['city_no' => $params['city_no']];

            // 通过楼盘归纳标签
            if(!empty($estateIds)) {
                $whereTags = [
                    ['type', '=', 1],
                    ['estate_id', 'in', $estateIds],
                ];
                $fieldsTags = 'tag_id, count(*) as count';
                $hasTags = $this->db->name('estates_has_tag')->where($whereTags)->field($fieldsTags)->group('tag_id')->order(['count' => 'desc'])->select()->toArray();
                if(!empty($hasTags)) {
                    $tagIds = array_column($hasTags, 'tag_id');
                    $tagIds = array_splice($tagIds, 2);// 取用前两个标签
                }
                if(!empty($tagIds)) {
                    $paramsEstates['tags'] = $tagIds;
                }
            } else {
                // 没有浏览记录拿推荐
                $paramsEstates['recommend'] = 1;
            }
        }

        $data = [
            'page_size' => 6,// 只取六条
        ];
        // 排序
        $data['order'] = [
            'en.sort' => 'desc',
            'en.id'   => 'desc',
        ];
        // 字段
        $data['fields'] = "en.id, en.name, en.list_cover, en.price, en.price_total, en.city_str, en.area_str, en.business_area_str, en.sale_status, en.built_area, en.house_purpose, en.discount, GROUP_CONCAT(tag_id) as feature_tag";
        // 联表
        $data['join'][] = ['table' => 'estates_has_tag eht', 'cond' => '(en.id=eht.estate_id and eht.type=1)', 'type' => 'left'];
        $data['join_already'][] = 'estates_has_tag';
        $data['group'][] = 'en.id';
        $this->buildWhere($paramsEstates, $data);
        $res = (new Estatesnew())->getListByParams($data);
        if(empty($res['code']) || 1 != $res['code']) {
            $this->error($res);
        }
        $res = $res['result']['list'] ?? [];
        if(!empty($res)) {
            foreach ($res as $k => &$v) {
                // 如果有当前楼盘，去掉当前楼盘
                if($v['id'] == $estateId) {
                    unset($res[$k]);
                }
                // 建筑用途
                $housePurpose = !empty($v['house_purpose']) ? explode(',', $v['house_purpose']) : [];
                $v['house_purpose'] = array_slice($housePurpose, 0, 1)[0] ?? 1;// 取出第一个
                // 特色标签
                $featureTag = !empty($v['feature_tag']) ? explode(',', $v['feature_tag']) : [];
                $v['feature_tag'] = array_slice($featureTag, 0, 2);// 取出前两个
                /**
                 * 卖点
                 */
                $sellingPoint = $this->dealSellingPoint($v);
                unset($v['discount']);
                $v['selling_point'] = $sellingPoint;
                $v['price'] = intval($v['price']);
            }
        }
        $res = array_values($res);

        $this->success($res);
    }

    /**
     * 地铁
     */
    public function getSubway()
    {
        $params = $this->request->param();

        if(empty($params['type'])) {
            $this->error('缺少必要参数');
        }

        $type = $params['type'];

        switch ($type) {
            // 地铁线
            case 'subway':
                if(empty($params['city_no'])) {
                    $this->error('请选择城市');
                }
                $subway = $this->db->name('site_city_set')->where(['region_no' => $params['city_no'], 'key' => 'subway'])->value('val');
                $res = [];
                if(!empty($subway)) {
                    $subway = json_decode($subway, TRUE);
                    if(!empty($subway)) {
                        foreach ($subway as $sk => $sv) {
                            $res[] = [
                                'id' => $sv,
                                'name' => "{$sv}号线",
                            ];
                        }
                    }
                }
                break;
            // 地铁站点
            case 'site':
                if(empty($params['city_no']) || empty($params['subway'])) {
                    $this->error('缺少必要参数');
                }
                $whereSites = [
                    ['region_no', '=', $params['city_no']],
                    ['status', '=', 1],
                    ['', 'exp', Db::raw("FIND_IN_SET({$params['subway']}, subway)")],
                ];
                $sites = $this->db->name('subway')->where($whereSites)->field('id, name')->select()->toArray();
                $res = [];
                if(!empty($sites)) {
                    $res = $sites;
                }
                break;
            default:
                $this->error('错误类型');
                break;
        }

        $this->success($res);
    }

    /**
     * 抓取利率
     */
    public function getInterestRate()
    {
        // 宁波银行官网网页
        $urlRate = 'http://www.nbcb.com.cn/shortcut/lending_rates/';
        $urlLPR = 'https://mybank.nbcb.com.cn/doorbank/queryRate.do';
        
        $arr1 = []; $arr2 = []; 
        $res = QueryList::get($urlRate)->find('tbody:eq(1)>.bg2')->map(function($item) {
            if(!empty($item->find('.bg01')->text())){
                
            }
            $arr2[] = $item;
            return $item->texts()->all();
        }); 
    }

    /**
     * 从数据库获取利率
     */
    public function getInterestRateList()
    {
        // 数据直接分组好排序
        // $res = Db::query("SELECT a.* FROM 9h_interest_rate a RIGHT JOIN (SELECT type, max(release_time) as max_release_time FROM 9h_interest_rate WHERE status=1 GROUP BY type) b ON a.type=b.type AND a.release_time=b.max_release_time GROUP BY type");

        // 缓存
        $key = MyConst::ESTATES_INTEREST_RATE;
        $redis = $this->getReids();
        $result = $redis->get($key);
        if(!empty($result)) {
            $result = json_decode($result, TRUE);
            if(!empty($result)) {
                $this->success($result);
            }
        }

        // 获取整体数据后进行分组排序
        $result = [
            'fund' => [],
            'business' => [],
            'businessLPR' => [],
            'titleLPR' => "",
        ];
        $res = $this->db->name('interest_rate')->where(['status' => 1])->select()->toArray();
        $fund = [];
        $business = [];
        $businessLPR = [];
        if(!empty($res)) {
            $releaseTime = array_column($res, 'release_time');
            array_multisort($releaseTime, SORT_DESC, $res);// 根据发布时间倒序，分组后，第一条就是最新的
            foreach($res as $v) {
                switch($v['type']) {
                    // LPR
                    case 1:
                        $fund[] = $v;
                        break;
                    // 旧版基准利率
                    case 2:
                        $business[] = $v;
                        break;
                    // 公积金基准利率
                    case 3:
                        $businessLPR[] = $v;
                        break;
                }
            }
            // LPR数据处理
            if(!empty($fund['0'])) {
                $result['businessLPR'] = $this->dealRate($fund['0']);
                // 历史处理
                $str = '';
                foreach($fund as $k => $f) {
                    $history = [];
                    $LPR = !empty($f['content']) ? json_decode($f['content'], TRUE) : [];
                    if(!empty($LPR)) {
                        foreach($LPR as $v) {
                            if(!empty($v['year']) && !empty($v['rate'])) {
                                if(0 == $k) {// 首条
                                    if(!empty($str)) {
                                        $str .= ',';
                                    }
                                    $str .= "{$v['year']}年期{$v['rate']}%";
                                }
                                $history[$v['year']] = $v['rate'];
                            }
                        }
                        $history['time'] = !empty($f['release_time']) ? date('Y/m/d', $f['release_time']) : '';
                    }
                    $result['historyLPR'][] = $history;
                    $result['historyLPR'] = array_reverse($result['historyLPR']);
                }
                $result['titleLPR'] = $str;
            }
            // 旧版基准处理
            if(!empty($business['0'])) {
                $result['business'] = $this->dealRate($business['0']);
            }
            // 公积金基准处理
            if(!empty($businessLPR['0'])) {
                $result['fund'] = $this->dealRate($businessLPR['0']);
            }
            $expireTime = 60;
            $redis->setex($key, $expireTime, json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        $this->success($result);
    }

    /**
     * 利率数据处理
     */
    protected function dealRate($rate)
    {
        $data = [];
        $content = !empty($rate['content']) ? json_decode($rate['content'], TRUE) : [];
        $basicPoint = !empty($rate['basic_point']) ? json_decode($rate['basic_point'], TRUE) : [];
        if(!empty($content)) {
            $prevYear = -1;// 上一条年限
            $count = sizeof($content);
            foreach($content as $k => $c) {
                $rateNum = (float)$c['rate'];
                if(1 == $rate['type']) {
                    if($count == $k+1) {// 最后一条，结束年限为30
                        $year = 30;
                    } else {
                        $year = $c['year'];
                    }
                    $time = [
                        $prevYear+1,
                        (int)$year,
                    ];
                    $prevYear = $year;
                } else {
                    $time = [
                        $c['start'] ?? 0,
                        $c['end'] ?? 0,
                    ];
                }
                $basicStr = $this->dealRateStr($rate['type'], $rateNum);
                $val = [$basicStr];
                if(!empty($basicPoint)) {
                    $basicSort = array_column($basicPoint, 'rate');
                    array_multisort($basicSort, SORT_ASC, $basicPoint);
                    foreach($basicPoint as $b) {
                        $val[] = $this->dealRateStr($rate['type'], $rateNum, (float)$b['rate']);
                    }
                }
                $data[] = [
                    'val' => $val,
                    'time' => $time,
                    'basic' => $basicStr,
                ];
            }
        }
        return $data;
    }

    /**
     * 处理利率文字
     */
    protected function dealRateStr($type=1, $rate=0, $point=0)
    {
        $fund = "%s(LPR+%u基点)";
        $business = "最新基准利率%s(%s)";
        $businessLPR = "旧版基准利率%s(%s)";
        $string = '';
        if(1 == $type) {
            $rateRes = bcadd($rate, bcdiv($point, 100, 2), 2);
            $rateRes .= '%';
            $string = sprintf($fund, $rateRes, $point);
        } else {
            $addStr = "";
            if(0 != $point) {
                if($point > 0) {
                    $addStr = "上浮{$point}%";
                } else {
                    // 打折转换
                    $p = bcadd(100, $point, 2);
                    $p = bcdiv($p, 10, 2);
                    $addStr = "{$p}折";
                }
            }
            $rateRes = bcmul($rate, 1+bcdiv($point, 100, 2), 2);
            $rateRes .= '%';
            if(2 == $type) {
                $string = sprintf($businessLPR, $addStr, $rateRes);
            } else {
                $string = sprintf($business, $addStr, $rateRes);
            }
        }
        return $string;
    }

    /**
     * 记录搜索日志
     */
    protected function addSelectLog($params)
    {
        $userId = $this->userId;
        if(empty($userId)) {
            return ;
        }

        $insertData = [
            'user_id' => $userId,
            'purpose' => $params['buy_purpose'] ?? '',
            'has_num' => $params['has_num'] ?? '',
            'price' => $params['price'] ?? '',
            'city' => !empty($params['city_no']) ? $params['city_no'] : '',
            'area' => !empty($params['area_no']) ? is_array($params['area_no']) ? implode(',', $params['area_no']) : $params['area_no'] : '',
            'business_area' => !empty($params['business_no']) ? is_array($params['business_no']) ? implode(',', $params['business_no']) : $params['business_no'] : '',
            'subway' => $params['subway'] ?? '',
            'subway_sites' => !empty($params['sites']) ? is_array($params['sites']) ? implode(',', $params['sites']) : $params['sites'] : '',
            'rooms' => $params['rooms'] ?? '',
            'built_area' => $params['built_area'] ?? '',
            'feature_tag' => !empty($params['tags']) ? implode(',', $params['tags']) : '',
            'other_requirements' => !empty($params['other_requirements']) ?? '',
        ];

        $res = (new SelectLog())->add($insertData);

        // $obj = Container::getInstance()->make(CoWxPool::class);
        // $result = $obj->addTask([
        //     [
        //         'key'=> 'result',
        //         'data'=>'',
        //         'callFun' => function() use ($insertData) {
        //             (new SelectLog())->add($insertData);
        //         }
        //     ]
        //  ]);

    }


    public function shareEstatesInfo(){
        //getEstatesnewHouseList- 户型

        $time = time();
        $params = $this->request->param();
        $id = intval($params['id']);

        if(empty($params['id'])) {
            $this->error('缺失必要参数');
        }

        $where[] = ['id', '=', $id];
        $fields = "id, name, price,discount, built_area,house_type,house_purpose,sale_status,list_cover,city,area";
        $estates = (new Estatesnew())->getInfo($where, $fields);
        if(empty($estates['code']) || 1 != $estates['code']) {
            $this->error($estates);
        }

        $estates = $estates['result'];

        // 标签整合
        $housePurpose = !empty($estates['house_purpose']) ? explode(',', $estates['house_purpose']) : [];
        $estates['common_tags'] = [
            'estatesnew_sale_status' => [$estates['sale_status']],
            'house_purpose' => $housePurpose,
            'feature_tag' => [],
        ];
        // 优惠

        $discount = $this->dealSellingPoint($estates);

        $estates['discount'] = empty($discount) ? [] : $discount ;


        // 特色标签
        $whereTags = [
            ['estate_id', '=', $id],
        ];
        $fieldsTags = 'tag_id';
        $tags = $this->db->name('estates_has_tag')->where($whereTags)->field($fieldsTags)->select()->toArray();
        if(!empty($tags)) {
            $tags = array_column($tags, 'tag_id');
            $estates['common_tags']['feature_tag'] = $tags;
            $estates['tags'] = array_splice($tags, 0, 1);
        } else {
            $estates['tags'] = [];
        }

        $arr = $this->getUnitType($params);

        $priceArray = array_column($arr,'price');
        $builtAreaArray = array_column($arr,'built_area');
        $roomsArray = array_column($arr,'rooms');

        if(empty(intval($estates['price']))){

            if(empty($priceArray)){
                $estates['price'] = '';
            }else{
                $estates['price'] = min($priceArray);
            }
        }

        if(empty($estates['built_area'])){
            if(empty($builtAreaArray)){
                $estates['built_area'] = '';
            }else{
                $min = intval(min($builtAreaArray));
                $max = intval(max($builtAreaArray));
                $estates['built_area'] = $min.'-'.$max.'㎡';
            }
        }

        if(empty($estates['house_type'])){
            if(empty($roomsArray)){
                $estates['house_type'] = '';
            }else{
                $roomsArray = array_unique($roomsArray);
                $roomsArray = array_values($roomsArray);

//                foreach ($roomsArray as &$v){
//                    $v = $v;
//                }
                $estates['house_type'] = (implode('/',$roomsArray)).'居';
            }
        }

        $housePurposeUser = $this->getHousekeeper($params['id'],$estates);

        $k = array_rand($housePurposeUser);
        $estates['house_purpose_user'] = $housePurposeUser[$k];


        return $this->success($estates);
    }


    //获取户型

    public function getUnitType($data){
        $is_group = 0;
        $rs = (new EstatesnewHouse())->getList([
            'estate_id' => $data['id']
        ], 'enh.*', 100);
        $res = $rs['result'];
        if (empty($res['list'])) {
            return [];
        } else {
            $arr = [];
            foreach($res['list'] as &$v) {
                $housePurpose = !empty($v['house_purpose']) ? explode(',', $v['house_purpose']) : [];
                $v['house_purpose'] = array_splice($housePurpose, 0, 1)[0] ?? '';
                $price = intval($v['price']);
                $priceTotal = intval($v['price_total']);
                $v['price'] = $priceTotal;// 总价
                $v['price_ave'] = $price;// 均价
                unset($v['price_total']);
                if($is_group==1){//以几居室分组
                    $arr['group'][$v['rooms']][] =  $v;
                }
            }
            unset($v);
            if ($is_group != 1) {
                $arr = $res['list'];
            } else {
                $arr['all'] = $res['list'];//以几居室分组，追加全部组
            }
            unset($res);

           return $arr;
        }
    }

    public function getHousekeeper($id,$estates){
        $data = [];
        $paramAgent = [
            'where' => [
                // ['ae.estate_id', '=', $id],
                ['a.status','=',1],
                ['', 'exp', Db::Raw("ae.estate_id={$id} or (a.is_default=1 and (a.city_no={$estates['city']} or a.area_no={$estates['area']}))")],
            ],
            'join' => [
                ['table' => 'estates_agent a', 'cond' => "a.id=ae.agent_id", 'type' => 'right'],
            ],
            'fields' => 'a.id, a.name, a.phone, a.head_img',
        ];
        $agent = (new Agent())->getListByRelation($paramAgent);

        if(!empty($agent['code']) && 1 == $agent['code']) {
            if(!empty($agent['result']['list'])) {
                $data = $agent['result']['list'];
            }
        }
        //去重
        if(!empty($data)){
            $data = array_unique($data,SORT_REGULAR);
        }
        return $data;
    }
    public function getTimeLabel($releaseTime){
        $time = time();
        if(empty($releaseTime)){
            return '';
        }
        $num = $time - $releaseTime;

        if($num <= 60){
            return '刚刚';
        }elseif ($num <= 60*60){ //一小时内
            $minute=floor((($time)-($releaseTime))/60);
            return $minute.'分钟';
        }elseif ($num>= 60 * 60 && $num <= 60*60*24){
            $minute=floor((($time)-($releaseTime))/3600);
            return $minute.'小时前';
        }elseif ($num >= 60*60*24 && $num <= 60*60*24*3){
            $minute=floor((($time)-($releaseTime))/86400);
            return $minute.'天前';
        }else{
            return '3天前';
        }

    }

    //地图搜索
    public function searchMap(){
        $param = $this->request->param();

        $res = (new Estatesnew())->searchMap($param);
        if($res['code'] != 1){
            $this->error($res['msg']);
        }

        $this->success($res['result']);
    }

    public function mapAddress(){
        $param = $this->request->param();

        $res = (new Estatesnew())->mapAddress($param);
        if($res['code'] != 1){
            $this->error($res['msg']);
        }

        $this->success($res['result']);
    }

}