<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of main
 *
 * @author Goods0
 */


use think\facade\Db;

include 'Common.php';
include 'phpqrcode.php';


class AgentAjax extends Common
{

    protected $worker = [2, 3, 4, 5, 6, 7];// 工作人员类型
    protected $manager = [3, 6];// 组长类型（项目、渠道）
    protected $teamMember = [2, 5, 7, 8];// 可审批类型
    protected $nomalAgent = [0, 1];// 普通经纪人
    protected $chargeType = [4];// 项目负责人操作环节
    protected $projectType = [1, 2, 3];// 项目组员操作环节
    protected $channelType = [5, 6];// 渠道组员操作环节
    protected $channelUser = [5, 6];// 渠道组类型
    // protected $apiSync = '127.0.0.1:8085/agentapi/agentAjax/sendTmpMsg';// 测试
    // protected $apiSync = 'http://chfx.999house.com/agentapi/agentAjax/sendTmpMsg';// 正式


    public function __construct()
    {
        parent::__construct();
        $userinfo = $this->getUserInfo();
        if ($userinfo['mestatus'] == -1) {
            $this->error('权限未开通');
            exit();
        } elseif ($userinfo['mestatus'] == '0') {
            $this->error('您的账号正在审核中请耐心等待开通');
            exit();
        } elseif ($userinfo['mestatus'] == '-2') {
            $this->error('账号未启用');
            exit();
        } elseif ($userinfo['is_emptyinfo'] == '-3') {
            $this->error('请先完善个人信息');
            exit();
        }
    }


    //获取我的客户信息
    public function getMyCustomerData()
    {
        $page = Context::Post('page');
        $agent_id = Session::get('agent_id');   //经纪人id
        $searchText = Context::Post('searchText');    //搜索内容
        $is_serach = empty(Context::Post('is_serach')) ? false : true;
        $DB = $this->db->Name('xcx_building_reported')->select("br.*,u.avatarUrl,u.nickName", "br")->leftJoin("xcx_user", "u", "br.user_id=u.id")->where_equalTo('br.agent_id', $agent_id)->page($page, self::MYLIMIT)->orderBy('br.create_time', 'desc');
        if (!empty($searchText)) {    //楼盘名称搜索
            $DB->where_like("br.user_name", '%' . $searchText . '%');
        }
        if (!empty(Context::Post('status_type'))) {
            $DB->where_equalTo('status_type', Context::Post('status_type'));
        }
        $reportedInfo = $DB->execute();
        if (!empty($reportedInfo)) {
            $dict = ['', '已报备', '已带看', '已成交', '已确认', '已开票', '已结佣'];
            $classDict = ['', 'tag-ybb', 'tag-ydk', 'tag-ycj', 'tag-zidinyi', 'tag-zidinyi2', 'tag-yjy'];
            foreach ($reportedInfo as &$val) {
                $val['create_time'] = date('Y.m.d', $val['create_time']);
                $val['take_time'] = date('Y.m.d', $val['take_time']);
                $val['status_type_name'] = $dict[$val['status_type']];
                $val['status_type_class'] = $classDict[$val['status_type']];
            }
            //echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>$reportedInfo],JSON_UNESCAPED_UNICODE);
            return $this->success(['is_serach' => $is_serach, 'data' => $reportedInfo]);
        } else {
            if (!empty($is_serach)) {
                //echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>[]]);
                return $this->success(['is_serach' => $is_serach]);
            } else {
                //echo json_encode(['success'=>false]);
                return $this->success();
            }
        }
    }

    //获取客户信息
    public function getCustomerData()
    {
        $page = Context::Post('page');
        $agent_id = Session::get('agent_id');   //经纪人id
        $searchText = Context::Post('searchText');    //搜索内容
        $is_serach = empty(Context::Post('is_serach')) ? false : true;
        $nowDate = Context::Post('nowDate');
        $orderby = Context::Post('orderby');
        if (!in_array($orderby, ['desc', 'asc'])) {
            $orderby = 'desc';
        }
        if (empty($nowDate)) {
            $nowDate = date('Y-m-d', time());
        }

        $DB = $this->db->Name('xcx_agent_customer')->select('ac.agent_focus,ac.user_name,u.id,u.nickName,u.avatarUrl,u.city', 'ac')->leftJoin('xcx_user', 'u', 'ac.user_id=u.id')->where_equalTo('ac.agent_id', $agent_id)->where_notEqualTo('ac.source', 4)->page($page, self::MYLIMIT)->orderBy('ac.agent_focus', 'desc')->orderBy('ac.create_time', $orderby);
        if (!empty($searchText)) {    //楼盘名称搜索
            $where_express = " (ac.user_name like \"%" . $searchText . "%\" OR u.nickName like \"%" . $searchText . "%\") ";
            @$DB->where_express($where_express);
        }
        if (!empty($nowDate)) {
            $endDate = strtotime($nowDate . ' +1 day');
            $nowDate = strtotime($nowDate);
            if (!empty($nowDate)) {
                $where_express = " (ac.create_time >= \"" . $nowDate . "\" AND ac.create_time <= \"" . $endDate . "\") ";
                @$DB->where_express($where_express);
            }
        }
        $agentInfo = $DB->execute();
        if (!empty($agentInfo)) {
            foreach ($agentInfo as &$val) {
                $val['city'] = empty($val['city']) ? '未知' : $val['city'];
                $val['user_name'] = empty($val['user_name']) ? $val['nickName'] : $val['user_name'];
                //获取最近一次的报备信息
                list($val['status_type'], $val['status_type_name'], $val['status_type_class']) = $this->getLastReported($val['id'], $agent_id);
                //统计浏览名片的次数
                $val['browse_num'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $val['id'])->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '1')->firstColumn();
                $val['share_num'] = $this->db->Name('xcx_user_browsing_history')->select('SUM(share_num)')->where_equalTo('user_id', $val['id'])->where_equalTo('agent_id', $agent_id)->firstColumn();
            }
            //echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>$agentInfo],JSON_UNESCAPED_UNICODE);
            return $this->success(['is_serach' => $is_serach, 'data' => $agentInfo]);
        } else {
            if (!empty($is_serach)) {
                //echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>[]]);
                return $this->success(['is_serach' => $is_serach, 'data' => []]);
            } else {
                //echo json_encode(['success'=>false]);
                return $this->success(['data' => []]);
            }
        }
    }

    //获取最近一次的报备信息
    protected function getLastReported($user_id, $agent_id)
    {
        $res = [];
        $reportedData = $this->db->Name('xcx_building_reported')->select()->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->orderBy('create_time', 'desc')->firstRow();
        if (empty($reportedData)) {
            $res[] = '0';
            $res[] = '';
            $res[] = '';
        } else {
            $dict = ['', '已报备', '已带看', '已成交', '已确认', '已开票', '已结佣'];
            $classDict = ['', 'tag-ybb', 'tag-ydk', 'tag-ycj', 'tag-zidinyi', 'tag-zidinyi2', 'tag-yjy'];
            $res[] = $reportedData['status_type'];
            $res[] = $dict[$reportedData['status_type']];
            $res[] = $classDict[$reportedData['status_type']];
        }
        return $res;
    }

    //获取客户最近一次访问时间
    protected function getAccessTime($user_id, $agent_id)
    {
        $res = "暂无";
        $historyData = $this->db->Name('xcx_user_browsing_history')->select()->where_equalTo('browse_type', '1')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->orderBy('start_time', 'desc')->firstRow();
        if (!empty($historyData)) {
            $res = date('Y.m.d H:i:s', $historyData['start_time']);
        }
        return $res;
    }

    //获取客户详情数据
    public function getCustomerDetail()
    {
        $id = Context::Post('id');    //客户id
        $agent_id = Session::get('agent_id');   //经纪人id
        //获取客户用户信息
        $data['userInfo'] = $this->db->Name('xcx_user')->select()->where_equalTo('id', $id)->firstRow();
        //获取经纪人与客户关系数据
        $data['agentCustomerInfo'] = $this->db->Name('xcx_agent_customer')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $id)->firstRow();
        $data['agentCustomerInfo']['user_name'] = empty($data['agentCustomerInfo']['user_name']) ? $data['userInfo']['nickName'] : $data['agentCustomerInfo']['user_name'];
        $data['agentCustomerInfo']['user_phone'] = empty($data['agentCustomerInfo']['user_phone']) ? '暂无' : $data['agentCustomerInfo']['user_phone'];
        list($data['agentCustomerInfo']['status_type'], $data['agentCustomerInfo']['status_type_name'], $data['agentCustomerInfo']['status_type_class']) = $this->getLastReported($id, $agent_id);   //获取最近一次带看状态
        $data['agentCustomerInfo']['access_time'] = $this->getAccessTime($id, $agent_id);    //获取最近访问时间
        //统计浏览记录
        $data['browsingHistoryInfo']['cardNum'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '1')->firstColumn();
        $data['browsingHistoryInfo']['articleNum'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '2')->firstColumn();
        $data['browsingHistoryInfo']['buildingNum'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '3')->firstColumn();
        //获取一周内的详细统计信息
        $weeksTime = $this->getWeeksHistory($id, $agent_id);
        $data['echartsInfo']['title'] = $weeksTime['title'];
        $data['echartsInfo']['card'] = $weeksTime['card'];
        $data['echartsInfo']['article'] = $weeksTime['article'];
        $data['echartsInfo']['building'] = $weeksTime['building'];
        //获取访问足迹详细信息
        $data['fwzjData'][0] = $this->getFwzjDataPage($id, $agent_id, '1');
        $data['fwzjData'][1] = $this->getFwzjDataPage($id, $agent_id, '2');
        $data['fwzjData'][2] = $this->getFwzjDataPage($id, $agent_id, '3');
        //获取跟进记录数据
        $data['followData'] = $this->db->Name('xcx_building_reported')->select("bb.*,br.status_type,br.json_data,br.user_name,br.user_phone,br.user_gender,br.take_time,br.id reported_id", "br")->leftJoin('xcx_building_building', 'bb', 'br.building_id=bb.id')->where_equalTo('br.agent_id', $agent_id)->where_equalTo('br.user_id', $id)->orderBy('br.create_time', 'desc')->execute();
        if (!empty($data['followData'])) {
            $followDict = ["", "报备", "带看", "成交", "确认业绩", "开票", "结佣"];
            $listDict = ["", "报备成功", "带看成功", "成交成功", "确认业绩成功", "开票成功", "结佣成功"];
            foreach ($data['followData'] as &$followVal) {
                $followVal['status_type_name'] = $followDict[$followVal['status_type']];
                $followVal['fold'] = floatval($followVal['fold']);
                $followVal['commission'] = floatval($followVal['commission']);
                $followVal['user_gender'] = $followVal['user_gender'] == '1' ? '先生' : '女士';
                $followVal['take_time'] = date('Y年m月d日', $followVal['take_time']);
                $followVal['list'] = empty($followVal['json_data']) ? [] : json_decode($followVal['json_data'], true);
                foreach ($followVal['list'] as &$listVal) {
                    $listVal['status_type_name'] = $listDict[$listVal['status_type']];
                    $listVal['time1'] = date('Y.m', $listVal['time']);
                    $listVal['time2'] = date('d', $listVal['time']);
                }
            }
        }
        //返回经纪人id
        $data['agent_id'] = $agent_id;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
//    //获取单条报备记录信息
//    public function getFollowData(){
//        $id=Context::Post('id');    //楼盘报备id
//        $agent_id=Session::get('agent_id');   //经纪人id
//        $followData=$this->db->Name('xcx_building_reported')->select("bb.*,br.status_type,br.json_data,br.user_name,br.user_phone,br.user_gender,br.take_time,br.id reported_id","br")->leftJoin('xcx_building_building','bb','br.building_id=bb.id')->where_equalTo('br.agent_id',$agent_id)->where_equalTo('br.id',$id)->execute();
//        if(!empty($followData)){
//            $followDict=["","报备","带看","成交","确认业绩","开票","结佣"];
//            $listDict=["","报备成功","带看成功","成交成功","确认业绩成功","开票成功","结佣成功"];
//            foreach($followData as &$followVal){
//                $followVal['status_type_name']=$followDict[$followVal['status_type']];
//                $followVal['fold']=floatval($followVal['fold']);
//                $followVal['commission']=floatval($followVal['commission']);
//                $followVal['user_gender']=$followVal['user_gender']=='1'?'先生':'女士';
//                $followVal['take_time']=date('Y年m月d日',$followVal['take_time']);
//                $followVal['list']=empty($followVal['json_data'])?[]:json_decode($followVal['json_data'],true);
//                foreach($followVal['list'] as &$listVal){
//                    $listVal['status_type_name']=$listDict[$listVal['status_type']];
//                    $listVal['time1']=date('Y.m',$listVal['time']);
//                    $listVal['time2']=date('d',$listVal['time']);
//                }
//            }
//            echo json_encode(['success'=>true,'data'=>$followData[0]]);
//        }else{
//            echo json_encode(['success'=>false]);
//        }
//    }

    // 客户信息
    public function getCustomerInfo()
    {
        try {
            $userId = Context::Post('id');    //客户id
            $acId = Context::Post('ac_id');    // 经纪人-客户 映射表 ID
            $agentId = $this->agentId;
            $customerInfo = [];

            if (empty($userId) && empty($acId)) {
                return $this->error('参数缺失');
            }

            $myDb = $this->db->Name('xcx_agent_customer')
                ->select('ac.user_name, ac.user_phone, u.nickname, u.avatarUrl, u.gender', 'ac')
                ->leftJoin('xcx_user', 'u', 'u.id=ac.user_id')
                ->where_equalTo('ac.agent_id', $agentId);

            if ($userId) {
                $myDb->where_equalTo('ac.user_id', $userId);
            } else {
                $myDb->where_equalTo('ac.id', $acId);
            }

            $data = $myDb->firstRow();

            if (!empty($data)) {
                $customerInfo['user_name'] = empty($data['user_name']) ? '' : $data['user_name'];
                $customerInfo['user_phone'] = empty($data['user_phone']) ? '' : $data['user_phone'];
                $customerInfo['headimgurl'] = !empty($data['avatarUrl']) ? $data['avatarUrl'] : $this->defaultHeadImg;
                $customerInfo['nickname'] = !empty($data['nickname']) ? $data['nickname'] : '';
                $customerInfo['gender'] = isset($data['gender']) ? $data['gender'] : 0;
                $customerInfo['last_visit_time'] = '';
                // 最后一次浏览记录
                $historyData = $this->db->Name('xcx_user_browsing_history')
                    ->select('start_time')
                    ->where_equalTo('browse_type', '1')
                    ->where_equalTo('user_id', $userId)
                    ->where_equalTo('agent_id', $agentId)
                    ->orderBy('start_time', 'desc')
                    ->firstRow();
                if (!empty($historyData)) {
                    $last_visit_time = date("Y.m.d H:i", $historyData['start_time']);
                    $customerInfo['last_visit_time'] = $last_visit_time;
                }
            }

            $res['customerInfo'] = $customerInfo;

            // 获取汇总数据
            $total = $this->getTotalCount($userId);
            if (isset($total['code'])) {
                return $this->error($total['msg']);
            }
            $res['totalInfo'] = $total;

            return $this->success($res);
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }

    // 获取客户数据分析
    public function getCustomerDetailNew()
    {
        try {
            $userId = Context::Post('id');    // 客户id
            $acId = Context::Post('ac_id');    // 经纪人-客户 映射表 ID
            $agentId = $this->agentId;    // 当前账号ID
            $tabType = !empty(Context::Post('tab_type')) ? Context::Post('tab_type') : 1; // 1-数据分析 2-访问足迹 3-跟进记录
            $browseType = !empty(Context::Post('browse_type')) ? Context::Post('browse_type') : 1; // 1-名片 2-文章 3-楼盘(tab_type为2时必须)
            $page = !empty(Context::Post('page')) ? Context::Post('page') : 1; // 页码
            $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : self::MYLIMIT; // 每页记录数

            if (empty($userId) && empty($acId)) {
                return $this->error('参数缺失');
            }

            switch ($tabType) {
                // 数据分析
                case 1:
                    $res = $this->dataAnalysis($userId);
                    break;
                // 访问足迹
                case 2:
                    $res = $this->dataFootprint($userId, $browseType, $page, $pageSize);
                    break;
                // 跟进记录
                case 3:
                    $res = $this->followLog($userId, $acId, $page, $pageSize);
                    break;
                default:
                    $res = ['code' => 0, 'msg' => '类别有误'];
                    break;
            }

            if (isset($res['code'])) {
                return $this->error($res['msg']);
            } else {
                return $this->success($res);
            }
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }

    // 汇总数据
    protected function getTotalCount($userId)
    {
        try {
            $agentId = $this->agentId;

            $total = [
                'articleNum'  => 0,
                'buildingNum' => 0,
                'cardNum'     => 0
            ];

            // 总的数据分析
            $countTotal = $this->db->Name('xcx_user_browsing_history')
                ->select('browse_type, COUNT(*) as count')
                ->where_equalTo('user_id', $userId)
                ->where_equalTo('agent_id', $agentId)
                ->groupBy('browse_type')
                ->execute();

            if (!empty($countTotal)) {
                foreach ($countTotal as $v) {
                    switch ($v['browse_type']) {
                        // 名片
                        case 1:
                            $total['cardNum'] = $v['count'];
                            break;
                        // 文章
                        case 2:
                            $total['articleNum'] = $v['count'];
                            break;
                        // 楼盘
                        case 3:
                            $total['buildingNum'] = $v['count'];
                            break;
                    }
                }
            }
            return $total;
        } catch (\ErrorException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 数据分析
    protected function dataAnalysis($userId)
    {
        try {
            $agentId = $this->agentId;

//            $total = [
//                'articleNum' => 0,
//                'buildingNum' => 6,
//                'cardNum' => 20
//            ];
//
//            // 总的数据分析
//            $countTotal = $this->db->Name('xcx_user_browsing_history')
//                                    ->select('browse_type, COUNT(*) as count')
//                                    ->where_equalTo('user_id', $userId)
//                                    ->where_equalTo('agent_id', $agentId)
//                                    ->groupBy('browse_type')
//                                    ->execute();
//
//            if(!empty($countTotal)) {
//                foreach ($countTotal as $v) {
//                    switch ($v['browse_type']) {
//                        // 名片
//                        case 1:
//                            $total['cardNum'] = $v['count'];
//                            break;
//                        // 文章
//                        case 2:
//                            $total['articleNum'] = $v['count'];
//                            break;
//                        // 楼盘
//                        case 3:
//                            $total['buildingNum'] = $v['count'];
//                            break;
//                    }
//                }
//            }

            // 近一周的数据分析
            $data = [];
            $todaystart = strtotime(date('Y-m-d' . '00:00:00', time()));   //今天0点时间戳
            for ($i = 6; $i >= 0; $i--) {
                $tempStartTime = $todaystart - ($i * 86400);
                $res[] = [
                    'startTime' => $tempStartTime,
                    'endTime'   => $tempStartTime + 86400
                ];
            }
            if (!empty($res)) {
                foreach ($res as $val) {
                    $data['title'][] = date('m/d', $val['startTime']);
                    $data['cardNum'][] = 0;
                    $data['articleNum'][] = 0;
                    $data['buildingNum'][] = 0;

                    $countWeek = $this->db->Name('xcx_user_browsing_history')
                        ->select('browse_type, COUNT(*) as count')
                        ->where_equalTo('user_id', $userId)
                        ->where_equalTo('agent_id', $agentId)
                        ->where_greatThanOrEqual('start_time', $val['startTime'])
                        ->where_lessThanOrEqual('end_time', $val['endTime'])
                        ->groupBy('browse_type')
                        ->execute();
                    if (!empty($countWeek)) {
                        foreach ($countWeek as $vv) {
                            switch ($vv['browse_type']) {
                                // 名片
                                case 1:
                                    $data['cardNum'][] = $vv['count'];
                                    break;
                                // 文章
                                case 2:
                                    $data['articleNum'][] = $vv['count'];
                                    break;
                                // 楼盘
                                case 3:
                                    $data['buildingNum'][] = $vv['count'];
                                    break;
                            }
                        }
                    }
                }
            } else {
                return ['code' => 0, 'msg' => '时间处理有未知错误'];
            }

            $result = [
//                'browsingHistoryInfo' => $total,
                'echartsInfo' => $data,
            ];

            return $result;
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 客户足迹
    protected function dataFootprint($userId, $browseType, $page, $pageSize)
    {
        try {
            $agentId = $this->agentId;

            switch ($browseType) {
                // 名片
                case 1:
                    $data = $this->db->Name('xcx_user_browsing_history')
                        ->select('viewing_hours, start_time, end_time')
                        ->where_equalTo('user_id', $userId)
                        ->where_equalTo('agent_id', $agentId)
                        ->where_equalTo('browse_type', $browseType)
                        ->orderBy('start_time', 'desc')
                        ->execute();

                    $res = [];
                    if (!empty($data)) {
                        $totalNum = sizeof($data);
                        foreach ($data as $k => $v) {
                            $res[$k]['time'] = date("Y年m月d日 H:i:s", $v['start_time']);// 浏览时间
                            $res[$k]['number'] = $totalNum - $k;// 第几次
                            $res[$k]['viewing_hours'] = $v['viewing_hours'];// 本次停留
                            // 总共停留
                            $total_viewing_hours = 0;
                            foreach ($data as $val) {
                                if ($v['start_time'] >= $val['start_time']) {
                                    $total_viewing_hours += $val['viewing_hours'];
                                }
                            }
                            if ($total_viewing_hours > 0) {
                                $hour = floor($total_viewing_hours / 3600);
                                $minute = floor(($total_viewing_hours - 3600 * $hour) / 60);
                                $second = floor((($total_viewing_hours - 3600 * $hour) - 60 * $minute) % 60);
                                $total_viewing_hours = (empty($hour) ? '' : $hour . '小时') . (empty($minute) ? '' : $minute . '分钟') . $second . '秒';
                            }
                            $res[$k]['total_viewing_hours'] = $total_viewing_hours;
                        }
                    }
                    break;
                // 文章
                case 2:
                    $data = $this->db->Name('xcx_user_browsing_history')
                        ->select('ubh.id, ubh.viewing_hours, ubh.start_time, ubh.end_time, ubh.article_id, aa.title, aa.comments_num, aa.aid, aa.create_time, count(*) as dynamicNum', 'ubh')
                        ->innerJoin('xcx_article_article', 'aa', 'aa.id=ubh.article_id')
                        ->where_equalTo('ubh.user_id', $userId)
                        ->where_equalTo('ubh.agent_id', $agentId)
                        ->where_equalTo('ubh.browse_type', $browseType)
                        ->orderBy('ubh.start_time', 'desc')
                        ->groupBy('ubh.article_id')
                        ->page($page, $pageSize)
                        ->execute();

                    $res = [];
                    if (!empty($data)) {
                        // 文章作者头像
                        $aDict = [];
                        $aids = array_column($data, 'aid');
                        $adminRow = (new Query())->Name('admin')->select('name, img')->where_in('id', $aids)->execute();
                        if (!empty($adminRow)) {
                            foreach ($adminRow as $v2) {
                                $aDict[$v2['id']]['name'] = $v2['name'];
                                $aDict[$v2['id']]['img'] = $v2['img'];
                            }
                        }

                        foreach ($data as $k => $v) {
                            $res[$k]['id'] = $v['id'];
                            $res[$k]['article_id'] = $v['article_id'];
                            $res[$k]['title'] = $v['title'];
                            $res[$k]['comments_num'] = $v['comments_num'];
                            $res[$k]['dynamicNum'] = $v['dynamicNum'];
                            $res[$k]['aname'] = !empty($aDict[$v['aid']]['name']) ? $aDict[$v['aid']]['name'] : '九房网';
                            $res[$k]['aimg'] = !empty($aDict[$v['aid']]['img']) ? $aDict[$v['aid']]['img'] : $this->defaultHeadImg;
                            $res[$k]['release_time'] = $this->format_dates($v['create_time']);
                        }
                    }
                    break;
                // 楼盘
                case 3:
                    $data = $this->db->Name('xcx_user_browsing_history')
                        ->select("ubh.id, ubh.building_id, bb.name, bb.pic as cover, bb.fold, bb.city, bb.area, bb.house_type, bb.commission, count(*) as dynamicNum", "ubh")
                        ->leftJoin('xcx_building_building', 'bb', 'ubh.building_id=bb.id')
                        ->where_equalTo('ubh.user_id', $userId)
                        ->where_equalTo('ubh.agent_id', $agentId)
                        ->where_equalTo('ubh.browse_type', $browseType)
                        ->orderBy('ubh.start_time', 'desc')
                        ->groupBy('ubh.building_id')
                        ->page($page, $pageSize)
                        ->execute();

                    if (!empty($data)) {
                        $res = $data;
                    }
                    break;
                default:
                    return $this->error('类型错误');
                    break;
            }

            return ['footprintData' => $res];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 跟进记录列表
    protected function followLog($userId, $acId, $page, $pageSize)
    {
        try {
            $agentId = $this->agentId;

            $myDb = $this->db->Name('xcx_building_reported')
                ->select('br.id, br.status_type, br.examine_type, bb.name, bb.pic as cover, bb.fold, bb.house_type, bb.city, bb.area, bb.commission', 'br')
                ->innerJoin('xcx_building_building', 'bb', 'br.building_id=bb.id')
                ->where_equalTo('br.agent_id', $agentId);

            if ($userId) {
                $myDb->where_equalTo('br.user_id', $userId);
            } else {
                $myDb->where_equalTo('br.ac_id', $acId);
            }

            $follow = $myDb->orderBy('br.create_time', 'desc')
                ->page($page, $pageSize)
                ->execute();

            if (!empty($follow)) {
                foreach ($follow as &$v) {
                    $key = "{$v['status_type']}|{$v['examine_type']}";
                    $v['status_str'] = $this->getReportStatus()[$key];
                }
            } else {
                $follow = [];
            }

            return ['followData' => $follow];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 报备记录详情
    public function getFollowData()
    {
        try {
            $id = Context::Post('id');    //楼盘报备id
            $agentId = $this->agentId;
            $saId = $this->saId;
            $adminId = $this->adminId;
            $leaderId = $this->builddingLeader;
            $data = [];

            $is_self = 1;// 是否自己提交的报备单
            $is_examine = 1;// 0-审核 1-审批（组长身份用）

            $typenameList = $this->getStoreType();

            $detail = $this->db->Name('xcx_building_reported')
                ->select("br.id, br.agent_id, br.said, br.examine_aid, br.examine_said, br.user_id, br.user_name, br.user_phone, br.building_id, br.status_type, br.examine_type, br.commission as iniCommission, br.user_gender, br.take_time, br.update_time, u.nickName, u.avatarUrl, bb.name as bname, bb.pic, bb.house_type, bb.commission,bb.commission_type,bb.fold,bb.store_manager_commission,bb.team_member_commission ,bb.city, bb.area, bb.protect_set, bb.aid", "br")
                ->leftJoin('xcx_user', 'u', "u.id=br.user_id")
                ->leftJoin('xcx_building_building', 'bb', "bb.id=br.building_id")
                ->where_equalTo("br.id", $id)
                ->firstRow();
            if (!empty($detail)) {
                $userInfo = $this->getUserInfo();
                // 权限校验
                if ($saId != $detail['said']) {
                    $is_self = 0;
                    switch ($userInfo['type']) {
                        // 店长
                        case 1:
                            $reportAgent = $this->db->Name('xcx_store_agent')
                                ->select("store_id")
                                ->where_equalTo('said', $detail['said'])
                                ->firstRow();
                            if (empty($reportAgent)) {
                                return $this->error('人员信息有误');
                            }
                            if ($reportAgent['store_id'] != $userInfo['storeInfo']['store_id']) {
                                return $this->error('不是您的店员的报备');
                            }
                            break;
                        // 项目组员
                        case 2:
                            if (0 != $userInfo['manageinfo']['building_ids']) {
                                $isBuild = TRUE;// 是否有楼盘权限
                                $isLog = FALSE;// 是否需要查日志
                                if (-1 == $userInfo['manageinfo']['building_ids']) {
//                                    $isLog = TRUE;
                                    $isBuild = FALSE;
                                } else {
                                    $buildingIds = explode(',', $userInfo['manageinfo']['building_ids']);
                                    if (!in_array($detail['building_id'], $buildingIds)) {
//                                        $isLog = TRUE;
                                        $isBuild = FALSE;
                                    }
                                }
                                if (!$isBuild) {
                                    // 查找是否同时具有项目负责人身份
                                    if (!empty($leaderId) && !empty($adminId)) {
                                        if ($adminId != $detail['aid']) {
                                            $isLog = TRUE;
                                        }
                                    } else {
                                        $isLog = TRUE;
                                    }
                                }
                                if ($isLog) {// 操作过
                                    $reportLog = $this->db->Name('xcx_reported_log')
                                        ->select("id")
                                        ->where_equalTo('report_id', $detail['id'])
                                        ->where_express(
                                            "(examine_said=:said or examine_aid=:aid)",
                                            [':said' => $saId, ':aid' => $agentId]
                                        )
                                        ->firstRow();
                                    if (empty($reportLog)) {
                                        return $this->error('您没有该楼栋权限');
                                    }
                                }
                            }
                            break;
                        // 渠道组员
                        case 5:
                            if (!empty($adminId)) {
                                $resStore = $this->db->Name("xcx_store_agent")
                                    ->select("sa.said", "sa")
                                    ->innerJoin("xcx_store_store", "ss", "sa.store_id=ss.id")
                                    ->where_equalTo('ss.aid', $adminId)
                                    ->where_equalTo('sa.said', $detail['said'])
                                    ->firstRow();
                                if (empty($resStore)) {
                                    return $this->error('您没有该经纪人所属店铺的权限');
                                }
                            } else {
                                return $this->error('您没有权限');
                            }
                            break;
                        // 组长(项目、渠道)
                        case 3:
                        case 6:
                            // 查找自己下辖组员
                            $work = $this->db->Name('xcx_store_agent')
                                ->select('said')
                                ->where_in('mgid', $userInfo['mgid'])
                                ->execute();
                            if (empty($work)) {
                                return $this->error('组员信息有误');
                            }
                            $saids = array_column($work, 'said');
//                            $saids = [];// 目前版本组员无法报备
                            if (!in_array($detail['said'], $saids)) {
//                                list($in, $val) = $this->buildWhereIn(":said", $saids);
                                // 查找是否被下辖组员操作过
                                $logs = $this->db->Name('xcx_reported_log')
                                    ->select('id')
                                    ->where_equalTo('report_id', $detail['id'])
                                    ->where_in("examine_said", $saids)
                                    ->execute();
                                if (empty($logs)) {
                                    if (!empty($userInfo['leader_id']) && !empty($userInfo['admin_id'])) {// 是否项目负责人
                                        if ($userInfo['admin_id'] != $detail['aid']) {
                                            return $this->error('您没有权限');
                                        }
                                    } else {
                                        return $this->error('不是您组员提交或审核的报备记录');
                                    }
//                                    return $this->error('不是您组员提交或审核的报备记录');
                                } else {
                                    $is_examine = 0;
                                }
                            }
                            break;
                        // 店员
                        case 0:
                            return $this->error('这不是您的报备');
                            break;
                        default:
                            // 是否是项目负责人
                            if (!empty($leaderId) && !empty($adminId)) {
                                if ($adminId != $detail['aid']) {
                                    return $this->error('您无权限');
                                }
                            }
                            break;
                    }
                }

                // 数据处理

                //失效状态判断
                if ($detail['status_type'] >= 1 && $detail['status_type'] <= 3) {
                    $paramCheck = [
                        'status'      => $detail['status_type'],
                        'take_time'   => $detail['take_time'],
                        'update_time' => $detail['update_time'],
                        'protect_set' => $detail['protect_set'],
                    ];
                    $checkProtect = $this->checkProtectTime($paramCheck);
                    if (!$checkProtect) {
                        $detail['examine_type'] = -2;
                    }
//                    if(1 == $detail['status_type']) {// 如果是报备流程，则从预约带看时间开始算起
//                        $baseTime = $detail['take_time'];
//                    } else {//按最后的更新时间-小时
//                        $baseTime = $detail['update_time'];
//                    }
//
//                    //每个流程环节保护时间-规范到小时
//                    $detail['protect_set'] = json_decode($detail['protect_set'], TRUE);
//                    $k = 'status'.$detail['status_type'].'_hours';
//                    $protect_set_hours = intval($detail['protect_set'][$k]);
//                    if($protect_set_hours > 0) {// 有设置才判断保护期
//                        if(1 == $detail['status_type']) {
//                            $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
//                        } else {
//                            $protect_time = $protect_set_hours * 3600;
//                        }
//                        $protectTimeEnd = $baseTime+$protect_time;
////                    var_dump([$protectTimeEnd, time(), $protect_set_hours]);
//                        if($protectTimeEnd<=time()){
//                            $detail['examine_type'] = -2;
//                        }
//                    }
                }

                // 上一次访问时间
                $last_visit_time = '';
                if (!empty($detail['user_id'])) {
                    $historyData = $this->db->Name('xcx_user_browsing_history')->select('start_time')->where_equalTo('browse_type', '1')->where_equalTo('user_id', $detail['user_id'])->where_equalTo('agent_id', $agentId)->orderBy('start_time', 'desc')->firstRow();
                    if (!empty($historyData)) {
                        $last_visit_time = date("Y.m.d H:i", $historyData['start_time']);
                    }
                }

                // 结佣时，佣金取用申请时的金额
//                switch ($userInfo['type']){
//                    case 0:
//                        $commission = $detail['commission'];
//                        break;
//                    case 1:
//                        $commission = $detail['store_manager_commission'];
//                        break;
//                    default:
//                        $commission = $detail['team_member_commission'];
//                        break;
//                }

                switch ($detail['commission_type']) {
                    // 固定金额
                    case 1:
                        $commission = $detail['commission'] . "元";
                        break;
                    // 百分比
                    case 2:
//                        $ratio = bcdiv($detail['commission'], 100, 2);
//                        $commission = bcmul($detail['fold'], $ratio, 2);
                        $commission = $detail['commission'] . "%";
                        break;
                    default:
                        $commission = 0;
                        break;
                }

//                $commission = $detail['commission'];


//                if(6 == $detail['status_type']) {
//                    $apply = $this->db->Name('xcx_reported_settlement')->select('commission')->where_equalTo('report_id', $id)->firstRow();
//                    if(isset($apply['commission'])) {
//                        $commission = $apply['commission'];
//                    }
//                }

                $data['name'] = empty($detail['user_name']) ? empty($detail['nickName']) ? '' : $detail['nickName'] : $detail['user_name'];
                $defaultHeadImg = $this->manImg;
                if (isset($detail['user_gender']) && 3 == $detail['user_gender']) {
                    $defaultHeadImg = $this->womanImg;
                }
                $data['headimgurl'] = empty($detail['avatarUrl']) ? $defaultHeadImg : $detail['avatarUrl'];
                $data['phone'] = empty($detail['user_phone']) ? '' : $detail['user_phone'];
                $data['last_visit_time'] = $last_visit_time;
                $data['status_type'] = $detail['status_type'];
                $data['examine_type'] = $detail['examine_type'];
                $keyType = "{$data['status_type']}|{$data['examine_type']}";
                $data['type_str'] = $this->getReportStatus()[$keyType];
                $data['building_name'] = empty($detail['bname']) ? "" : $detail['bname'];
                $data['building_cover'] = empty($detail['pic']) ? "" : $detail['pic'];
                $data['house_type'] = $detail['house_type'];
                $data['city'] = $detail['city'];
                $data['area'] = $detail['area'];
                $data['commission'] = $commission;
//                $data['commission_change'] = bcsub($commission, $detail['iniCommission'], 2);
//                if(empty($detail['iniCommission'])) {
//                    $data['commission_change_rate'] = bcdiv($data['commission_change'], $detail['iniCommission'], 2) * 100 . '%';
//                } else {
//                    $data['commission_change_rate'] = "0%";
//                }
                $data['commission_change_rate'] = "0%";// 佣金变化率，当前版本弃用
                $data['is_self'] = $is_self;
                $data['is_examine'] = $is_examine;
                // 项目驻场电话
                $data['agent_phone'] = '';
                $ab = $this->db->Name('xcx_manager_building')->select("au.phone", 'mb')->leftJoin('xcx_store_agent', 'sa', 'sa.said=mb.said')->leftJoin('xcx_agent_user', 'au', 'au.id=sa.agent_id')->where_express('FIND_IN_SET(:build, mb.building_ids)', [':build' => $detail['building_id']])->firstRow();
                if (!empty($ab['phone'])) {
                    $data['agent_phone'] = $ab['phone'];
                }
                // 获取详细进程
                $list = $this->db->Name('xcx_reported_log')->select("rl.content, rl.imgs, rl.status_type, rl.agent_type, rl.examine_type, rl.is_admin, rl.updated_at, au.nickname, au.headimgurl, au.sex, au.phone", "rl")
                    ->leftJoin('xcx_agent_user', 'au', 'au.id=rl.examine_aid')
                    ->where_equalTo('rl.report_id', $detail['id'])
                    ->orderBy('rl.updated_at', 'desc')
                    ->execute();
                if (!empty($list)) {
                    foreach ($list as &$v) {
                        if ($v['is_admin']) {
                            // 后台操作
                            $v['nickname'] = '后台管理员';
                            $v['headimgurl'] = $this->defaultHeadImg;
                            $v['position'] = '管理员';
                        } else {
                            $v['nickname'] = !empty($v['nickname']) ? $v['nickname'] : '';
                            $defaultImg = $this->manImg;
                            if (isset($v['sex']) && 3 == $v['sex']) {
                                $defaultImg = $this->womanImg;
                            }
                            $v['headimgurl'] = !empty($v['headimgurl']) ? $v['headimgurl'] : $defaultImg;
                            $v['position'] = isset($typenameList[$v['agent_type']]) ? $typenameList[$v['agent_type']] : '未知身份';
                        }

                        $v['imgs'] = json_decode($v['imgs'], TRUE);
                        if (!empty($v['status_type'])) {
                            $key = "{$v['status_type']}|{$v['examine_type']}";
                            $v['status_str'] = $this->getReportStatus2()[$key];// 状态描述
                        }
                        $v['time_day'] = date("d", $v['updated_at']);
                        $v['time_year_month'] = date("Y / m", $v['updated_at']);
                    }
                }
                $data['list'] = $list;

                // 不是自己的提交的报备单时，检查/标记已读记录
                if ($saId != $detail['said']) {
                    $readLog = $this->db->Name('xcx_report_read_log')
                        ->select('id, is_read')
                        ->where_equalTo('said', $saId)
                        ->where_equalTo('report_id', $id)
                        ->firstRow();
                    if (!empty($readLog)) {
                        if (0 == $readLog['is_read']) {
                            $resLog = $this->db->Name('xcx_report_read_log')->update(['is_read' => 1, 'updated_at' => time()])->where_equalTo('id', $readLog['id'])->execute();
                        }
                    } else {
                        $inserData = [
                            'report_id'  => $id,
                            'said'       => $saId,
                            'agent_id'   => $agentId,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        $resLog = $this->db->Name('xcx_report_read_log')->insert($inserData)->execute();
                    }
                }
            }

            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //获取一周内浏览记录
    protected function getWeeksHistory($user_id, $agent_id)
    {
        $res = [];
        $todaystart = strtotime(date('Y-m-d' . '00:00:00', time()));   //今天0点时间戳
        for ($i = 6; $i >= 0; $i--) {
            $tempStartTime = $todaystart - ($i * 86400);
            $res[] = ['startTime' => $tempStartTime, 'endTime' => $tempStartTime + 86400];
        }
        $data = [];
        foreach ($res as $val) {
            $data['title'][] = date('m/d', $val['startTime']);
            $data['card'][] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '1')->where_greatThanOrEqual('start_time', $val['startTime'])->where_lessThanOrEqual('start_time', $val['endTime'])->firstColumn();
            $data['article'][] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '2')->where_greatThanOrEqual('start_time', $val['startTime'])->where_lessThanOrEqual('start_time', $val['endTime'])->firstColumn();
            $data['building'][] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '3')->where_greatThanOrEqual('start_time', $val['startTime'])->where_lessThanOrEqual('start_time', $val['endTime'])->firstColumn();
        }
        return $data;
    }

    //获取浏览数据api
    public function getFwzjDataPage($user_id, $agent_id, $browse_type)
    {
        if ($browse_type == '1') {
            $browsingHistoryRow = $this->db->Name('xcx_user_browsing_history')->select()->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', $browse_type)->orderBy('start_time', 'desc')->execute();
        } else if ($browse_type == '2') {
            $browsingHistoryRow = $this->db->Name('xcx_user_browsing_history')->select("aa.*", "ubh")->leftJoin('xcx_article_article', 'aa', 'ubh.article_id=aa.id')->where_equalTo('ubh.user_id', $user_id)->where_equalTo('ubh.agent_id', $agent_id)->where_equalTo('ubh.browse_type', $browse_type)->orderBy('ubh.start_time', 'desc')->execute();
        } else if ($browse_type == '3') {
            $browsingHistoryRow = $this->db->Name('xcx_user_browsing_history')->select("bb.*", "ubh")->leftJoin('xcx_building_building', 'bb', 'ubh.building_id=bb.id')->where_equalTo('ubh.user_id', $user_id)->where_equalTo('ubh.agent_id', $agent_id)->where_equalTo('ubh.browse_type', $browse_type)->orderBy('ubh.start_time', 'desc')->execute();
        }
        if (empty($browsingHistoryRow)) {
            $browsingHistoryRow = [];
        } else {
            if ($browse_type == '1') {
                $cardTotalNum = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '1')->firstColumn();
                foreach ($browsingHistoryRow as $key => &$val) {
                    $chishu = $cardTotalNum - $key;
                    $total_viewing_hours = $this->db->Name('xcx_user_browsing_history')->select('sum(viewing_hours)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('browse_type', '1')->where_lessThanOrEqual('start_time', $val['start_time'])->firstColumn();
                    $hour = floor($total_viewing_hours / 3600);
                    $minute = floor(($total_viewing_hours - 3600 * $hour) / 60);
                    $second = floor((($total_viewing_hours - 3600 * $hour) - 60 * $minute) % 60);
                    $total_viewing_hours = (empty($hour) ? '' : $hour . '小时') . (empty($minute) ? '' : $minute . '分钟') . $second . '秒';
                    $val['time'] = date('Y年m月d日 H:i:s', $val['start_time']);
                    $val['data']['cishu'] = $chishu;
                    $val['data']['viewing_hours'] = $val['viewing_hours'];
                    $val['data']['total_viewing_hours'] = $total_viewing_hours;
                }
            } else if ($browse_type == '2') {
                $unArticleIds = [];
                foreach ($browsingHistoryRow as $k => &$value0) {
                    if (in_array($value0['id'], $unArticleIds)) {
                        unset($browsingHistoryRow[$k]);
                    } else {
                        $unArticleIds[] = $value0['id'];
                    }
                }
                sort($browsingHistoryRow);
                //获取后台发布者信息
                $aids = [];
                $aDict = [];
                foreach ($browsingHistoryRow as $v) {
                    $aids[] = $v['aid'];
                }
                $aids = array_unique($aids);
                $adminRow = (new Query())->Name('admin')->select()->where_in('id', $aids)->execute();
                if (!empty($adminRow)) {
                    foreach ($adminRow as $v2) {
                        $aDict[$v2['id']]['name'] = $v2['name'];
                        $aDict[$v2['id']]['img'] = $v2['img'];
                    }
                    foreach ($browsingHistoryRow as &$value) {
                        $value['comments_num'] = $this->getCommentsNum($value['id']);
                        $value['aname'] = $aDict[$value['aid']]['name'];
                        $value['aimg'] = $aDict[$value['aid']]['img'];
                        $value['release_time'] = $this->format_dates($value['create_time']);
                        $value['dynamicNum'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('article_id', $value['id'])->where_equalTo('browse_type', '2')->firstColumn();
                    }
                }
            } else if ($browse_type == '3') {
                $unBuildingIds = [];
                foreach ($browsingHistoryRow as $k => &$vvv) {
                    if (in_array($vvv['id'], $unBuildingIds)) {
                        unset($browsingHistoryRow[$k]);
                    } else {
                        $unBuildingIds[] = $vvv['id'];
                        $vvv['fold'] = floatval($vvv['fold']);
                        $vvv['commission'] = floatval($vvv['commission']);
                        $vvv['dynamicNum'] = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('building_id', $vvv['id'])->where_equalTo('browse_type', '3')->firstColumn();
                    }
                }
                sort($browsingHistoryRow);
            }
        }
        return $browsingHistoryRow;
    }

    //获取文章评论数
    public function getCommentsNum($article_id)
    {
        $num = $this->db->Name('xcx_article_comments')->select('count(*)')->where_equalTo('aid', $article_id)->firstColumn();
        return empty($num) ? 0 : $num;
    }

    //修改经纪人关注客户状态
    public function updateAgentCustomer()
    {
        $id = Context::Post('id');    //客户id
        $agent_id = Session::get('agent_id');   //经纪人id
        $agent_focus = Context::Post('agent_focus');
        $res = $this->db->Name('xcx_agent_customer')->update(['agent_focus' => $agent_focus, 'update_time' => time()])->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $id)->execute();
        if ($res)
            echo json_encode(['success' => true]);
        else
            echo json_encode(['success' => false]);
    }

    //修改客户备注姓名电话
    public function updateCustomerName()
    {
        $id = Context::Post('id');    //客户id
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_name = Context::Post('user_name');
        $user_phone = Context::Post('user_phone');
        $res = $this->db->Name('xcx_agent_customer')->update(['user_name' => $user_name, 'user_phone' => $user_phone, 'update_time' => time()])->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $id)->execute();
        if ($res)
            echo json_encode(['success' => true]);
        else
            echo json_encode(['success' => false]);
    }

    //获取文章足迹信息
    public function getFootprintArticle()
    {
        $id = Context::Post('id');    //文章id
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_id = Context::Post('user_id');    //客户id
        //获取文章信息
        $data['articleInfo'] = $this->db->Name('xcx_article_article')->select()->where_equalTo('id', $id)->firstRow();
        $adminRow = (new Query())->Name('admin')->select()->where_equalTo('id', $data['articleInfo']['aid'])->firstRow();
        $data['articleInfo']['aname'] = $adminRow['name'];
        $data['articleInfo']['aimg'] = $adminRow['img'];
        $data['articleInfo']['read_num'] = $data['articleInfo']['read_num'] >= 10000 ? sprintf("%.1f", $data['articleInfo']['read_num'] / 10000) . '万' : $data['articleInfo']['read_num'];
        //获取客户用户信息
        $userInfo = $this->db->Name('xcx_user')->select()->where_equalTo('id', $user_id)->firstRow();
        //获取经纪人与客户关系数据
        $data['agentCustomerInfo'] = $this->db->Name('xcx_agent_customer')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->firstRow();
        $data['agentCustomerInfo']['user_name'] = empty($data['agentCustomerInfo']['user_name']) ? $userInfo['nickName'] : $data['agentCustomerInfo']['user_name'];
        $data['agentCustomerInfo']['avatarUrl'] = $userInfo['avatarUrl'];
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    //获取文章浏览记录
    public function getFootprintArticleData()
    {
        $page = Context::Post('page');
        $id = Context::Post('id');    //文章id
        $user_id = Context::Post('user_id');    //客户id
        $agent_id = Session::get('agent_id');   //经纪人id
        $browsingHistoryRow = $this->db->Name('xcx_user_browsing_history')->select()->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('article_id', $id)->where_equalTo('browse_type', '2')->orderBy('start_time', 'desc')->page($page, self::MYLIMIT)->execute();
        if (!empty($browsingHistoryRow)) {
            $cardTotalNum = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('article_id', $id)->where_equalTo('browse_type', '2')->firstColumn();
            foreach ($browsingHistoryRow as $key => &$val) {
                $chishu = $cardTotalNum - (($page - 1) * self::MYLIMIT) - $key;
                $total_viewing_hours = $this->db->Name('xcx_user_browsing_history')->select('sum(viewing_hours)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('article_id', $id)->where_equalTo('browse_type', '2')->where_lessThanOrEqual('start_time', $val['start_time'])->firstColumn();
                $hour = floor($total_viewing_hours / 3600);
                $minute = floor(($total_viewing_hours - 3600 * $hour) / 60);
                $second = floor((($total_viewing_hours - 3600 * $hour) - 60 * $minute) % 60);
                $total_viewing_hours = (empty($hour) ? '' : $hour . '小时') . (empty($minute) ? '' : $minute . '分钟') . $second . '秒';
                $val['cishu'] = $chishu;
                $val['total_viewing_hours'] = $total_viewing_hours;
                $val['start_time'] = date('Y年m月d日 H:i:s', $val['start_time']);
            }
            echo json_encode(['success' => true, 'data' => $browsingHistoryRow], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    //获取楼盘足迹信息
    public function getFootprintBuild()
    {
        $id = Context::Post('id');    //楼盘id
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_id = Context::Post('user_id');    //客户id
        //获取楼盘信息
        $data['buildingInfo'] = $this->db->Name('xcx_building_building')->select()->where_equalTo('id', $id)->firstRow();
        $data['buildingInfo']['fold'] = floatval($data['buildingInfo']['fold']);
        $data['buildingInfo']['commission'] = floatval($data['buildingInfo']['commission']);
        //获取访客数量
        $visitorsNum = $this->db->Name('xcx_user_browsing_history')->select()->where_equalTo('building_id', $id)->where_equalTo('browse_type', '3')->groupBy('user_id')->execute();
        $data['visitorsNum'] = count($visitorsNum);
        //获取客户用户信息
        $userInfo = $this->db->Name('xcx_user')->select()->where_equalTo('id', $user_id)->firstRow();
        //获取经纪人与客户关系数据
        $data['agentCustomerInfo'] = $this->db->Name('xcx_agent_customer')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->firstRow();
        $data['agentCustomerInfo']['user_name'] = empty($data['agentCustomerInfo']['user_name']) ? $userInfo['nickName'] : $data['agentCustomerInfo']['user_name'];
        $data['agentCustomerInfo']['avatarUrl'] = $userInfo['avatarUrl'];
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    //获取楼盘浏览记录
    public function getFootprintBuildData()
    {
        $page = Context::Post('page');
        $id = Context::Post('id');    //楼盘id
        $user_id = Context::Post('user_id');    //客户id
        $agent_id = Session::get('agent_id');   //经纪人id
        $browsingHistoryRow = $this->db->Name('xcx_user_browsing_history')->select()->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('building_id', $id)->where_equalTo('browse_type', '3')->orderBy('start_time', 'desc')->page($page, self::MYLIMIT)->execute();
        if (!empty($browsingHistoryRow)) {
            $cardTotalNum = $this->db->Name('xcx_user_browsing_history')->select('COUNT(*)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('building_id', $id)->where_equalTo('browse_type', '3')->firstColumn();
            foreach ($browsingHistoryRow as $key => &$val) {
                $chishu = $cardTotalNum - (($page - 1) * self::MYLIMIT) - $key;
                $total_viewing_hours = $this->db->Name('xcx_user_browsing_history')->select('sum(viewing_hours)')->where_equalTo('user_id', $user_id)->where_equalTo('agent_id', $agent_id)->where_equalTo('building_id', $id)->where_equalTo('browse_type', '3')->where_lessThanOrEqual('start_time', $val['start_time'])->firstColumn();
                $hour = floor($total_viewing_hours / 3600);
                $minute = floor(($total_viewing_hours - 3600 * $hour) / 60);
                $second = floor((($total_viewing_hours - 3600 * $hour) - 60 * $minute) % 60);
                $total_viewing_hours = (empty($hour) ? '' : $hour . '小时') . (empty($minute) ? '' : $minute . '分钟') . $second . '秒';
                $val['cishu'] = $chishu;
                $val['total_viewing_hours'] = $total_viewing_hours;
                $val['start_time'] = date('Y年m月d日 H:i:s', $val['start_time']);
            }
            echo json_encode(['success' => true, 'data' => $browsingHistoryRow], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    //获取经纪人对应的楼盘信息
    public function getAgentBuilding()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        //$agentBuildingRow=$this->db->Name('xcx_agent_building')->select("bb.id,bb.name","ab")->leftJoin('xcx_building_building','bb','ab.building_id=bb.id')->where_equalTo('ab.agent_id',$agent_id)->where_equalTo('ab.status','1')->orderBy('ab.create_time','desc')->execute();
        $myDB = $this->db->Name('xcx_building_building')->select("id,name")->where_equalTo('status', 1)->where_equalTo('is_open_project', '1');
//        $myDB = $this->db->Name('xcx_agent_building')->select("bb.id,bb.name","ab")->leftJoin('xcx_building_building','bb','ab.building_id=bb.id')->where_equalTo('ab.agent_id',$agent_id)->where_equalTo('ab.status','1');
        if (!empty(Context::Post('city'))) {    //城市搜索
            $myDB->where_like('city', "%" . Context::Post('city') . "%");
        }
//        $myDB->orderBy('ab.create_time','desc');
        $agentBuildingRow = $myDB->execute();
        if ($agentBuildingRow) {
            $buildingDict = [];
            foreach ($agentBuildingRow as $val) {
                $buildingDict[] = ['value' => $val['id'], 'text' => $val['name']];
            }
            echo json_encode(['success' => true, 'data' => $buildingDict], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    //获取店铺二维码
    public function getStoreQrcode()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        if (!empty($agent_id)) {
            $storeInfo = $this->db->Name('xcx_store_agent')
                ->select('sg.said,ss.title as name,sg.store_id', 'sg')
                ->leftJoin('xcx_store_store', 'ss', 'ss.id = sg.store_id')
                ->where_equalTo('sg.agent_id', $agent_id)->where_equalTo('sg.type', 1)->firstRow();
            if (!empty($storeInfo)) {
                $agentInfo = $this->db->Name('xcx_agent_user')
                    ->select('name,nickname,headimgurl')->where_equalTo('id', $agent_id)->firstRow();
                if (empty($agentInfo)) {
                    $agentInfo = [];
                } else {
                    $agentInfo['name'] = empty($agentInfo['name']) ? '' : $agentInfo['name'];
                }
                $storeInfo['store_id'] = Encryption::authcode($storeInfo['store_id'], false);
                return $this->success(['data' => $storeInfo, 'agentInfo' => $agentInfo]);
                echo json_encode(['success' => true, 'data' => $storeInfo, 'agentInfo' => $agentInfo]);
            } else {
                return $this->error('你还不是店长');
//                echo json_encode(['success' => false, 'message' => '你还不是店长']);
            }
        } else {
            return $this->error('经纪人id有误');
            echo json_encode(['success' => false, 'message' => '经纪人id有误']);
        }
    }

    //添加楼盘报备
    public function addBuildingReported()
    {
        //$data['user_id']=intval(Context::Post('user_id'));    //客户id
//        $data['user_id'] = !empty(Context::Post('user_id')) ? (int)Context::Post('user_id') : 0;    //客户ID
        $storeId = empty(Context::Post('store_id')) ? 0 : Context::Post('store_id');
        $data['agent_id'] = $this->getAgentId();   //经纪人id
//        $data['said'] = $this->saId;   // 店铺成员ID

//        $this->saId = [150=>['type'=>1,'store'=>['id' => 1,'title'=>'项目'],'group'=>['id' => 1,'title'=>'项目']]];

        if(empty($storeId)){
            return $this->error('请选择店铺');
        }
        //权限判断
        foreach ($this->saId as $k => $v) {
            if ($v['store']['id'] == $storeId) {
                if (empty($data['said'])) {
                    $data['said'] = $k;
                    $type = $v['type'];
                } else {
                    if ($v['type'] == 1) {
                        $type = $v['type'];
                        $data['said'] = $k;
                    }
                }

            }
        }
        if(!isset($type)){
            return $this->error('非法身份');
        }

        if (empty($this->RoleAuth[$type]['add'])) {
            return $this->error('没有权限报备');
        }

        $custInfoLog = 0;// 客户信息是否已经修改过
        $isNew = 0;// 是否新增

        // $agentType = $this->getAgentType();
        // if (in_array($agentType, $this->manager)) {
        //     return $this->error('您是工作人员组长不可进行报备，只可审核');
        // }

        $data['user_name'] = Context::Post('name');
        if (empty($data['user_name'])) {
            return $this->error('请填写客户姓名');
        }
        $data['user_phone'] = Context::Post('phone');
        if (empty($data['user_phone'])) {
            return $this->error('请填写客户手机号');
        }
        if (!preg_match("/^1[345789]{1}\d{9}$/", $data['user_phone'])) {
            return $this->error('手机格式不正确');
        }

        $data['user_gender'] = intval(Context::Post('sex'));
        $data['user_gender'] = $data['user_gender'] ? $data['user_gender'] : 1;
        $data['`describe`'] = empty(Context::Post('describe')) ? '' : Context::Post('describe');
//        if(empty(Context::Post('daikan'))){
//            return $this->error('请选择带看时间');
//        }
//        $data['take_time'] = strtotime(Context::Post('daikan'));
//        if($data['take_time']===false){
//            return $this->error('时间格式错误');
//        }
        $data['status_type'] = '1';
        $data['json_data'] = json_encode([['time' => time(), 'status_type' => 1, 'content' => '']]);
        //$data['order_no']=date('YmdHis').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $data['order_no'] = create_order_no('R'); //单号
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['agent_type'] = $type;

        // 图片
        $imgs = [];
        $imgs = json_encode($imgs);

//        if(empty(Context::Post('building_ids'))){
//            return $this->error('请选择报备楼盘');
//        }
//        $buildingids= trim(Context::Post('building_ids'), ',');
//        $buildingids = Context::Post('building_ids');
        $resultErr = [];
        $resultSuccess = [];
//        $buildingids=explode(',',$buildingids);
//        $buildingInfo = htmlspecialchars_decode(Context::Post('building_info'));
        $buildingInfo = Context::Post('building_info');
        if (empty($buildingInfo)) {
            return $this->error('请选择报备楼盘');
        }

        // 根据手机查询经纪人-客户关系
        $acId = 0;
        $acInfo = $this->db->Name('xcx_agent_customer')
            ->select('id, user_id, user_name, user_phone')
            ->where_equalTo('agent_id', $data['agent_id'])
            ->where_equalTo('user_phone', $data['user_phone'])
            ->firstRow();
        if (empty($acInfo)) {
            $isNew = 1;
            $data['user_id'] = 0;
        } else {
            $acId = $acInfo['id'];
            $data['user_id'] = $acInfo['user_id'];
        }
//        $buildingInfo = json_decode($buildingInfo, TRUE);
        foreach ($buildingInfo as $buildingIdval) {
            if (empty($buildingIdval['time'])) {
                return $this->error('请选择带看时间');
            }
            $data['take_time'] = strtotime($buildingIdval['time']);
            if ($data['take_time'] === false) {
                return $this->error('时间格式错误');
            }

            $data['building_id'] = $buildingIdval['building_id'];
            $buildingRow = $this->db->Name('xcx_building_building')->select('id,name,fold,commission,commission_type,store_manager_commission,team_member_commission,early_hours,protect_set,city')->where_equalTo('id', $data['building_id'])->firstRow();
            if (empty($buildingRow['id'])) {
                $resultErr[] = '报备的楼盘信息不存在';
                continue;
            }
            $buildingName = $buildingRow['name'];
            if ($buildingRow['status'] == '0') {
                $resultErr[] = '报备的“' . $buildingName . '”楼盘信息已下架';
                continue;
            }
            if (!empty($buildingRow['early_hours'])) {
                if ($data['take_time'] < time() + $buildingRow['early_hours'] * 60) {//报备限制时间 分钟
                    // $early = bcdiv($buildingRow['early_hours'], 60, 1);
                    $resultErr[] = '该客户在“' . $buildingName . '”的带看时间需提前' . $buildingRow['early_hours'] . '分钟！';
                    continue;
                }
            }
//            $buildingRow['protect_set'] = json_decode($buildingRow['protect_set'],1);//楼盘设置的报备保护机制

            //判断该客户报备的楼盘是否在保护期
            $reportedRow = $this->db->Name('xcx_building_reported')->select()->where_equalTo('user_phone', $data['user_phone'])->where_equalTo('building_id', $data['building_id'])->orderBy('create_time', 'desc')->firstRow();
            if (!empty($reportedRow)) {
                if ($reportedRow['status_type'] >= 1 && $reportedRow['status_type'] <= 3) {
                    $paramCheck = [
                        'status'      => $reportedRow['status_type'],
                        'take_time'   => $reportedRow['take_time'],
                        'update_time' => $reportedRow['update_time'],
                        'protect_set' => $buildingRow['protect_set'],
                    ];
                    $checkProtect = $this->checkProtectTime($paramCheck);
                    if ($checkProtect) {
                        $resultErr[] = '该客户在“' . $buildingName . '”已报备过！';
                        continue;
                    }
//                    if(1 == $reportedRow['status_type']) {// 如果是报备流程，则从预约带看时间开始算起
//                        $baseTime = $reportedRow['take_time'];
//                    } else {
//                        $baseTime = $reportedRow['update_time'];
//                    }
//                    //每个流程环节保护时间-规范到小时
//                    $k = 'status'.$reportedRow['status_type'].'_hours';
//                    $protect_set_hours = intval($buildingRow['protect_set'][$k]);
//                    if($protect_set_hours > 0) {// 当有设置保护时间时，才校验保护期
//                        if(1 == $reportedRow['status_type']) {
//                            $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
//                        } else {
//                            $protect_time = $protect_set_hours * 3600;
//                        }
//                        $protectTimeEnd=$baseTime+$protect_time;//按最后的更新时间-小时
//                        if($protectTimeEnd>time()){
//                            $resultErr[]='该客户在“'.$buildingName.'”已报备过！';
//                            continue;
//                        }
//                    }
                }
            }
            //判断经纪人是否开通该楼盘
            // $yz= $this->db->Name('xcx_agent_building')->select('is_focus')->where_equalTo('agent_id',$data['agent_id'])->where_equalTo('building_id',$data['building_id'])->where_equalTo('status','1')->firstRow();
            // if(empty($yz['is_focus'])){
            //     $resultErr[]='您还未开通“'.$buildingName.'”楼盘！';
            //     continue;
            // }else{
            // 报备时的初始佣金  当前版本弃用，不记录
            $data['commission'] = 0;

            // 报备保护期
            $data['protect_day'] = 0;
            $protectTime = $this->getProtectTime($buildingRow['protect_set'], 1);
            if(!empty($protectTime)) {
                $data['protect_day'] = bcdiv($protectTime, 86400);
            }

            $pdo = new DataBase();
            $pdo->beginTransaction();

            // 对比客户信息是否新增或变化
            if (!$custInfoLog) {
                if ($isNew) {
                    $insertUser['agent_id'] = $data['agent_id'];
                    $insertUser['user_id'] = $data['user_id'];
                    $insertUser['source'] = 4;
                    $insertUser['user_name'] = $data['user_name'];
                    $insertUser['user_phone'] = $data['user_phone'];
                    $insertUser['create_time'] = time();
                    $insertUser['update_time'] = time();
                    $resUserNew = $this->db->Name('xcx_agent_customer')->insert($insertUser)->execute();
                    if (empty($resUserNew)) {
                        $pdo->rollBack();
                        $resultErr[] = '报备客户信息添加失败！';
                        continue;
                    }
                    $acId = $resUserNew;
                } else {
                    if ($acInfo['user_name'] != $data['user_name']) {// 姓名做了修改
                        $updateAc['user_name'] = $data['user_name'];
                        $resAc = $this->db->Name('xcx_agent_customer')->update($updateAc)->where_equalTo('id', $acInfo['id'])->execute();
                        if (empty($resAc)) {
                            $pdo->rollBack();
                            $resultErr[] = '报备客户信息修改失败！';
                            continue;
                        }
                    }
                }
                $custInfoLog = 1;// 标记客户信息已修改成功，下次循环无需再处理
            }
            $data['ac_id'] = $acId;
            $data['store_id'] = $storeId; //店铺id

            // 插入楼盘报备记录
            $res = $this->db->Name('xcx_building_reported')->insert($data)->execute();
            if (empty($res)) {
                $pdo->rollBack();
                $resultErr[] = '报备“' . $buildingName . '”楼盘数据保存有误！';
                continue;
            }

            // 插入报备日志
            $logInsert = [
                'said'         => $data['said'],
                'agent_id'     => $data['agent_id'],
                'examine_said' => $data['said'],
                'examine_aid'  => $data['agent_id'],
                'report_id'    => $res,
                'agent_type'   => $type,
                'status_type'  => 1,
                'examine_type' => 1,
                'content'      => $data['`describe`'],
                'imgs'         => $imgs,
                'created_at'   => time(),
                'updated_at'   => time(),
            ];
            $resLog = $this->db->Name('xcx_reported_log')->insert($logInsert)->execute();
            if (empty($resLog)) {
                $pdo->rollBack();
                $resultErr[] = '报备“' . $buildingName . '”报备日志保存有误！';
                continue;
            }

            $resultSuccess[] = $buildingName;

            $pdo->commit();

            // 微信推送
            $sendParam = [
                'order_no'    => $data['order_no'],
                'status_type' => $data['status_type'],
                'next_status' => $data['status_type'],
                'protect_set' => $buildingRow['protect_set'],
            ];
            $this->sendParamToWx($sendParam);
        }
        if (!empty($resultErr)) {
            $msg = implode(';', $resultErr);
            return $this->error($msg);
        } else {
            return $this->success();
        }

    }

    //获取access_toke
    public function getAccessToken($reflash = 0)
    {
        $appid = WXAPPID;
        $secret = WXSECRET;
        $key = "wxfwaccesstokens_{$appid}";
        $redis = RedisBase::getInstance();
        $accessTokenData = $redis->get($key);
        if ($reflash == 1 || empty($accessTokenData)) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
            $token = $this->sendPost($url);
            //打印获得的数据
            $arr = json_decode($token, true);
            $access_token = $arr['access_token'];
            // $expires_in = $arr['expires_in']-3600+time();
            $expires_in = 5400;

            $this->redis->set($key, serialize($access_token));
            $this->redis->expireAt($key, $expires_in);
            return $access_token;
        } else {
            return unserialize($accessTokenData);
        }
    }

    //获取店铺所有经纪人数据
    public function getSortAgent()
    {
        $data = [];
        $agent_id = Session::get('agent_id');   //经纪人id(店长)
        $name = Context::Post('name');
        $store_id = Context::Post('store_id');
        //判断是否店长
        $manager = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('type', 1)->execute();
        if (empty($manager)) {
            return $this->success([]);
        }

        $manager = array_column($manager, 'store_id');

        if (!in_array($store_id, $manager)) {
            return $this->error('该店铺不是这个经纪人的');
        }
//        $data['store_id'] = $manager['store_id'];
        $data['store_id'] = $store_id;
        //获取申请入驻经纪人
        //return $manager['store_id'];
        /**
         * $bindingInfo = $this->db->Name('xcx_agent_user')->select()->where_equalTo('sq_store_id', $manager['store_id'])->where_equalTo('sq_store_status', '1')->orderBy('sq_store_addtime', 'desc')->execute();
         * if (empty($bindingInfo)) {
         * $bindingInfo = [];
         * } else {
         * foreach ($bindingInfo as &$bindingVal) {
         * $bindingVal['name'] = empty($bindingVal['name']) ? $bindingVal['nickname'] : $bindingVal['name'];
         * }
         * }
         * $data['bindingInfo'] = $bindingInfo;
         **/
        //获取网店的所有经纪人id
        $agentIds = [];
        $saIds = [];
        $storeRow = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('type', '1')->firstRow();
        if (empty($storeRow)) {
            return $this->success($data);
//            echo json_encode($data);
//            exit;
        }
        $storeData = $this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id', $storeRow['store_id'])->where_equalTo('status', 1)->where_equalTo('is_delete', 0)->execute();
        foreach ($storeData as $val) {
            if (!empty($val['agent_id'])) {
                $agentIds[] = $val['agent_id'];
                $saIds[$val['agent_id']] = $val['said'];
            }
        }
        //获取所有经纪人信息
        $agentsData = $this->db->Name('xcx_agent_user')
            ->select('id,name,nickname,headimgurl')
            ->where_in('id', $agentIds)->where_equalTo('status', 1);


        if (!empty($name)) {
            $agentsData = $agentsData->where_like("name", "%{$name}%");
        }
        $agentsData = $agentsData->execute();
        if (!empty($agentsData)) {
            foreach ($agentsData as &$val) {
                $val['name'] = empty($val['name']) ? $val['nickname'] : $val['name'];
                $val['said'] = !empty($saIds[$val['id']]) ? $saIds[$val['id']] : 0;
                $val['headimgurl'] = empty($val['headimgurl']) ? '' : $val['headimgurl'];
            }
        }
        $data['members'] = $agentsData;

        return $this->success($data);
//        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    //申请的
    public function userBindingInfo()
    {
        $data = [];
        $agent_id = Session::get('agent_id');   //经纪人id(店长)
        $store_id = Context::Post('store_id');
        $name = Context::Post('name');
        //判断是否店长
        $manager = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('type', 1)->execute();
        if (empty($manager)) {
            return $this->success([]);
        }
        $manager = array_column($manager, 'store_id');
        if (!in_array($store_id, $manager)) {
            return $this->error('该店铺不是这个经纪人的');
        }
//        $data['store_id'] = $manager['store_id'];
        $data['store_id'] = $store_id;
        //获取申请入驻经纪人
        //return $manager['store_id'];
        $bindingInfo = $this->db->Name('xcx_agent_user')
            ->select('id,nickname,name,headimgurl')
            ->where_equalTo('sq_store_id', $store_id);


        if (!empty($name)) {
            $bindingInfo = $bindingInfo->where_like("name", "%{$name}%");
        }

        $bindingInfo = $bindingInfo->where_equalTo('sq_store_status', '1')
            ->orderBy('sq_store_addtime', 'desc')
            ->execute();

        if (empty($bindingInfo)) {
            $bindingInfo = [];
        } else {
            foreach ($bindingInfo as &$bindingVal) {
                $bindingVal['name'] = empty($bindingVal['name']) ? $bindingVal['nickname'] : $bindingVal['name'];
                $bindingVal['headimgurl'] = empty($bindingVal['headimgurl']) ? '' : $bindingVal['headimgurl'];
            }
        }
        $data['bindingInfo'] = $bindingInfo;
        return $this->success($data);
    }
    //获取我的网店数据
//    public function getSortData(){
//        $agent_id=Session::get('agent_id');   //经纪人id(店长)
//        $nowDate=Context::Post('nowDate');  //搜查询的日期
//        $startTime=strtotime($nowDate);    //当天开始时间戳
//        $endTime=$startTime+86400;      //当天结束时间戳
//        $beginThismonth=mktime(0,0,0,date('m',$startTime),1,date('Y',$startTime));
//        $endThismonth=mktime(23,59,59,date('m',$startTime),date('t',$startTime),date('Y',$startTime));
//        $data=[];
//        //获取网店的所有经纪人id
//        $agentIds=[];
//        $storeRow=$this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id',$agent_id)->where_equalTo('type','1')->firstRow();
//        if(empty($storeRow)){echo json_encode($data);exit;}
//        $storeData=$this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id',$storeRow['store_id'])->execute();
//        foreach($storeData as $val){
//            if(!empty($val['agent_id'])){
//                $agentIds[]=$val['agent_id'];
//            }
//        }
//        //获取分享数据
//        $currentchildData=[[],[],[]];
//        $share=$this->db->Name('xcx_agent_share')->select()->where_in('agent_id',$agentIds)->where_greatThanOrEqual('create_time',$startTime)->where_lessThanOrEqual('create_time',$endTime)->where_equalTo('client_type','2')->execute();
//        if(!empty($share)){
//            foreach($share as $value){
//                if($value['share_type']=='1'){
//                    if(empty($currentchildData[0][$value['agent_id']])){
//                        $currentchildData[0][$value['agent_id']]['shareNum']=1;
//                    }else{
//                        $currentchildData[0][$value['agent_id']]['shareNum']++;
//                    }
//                }else if($value['share_type']=='2'){
//                    if(empty($currentchildData[2][$value['agent_id'].'|'.$value['article_id']])){
//                        $currentchildData[2][$value['agent_id'].'|'.$value['article_id']]['shareNum']=1;
//                    }else{
//                        $currentchildData[2][$value['agent_id'].'|'.$value['article_id']]['shareNum']++;
//                    }
//                }else if($value['share_type']=='3'){
//                    if(empty($currentchildData[1][$value['agent_id'].'|'.$value['building_id']])){
//                        $currentchildData[1][$value['agent_id'].'|'.$value['building_id']]['shareNum']=1;
//                    }else{
//                        $currentchildData[1][$value['agent_id'].'|'.$value['building_id']]['shareNum']++;
//                    }
//                }
//            }
//        }
//        foreach($currentchildData as $k=>&$v){
//            foreach($v as $kk=>&$vv){
//                if($k==0){  //名片
//                    list($vv['name'],$vv['headimgurl'])=$this->getAgentImgName($kk);
//                    $vv['viewNum']=$this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id',$kk)->where_equalTo('browse_type','1')->where_greatThanOrEqual('start_time',$startTime)->where_lessThanOrEqual('start_time',$endTime)->firstColumn();
//                }else if($k==2){ //文章
//                    $kkTemp=explode('|',$kk);
//                    list($vv['name'],$vv['headimgurl'])=$this->getAgentImgName($kkTemp[0]);
//                    $vv['viewNum']=$this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id',$kkTemp[0])->where_equalTo('article_id',$kkTemp[1])->where_equalTo('browse_type','2')->where_greatThanOrEqual('start_time',$startTime)->where_lessThanOrEqual('start_time',$endTime)->firstColumn();
//                    $articleInfo=$this->db->Name('xcx_article_article')->select()->where_equalTo('id',$kkTemp[1])->firstRow();
//                    if(!empty($articleInfo)){
//                        $vv['id']=$articleInfo['id'];
//                        $vv['title']=$articleInfo['title'];
//                        $vv['cover']=$articleInfo['cover'];
//                        $vv['comments_num']=$this->getCommentsNum($articleInfo['id']);
//                        $adminRow=(new Query())->Name('admin')->select()->where_equalTo('id',$articleInfo['aid'])->firstRow();
//                        $vv['aname']=$adminRow['name'];
//                        $vv['aimg']=$adminRow['img'];
//                    }
//                }else if($k==1){ //楼盘
//                    $kkTemp=explode('|',$kk);
//                    list($vv['name'],$vv['headimgurl'])=$this->getAgentImgName($kkTemp[0]);
//                    $vv['viewNum']=$this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id',$kkTemp[0])->where_equalTo('building_id',$kkTemp[1])->where_equalTo('browse_type','3')->where_greatThanOrEqual('start_time',$startTime)->where_lessThanOrEqual('start_time',$endTime)->firstColumn();
//                    $buildingInfo=$this->db->Name('xcx_building_building')->select()->where_equalTo('id',$kkTemp[1])->firstRow();
//                    if(!empty($buildingInfo)){
//                        $vv['id']=$buildingInfo['id'];
//                        $vv['pic']=$buildingInfo['pic'];
//                        $vv['name']=$buildingInfo['name'];
//                        $vv['house_type']=$buildingInfo['house_type'];
//                        $vv['city']=$buildingInfo['city'];
//                        $vv['area']=$buildingInfo['area'];
//                        $vv['sales_status']=$buildingInfo['sales_status'];
//                        $vv['flag']=empty($buildingInfo['flag'])?[]:explode(',',$buildingInfo['flag']);
//                        $vv['fold']=floatval($buildingInfo['fold']);
//                        $vv['commission']=floatval($buildingInfo['commission']);
//                    }
//                }
//            }
//        }
//        $data['currentchildData']=$currentchildData;
//        //获取报备记录
//        $reported['bb']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','1')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        $reported['dk']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','2')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        $reported['cj']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','3')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        $reported['yj']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','4')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        $reported['kp']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','5')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        $reported['jy']=$this->db->Name('xcx_building_reported')->select('au.id,au.headimgurl,au.name,au.nickname,br.status_type,count(br.id) daytotal','br')->leftJoin('xcx_agent_user','au','br.agent_id=au.id')->where_equalTo('br.status_type','6')->where_in('br.agent_id',$agentIds)->where_greatThanOrEqual('br.create_time',$startTime)->where_lessThanOrEqual('br.create_time',$endTime)->groupBy('br.agent_id')->execute();
//        //统计月报表数
//        foreach($reported as &$vvv){
//            foreach($vvv as &$vvvv){
//                $vvvv['name']=empty($vvvv['name'])?$vvvv['nickname']:$vvvv['name'];
//                $vvvv['monthtotal']=$this->db->Name('xcx_building_reported')->select('count(*)')->where_equalTo('agent_id',$vvvv['id'])->where_equalTo('status_type',$vvvv['status_type'])->where_greatThanOrEqual('create_time',$beginThismonth)->where_lessThanOrEqual('create_time',$endThismonth)->firstColumn();
//            }
//        }
//        $data['channel']=[['title'=>'分享','data'=>[]],['title'=>'报备','data'=>$reported['bb']],['title'=>'带看','data'=>$reported['dk']],['title'=>'成交','data'=>$reported['cj']],['title'=>'确认业绩','data'=>$reported['yj']],['title'=>'开票','data'=>$reported['kp']],['title'=>'结佣','data'=>$reported['jy']]];
//        echo json_encode($data,JSON_UNESCAPED_UNICODE);
//    }

    //踢人
    public function getKicking()
    {
        $member_ids = Context::Post('member_ids');
        $agent_id = Session::get('agent_id');
        if (empty($member_ids)) {
            return $this->error('保存失败');
//            echo json_encode(['success' => false, 'message' => "保存失败"]);
//            exit;
        }
        $member_ids = trim($member_ids, ',');
        $member_ids = explode(',', $member_ids);
        if (in_array($agent_id, $member_ids)) {
            return $this->error('自己不可踢出');
//            echo json_encode(['success' => false, 'message' => "自己不可踢出"]);
//            exit;
        }
        $res = $this->db->Name('xcx_agent_user')->update(['sq_store_status' => 0])->where_in('id', $member_ids)->execute();
        $re = $this->db->Name('xcx_store_agent')->update(['agent_id' => 0])->where_in('agent_id', $member_ids)->execute();
        if ($res && $re) {
            return $this->success([], '踢出成功');
//            echo json_encode(['success' => true, 'message' => "踢出成功"]);
        } else {
            return $this->error('保存失败');
//            echo json_encode(['success' => false, 'message' => "保存失败"]);
        }
    }


    // 分栏类别 暂时无用
    public function getColunmList()
    {
        $data = [
            [
                "title" => "分享",
                "value" => 1,
                "child" => [
                    ["title" => "名片", "value" => "card"],
                    ["title" => "新闻", "value" => "news"],
                    ["title" => "楼盘", "value" => "buiding"],
                ],
            ],
            [
                "title" => "报备流程",
                "value" => 2,
                "child" => [
                    ["title" => "报备", "value" => "bb"],
                    ["title" => "带看", "value" => "dk"],
                    ["title" => "成交", "value" => "cj"],
                    ["title" => "确认业绩", "value" => "yj"],
                    ["title" => "开票", "value" => "kp"],
                    ["title" => "结佣", "value" => "jy"],
                ],
            ]
        ];
        return $this->success($data);
    }

    // 获取网点数据
    public function getSortData()
    {
        try {
            $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;// 页码
            $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : self::MYLIMIT;// 每页记录条数
            if ($pageSize > 100) {
                return $this->error('请求数据超出限制');
            }
            $dataType = !empty(Context::Post('data_type')) ? Context::Post('data_type') : 0;// 数据类型 1-分享 2-报备流程 0-默认，由下面程序判断
            $type = !empty(Context::Post('type')) ? Context::Post('type') : 0;// 类型 (分享时 1-名片 2-文章 3-楼盘) | (报备时 1-报备 2-带看 3-成交 4-确认业绩 5-开票 6-结佣) 0-默认，由下面程序判断

            $searchIds = !empty(Context::Post('search_ids')) ? Context::Post('search_ids') : [];
            $name = !empty(Context::Post('name')) ? Context::Post('name') : [];// 客户/经纪人姓名
            $nameType = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $isSelf = !empty(Context::Post('is_self')) ? Context::Post('is_self') : 0;// 是否看自己的报备 0-全部 1-自己 2-别人
            $orderBy = !empty(Context::Post('order_by')) ? Context::Post('order_by') : "";
            // 查看类型 1-我的网店（提交类型） 2-我的审批（审批类型）
            $selectType = !empty(Context::Post('select_type')) ? Context::Post('select_type') : 1;
            switch ($orderBy) {
                case "asc":
                    $orderBy = "ASC";
                    break;
                case "desc":
                default:
                    $orderBy = "DESC";
                    break;
            }

            $startTime = 0;
            $endTime = 0;
            if (!empty(Context::Post('nowDate'))) {
                $nowDate = Context::Post('nowDate');  //搜查询的日期
                $startTime = strtotime($nowDate);    //当天开始时间戳
                $endTime = $startTime + 86400;      //当天结束时间戳
            }

            // 获取当前账号信息
            $userInfo = $this->getUserInfo();
            // 判断账号角色，获取对应信息
            if (1 != $userInfo['type']) {
                if (1 == $dataType) {// 只有店长有分享数据查看权限
                    return $this->error("权限有误", 0, ["currentchildData" => []]);
                }
                if (empty($dataType)) {
                    // 默认数据为 报备
                    $dataType = 2;
                    $type = 1;
                }
            } else {
                if (empty($dataType)) {
                    // 默认数据为 分享-名片
                    $dataType = 1;
                    $type = 1;
                }
            }
            switch ($dataType) {
                // 分享
                case 1:
                    $resData = $this->getShareData($userInfo, $type, $name, $startTime, $endTime, $page, $pageSize, $orderBy);
                    break;
                // 报备流程
                case 2:
                    $resData = $this->getReportDataNew($userInfo, $type, $selectType, $searchIds, $name, $nameType, $startTime, $endTime, $isSelf, $page, $pageSize, $orderBy);
                    break;
                default:
                    $resData = ['code' => 0, 'msg' => "类型错误"];
                    break;
            }
            if (isset($resData['code'])) {
                return $this->error($resData['msg'], $resData['code'], ["currentchildData" => []]);
            } else {
                $resData = array_values($resData);
                return $this->success(["currentchildData" => $resData]);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 0, ["currentchildData" => []]);
        }
    }

    // 获取分享数据统计
    protected function getShareData($userInfo, $type, $name, $startTime, $endTime, $page, $pageSize, $orderBy)
    {
        try {
            $agentId = $this->agentId;
            //获取网店的所有经纪人id
            $agentIds = [];
            if (empty($userInfo['storeInfo']['store_id'])) {// 没有店铺信息
                return ['code' => 0, 'msg' => "没有店铺信息"];
            }
            $storeData = $this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id', $userInfo['storeInfo']['store_id'])->execute();
//            $dbSQL = $this->db->Name('xcx_store_agent')->select("sa.agent_id", "sa");
//            if(!empty($name)) {
//                $dbSQL->innerJoin('xcx_agent_user', 'au', 'au.id=sa.agent_id')
//                    ->where_express("(au.nickname like :nickname or au.name like :name)", [':nickname' => "%{$name}%", ':name' => "%{$name}%"]);
//            }
//            $storeData = $dbSQL->execute();
            foreach ($storeData as $val) {
                if (!empty($val['agent_id'])) {
                    $agentIds[] = $val['agent_id'];
                }
            }
            // 分享数据
//            $share = $this->db->Name('xcx_agent_share')->select()->where_in('agent_id',$agentIds)->where_equalTo('client_type','2')->where_equalTo('share_type', $type)->where_greatThanOrEqual('create_time',$startTime)->where_lessThanOrEqual('create_time',$endTime)->orderBy("create_time", $orderBy)->page($page, $pageSize)->execute();
            $dbSQL = $this->db->Name('xcx_agent_share')->select("ase.agent_id, au.nickname, au.headimgurl", "ase")
                ->innerJoin('xcx_agent_user', 'au', 'au.id=ase.agent_id')
                ->where_in('ase.agent_id', $agentIds)
                ->where_equalTo('ase.client_type', '2')
                ->where_equalTo('ase.share_type', $type)
                ->where_greatThanOrEqual('ase.create_time', $startTime)
                ->where_lessThanOrEqual('ase.create_time', $endTime);
            if (!empty($name)) {
                $dbSQL->where_express("(au.nickname like :nickname or au.name like :name)", [':nickname' => "%{$name}%", ':name' => "%{$name}%"]);
            }
            $share = $dbSQL->orderBy('ase.create_time', $orderBy)
                ->page($page, $pageSize)
                ->execute();
            if (empty($share)) {
                return ['code' => 0, 'msg' => "没有分享数据"];
            }
            $currentchildData = [];
            switch ($type) {
                // 名片
                case 1:
                    // 计算及去重
                    $shareAgentIds = [];
                    foreach ($share as $v) {
                        if (empty($currentchildData[$v['agent_id']])) {
                            $currentchildData[$v['agent_id']]['shareNum'] = 1;
                            $currentchildData[$v['agent_id']]['name'] = $v['nickname'];
                            $currentchildData[$v['agent_id']]['headimgurl'] = $v['headimgurl'];
//                            $shareAgentIds[] = $v['agent_id'];
                        } else {
                            $currentchildData[$v['agent_id']]['shareNum']++;
                        }
                    }
//                    // 获取分享者的信息
//                    $shareAgentIds = array_unique($shareAgentIds);
//                    $agentInfo = $this->db->Name('xcx_agent_user')->select("id, nickname, headimgurl")->where_in('id', $shareAgentIds)->execute();
                    // 分发头像及完成浏览量查询
                    foreach ($currentchildData as $key => $val) {
                        $currentchildData[$key]['viewNum'] = $this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id', $key)->where_equalTo('browse_type', 1)->where_greatThanOrEqual('start_time', $startTime)->where_lessThanOrEqual('start_time', $endTime)->firstColumn();
                    }
                    break;
                // 文章
                case 2:
                    // 计算及去重
                    $shareAgentIds = [];
                    $shareArticleIds = [];
                    foreach ($share as $v) {
                        if (empty($currentchildData[$v['agent_id'] . '|' . $v['article_id']])) {
                            $currentchildData[$v['agent_id'] . '|' . $v['article_id']]['shareNum'] = 1;
//                            $shareAgentIds[] = $v['agent_id'];
                            $shareArticleIds[] = $v['article_id'];
                            $currentchildData[$v['agent_id'] . '|' . $v['article_id']]['name'] = $v['nickname'];
                            $currentchildData[$v['agent_id'] . '|' . $v['article_id']]['headimgurl'] = $v['headimgurl'];
                        } else {
                            $currentchildData[$v['agent_id'] . '|' . $v['article_id']]['shareNum']++;
                        }
                    }
//                    // 获取分享者的信息
//                    $shareAgentIds = array_unique($shareAgentIds);
//                    $agentInfo = $this->db->Name('xcx_agent_user')->select("id, nickname, headimgurl")->where_in('id', $shareAgentIds)->execute();
                    // 获取分享的文章详情
                    $shareArticleIds = array_unique($shareArticleIds);
                    $articleInfo = $this->db->Name('xcx_article_article')->select("id, title, cover, comments_num, aid")->where_in('id', $shareArticleIds)->execute();
                    // 分发作者信息和文章信息及完成浏览量查询
                    $articleAuth = [];// 文章作者
                    foreach ($currentchildData as $key => $val) {
                        $keyArr = explode('|', $key);
//                        // 头像昵称
//                        if(!empty($agentInfo)) {
//                            foreach ($agentInfo as $vv) {
//                                if($vv['id'] == $keyArr['0']) {
//                                    $currentchildData[$key]['name'] = $vv['nickname'];
//                                    $currentchildData[$key]['headimgurl'] = $vv['headimgurl'];
//                                    continue;
//                                }
//                            }
//                        }
                        // 文章信息
                        if (!empty($articleInfo)) {
                            foreach ($articleInfo as $vvv) {
                                if ($vvv['id'] == $keyArr['1']) {
                                    $currentchildData[$key]['id'] = $vvv['id'];
                                    $currentchildData[$key]['title'] = $vvv['title'];
                                    $currentchildData[$key]['cover'] = $vvv['cover'];
                                    $currentchildData[$key]['comments_num'] = $vvv['comments_num'];
                                    $currentchildData[$key]['aid'] = $vvv['aid'];
                                    $articleAuth[] = $vvv['aid'];
                                    continue;
                                }
                            }
                        }
                        // 浏览量
                        $currentchildData[$key]['viewNum'] = $this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id', $keyArr['0'])->where_equalTo('article_id', $keyArr['1'])->where_equalTo('browse_type', 2)->where_greatThanOrEqual('start_time', $startTime)->where_lessThanOrEqual('start_time', $endTime)->firstColumn();
                    }
                    // 获取文章作者
                    $articleAuth = array_unique($articleAuth);
                    $adminRow = (new Query())->Name('admin')->select("id, name, img")->where_in('id', $articleAuth)->execute();
                    // 分发文章作者
                    foreach ($currentchildData as $ck => $cv) {
                        $currentchildData[$ck]['aname'] = "九房网";
                        $currentchildData[$ck]['aimg'] = "/upload/default/default_head.png";
                        if (!empty($adminRow)) {
                            foreach ($adminRow as $ak => $av) {
                                if ($cv['aid'] == $av['id']) {
                                    $currentchildData[$ck]['aname'] = $av['name'];
                                    $currentchildData[$ck]['aimg'] = $av['img'];
                                }
                            }
                        }
                    }
                    break;
                // 楼盘
                case 3:
                    // 计算及去重
                    $shareAgentIds = [];
                    $shareBuildingsIds = [];
                    foreach ($share as $v) {
                        if (empty($currentchildData[$v['agent_id'] . '|' . $v['building_id']])) {
                            $currentchildData[$v['agent_id'] . '|' . $v['building_id']]['shareNum'] = 1;
//                            $shareAgentIds[] = $v['agent_id'];
                            $shareBuildingsIds[] = $v['building_id'];
                            $currentchildData[$v['agent_id'] . '|' . $v['building_id']]['name'] = $v['nickname'];
                            $currentchildData[$v['agent_id'] . '|' . $v['building_id']]['headimgurl'] = $v['headimgurl'];
                        } else {
                            $currentchildData[$v['agent_id'] . '|' . $v['building_id']]['shareNum']++;
                        }
                    }
//                    // 获取分享者的信息
//                    $shareAgentIds = array_unique($shareAgentIds);
//                    $agentInfo = $this->db->Name('xcx_agent_user')->select("id, nickname, headimgurl")->where_in('id', $shareAgentIds)->execute();
                    // 获取楼盘信息
                    $shareBuildingsIds = array_unique($shareBuildingsIds);
                    $buildingInfo = $this->db->Name('xcx_building_building')->select("id, pic, name, house_type, city, area, sales_status, flag, fold, commission")->where_in('id', $shareBuildingsIds)->execute();
                    // 分发楼盘信息及完成浏览量查询
                    foreach ($currentchildData as $key => $val) {
                        $keyArr = explode('|', $key);
//                        // 头像昵称
//                        if(!empty($agentInfo)) {
//                            foreach ($agentInfo as $vv) {
//                                if($vv['id'] == $keyArr['0']) {
//                                    $currentchildData[$key]['name'] = $vv['nickname'];
//                                    $currentchildData[$key]['headimgurl'] = $vv['headimgurl'];
//                                    continue;
//                                }
//                            }
//                        }
                        // 楼盘信息
                        if (!empty($buildingInfo)) {
                            foreach ($buildingInfo as $vvv) {
                                if ($vvv['id'] == $keyArr['1']) {
                                    $currentchildData[$key]['id'] = $vvv['id'];
                                    $currentchildData[$key]['pic'] = $vvv['pic'];
                                    $currentchildData[$key]['name'] = $vvv['name'];
                                    $currentchildData[$key]['house_type'] = $vvv['house_type'];
                                    $currentchildData[$key]['city'] = $vvv['city'];
                                    $currentchildData[$key]['area'] = $vvv['area'];
                                    $currentchildData[$key]['sales_status'] = $vvv['sales_status'];
                                    $currentchildData[$key]['flag'] = empty($vvv['flag']) ? [] : explode(',', $vvv['flag']);
                                    $currentchildData[$key]['fold'] = (float)$vvv['fold'];
                                    $currentchildData[$key]['commission'] = (float)$vvv['commission'];
                                    continue;
                                }
                            }
                        }
                        // 浏览量
                        $currentchildData[$key]['viewNum'] = $this->db->Name('xcx_user_browsing_history')->select('count(*)')->where_equalTo('agent_id', $keyArr['0'])->where_equalTo('building_id', $keyArr['1'])->where_equalTo('browse_type', 2)->where_greatThanOrEqual('start_time', $startTime)->where_lessThanOrEqual('start_time', $endTime)->firstColumn();
                    }
                    break;
                default:
                    return ['code' => 0, 'msg' => "具体类型错误"];
                    break;
            }
            return $currentchildData;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    // 获取的报备流程数据
    protected function getReportDataNew($userInfo, $type, $selectType, $searchIds, $name, $nameType, $startTime, $endTime, $isSelf, $page, $pageSize, $orderBy)
    {
        try {
            $param = [
                'userInfo'  => $userInfo,
                'type'      => $type,
                'searchIds' => $searchIds,
                'name'      => $name,
                'nameType'  => $nameType,
                'startTime' => $startTime,
                'endTime'   => $endTime,
                'isSelf'    => $isSelf,
                'page'      => $page,
                'pageSize'  => $pageSize,
                'orderBy'   => $orderBy,
            ];

            switch ($selectType) {
                // 提交
                case 1:
                    $data = $this->getReportDataSubmit($param);
                    break;
                // 审批
                case 2:
                    $data = $this->getReportDataExamine($param);
                    break;
                default:
                    return ['code' => 0, 'msg' => '查看类型有误'];
                    break;
            }

            return $data;
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 获取我的网店（即报备提交单）
    protected function getReportDataSubmit($param)
    {
        try {
            $agentId = $this->agentId;
            $saId = $this->saId;
            $userInfo = $param['userInfo'];

            if (in_array($userInfo['type'], $this->manager)) {
                return ['code' => 0, "msg" => '组长无报备功能'];
            }

            if (1 == $userInfo['type']) {// 店长需要搜索所辖店员(包括自己)
                $agents = $this->db->Name("xcx_store_agent")->select("said, agent_id")->where_equalTo('store_id', $userInfo['storeInfo']['store_id'])->execute();
                if (empty($agents)) {
                    return ['code' => 0, '店长数据错误'];
                }
            }

            $fields = "br.id, br.said, br.agent_type, br.status_type, br.examine_said, br.examine_type, br.update_time, br.user_name, br.user_gender, br.take_time, sa.agent_name as sa_name, sa.agent_img as sa_img, au.nickname as agent_nickname, au.headimgurl, au.name as agent_name, au.sex, u.nickName as customer_nickname, u.avatarUrl, bb.name as building_name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission,bb.store_manager_commission,bb.team_member_commission, bb.protect_set, rrl.is_read";
            $dbSQL = $this->db->Name('xcx_building_reported')
                ->select($fields, "br")
                ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                ->leftJoin("xcx_agent_user", "au", "au.id=sa.agent_id")
                ->leftJoin("xcx_user", "u", "br.user_id=u.id")
                ->leftJoin('xcx_report_read_log', 'rrl', "(rrl.report_id=br.id and rrl.said={$saId})");

            switch ($userInfo['type']) {
                // 店员
                case 0:
                    if (!empty($param['name'])) {
                        if (1 == $param['nameType']) {// 搜索姓名
                            $dbSQL->where_like("br.user_name", "%{$param['name']}%");
                        } elseif (2 == $param['nameType']) {// 搜索楼盘
                            $dbSQL->where_like("bb.name", "%{$param['name']}%");
                        }
                    }
                    $dbSQL->where_express('(br.agent_id=:agentId or br.said=:said)', [':agentId' => $agentId, ':said' => $saId]);
                    break;
                // 店长
                case 1:
                    if (!empty($param['name'])) {
                        if (1 == $param['nameType']) {// 搜索姓名
                            $dbSQL->where_express(
                                "(br.user_name like :name or au.nickname like :name or au.name like :name or u.nickName like :name)",
                                [':name' => "%{$param['name']}%"]
                            );
                        } elseif (2 == $param['nameType']) {// 搜索楼盘
                            $dbSQL->where_like("bb.name", "%{$param['name']}%");
                        }
                    }

                    $agentSaids = array_column($agents, 'said');// 店铺所有成员ID
                    if ($param['searchIds']) {// 是否搜索了店员
                        $dbSQL->where_in('br.said', $param['searchIds'])->where_in('br.said', $agentSaids);
                    } else {
                        switch ($param['isSelf']) {
                            // 看自己
                            case 1:
                                $dbSQL->where_express('(br.agent_id=:agentId or br.said=:said)', [':agentId' => $agentId, ':said' => $saId]);
                                break;
                            // 看别人
                            case 2:
                                $dbSQL->where_in('br.said', $agentSaids)->where_notEqualTo('br.said', $saId);
                                break;
                            // 看全部
                            case 0:
                            default:
                                list($in, $val) = $this->buildWhereIn(':said', $agentSaids);
                                $paramArr = array_merge([':agentId' => $agentId], $val);
                                $dbSQL->where_express("(br.agent_id=:agentId or br.said in {$in})", $paramArr);
                                break;
                        }
                    }
                    break;
                // 组员 20-07-13 改为不可报备
//                case 2:
//                    if(!empty($param['name'])) {
//                        if(1 == $param['nameType']) {// 搜索姓名
//                            $dbSQL->where_like("br.user_name", "%{$param['name']}%");
//                        } elseif(2 == $param['nameType']) {// 搜索楼盘
//                            $dbSQL->where_like("bb.name", "%{$param['name']}%");
//                        }
//                    }
//                    $dbSQL->where_express('(br.agent_id=:agentId or br.said=:said)', [':agentId' => $agentId, ':said' => $saId]);
//                    break;
                // 非法身份和组长财务没有提交报备功能，无法查看该类型
                default:
                    $dbSQL->reset();
                    return ['code' => 0, 'msg' => '非法身份'];
                    break;
            }

            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $dbSQL->where_greatThanOrEqual('br.update_time', $param['startTime'])->where_lessThanOrEqual('br.update_time', $param['endTime']);
            }

            $result = $dbSQL->where_equalTo("br.status_type", $param['type'])
                ->orderBy('br.update_time', $param['orderBy'])
                ->page($param['page'], $param['pageSize'])
                ->execute();

            // $count = $this->db->getDb()->Exec('select FOUND_ROWS()');
            // var_dump($count);

            $agentType = $this->getStoreType();
            $data = [];
            if (!empty($result)) {
                foreach ($result as $k => $v) {
                    $data[$k]['id'] = $v['id'];
                    $data[$k]['status_type'] = $v['status_type'];
                    $data[$k]['examine_type'] = $v['examine_type'];
                    $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                    $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];
                    $data[$k]['is_read'] = !empty($v['is_read']) ? 1 : 0;
                    // 经纪人信息
                    $defaultAgentImg = $this->manImg;// 默认头像
                    if (isset($v['sex']) && 2 == $v['sex']) {
                        $defaultAgentImg = $this->womanImg;
                    }
                    $data[$k]['nickname'] = empty($v['agent_name']) ? empty($v['sa_name']) ? empty($v['agent_nickname']) ? "" : $v['agent_nickname'] : $v['sa_name'] : $v['agent_name'];
                    $data[$k]['headimgurl'] = empty($v['headimgurl']) ? empty($v['sa_img']) ? $defaultAgentImg : $v['sa_img'] : $v['headimgurl'];
                    $data[$k]['position'] = $agentType[$v['agent_type']];
                    // 客户信息
                    $defaultCustomerImg = $this->manImg;// 默认头像
                    if (isset($v['user_gender']) && 2 == $v['user_gender']) {
                        $defaultCustomerImg = $this->womanImg;
                    }
                    $data[$k]['customer_name'] = empty($v['user_name']) ? empty($v['customer_nickname']) ? "" : $v['customer_nickname'] : $v['user_name'];
                    $data[$k]['customer_img'] = !empty($v['avatarUrl']) ? $v['avatarUrl'] : $defaultCustomerImg;
                    $data[$k]['customer_position'] = "客户";
                    // 楼盘信息
                    $data[$k]['name'] = $v['building_name'];
                    $data[$k]['cover'] = $v['cover'];
                    $data[$k]['house_type'] = $v['house_type'];
                    $data[$k]['city'] = $v['city'];
                    $data[$k]['area'] = $v['area'];
                    $data[$k]['sales_status'] = $v['sales_status'];
                    $data[$k]['fold'] = $v['fold'];
                    $data[$k]['commission'] = $v['commission'];
//                    switch ($userInfo['type']){ //根据不同角色显示不同佣金
//                        case 0:
//                            $data[$k]['commission'] = $v['commission'];
//                            break;
//                        case 1:
//                            $data[$k]['commission'] = $v['store_manager_commission'];
//                            break;
//                        default:
//                            $data[$k]['commission'] = $v['team_member_commission'];
//                            break;
//                    }

                    $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);

                    // 其他处理
//                    if(1 == $v['status_type']) {
//                        $updateTime = $v['take_time'];
//                    } else {
//                        $updateTime = $v['update_time'];
//                    }
                    $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);

                    // 当前记录状态如果与所查询状态一致，则判断是否过期
//                    $protect = json_decode($v['protect_set'], TRUE);
                    if ($v['status_type'] >= 1 && $v['status_type'] <= 3) {
                        $paramCheck = [
                            'status'      => $v['status_type'],
                            'take_time'   => $v['take_time'],
                            'update_time' => $v['update_time'],
                            'protect_set' => $v['protect_set'],
                        ];
                        $checkProtect = $this->checkProtectTime($paramCheck);
                        if (!$checkProtect) {
                            $data[$k]['examine_type'] = -2;
                        }
//                        //每个流程环节保护时间-规范到小时
//                        $keyStatus = 'status' . $v['status_type'] . '_hours';
//                        $protect_set_hours = intval($protect[$keyStatus]);
//                        if(1 == $v['status_type']) {
//                            $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
//                        } else {
//                            $protect_time = $protect_set_hours * 3600;
//                        }
//                        $protectTimeEnd = $updateTime + $protect_time;//按最后的更新时间-小时
//                        if($protectTimeEnd <= time()){
//                            $data[$k]['examine_type'] = -2;
//                        }
                    }

                    // 是否自己提交
                    if ($saId == $v['said'] || $agentId == $v['agent_id']) {
                        $data[$k]['isSelf'] = 1;
                    } else {
                        $data[$k]['isSelf'] = 0;
                    }
                }
            }
            return $data;
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 获取我审批的报备
    protected function getReportDataExamine($param)
    {
        try {
            $agentId = $this->agentId;
            $saId = $this->saId;
            $adminId = $this->adminId;
            $leaderId = $this->builddingLeader;

            $userInfo = $param['userInfo'];

            // 判断权限(工作人员/工作组长)
            if (!in_array($userInfo['type'], $this->worker)) {
                return ['code' => 0, 'msg' => '您不是工作人员'];
            }
            if (empty($userInfo['manageinfo']['auth_report_types']) || !is_array($userInfo['manageinfo']['auth_report_types'])) {
                $power = [];
            } else {
                $power = $userInfo['manageinfo']['auth_report_types'];
            }

            if (in_array($param['type'], $this->chargeType) && !in_array($userInfo['type'], $this->channelUser)) {
                // 第四环节只有项目负责人可查看
                // 现在改为渠道也可查看
//                if(empty($leaderId)) {
                return ['code' => 0, 'msg' => '无该类型权限'];
//                }
            } else {
                if (!in_array($param['type'], $power)) {
                    return ['code' => 0, 'msg' => '无该类型权限'];
                }
            }

            if (in_array($userInfo['type'], $this->manager)) {// 项目/渠道组长需要搜索所辖组员
                switch ($userInfo['type']) {
                    // 项目组长
                    case 3:
                        $workType = 2;// 项目组员
                        break;
                    // 渠道组长
                    case 6:
                        $workType = 5;// 渠道组员
                        break;
                    default:
                        return ['code' => 0, 'msg' => '身份异常'];
                        break;
                }
                if (!empty($userInfo['mgid'])) {
                    $myWork = $this->db->Name('xcx_store_agent')->select("said, agent_id")->where_in('mgid', $userInfo['mgid'])->where_equalTo('type', $workType)->execute();
                    if (empty($myWork)) {
                        return ['code' => 0, 'msg' => '未搜索到组员'];
                    }
                } else {
                    return ['code' => 0, 'msg' => '工作组错误'];
                }
            }

            // 渠道组员，需要先找出管辖店铺
            if (5 == $userInfo['type']) {
                $store = $this->db->Name('xcx_store_store')
                    ->select('id')
                    ->where_equalTo('aid', $adminId)
//                                ->where_equalTo('is_delete', 0)
                    ->execute();
                if (!empty($store)) {
                    $storeIds = array_column($store, 'id');
                    $storeIds = array_unique($storeIds);
                }
            }

            // 是否有下一步权限
            $isNext = TRUE;
            $type = $param['type'];
            $nextType = $type + 1;
            if (!in_array($nextType, $power)) {
                $isNext = FALSE;
            }

            if (3 == $userInfo['type'] && !empty($param['name']) && 1 == $param['nameType']) {// 当前为组员时且要搜索名字时，需转换为searchId
                $saidUser = $this->db->name('xcx_store_agent')
                    ->select('sa.said, sa.agent_id', "sa")
                    ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                    ->where_express("(au.nickname like :name or au.name like :name or sa.agent_name like :name)", [":name" => "%{$param['name']}%"])
                    ->execute();
                if (!empty($saidUser)) {
                    $saidUserIds = array_column($saidUser, 'said');
                    $searchIds = array_merge($param['searchIds'], $saidUserIds);
                    $param['searchIds'] = array_unique($searchIds);
                }
            }

            $field = "br.id, br.said, br.agent_type, br.status_type, rl.examine_said, br.examine_type, br.update_time, br.take_time, bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission,bb.store_manager_commission,bb.team_member_commission, bb.protect_set";
            $dbSQL = $this->db->Name('xcx_building_reported')
                ->select($field, "br")
                ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                ->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ");
//                            ->leftJoin('xcx_report_read_log', 'rrl', "(rrl.report_id=br.id and rrl.said={$saId})");

            switch ($userInfo['type']) {
                // 项目组员
                case 2:
                    /**
                     * 通过的情况，查找状态A的意思为，谁审核了状态A的订单，即谁将该订单由A改为下一状态B,而log表记录的是变更后状态，所以搜索状态A在log表中即为搜状态B
                     * 驳回的情况，log记录状态仍为A，examine_type为-1
                     * 因为环节1存在两个状态，所以搜索时，不能直接搜2，而是1,2都要搜
                     */
                    if (1 == $param['type']) {
                        list($typeRlStr, $typeRlVal) = $this->buildWhereIn(':rlType', [1, 2]);
                        $typeRlStr = "rl.status_type in {$typeRlStr}";
                    } else {
                        $typeRlStr = "(rl.status_type=:rlType or (rl.status_type=:nType and rl.examine_type=:nEtype))";
                        $typeRlVal = [':rlType' => $nextType, ':nType' => $type, ':nEtype' => -1];
                    }
                    /**
                     * 当有下一环节权限时，当前环节只需要展示需要审批的信息，用等于能把不是所选环节的记录筛掉
                     * 当没有下一环节权限时，需要展示被我处理该环节的报备记录，但是既然已经处理过此时环节必然不是当前所选环节，所以用大于使其显现
                     */
                    if ($isNext) {// 有下一环节权限
                        $typeBrStr = "br.status_type=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    } else {
                        $typeBrStr = "br.status_type>=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    }
                    $typeStr = "{$typeRlStr} and {$typeBrStr}";// 合并上面两种判断-用于找出历史处理过的，不在该环节的订单
                    $typeVal = array_merge($typeRlVal, $typeBrVal);

                    $brTypeStr = "br.status_type=:brType";// 用于找出当前处于该环节，尚未处理的订单
                    $brTypeVal = [':brType' => $type];

                    if (-1 != $userInfo['manageinfo']['building_ids']) {
                        // 绑定了楼盘
                        if (0 != $userInfo['manageinfo']['building_ids']) {// 没绑定所有楼盘
                            $buildings = explode(',', $userInfo['manageinfo']['building_ids']);
                            list($in, $val) = $this->buildWhereIn(':building', $buildings);// 该组员绑定的楼盘
                            list($inAgent, $valAgent) = $this->buildWhereIn(':agentType', $this->nomalAgent);// 由经纪人提交的报备单
                            /**
                             * 当报备单属于
                             * 该用户所绑定的楼盘且由经纪人提交时
                             * 或
                             * 之前已有该用户审核过当前查询环节时
                             */
                            $sqlStr = "((br.building_id in {$in} and {$brTypeStr} and br.agent_type in {$inAgent}) or ((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr} ))";
                            $sqlArr = [
                                ':examineSaid' => $saId,
                                ':examineAid'  => $agentId,
                            ];
                            $sqlArr = array_merge($sqlArr, $val, $typeVal, $brTypeVal, $valAgent);
                        } else {
                            // 当绑定所有楼盘时，相较于绑定部分，去掉楼盘条件
                            list($inAgent, $valAgent) = $this->buildWhereIn(':agentType', $this->nomalAgent);
                            $sqlStr = "(({$brTypeStr} and br.agent_type in {$inAgent}) or ((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr} ))";
                            $sqlArr = [
                                ':examineSaid' => $saId,
                                ':examineAid'  => $agentId,
                            ];
                            $sqlArr = array_merge($sqlArr, $typeVal, $brTypeVal, $valAgent);
                        }
                    } else {
                        // 未绑定楼盘则查找处理过的
                        $sqlStr = "((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr})";
                        $sqlArr = [
                            ':examineSaid' => $saId,
                            ':examineAid'  => $agentId,
                        ];
                        $sqlArr = array_merge($sqlArr, $typeVal);
                    }

                    // 去掉当前账号的申请
//                    $dbSQL->where_notEqualTo('br.agent_id', $agentId);
                    $dbSQL->where_notEqualTo('br.said', $saId);

                    if (!empty($sqlStr) && !empty($sqlArr)) {
                        $dbSQL->where_express($sqlStr, $sqlArr);
                    }
                    if (!empty($param['searchIds'])) {
                        $dbSQL->where_in("br.agent_id", $param['searchIds']);
                    }
                    if (!empty($param['name'])) {
                        if (1 == $param['nameType']) {// 搜索名字
                            $dbSQL->where_express('(au.nickname like :name or au.name like :name or sa.agent_name like :name)', [':name' => "%{$param['name']}%"]);
                        } elseif (2 == $param['nameType']) {// 搜索楼盘
                            $dbSQL->where_like('bb.name', "%{$param['name']}%");
                        }
                    }
                    break;
                // 渠道组员
                case 5:
                    /**
                     * 历史报备单条件
                     */
                    if (1 == $param['type']) {
                        list($typeRlStr, $typeRlVal) = $this->buildWhereIn(':rlType', [1, 2]);
                        $typeRlStr = "rl.status_type in {$typeRlStr}";
                    } else {
                        $typeRlStr = "(rl.status_type=:rlType or (rl.status_type=:nType and rl.examine_type=:nEtype))";
                        $typeRlVal = [':rlType' => $nextType, ':nType' => $type, ':nEtype' => -1];
                    }
                    if ($isNext) {// 有下一环节权限
                        $typeBrStr = "br.status_type=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    } else {
                        $typeBrStr = "br.status_type>=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    }
                    $typeStr = "{$typeRlStr} and {$typeBrStr}";// 合并上面两种判断-用于找出历史处理过的，不在该环节的订单
                    $typeVal = array_merge($typeRlVal, $typeBrVal);

                    $strOp = "((rl.examine_said=:saId or rl.examine_aid=:aId) and {$typeStr})";
                    $typeVal = array_merge([':saId' => $saId, ':aId' => $agentId], $typeVal);

                    /**
                     * 当前报备单条件
                     * 用于找出当前处于该环节，尚未处理的订单
                     */
                    $strWait = '';
                    $valBind = [];
                    $valAgent = [];

                    // 绑定的店铺
                    if (!empty($storeIds)) {// 店铺所属成员的报备单
                        list($strStore, $valBind) = $this->buildWhereIn(':store', $storeIds);
                        $strBind = "sa.store_id in {$strStore}";
                    }
                    if (!empty($strBind)) {// 如果当前账号没有绑定楼盘或店铺，那么就无法查看任何未审批的报备单
                        list($strAgent, $valAgent) = $this->buildWhereIn(':agentType', $this->nomalAgent);// 普通经纪人
                        $strWait = "({$strBind} and br.status_type = {$type} and br.agent_type in {$strAgent}) or";
                    }

                    /**
                     * 历史和当前条件合并
                     */
                    $sqlStr = "({$strWait} {$strOp})";
                    $sqlArr = array_merge($typeVal, $valBind, $valAgent);

                    // 去掉当前账号的申请
//                    $dbSQL->where_notEqualTo('br.agent_id', $agentId);
                    $dbSQL->where_notEqualTo('br.said', $saId);

                    if (!empty($sqlStr) && !empty($sqlArr)) {
                        $dbSQL->where_express($sqlStr, $sqlArr);
                    }
                    if (!empty($param['searchIds'])) {
                        $dbSQL->where_in("br.agent_id", $param['searchIds']);
                    }
                    if (!empty($param['name'])) {
                        if (1 == $param['nameType']) {// 搜索名字
                            $dbSQL->where_express('(au.nickname like :name or au.name like :name or sa.agent_name like :name)', [':name' => "%{$param['name']}%"]);
                        } elseif (2 == $param['nameType']) {// 搜索楼盘
                            $dbSQL->where_like('bb.name', "%{$param['name']}%");
                        }
                    }
                    break;
                // 项目/渠道 组长
                case 3:
                case 6:
                    if (!empty($param['searchIds'])) {// 筛选工作人员
                        $param['isSelf'] = 0;
                        $myWorkIds = $param['searchIds'];
                    } else {
                        $myWorkIds = array_column($myWork, 'said');
                    }

                    if ($isNext) {// 有下一环节权限
                        /**
                         * 由组员提交的订单的条件
                         * 只有两种情况，有或没有下一环节的权限
                         * 有下一环节权限时，查看什么环节就只展示什么环节的单
                         * 没有下一环节权限时，要把已处理过的、不属于当前环节的单也找出
                         */
                        $brTypeStr = "br.status_type=:brType";

                        /**
                         * 由组员审核的订单的条件
                         * 通过的情况下，查组员审核A环节的单=查被组员由A状态改为下一环节B状态的单，又因为log表记录改后环节B，所以查看组员审核的A环节单就是查当前环节为B的单
                         * 驳回的情况下，环节未变更，只是examine_type为-1
                         */
                        if (1 == $type) {
                            list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                            $typeStr = "rl.status_type in {$typeStr} and br.status_type in {$typeStr}";
                        } else {
                            $typeStr = "((rl.status_type=:rlType and rl.examine_type=1 and br.status_type=:rlBrType and br.examine_type<>-1 and br.examine_type<>2) or (rl.status_type=:fType  and rl.examine_type=-1 and br.status_type=:fBrType and br.examine_type=-1))";
                            $typeVal = [':rlType' => $nextType, ':rlBrType' => $nextType, ':fType' => $type, ':fBrType' => $type];
                        }
                    } else {
                        $brTypeStr = "br.status_type>=:brType";

                        if (1 == $type) {
                            list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                            $typeStr = "rl.status_type in {$typeStr} and br.status_type>=:rlBrType";
                            $typeVal = array_merge($typeVal, [':rlBrType' => $type]);
                        } elseif (6 == $type) {
                            $typeStr = "(rl.status_type = 6 and rl.examine_type = 2 and br.status_type>=:rlBrType and br.examine_type = 2)";
                            $typeVal = [':rlBrType' => $type];
                        } else {
                            $typeStr = "((rl.status_type=:rlType and rl.examine_type<>-1 and br.status_type>=:rlBrType) or (rl.status_type=:fType and rl.examine_type=-1 and br.status_type>=:fBrType))";
                            $typeVal = [':rlType' => $nextType, ':rlBrType' => $nextType, ':fType' => $type, ':fBrType' => $type];
                        }
                    }
                    $brTypeVal = [':brType' => $type];

                    switch ($param['isSelf']) {
                        // 看自己(下辖组员提交的需要自己审批的报备单)
                        case 1:
                            $dbSQL->where_in('br.said', $myWorkIds);
                            $dbSQL->where_express($brTypeStr, $brTypeVal);
                            break;
                        // 看别人(下辖组员审核的报备单)
                        case 2:
                            $dbSQL->where_in('rl.examine_said', $myWorkIds);
                            $dbSQL->where_express($typeStr, $typeVal);
                            $dbSQL->where_notEqualTo('rl.examine_said', $saId);// 去掉自己审核的
                            list($selfStr, $selfVal) = $this->buildWhereIn(':selfRecord', $myWorkIds);
                            $dbSQL->where_express("(br.said not in {$selfStr})", $selfVal);// 去掉组员自己申请的
                            break;
                        // 看全部
                        case 0:
                        default:
                            list($inSaid, $whereValSaid) = $this->buildWhereIn(':said', $myWorkIds);// 下辖组员提交的报备单
                            list($inExamine, $whereValExamine) = $this->buildWhereIn(':examineSaid', $myWorkIds);// 下辖组员审核的报备单
                            $whereVal = array_merge($whereValSaid, $whereValExamine, $typeVal, $brTypeVal);
                            $dbSQL->where_express(
                                "((br.said in {$inSaid} and {$brTypeStr}) or (rl.examine_said in {$inExamine} and {$typeStr}))",
                                $whereVal
                            );
                            break;
                    }

                    if (!empty($param['name'])) {
                        if (2 == $param['nameType']) {
                            $dbSQL->where_like('bb.name', "%{$param['name']}%");
                        }
                    }
                    break;
                default:
                    $dbSQL->reset();
                    return ['code' => 0, 'msg' => '身份有误'];
                    break;
            }

            $dbSQL->groupBy('br.id');

            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $dbSQL->where_greatThanOrEqual('rl.updated_at', $param['startTime'])->where_lessThanOrEqual('rl.updated_at', $param['endTime']);
            }

            $result = $dbSQL->orderBy('br.update_time', $param['orderBy'])->page($param['page'], $param['pageSize'])->execute();

            $data = [];
            $agentInfo = [];
            $agentType = $this->getStoreType();
            if (!empty($result)) {
                $reportSaids = array_column($result, 'said');
                $examineSaids = array_column($result, 'examine_said');
                $saidArr = array_merge($reportSaids, $examineSaids);
                $saidArr = array_unique($saidArr);
                $saidAgents = $this->db->name('xcx_store_agent')
                    ->select('sa.said, sa.agent_name, sa.agent_img, sa.type, au.name, au.nickname, au.headimgurl, au.sex', "sa")
                    ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                    ->where_in('said', $saidArr)
                    ->execute();
                if (!empty($saidAgents)) {
                    foreach ($saidAgents as $val) {
                        $defaultImg = $this->manImg;
                        if (isset($val['sex']) && 3 == $val['sex']) {
                            $defaultImg = $this->womanImg;
                        }
                        $agentInfo[$val['said']]['name'] = empty($val['name']) ? empty($val['nickname']) ? empty($val['agent_name']) ? "" : $val['agent_name'] : $val['nickname'] : $val['name'];
                        $agentInfo[$val['said']]['img'] = empty($val['headimgurl']) ? empty($val['agent_img']) ? $defaultImg : $val['agent_img'] : $val['headimgurl'];
                        $agentInfo[$val['said']]['type'] = $val['type'];
                    }
                }

                foreach ($result as $k => $v) {
                    $data[$k]['id'] = $v['id'];
//                    if(1 == $v['status_type']) {
//                        $updateTime = $v['take_time'];
//                    } else {
//                        $updateTime = $v['update_time'];
//                    }
                    $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);
                    $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);

                    $data[$k]['status_type'] = $v['status_type'];
                    $data[$k]['examine_type'] = $v['examine_type'];

                    $data[$k]['is_read'] = !empty($v['is_read']) ? 1 : 0;

                    // 当前记录状态如果与所查询状态一致，则判断是否过期
//                    $protect = json_decode($v['protect_set'], TRUE);
                    if ($v['status_type'] == $type && $v['status_type'] >= 1 && $v['status_type'] <= 3) {
                        $paramCheck = [
                            'status'      => $v['status_type'],
                            'take_time'   => $v['take_time'],
                            'update_time' => $v['update_time'],
                            'protect_set' => $v['protect_set'],
                        ];
                        $checkProtect = $this->checkProtectTime($paramCheck);
                        if (!$checkProtect) {
                            $data[$k]['examine_type'] = -2;
                        }
//                        //每个流程环节保护时间-规范到小时
//                        $keyStatus = 'status' . $v['status_type'] . '_hours';
//                        $protect_set_hours = intval($protect[$keyStatus]);
//                        if($protect_set_hours > 0) {// 有设置才判断保护期
//                            if(1 == $v['status_type']) {
//                                $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
//                            } else {
//                                $protect_time = $protect_set_hours * 3600;
//                            }
//                            $protectTimeEnd = $updateTime + $protect_time;//按最后的更新时间-小时
////                        var_dump([$keyStatus, $protectTimeEnd, time()]);
//                            if($protectTimeEnd <= time()){
//                                $data[$k]['examine_type'] = -2;
//                            }
//                        }
                    }

                    if ($v['status_type'] > $type) {// 当前报备记录状态大于操作原状态时，标记为已完成
                        $data[$k]['status_type'] = $type;
                        $data[$k]['examine_type'] = '2';
                    }
                    $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                    $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];

                    // 审核还是审批，目前只有工作组长需要
                    $data[$k]['top_info'] = "";
                    if (3 == $userInfo['type'] || 6 == $userInfo['type']) {
                        if (2 == $v['agent_type']) {
                            $data[$k]['top_info'] = '审批';
                        } else {
                            $data[$k]['top_info'] = '审核';
                        }
                    }
                    // 经纪人
                    $data[$k]['nickname'] = !empty($agentInfo[$v['said']]['name']) ? $agentInfo[$v['said']]['name'] : "";
                    $data[$k]['headimgurl'] = !empty($agentInfo[$v['said']]['img']) ? $agentInfo[$v['said']]['img'] : "";
                    $data[$k]['position'] = isset($agentInfo[$v['said']]['type']) ? $agentType[$agentInfo[$v['said']]['type']] : "";
                    // 审核人
                    if (1 == $v['status_type'] && 1 == $v['examine_type']) {
                        $data[$k]['examine_name'] = "";
                        $data[$k]['examine_img'] = "";
                        $data[$k]['examine_position'] = "";
                    } else {
                        $data[$k]['examine_name'] = !empty($agentInfo[$v['examine_said']]['name']) ? $agentInfo[$v['examine_said']]['name'] : "";
                        $data[$k]['examine_img'] = !empty($agentInfo[$v['examine_said']]['img']) ? $agentInfo[$v['examine_said']]['img'] : "";
                        $data[$k]['examine_position'] = !empty($agentInfo[$v['examine_said']]['type']) ? $agentType[$agentInfo[$v['examine_said']]['type']] : "";
                    }
                    // 楼盘信息
                    $data[$k]['name'] = $v['name'];
                    $data[$k]['cover'] = $v['cover'];
                    $data[$k]['house_type'] = $v['house_type'];
                    $data[$k]['city'] = $v['city'];
                    $data[$k]['area'] = $v['area'];
                    $data[$k]['sales_status'] = $v['sales_status'];
                    $data[$k]['fold'] = $v['fold'];
                    $data[$k]['commission'] = $v['commission'];
//                    switch ($userInfo['type']){ //根据不同角色显示不同佣金
//                        case 0:
//                            $data[$k]['commission'] = $v['commission'];
//                            break;
//                        case 1:
//                            $data[$k]['commission'] = $v['store_manager_commission'];
//                            break;
//                        default:
//                            $data[$k]['commission'] = $v['team_member_commission'];
//                            break;
//                    }
                }
            }

            return $data;

        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 获取项目负责人需要审批的报备单
    public function getChargeReportData()
    {
        try {
            $name = !empty(Context::Post('name')) ? Context::Post('name') : "";
            $searchIds = !empty(Context::Post('search_ids')) ? Context::Post('search_ids') : [];
            $nameType = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $orderBy = !empty(Context::Post('order_by')) ? Context::Post('order_by') : 'asc';// 排序
            $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;// 页码
            $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : self::MYLIMIT;// 每页记录条数

            $startTime = 0;
            $endTime = 0;
            if (!empty(Context::Post('nowDate'))) {
                $nowDate = Context::Post('nowDate');  //搜查询的日期
                $startTime = strtotime($nowDate);    //当天开始时间戳
                $endTime = $startTime + 86400;      //当天结束时间戳
            }

            if ($pageSize > 100) {
                return $this->error('请求数据超出限制');
            }
            if (!in_array($orderBy, ['asc', 'desc'])) {
                $orderBy = 'desc';
            }

            $agentId = $this->agentId;
            $leaderId = $this->builddingLeader;
            $adminId = $this->adminId;

            // 用户信息
            $userInfo = $this->getUserInfo();

            // 权限校验
            if (empty($leaderId) && empty($adminId)) {
                return $this->error('您没有权限', 0, ["currentchildData" => []]);
            }

            // 报备单查询
            $field = "br.id, br.said, br.agent_type, br.status_type, br.examine_type, br.update_time, br.user_gender,
                      sa.agent_name, sa.agent_img, 
                      bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission,bb.store_manager_commission,bb.team_member_commission,
                      au.name as auname, au.nickname, au.headimgurl";
            $dbSQL = $this->db->Name('xcx_building_reported')
                ->select($field, "br")
                ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                ->innerJoin("admin", "a", "bb.aid=a.id")
                ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                ->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ")
//                            ->where_equalTo('bb.aid', $userInfo['admin_id'])
                ->where_greatThanOrEqual("br.status_type", $this->chargeType[0]);

            /**
             * 当前单
             */
            $nowStr = "(bb.aid=:adminId)";
            $nowVal = [":adminId" => $adminId];

            /**
             * 历史单
             */
            $hisStr = "(rl.examine_said=:examineSaid or rl.examine_aid=:examineAid)";
            $hisVal = [':examineSaid' => $leaderId, ':examineAid' => $agentId];

            /**
             * 合并
             */
            $sqlStr = "({$nowStr} or {$hisStr})";
            $sqlVal = array_merge($nowVal, $hisVal);
            $dbSQL->where_express($sqlStr, $sqlVal);

            if (!empty($name)) {
                if (1 == $nameType) {// 搜索名字
                    $dbSQL->where_express('(au.nickname like :name or au.name like :name or sa.agent_name like :name)', [':name' => "%{$name}%"]);
                } elseif (2 == $nameType) {// 搜索楼盘
                    $dbSQL->where_like('bb.name', "%{$name}%");
                }
            }

            if (!empty($searchIds)) {
                $dbSQL->where_in('br.said', $searchIds);
            }

            if (!empty($startTime) && !empty($endTime)) {
                $dbSQL->where_greatThanOrEqual('rl.updated_at', $startTime)->where_lessThanOrEqual('rl.updated_at', $endTime);
            }

            $dbSQL->groupBy('br.id');

            $result = $dbSQL->orderBy('status_type', 'asc')->orderBy('br.update_time', $orderBy)->page($page, $pageSize)->execute();

            // 数据整理
            $data = [];
            $agentInfo = [];
            $agentType = $this->getStoreType();
            if (!empty($result)) {
                foreach ($result as $k => $v) {
                    $data[$k]['id'] = $v['id'];

                    $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);
                    $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);

                    $data[$k]['status_type'] = $v['status_type'];
                    $data[$k]['examine_type'] = $v['examine_type'];

                    if ($v['status_type'] > $this->chargeType[0]) {// 当前报备记录状态大于操作原状态时，标记为已完成
                        $data[$k]['status_type'] = $this->chargeType[0];
                        $data[$k]['examine_type'] = '2';
                    }
                    $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                    $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];

                    // 默认头像
                    if (2 == $v['user_gender']) {
                        $defaultImg = $this->womanImg;
                    } else {
                        $defaultImg = $this->manImg;
                    }

                    // 经纪人
                    $data[$k]['nickname'] = empty($v['agent_name']) ? empty($v['auname']) ? empty($v['nickname']) ? "" : $v['nickname'] : $v['auname'] : $v['agent_name'];
                    $data[$k]['headimgurl'] = empty($v['agent_img']) ? empty($v['headimgurl']) ? $defaultImg : $v['headimgurl'] : $v['agent_img'];
                    $data[$k]['position'] = $agentType[$v['agent_type']];

                    // 楼盘信息
                    $data[$k]['name'] = $v['name'];
                    $data[$k]['cover'] = $v['cover'];
                    $data[$k]['house_type'] = $v['house_type'];
                    $data[$k]['city'] = $v['city'];
                    $data[$k]['area'] = $v['area'];
                    $data[$k]['sales_status'] = $v['sales_status'];
                    $data[$k]['fold'] = $v['fold'];
                    $data[$k]['commission'] = $v['commission'];
//                    switch ($userInfo['type']){ //根据不同角色显示不同佣金
//                        case 0:
//                            $data[$k]['commission'] = $v['commission'];
//                            break;
//                        case 1:
//                            $data[$k]['commission'] = $v['store_manager_commission'];
//                            break;
//                        default:
//                            $data[$k]['commission'] = $v['team_member_commission'];
//                            break;
//                    }
                }
            }
            return $this->success(["currentchildData" => $data]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 0, ["currentchildData" => []]);
        }
    }

    // 获取区域负责人的报备单
    public function getAreaReportData()
    {
        try {
            $name = !empty(Context::Post('name')) ? Context::Post('name') : "";
            $searchIds = !empty(Context::Post('search_ids')) ? Context::Post('search_ids') : [];
            $nameType = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $orderBy = !empty(Context::Post('order_by')) ? Context::Post('order_by') : 'desc';// 排序
            $type = !empty(Context::Post('type')) ? Context::Post('type') : 0;
            $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;// 页码
            $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : self::MYLIMIT;// 每页记录条数

            $startTime = 0;
            $endTime = 0;
            if (!empty(Context::Post('nowDate'))) {
                $nowDate = Context::Post('nowDate');  //搜查询的日期
                $startTime = strtotime($nowDate);    //当天开始时间戳
                $endTime = $startTime + 86400;      //当天结束时间戳
            }

            if ($pageSize > 100) {
                return $this->error('请求数据超出限制');
            }
            if (!in_array($orderBy, ['asc', 'desc'])) {
                $orderBy = 'desc';
            }
            // 用户信息
            $userInfo = $this->getUserInfo();

            if (8 != $userInfo['type']) {
                return $this->error('非法身份');
            }

            $area = $userInfo['storeInfo']['city'];

            $buildingIds = [];
            // 根据区域获取所有该地区的楼盘
            $buildings = $this->db->Name('xcx_building_building')->select("id")->where_like("city", "%{$area}%")->execute();
            if (!empty($buildings)) {
                $buildingIds = array_column($buildings, 'id');
            }

            if (!empty($buildingIds)) {
                $fields = "br.id, br.said, br.examine_said, br.agent_type, br.status_type, br.examine_type, br.update_time, br.user_gender,
                          sa.agent_name, sa.agent_img, 
                          bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission,bb.store_manager_commission,bb.team_member_commission,
                          au.name as auname, au.nickname, au.headimgurl";
                $dbSQL = $this->db->name('xcx_building_reported')
                    ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                    ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                    ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                    ->select($fields, 'br')
                    ->where_in('building_id', $buildingIds)
                    ->where_equalTo('br.status_type', $type);

                // 名称搜索(经纪人 姓名、昵称/楼盘名称)
                if (!empty($name)) {
                    if (1 == $nameType) {// 搜索名字
                        $dbSQL->where_express('(au.nickname like :name or au.name like :name or sa.agent_name like :name)', [':name' => "%{$name}%"]);
                    } elseif (2 == $nameType) {// 搜索楼盘
                        $dbSQL->where_like('bb.name', "%{$name}%");
                    }
                }

                // 审核的工作人员搜索(当前的)
                if (!empty($searchIds)) {
                    $dbSQL->leftJoin('xcx_reported_log', 'rl', 'br.id=rl.report_id');
                    $dbSQL->where_in('rl.examine_said', $searchIds);
                }

                // 时间范围搜索
                if (!empty($param['startTime']) && !empty($param['endTime'])) {
                    $dbSQL->where_greatThanOrEqual('br.update_time', $startTime)->where_lessThanOrEqual('br.update_time', $endTime);
                }

                $dbSQL->groupBy('br.id');

                $result = $dbSQL->orderBy('br.update_time', $orderBy)->orderBy('br.id', $orderBy)->page($page, $pageSize)->execute();

//                var_dump($result);

                $data = [];
                $agentInfo = [];
                $agentType = $this->getStoreType();
                if (!empty($result)) {
                    // 获取申请人和审核人的微信信息
                    $reportSaids = array_column($result, 'said');
                    $examineSaids = array_column($result, 'examine_said');
                    $saidArr = array_merge($reportSaids, $examineSaids);
                    $saidArr = array_unique($saidArr);
                    $saidAgents = $this->db->name('xcx_store_agent')
                        ->select('sa.said, sa.agent_name, sa.agent_img, sa.type, au.name, au.nickname, au.headimgurl, au.sex', "sa")
                        ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                        ->where_in('said', $saidArr)
                        ->execute();
                    if (!empty($saidAgents)) {
                        foreach ($saidAgents as $val) {
                            $defaultImg = $this->manImg;
                            if (isset($val['sex']) && 3 == $val['sex']) {
                                $defaultImg = $this->womanImg;
                            }
                            $agentInfo[$val['said']]['name'] = empty($val['name']) ? empty($val['nickname']) ? empty($val['agent_name']) ? "" : $val['agent_name'] : $val['nickname'] : $val['name'];
                            $agentInfo[$val['said']]['img'] = empty($val['headimgurl']) ? empty($val['agent_img']) ? $defaultImg : $val['agent_img'] : $val['headimgurl'];
                            $agentInfo[$val['said']]['type'] = $val['type'];
                        }
                    }

                    foreach ($result as $k => $v) {
                        $data[$k]['id'] = $v['id'];// 报备单ID

                        $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);// 审核时间
                        $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);

                        $data[$k]['status_type'] = $v['status_type'];
                        $data[$k]['examine_type'] = $v['examine_type'];

                        // 当前记录状态如果与所查询状态一致，则判断是否过期（保护期）
                        if ($v['status_type'] == $type && $v['status_type'] >= 1 && $v['status_type'] <= 3) {
                            $paramCheck = [
                                'status'      => $v['status_type'],
                                'take_time'   => $v['take_time'],
                                'update_time' => $v['update_time'],
                                'protect_set' => $v['protect_set'],
                            ];
                            $checkProtect = $this->checkProtectTime($paramCheck);
                            if (!$checkProtect) {
                                $data[$k]['examine_type'] = -2;
                            }
                        }

                        if ($v['status_type'] > $type) {// 当前报备记录状态大于操作原状态时，标记为已完成
                            $data[$k]['status_type'] = $type;
                            $data[$k]['examine_type'] = '2';
                        }
                        $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                        $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];

                        // 审核还是审批，目前只有工作组长需要
                        $data[$k]['top_info'] = "";
                        if (3 == $userInfo['type'] || 6 == $userInfo['type']) {
                            if (2 == $v['agent_type']) {
                                $data[$k]['top_info'] = '审批';
                            } else {
                                $data[$k]['top_info'] = '审核';
                            }
                        }
                        // 经纪人
                        $data[$k]['nickname'] = !empty($agentInfo[$v['said']]['name']) ? $agentInfo[$v['said']]['name'] : "";
                        $data[$k]['headimgurl'] = !empty($agentInfo[$v['said']]['img']) ? $agentInfo[$v['said']]['img'] : "";
                        $data[$k]['position'] = isset($agentInfo[$v['said']]['type']) ? $agentType[$agentInfo[$v['said']]['type']] : "";
                        // 审核人
                        if (1 == $v['status_type'] && 1 == $v['examine_type']) {
                            $data[$k]['examine_name'] = "";
                            $data[$k]['examine_img'] = "";
                            $data[$k]['examine_position'] = "";
                        } else {
                            $data[$k]['examine_name'] = !empty($agentInfo[$v['examine_said']]['name']) ? $agentInfo[$v['examine_said']]['name'] : "";
                            $data[$k]['examine_img'] = !empty($agentInfo[$v['examine_said']]['img']) ? $agentInfo[$v['examine_said']]['img'] : "";
                            $data[$k]['examine_position'] = !empty($agentInfo[$v['examine_said']]['type']) ? $agentType[$agentInfo[$v['examine_said']]['type']] : "";
                        }
                        // 楼盘信息
                        $data[$k]['name'] = $v['name'];
                        $data[$k]['cover'] = $v['cover'];
                        $data[$k]['house_type'] = $v['house_type'];
                        $data[$k]['city'] = $v['city'];
                        $data[$k]['area'] = $v['area'];
                        $data[$k]['sales_status'] = $v['sales_status'];
                        $data[$k]['fold'] = $v['fold'];
                        $data[$k]['commission'] = $v['commission'];
                    }
                }
            }

            return $this->success(["currentchildData" => $data]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 0, ["currentchildData" => []]);
        }
    }

    // 获取报备流程数据
    protected function getReportData($userInfo, $type, $searchIds, $name, $startTime, $endTime, $isSelf, $page, $pageSize, $orderBy)
    {
        try {
            $sqlAdd = '';// select附加
            $isNext = FALSE;// 是否拥有下一环节的操作权限，工作人员用
            $isWorker = FALSE;// 是否是工作人员

            // 判断权限(工作人员/工作组长)
            if (empty($userInfo['manageinfo']['auth_report_types']) || !is_array($userInfo['manageinfo']['auth_report_types'])) {
                $power = [];
            } else {
                $power = $userInfo['manageinfo']['auth_report_types'];
            }
            if (in_array($userInfo['type'], $this->worker)) {
                $isWorker = TRUE;
                if (!in_array($type, $power)) {
                    if (2 == $userInfo['type']) {
                        $isSelf = 1;// 如果组员没权限，只看自己
                    } else {
                        return ['code' => 0, 'msg' => '无该类型权限'];
                    }
                }

                if (1 != $isSelf) {// 当前身份为工作人员时且查看的不止有自己提交的报备申请时，需要附加搜索
                    $sqlAdd = ', rl.status_type as rlType';
                }

                $nextStatus = $type + 1;
                if (in_array($nextStatus, $power)) {
                    $isNext = TRUE;
                }
            }

            $agentId = $this->agentId;
            $saId = $this->saId;

            // 不同角色取用不同数据
            if (1 == $userInfo['type']) {// 店长需要搜索所辖店员(包括自己)
                $agents = $this->db->Name("xcx_store_agent")->select("said, agent_id")->where_equalTo('store_id', $userInfo['storeInfo']['store_id'])->execute();
                if (empty($agents)) {
                    return ['code' => 0, '店长数据错误'];
                }
            }
            if (in_array($userInfo['type'], $this->manager)) {// 工作组长、财务需要搜索所辖组员
                if (!empty($userInfo['mgid'])) {
                    $myWork = $this->db->Name('xcx_store_agent')->select("said, agent_id")->where_in('mgid', $userInfo['mgid'])->where_equalTo('type', 2)->execute();
                    if (empty($myWork)) {
                        return ['code' => 0, 'msg' => '未搜索到组员'];
                    }
                } else {
                    return ['code' => 0, 'msg' => '工作组错误'];
                }
            }

            $dbSQL = $this->db->Name('xcx_building_reported')
                ->select("br.id, br.said, br.agent_type, br.status_type, br.examine_said, br.examine_type, br.update_time, br.take_time, bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission, bb.protect_set {$sqlAdd}", "br")
                ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id");

            $fieldTime = 'br.update_time';
            // 角色条件不同
            switch ($userInfo['type']) {
                // 店员
                case 0:
                    $dbSQL->where_express('(br.agent_id=:agentId or br.said=:said)', [':agentId' => $agentId, ':said' => $saId])
                        ->where_equalTo("br.status_type", $type);
                    break;
                // 店长
                case 1:
                    if (!empty($searchIds)) {
                        $agentSaids = array_column($agents, 'said');
                        $dbSQL->where_in('br.said', $searchIds)->where_in('br.said', $agentSaids);
                    } else {
                        switch ($isSelf) {
                            // 看自己
                            case 1:
                                $dbSQL->where_express('(br.agent_id=:agentId or br.said=:said)', [':agentId' => $agentId, ':said' => $saId]);
                                break;
                            // 看店员
                            case 2:
                                $agentSaids = array_column($agents, 'said');
                                $dbSQL->where_in('br.said', $agentSaids)->where_notEqualTo('br.said', $saId);
                                break;
                            // 看全部
                            case 0:
                            default:
                                $agentSaids = array_column($agents, 'said');
//                            $agentSaidsStr = implode(',', $agentSaids);
//                            $dbSQL->where_in('br.said', $agentSaids);
                                list($in, $val) = $this->buildWhereIn(':said', $agentSaids);
                                $paramArr = array_merge([':agentId' => $agentId], $val);
                                $dbSQL->where_express("(br.agent_id=:agentId or br.said in {$in})", $paramArr);
                                break;
                        }
                    }
                    $dbSQL->where_equalTo("br.status_type", $type);
                    break;
                // 工作人员
                case 2:
                    if (1 == $type) {
                        list($typeRlStr, $typeRlVal) = $this->buildWhereIn(':rlType', [1, 2]);
                        $typeRlStr = "rl.status_type in {$typeRlStr}";
                    } else {
                        $typeRlStr = "rl.status_type=:rlType ";
                        $typeRlVal = [':rlType' => $type + 1];
                    }
                    if ($isNext) {// 有下一环节权限
                        $typeBrStr = "br.status_type=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    } else {
                        $typeBrStr = "br.status_type>=:rlBrType";
                        $typeBrVal = [':rlBrType' => $type];
                    }
                    $typeStr = "{$typeRlStr} and {$typeBrStr}";
                    $typeVal = array_merge($typeRlVal, $typeBrVal);

                    $brTypeStr = "br.status_type=:brType";
                    $brTypeVal = [':brType' => $type];

                    switch ($isSelf) {
                        // 看自己(需要提给组长审批的自己的申请)
                        case 1:
                            $sqlStr = "((br.said=:saId or br.agent_id=:agentId) and br.status_type=:statusType)";
                            $sqlArr = [
                                ':saId'       => $saId,
                                ':agentId'    => $agentId,
                                ':statusType' => $type,
                            ];
                            break;
                        // 看别人(需要自己审批的经纪人的申请)
                        case 2:
                            $dbSQL->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ");
                            $fieldTime = 'rl.updated_at';
                            $dbSQL->where_notEqualTo('br.agent_id', $agentId);
                            $dbSQL->where_notEqualTo('br.said', $saId);// 去掉当前账号的申请
                            if (-1 != $userInfo['manageinfo']['building_ids']) {
                                // 绑定了楼盘
                                if (0 != $userInfo['manageinfo']['building_ids']) {// 没绑定所有楼盘
                                    $buildings = explode(',', $userInfo['manageinfo']['building_ids']);
                                    list($in, $val) = $this->buildWhereIn(':building', $buildings);
                                    list($inAgent, $valAgent) = $this->buildWhereIn(':agentType', $this->nomalAgent);
                                    $sqlStr = "((br.building_id in {$in} and {$brTypeStr} and br.agent_type in {$inAgent}) or ((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr} ))";
                                    $sqlArr = [
                                        ':examineSaid' => $saId,
                                        ':examineAid'  => $agentId,
                                        ':brType'      => $type,
                                    ];
                                    $sqlArr = array_merge($sqlArr, $val, $typeVal, $valAgent);
                                } else {
                                    $sqlStr = $brTypeStr;
                                    $sqlArr = $brTypeVal;
                                }
                            } else {
                                // 未绑定楼盘则查找处理过的
                                $sqlStr = "((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr})";
                                $sqlArr = [
                                    ':examineSaid' => $saId,
                                    ':examineAid'  => $agentId,
                                ];
                                $sqlArr = array_merge($sqlArr, $typeVal);
                            }
                            break;
                        // 看全部
                        case 0:
                        default:
                            $dbSQL->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ");
                            $fieldTime = 'rl.updated_at';
                            if (-1 != $userInfo['manageinfo']['building_ids']) {
                                // 绑定了楼盘
                                if (0 != $userInfo['manageinfo']['building_ids']) {// 没绑定所有楼盘 (绑定了所有楼盘就所有报备记录都可以看，不需要条件)
                                    $buildings = explode(',', $userInfo['manageinfo']['building_ids']);
                                    list($in, $val) = $this->buildWhereIn(':building', $buildings);
                                    list($inAgent, $valAgent) = $this->buildWhereIn(':agentType', $this->nomalAgent);
                                    $sqlStr = "((br.building_id in {$in} and {$brTypeStr} and br.agent_type in {$inAgent}) or ((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr}) or ((br.said=:saId or br.agent_id=:agentId) and br.status_type=:statusType))";
                                    $sqlArr = [
                                        ':examineSaid' => $saId,
                                        ':examineAid'  => $agentId,
                                        ':saId'        => $saId,
                                        ':agentId'     => $agentId,
                                        ':brType'      => $type,
                                        ':statusType'  => $type,
                                    ];
                                    $sqlArr = array_merge($sqlArr, $val, $typeVal, $brTypeVal, $valAgent);
                                } else {
                                    $sqlStr = $brTypeStr;
                                    $sqlArr = $brTypeVal;
                                }
                            } else {
                                // 未绑定楼盘则查找处理过的
                                $sqlStr = "(((rl.examine_said=:examineSaid or rl.examine_aid=:examineAid) and {$typeStr}) or ((br.said=:saId or br.agent_id=:agentId) and br.status_type=:statusType))";
                                $sqlArr = [
                                    ':examineSaid' => $saId,
                                    ':examineAid'  => $agentId,
                                    ':saId'        => $saId,
                                    ':agentId'     => $agentId,
                                    ':statusType'  => $type,
                                ];
                                $sqlArr = array_merge($sqlArr, $typeVal);
                            }
                            break;
                    }
                    if (!empty($sqlStr) && !empty($sqlArr)) {
                        $dbSQL->where_express($sqlStr, $sqlArr);
                    }
                    if (!empty($searchIds) && 1 != $isSelf) {
                        $dbSQL->where_in("br.agent_id", $searchIds);
                    }
                    $dbSQL->groupBy('br.id');
                    break;
                // 工作组长、财务
                case 3:
                case 4:
                    if (!empty($searchIds)) {// 筛选工作人员
                        $myWorkIds = $searchIds;
                    } else {
                        $myWorkIds = array_column($myWork, 'said');
                    }

                    switch ($isSelf) {
                        // 看组员提交申请（需要自己审批）
                        case 1:
                            $dbSQL->where_in('br.said', $myWorkIds);
                            if ($isNext) {
                                $dbSQL->where_equalTo('br.status_type', $type);
                            } else {
                                $dbSQL->where_greatThanOrEqual('br.status_type', $type);
                            }
                            break;
                        // 看组员审批的申请（组员已审核过的）
                        case 2:
                            $dbSQL->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ");
                            $dbSQL->where_in('rl.examine_said', $myWorkIds);
                            if ($isNext) {// 有下一环节权限
                                if (1 == $type) {
                                    list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                                    $typeStr = "rl.status_type in {$typeStr} and br.status_type in {$typeStr}";
                                } else {
                                    $typeStr = "rl.status_type=:rlType and br.status_type=:rlBrType";
                                    $typeVal = [':rlType' => $type, ':rlBrType' => $type];
                                }
                            } else {
                                if (1 == $type) {
                                    list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                                    $typeStr = "rl.status_type in {$typeStr} and br.status_type>=:rlBrType";
                                    $typeVal = array_merge($typeVal, [':rlBrType' => $type]);
                                } else {
                                    $typeStr = "rl.status_type=:rlType and br.status_type>=:rlBrType";
                                    $typeVal = [':rlType' => $type + 1, ':rlBrType' => $type + 1];
                                }
                            }
                            $dbSQL->where_express($typeStr, $typeVal);
                            $dbSQL->where_notEqualTo('rl.examine_said', $saId);// 去掉自己审核的
                            break;
                        // 看全部
                        case 0:
                        default:
                            if ($isNext) {// 有下一环节权限
                                $brTypeStr = "br.status_type=:brType";
                                if (1 == $type) {
                                    list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                                    $typeStr = "rl.status_type in {$typeStr} and br.status_type in {$typeStr}";
                                } else {
                                    $typeStr = "rl.status_type=:rlType and br.status_type=:rlBrType";
                                    $typeVal = [':rlType' => $type, ':rlBrType' => $type];
                                }
                            } else {
                                $brTypeStr = "br.status_type>=:brType";
                                if (1 == $type) {
                                    list($typeStr, $typeVal) = $this->buildWhereIn(':rlType', [1, 2]);
                                    $typeStr = "rl.status_type in {$typeStr} and br.status_type>=:rlBrType";
                                    $typeVal = array_merge($typeVal, [':rlBrType' => $type]);
                                } else {
                                    $typeStr = "rl.status_type=:rlType and br.status_type>=:rlBrType";
                                    $typeVal = [':rlType' => $type + 1, ':rlBrType' => $type + 1];
                                }
                            }
                            $brTypeVal = [':brType' => $type];

                            $dbSQL->leftJoin("xcx_reported_log", "rl", "rl.report_id=br.id ");
                            $fieldTime = 'rl.updated_at';
//                                $myWorkIdsStr = implode(',', $myWorkIds);
                            list($inSaid, $whereValSaid) = $this->buildWhereIn(':said', $myWorkIds);
                            list($inExamine, $whereValExamine) = $this->buildWhereIn(':examineSaid', $myWorkIds);
                            $whereVal = array_merge($whereValSaid, $whereValExamine, $typeVal, $brTypeVal);
                            $dbSQL->where_express(
                                "((br.said in {$inSaid} and {$brTypeStr}) or (rl.examine_said in {$inExamine} and {$typeStr}))",
                                $whereVal
                            );
                            break;
                    }
                    $dbSQL->groupBy('br.id');
                    break;
                // 其他
                default:
                    break;
            }
            // 客户姓名
            if (!empty($name)) {
                $dbSQL->where_like('br.user_name', "%{$name}%");
            }
            if (!empty($startTime) && !empty($endTime)) {
                $dbSQL->where_greatThanOrEqual($fieldTime, $startTime)->where_lessThanOrEqual($fieldTime, $endTime);
            }

            $data = $dbSQL->orderBy('br.update_time', $orderBy)->page($page, $pageSize)->execute();

            $agentInfo = [];
            $agentType = $this->getStoreType();
            if (!empty($data)) {
                $reportSaids = array_column($data, 'said');
                $examineSaids = array_column($data, 'examine_said');
                $saidArr = array_merge($reportSaids, $examineSaids);
                $saidArr = array_unique($saidArr);
                $saidAgents = $this->db->name('xcx_store_agent')
                    ->select('sa.said, sa.agent_name, sa.agent_img, sa.type, au.nickname, au.headimgurl', "sa")
                    ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                    ->where_in('said', $saidArr)
                    ->execute();
                if (!empty($saidAgents)) {
                    foreach ($saidAgents as $val) {
                        $agentInfo[$val['said']]['name'] = empty($val['nickname']) ? empty($val['agent_name']) ? "" : $val['agent_name'] : $val['nickname'];
                        $agentInfo[$val['said']]['img'] = empty($val['headimgurl']) ? empty($val['agent_img']) ? "" : $val['agent_img'] : $val['headimgurl'];
                        $agentInfo[$val['said']]['type'] = $val['type'];
                    }
                }

                foreach ($data as $k => $v) {
                    if (1 == $v['status_type']) {
                        $updateTime = $v['take_time'];
                    } else {
                        $updateTime = $v['update_time'];
                    }
                    $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);
                    $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);
//                    if($type != $v['status_type']) {
//                        $data[$k]['examine_type'] = 2;
//                    }
                    if ($isWorker) {// 工作人员有权限区分，查看时的信息有所不同
                        if ($v['status_type'] > $type) {// 当前报备记录状态大于操作原状态时，标记为已完成
                            $data[$k]['examine_type'] = '2';
                        }
                    }

                    // 当前记录状态如果与所查询状态一致，则判断是否过期
                    $protect = json_decode($v['protect_set'], TRUE);
                    if ($v['status_type'] == $type && $v['status_type'] >= 1 && $v['status_type'] <= 3) {
                        //每个流程环节保护时间-规范到小时
                        $keyStatus = 'status' . $v['status_type'] . '_hours';
                        $protect_set_hours = intval($protect[$keyStatus]);
                        if (1 == $v['status_type']) {
                            $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
                        } else {
                            $protect_time = $protect_set_hours * 3600;
                        }
                        $protectTimeEnd = $updateTime + $protect_time;//按最后的更新时间-小时
                        if ($protectTimeEnd <= time()) {
                            $data[$k]['examine_type'] = -2;
                        }
                    }

                    // 审核还是审批，目前只有工作组长需要
                    $data[$k]['top_info'] = "";
                    if (3 == $userInfo['type']) {
                        if (2 == $v['agent_type']) {
                            $data[$k]['top_info'] = '审批';
                        } else {
                            $data[$k]['top_info'] = '审核';
                        }
                    }
                    $data[$k]['nickname'] = !empty($agentInfo[$v['said']]['name']) ? $agentInfo[$v['said']]['name'] : "";
                    $data[$k]['headimgurl'] = !empty($agentInfo[$v['said']]['img']) ? $agentInfo[$v['said']]['img'] : "";
                    $data[$k]['position'] = isset($agentInfo[$v['said']]['type']) ? $agentType[$agentInfo[$v['said']]['type']] : "";
                    if (1 == $v['status_type'] && 1 == $v['examine_type']) {
                        $data[$k]['examine_name'] = "";
                        $data[$k]['examine_img'] = "";
                        $data[$k]['examine_position'] = "";
                    } else {
                        $data[$k]['examine_name'] = !empty($agentInfo[$v['examine_said']]['name']) ? $agentInfo[$v['examine_said']]['name'] : "";
                        $data[$k]['examine_img'] = !empty($agentInfo[$v['examine_said']]['img']) ? $agentInfo[$v['examine_said']]['img'] : "";
                        $data[$k]['examine_position'] = !empty($agentInfo[$v['examine_said']]['type']) ? $agentType[$agentInfo[$v['examine_said']]['type']] : "";
                    }
                }
            }
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 构建wherein
    protected function buildWhereIn($key = ':where', $value = [])
    {
        $in = '';
        $whereValue = [];
        if (empty($value)) {
            $value[] = 0;
        }
        foreach ($value as $k => $v) {
            $keyStr = "{$key}_$k,";
            $keyStrT = rtrim($keyStr, ',');
            $in .= $keyStr;
            $whereValue[$keyStrT] = $v;
        }
        $in = rtrim($in, ',');
        $in = "($in)";
        return [$in, $whereValue];
    }

    //获取经纪人用户名头像
    protected function getAgentImgName($agent_id)
    {
        $agentInfo = $this->db->Name('xcx_agent_user')->select()->where_equalTo('id', $agent_id)->firstRow();
        $data[] = empty($agentInfo['name']) ? $agentInfo['nickname'] : $agentInfo['name'];
        $data[] = $agentInfo['headimgurl'];
        return $data;
    }

    //获取经纪人与用户头像名称信息
    public function getPortraitData()
    {
        $user_id = intval(Context::Post('user_id'));    //用户id
        if (empty($user_id)) {
            return $this->error('参数缺失');
        }

        $agent_id = Session::get('agent_id');   //经纪人id
        $agentInfo = $this->db->Name('xcx_agent_user')->select('headimgurl,nickname,name')->where_equalTo('id', $agent_id)->firstRow();
        if (empty($agentInfo)) {
            $agentInfo = [];
        } else {
            $agentInfo['name'] = empty($agentInfo['name']) ? $agentInfo['nickname'] : $agentInfo['name'];
        }
        $data['agentInfo'] = $agentInfo;
        $userInfo = $this->db->Name('xcx_user')->select('avatarUrl,nickName')->where_equalTo('id', $user_id)->firstRow();
        if (empty($userInfo)) {
            $userInfo = [];
        }
        $data['userInfo'] = $userInfo;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    //经纪人带看二维码页面
    public function getTakeApply()
    {
        $reported_id = intval(Context::Post('reported_id'));    //报备id
        if (empty($reported_id)) {
            return $this->error('参数缺失');
        }
        $agent_id = $this->getAgentId();   //经纪人id
        $saId = $this->saId;   //经纪人said

        // 详情页及提交都有做判断，此处暂不用做
        $reportedData = $this->db->Name('xcx_building_reported')->select("br.*,bb.name building_name", "br")->leftJoin('xcx_building_building', 'bb', 'br.building_id=bb.id')->where_equalTo('br.id', $reported_id)->firstRow();

        if (empty($reportedData)) {
            return $this->error('数据有误');
        }
        //获取报备信息
        $reportedData['take_time'] = date('Y-m-d H:i', $reportedData['take_time']);
        $k = $reportedData['status_type'] . '|' . $reportedData['examine_type'];
        $reportedData['status_type_name'] = $this->getReportStatus()[$k];

        return $this->success($reportedData);
    }

    //报备流程审核
    public function examineReported()
    {
        $reported_id = intval(Context::Post('reported_id'));
        $changeCommission = Context::Post('changeCommission');
        $buildFold = Context::Post('build_fold');
        $status_type = !empty(Context::Post('status_type')) ? Context::Post('status_type') : 0;// 为0是普通备注
        $isPass = !empty(Context::Post('is_pass')) ? Context::Post('is_pass') : 0;// -1-驳回 1-申请 2-通过
        $content = !empty(Context::Post('content')) ? Context::Post('content') : '';// 备注信息
        $agent_id = $this->getAgentId();
        $agent_type = $this->getAgentType(); //角色类型
        $saId = $this->saId;
        $adminId = $this->adminId;
        $leaderId = $this->builddingLeader;
        $userinfo = $this->getUserInfo();
        $manageinfo = $userinfo['manageinfo'];
        $initMgid = 0;

        if (empty($manageinfo['auth_report_types']) || !is_array($manageinfo['auth_report_types'])) {
            $manageinfo['auth_report_types'] = [];
        }

        if (empty($reported_id)) {
            return $this->error('参数缺失');
        }

        $reportType = $this->getReportType();
        $reportType['0'] = '日志';
        if (!array_key_exists($status_type, $reportType)) {
            return $this->error('非法环节');
        }

        if (!empty($status_type)) {// 环节变更
            if (!in_array($isPass, [-1, 2])) {
                return $this->error('非法状态');
            }
            if(-1 == $isPass && 1 != $status_type) {
                return $this->error('非法状态');
            }
        }

        // 图片
        $imgs = [];
        $upfile = new UploadFiles(array('filepath' => BasePath . DS . 'upload' . DS . 'figure'));
        if ($upfile->checkFile('imgs')) {// 有图片上传
            if ($upfile->uploadeFile('imgs')) {
                $arrfile = $upfile->getnewFile();
                if (!empty($arrfile)) {
                    if (sizeof($arrfile) > 6) {
                        return $this->error('图片数量超出限制');
                    }
                    foreach ($arrfile as $k => $v) {
                        $imgs[] = '/upload/figure/' . $v;
                    }
                }
            } else {
                $err = $upfile->gteerror();
//                var_dump($err);
                $err = join(',', $err);
                return $this->error($err);
            }

        }
        $imgs = json_encode($imgs);

        // 报备信息
        $reportedInfo = $this->db->Name('xcx_building_reported')->select('r.*,sa.type,sa.mgid,sa.store_id', 'r')->where_equalTo('r.id', $reported_id)->innerJoin('xcx_store_agent', 'sa', 'sa.said=r.said')->firstRow();
        if (empty($reportedInfo)) {
            return $this->error('报备数据有误');
        }

        // 楼盘信息
        $buildingRow = $this->db->Name('xcx_building_building')->select('id,name,fold,commission,commission_type,store_manager_commission,team_member_commission,protect_set, aid, province, city, area')->where_equalTo('id', $reportedInfo['building_id'])->firstRow();
        if (empty($buildingRow) || $buildingRow['status'] == '0') {
            return $this->error('该报备记录楼盘信息已经下架请联系后台人员');
        }

        if (!empty($status_type)) {
            if ($reportedInfo['status_type'] != $status_type) {//状态是否已经发生变化了
                return $this->error('该报备状态已经操作过，请刷新页面');
            }

            if (1 == $reportedInfo['status_type'] && 2 == $reportedInfo['examine_type'] && -1 == $status_type) {// 当已报备完成，不可再驳回
                return $this->error('当前状态不可驳回');
            }

            // 报备环节审批校验时间
            if (2 == $reportedInfo['status_type']) {
                if (!empty($buildingRow['early_hours'])) {
                    $buildingRow['early_hours'] = 60;
                }
                $earlyTime = $reportedInfo['create_time'] + ($buildingRow['early_hours'] * 60);

                if (time() < $earlyTime) {
                    return $this->error('当前时间还不能确认带看');
                }
            }
        }

        // 已结佣完成就不可操作
        if (6 == $reportedInfo['status_type'] && 2 == $reportedInfo['examine_type']) {
            return $this->error('已完成结佣，无法操作');
        }
        $logSaid = [];
        $examineSaid = [];

        if ($status_type == 0) {
            foreach ($this->saId as $k => $v) {
                $logArray = $this->RoleAuth[$v['type']]['log'][$reportedInfo['examine_type']];
                if (!empty($logArray)) {
                    foreach ($logArray as $lk => $lv) {

                        $processing = $this->processing($reported_id, $reportedInfo['said'], $k, $lv); //处理
                        if ($processing) {
                            array_push($logSaid, $k);
                        }
                    }
                }
            }

            if (empty($logSaid)) {
                return $this->error('没有权限添加材料');
            }
        } else {


            //新-判断是否有该流程环节的权限
            foreach ($this->saId as $k => $v) {
                $logArray = $this->RoleAuth[$v['type']]['examine'][$reportedInfo['status_type']];

                if (!empty($logArray)) {
                    foreach ($logArray as $lk => $lv) {

                        $processing = $this->processing($reported_id, $reportedInfo['said'], $k, $lv); //处理

                        if ($processing) {
                            array_push($examineSaid, $k);
                        }

                    }
                }
            }

            if (empty($examineSaid)) {
                return $this->error('没有权限报备');
            }

        }

        //失效状态判断
        if ($reportedInfo['status_type'] >= 1 && $reportedInfo['status_type'] <= 3) {
            if ($reportedInfo['examine_type'] == -2) {
                return $this->error('该报备记录已经失效请联系后台人员');
            }
            $paramCheck = [
                'status'      => $reportedInfo['status_type'],
                'take_time'   => $reportedInfo['take_time'],
                'update_time' => $reportedInfo['update_time'],
                'protect_set' => $buildingRow['protect_set'],
            ];
            $checkProtect = $this->checkProtectTime($paramCheck);
            if (!$checkProtect) {
                return $this->error('该报备记录已经失效请联系后台人员');
            }
        }


        try {
            DataBase::beginTransaction();

            if (!empty($status_type)) {
                // 环节变更判断
                if (6 == $status_type) {
                    $nextStatus = $status_type;
                    $examineType = $isPass;
                    $update = ['status_type' => $status_type, 'examine_type' => $isPass];
                } else {
                    $nextStatus = $status_type + 1;
                    switch ($isPass) {
                        // 驳回
                        case -1:
                            $nextStatus = $status_type;
                            $examineType = -1;
                            break;
                        // 重新申请
                        case 1:
                            $nextStatus = $status_type;
                            $examineType = 1;
                            break;
                        // 通过
                        case 2:
                            $nextStatus = $status_type + 1;
                            $examineType = 1;
                            break;
                        default:
                            // 上面已做判断，is_pass的值只在-1和2
                            break;
                    }
                    $update = ['status_type' => $nextStatus, 'examine_type' => $examineType];
                }
                $update['examine_said'] = $examineSaid['0'];
                $update['examine_aid'] = $agent_id;
                $update['update_time'] = time();
                $update['init_mgid'] = $initMgid;
                if (5 <= $status_type) {
                    $update['change_commission'] = empty($changeCommission) ? 0 : $changeCommission;
                }
                if (3 == $status_type) {
                    $update['building_fold'] = empty($buildFold) ? 0 : $buildFold;
                }

                // 报备保护期
                $update['protect_day'] = 0;
                if(in_array($nextStatus, [1, 2, 3])) {
                    $protectTime = $this->getProtectTime($buildingRow['protect_set'], $nextStatus);
                    if(!empty($protectTime)) {
                        $update['protect_day'] = bcdiv($protectTime, 86400);
                    }
                }

                $isupdate = $this->db->Name('xcx_building_reported')->update($update)->where_equalTo('id', $reported_id)->where_equalTo('status_type', $reportedInfo['status_type'])->execute();
                if (empty($isupdate)) {
                    DataBase::rollBack();
                    return $this->error('确认失败-1');
                }

                $this->db->name('xcx_report_read_log')->where_equalTo('id', $reported_id)->delete()->execute();

            } else {
                $nextStatus = 0;
                $examineType = 0;
            }

            if (empty($status_type)) {

                foreach ($logSaid as $v) {
                    $log = $this->db->Name('xcx_reported_log')->insert([
                        'said'         => $reportedInfo['said'],
                        'agent_id'     => $reportedInfo['agent_id'],
                        'examine_said' => $v,
                        'examine_aid'  => $agent_id,
                        'report_id'    => $reportedInfo['id'],
                        'status_type'  => $nextStatus,
                        'examine_type' => $examineType,
                        'agent_type'   => !empty($saId[$v]['type']) ? $saId[$v]['type'] : 0,
                        'content'      => $content,
                        'imgs'         => $imgs,
                        'created_at'   => time(),
                        'updated_at'   => time(),
                    ])->execute();
                    if (empty($log)) {
                        DataBase::rollBack();
                        return $this->error('确认失败-2');
                    }
                }
            } else {
                foreach ($examineSaid as $v) {
                    $log = $this->db->Name('xcx_reported_log')->insert([
                        'said'         => $reportedInfo['said'],
                        'agent_id'     => $reportedInfo['agent_id'],
                        'examine_said' => $v,
                        'examine_aid'  => $agent_id,
                        'report_id'    => $reportedInfo['id'],
                        'status_type'  => $status_type,
                        'examine_type' => $isPass,
                        'agent_type'   => !empty($saId[$v]['type']) ? $saId[$v]['type'] : 0,
                        'content'      => $content,
                        'imgs'         => $imgs,
                        'created_at'   => time(),
                        'updated_at'   => time(),
                    ])->execute();

                    if (empty($log)) {

                        DataBase::rollBack();
                        return $this->error('确认失败-2');
                    }
                }

            }


            DataBase::commit();

            // 微信推送
            $sendParam = [
                'order_no'    => $reportedInfo['order_no'],
                'status_type' => $status_type,
                'next_status' => $nextStatus,
                'protect_set' => $buildingRow['protect_set'],
                'is_pass' => $isPass,
            ];
            $this->sendParamToWx($sendParam);

            return $this->success();
        } catch (Exception $ex) {
            DataBase::rollBack();
            return $this->error('确认失败-3');
        }
    }

    /**
     * @param $reported_id 单id
     * @param $said 报备的said
     * @param $pSaid 审核的said
     * @param $type   类型
     * @return bool
     */
    public function processing($reported_id, $said, $pSaid, $type)
    {
        switch ($type) {
            case 'self': //self 自己
                if ($said == $pSaid) {
                    return true;
                }
                break;
            case 'subordinate': //subordinate 下级店员

                $info = $this->db->Name('xcx_building_reported')
                    ->select('r.id,ss.aid', 'r')
                    ->where_equalTo('r.id', $reported_id)
                    ->leftJoin('xcx_store_store', 'ss', 'ss.id = r.store_id')->firstRow();
                if (!$info) {
                    return false;
                }

                $channelData = $this->db->Name('admin')->where_equalTo('id', $info['aid'])
                    ->select('channel_id')->execute();

                $channelId = array_column($channelData, 'channel_id');
                if (in_array($pSaid, $channelId)) {
                    return true;
                }

                break;
            case 'building':  //building 绑定的楼盘
                $info = $this->db->Name('xcx_building_reported')->select('building_id')->where_equalTo('id', $reported_id)->firstRow();
                $buildingData = $this->db->Name('xcx_manager_building')->select('said')->find_in_set($info['building_id'], 'building_ids')->firstRow();
                if (!empty($buildingData)) {
                    if ($buildingData['said'] == $pSaid) {
                        return true;
                    }
                }

                break;
            case 'subordinate-building': //subordinate-building 下级绑定的楼盘
                $info = $this->db->Name('xcx_building_reported')->select('building_id')->where_equalTo('id', $reported_id)->firstRow();
                $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$info['building_id']}, building_ids)")];
                $buildingData = $this->db->Name('xcx_manager_building')->select('said')->find_in_set($info['building_id'], 'building_ids')->firstRow();

                $pInfo = $this->db->name('xcx_store_agent')->select('mgid')->where_equalTo('said', $pSaid)->firstRow();

                if (!empty($pInfo)) {
                    $mgIdData = explode(',', $pInfo);
                    $saidData = [];
                    foreach ($mgIdData as $v) {
                        $data = $this->db->name('xcx_store_agent')->select('said')->find_in_set($v, 'mgid')->execute();
                        $saidArray = array_column($data, 'said');
                        $saidData = array_merge($saidData, $saidArray);
                    }

                    //判断是否在里面
                    if (!empty($buildingData)) {
                        if (in_array($buildingData, $saidData)) {
                            return true;
                        }
                    }
                }


                break;
            case 'create-store':  //create-store 创建的店铺
                $storeInfo = $this->db->name('admin')->select('id')->where_equalTo('channel_id', $pSaid)->firstRow();
                $info = $this->db->name('xcx_building_reported')->select('store_id')->where_equalTo('id', $reported_id)->firstRow();
                $aid = $this->db->name('xcx_store_store')->select('aid')->where_equalTo('id', $info['store_id'])->firstRow();


                if (!empty($storeInfo)) {

                    if ($storeInfo['id'] == $aid['aid']) {
                        return true;
                    }
                }

                break;
            case 'subordinate-store':   //subordinate-store 下级绑定的店铺
                $info = $this->db->name('xcx_building_reported')
                    ->leftJoin('9h_xcx_store_store', 'ss', 'ss.id = r.store_id')
                    ->select('r.id,ss.aid', 'r')->where_equalTo('r.id', $reported_id)->firstRow();
                if (!$info) {
                    $channelId = $this->db->name('admin')->select('channel_id')->where_equalTo('id', $info['aid'])->firstRow();
                }

                $pInfo = $this->db->name('xcx_store_agent')->select('mgid')->where_equalTo('said', $pSaid)->firstRow();

                if (!empty($pInfo)) {
                    $mgIdData = explode(',', $pInfo);
                    $saidData = [];
                    foreach ($mgIdData as $v) {
                        $data = $this->db->name('xcx_store_agent')->select('said')->find_in_set($v, 'mgid')->execute();
                        $saidArray = array_column($data, 'said');
                        $saidData = array_merge($saidData, $saidArray);
                    }

                    //判断是否在里面
                    if (!empty($channelId)) {
                        if (in_array($channelId, $saidData)) {
                            return true;
                        }
                    }
                }


                break;
            case 'create-building': //create-building 创建的楼盘

                $info = $this->db->name('xcx_building_reported')->select('r.id,bb.aid', 'r')
                    ->leftJoin('xcx_building_building', 'bb', 'bb.id = r.building_id')
                    ->where_equalTo('r.id', $reported_id)->firstRow(true);

                if (!empty($info)) {
                    $data = $this->db->name('admin')->select('charge_id')->where_equalTo('id', $info['aid'])->execute();
                    $channelId = array_column($data, 'charge_id');

                    if (in_array($pSaid, $channelId)) {
                        return true;
                    }

                }

                break;
            case 'city': //city 城市
                $pCity = $this->db->name('xcx_store_agent')->select('city')->where_equalTo('said', $pSaid)->firstRow();
                $info = $this->db->name('xcx_building_reported')->select('building_id')
                    ->where_equalTo('id', $reported_id)->firstRow();

                if (!empty($info)) {
                    $city = $this->db->name('xcx_building_building')->select('city')
                        ->where_equalTo('id', $info['building_id'])->firstRow();
                    if ($city['city'] == $pCity['city']) {
                        return true;
                    }
                }

                break;
            default:
                return false;
        }

        return false;
    }

    public function examineReported1()
    {
        $reported_id = intval(Context::Post('reported_id'));
        $status_type = !empty(Context::Post('status_type')) ? Context::Post('status_type') : 0;// 为0是普通备注
        $isPass = !empty(Context::Post('is_pass')) ? Context::Post('is_pass') : 0;// -1-驳回 1-申请 2-通过
        $content = !empty(Context::Post('content')) ? Context::Post('content') : '';// 备注信息
        $agent_id = $this->getAgentId();
        $agent_type = $this->getAgentType();
        $saId = $this->saId;
        $adminId = $this->adminId;
        $leaderId = $this->builddingLeader;
        $userinfo = $this->getUserInfo();
        $manageinfo = $userinfo['manageinfo'];
        $initMgid = 0;

        if (empty($manageinfo['auth_report_types']) || !is_array($manageinfo['auth_report_types'])) {
            $manageinfo['auth_report_types'] = [];
        }

        if (empty($reported_id)) {
            return $this->error('参数缺失');
        }

        $reportType = $this->getReportType();
        $reportType['0'] = '日志';
        if (!array_key_exists($status_type, $reportType)) {
            return $this->error('非法环节');
        }

        if (!empty($status_type)) {// 环节变更
            if (!in_array($isPass, [-1, 2])) {
                return $this->error('非法状态');
            }
        }

        // 图片
        $imgs = [];
        $upfile = new UploadFiles(array('filepath' => BasePath . DS . 'upload' . DS . 'figure'));
        if ($upfile->checkFile('imgs')) {// 有图片上传
            if ($upfile->uploadeFile('imgs')) {
                $arrfile = $upfile->getnewFile();
                if (!empty($arrfile)) {
                    if (sizeof($arrfile) > 6) {
                        return $this->error('图片数量超出限制');
                    }
                    foreach ($arrfile as $k => $v) {
                        $imgs[] = '/upload/figure/' . $v;
                    }
                }
            } else {
                $err = $upfile->gteerror();
//                var_dump($err);
                $err = join(',', $err);
                return $this->error($err);
            }

        }
        $imgs = json_encode($imgs);

        // 报备信息
        $reportedInfo = $this->db->Name('xcx_building_reported')->select('r.*,sa.type,sa.mgid,sa.store_id', 'r')->where_equalTo('r.id', $reported_id)->innerJoin('xcx_store_agent', 'sa', 'sa.said=r.said')->firstRow();
        if (empty($reportedInfo)) {
            return $this->error('报备数据有误');
        }

        // 楼盘信息
        $buildingRow = $this->db->Name('xcx_building_building')->select('id,name,fold,commission,commission_type,store_manager_commission,team_member_commission,protect_set, aid, province, city, area')->where_equalTo('id', $reportedInfo['building_id'])->firstRow();
        if (empty($buildingRow) || $buildingRow['status'] == '0') {
            return $this->error('该报备记录楼盘信息已经下架请联系后台人员');
        }

        // 已结佣完成就不可操作
        if (6 == $reportedInfo['status_type'] && 2 == $reportedInfo['examine_type']) {
            return $this->error('已完成结佣，无法操作');
        }

        if ($saId == $reportedInfo['said']) {// 为本人的报备信息只能添加日志，不能操作环节
            if (!empty($status_type)) {
                return $this->error('您没有权限进行操作');
            }
        } else {// 非本人
            if (!empty($status_type)) {// 环节变更
                if (!in_array($agent_type, $this->teamMember)) {
                    return $this->error('抱歉您不是工作人员不可操作');
                }

                if ($reportedInfo['status_type'] != $status_type) {//状态是否已经发生变化了
                    return $this->error('该报备状态已经操作过，请刷新页面');
                }

                if (1 == $reportedInfo['status_type'] && 2 == $reportedInfo['examine_type'] && -1 == $status_type) {// 当已报备完成，不可再驳回
                    return $this->error('当前状态不可驳回');
                }

                if (-1 == $reportedInfo['status_type'] && 2 == $status_type) {// 被驳回记录，转为重新申请
                    $status_type = 1;
                }
            } else {// 添加备注
                if (0 == $agent_type) {
                    return $this->error('不是您的报备');
                }
            }

            //判断是否有该流程环节的权限
            if (in_array($agent_type, $this->teamMember)) {// 是工作人员时
                if (!in_array($reportedInfo['status_type'], $this->chargeType)) {
                    // 当身份是渠道组员时，要做特殊处理，渠道组员能查看所有环节，但只能审核5,6环节
                    if (5 == $agent_type) {
                        $manageinfo['auth_report_types'] = [5, 6];
                    }

                    if (!in_array($reportedInfo['status_type'], $manageinfo['auth_report_types'])) {
                        return $this->error('抱歉您没有操作“' . $this->getReportType()[$reportedInfo['status_type']] . '”环节的权限！');
                    }

                    switch ($agent_type) {
                        // 项目组员
                        case 2:
                            //=======楼盘的操作权限========//
                            if ($manageinfo['building_ids'] == -1) {//未绑定任何楼盘时
                                return $this->error('抱歉您没有操作“' . $buildingRow['name'] . '”楼盘的权限！');
                            }
                            if (!empty($manageinfo['building_ids'])) {//判断是否有该楼盘操作权限
                                $building_ids = explode(',', $manageinfo['building_ids']);
                                if (!in_array($reportedInfo['building_id'], $building_ids)) {
                                    return $this->error('抱歉您没有操作“' . $buildingRow['name'] . '”楼盘的权限！');
                                }
                            }
                            //=======楼盘的操作权限end========//
                            break;
                        // 渠道组员
                        case 5:
                            // 查看店铺操作权限
                            if (!empty($adminId) && !empty($reportedInfo['store_id'])) {
                                $resStore = $this->db->Name("xcx_store_store")->select("id")
                                    ->where_equalTo('id', $reportedInfo['store_id'])
                                    ->where_equalTo('aid', $adminId)
                                    ->firstRow();
                                if (empty($resStore)) {
                                    return $this->error('抱歉您没有店铺权限！');
                                }
                            } else {
                                return $this->error('抱歉您没有操作“' . $this->getReportType()[$reportedInfo['status_type']] . '”环节的权限！');
                            }
                            break;
                        // 区域负责人
                        case 8:
                            // 查看楼盘所属城市是否与该人员相符
                            $city = $userinfo['storeInfo']['city'];
                            if ($buildingRow['city'] != $city) {
                                return $this->error('抱歉您没有操作“' . $buildingRow['city'] . '”城市相关报备的权限！');
                            }
                            break;
                        default:
                            return $this->error('抱歉，身份有误');
                            break;
                    }
                } else {
                    // 环节为确认业绩时需判断是否项目负责人及是否有该楼盘权限
                    if (!empty($leaderId)) {
                        if ($adminId != $buildingRow['aid']) {
                            return $this->error('抱歉您没有操作“' . $buildingRow['name'] . '”楼盘的权限！');
                        }
                    } else {
                        if (8 == $agent_type) {
                            // 查看楼盘所属城市是否与该人员相符
                            $city = $userinfo['storeInfo']['city'];
                            if ($buildingRow['city'] != $city) {
                                return $this->error('抱歉您没有操作“' . $buildingRow['city'] . '”城市相关报备的权限！');
                            }
                        } else {
                            return $this->error('抱歉您没有操作“' . $this->getReportType()[$reportedInfo['status_type']] . '”环节的权限！');
                        }
                    }
                }
            }

            // 为工作人员身份时，需要判断之前是否由同一个组的成员处理过
//            if(2 == $agent_type || 5 == $agent_type) {
//                if(!empty($reportedInfo['init_mgid'])) {
//                    if(!in_array($reportedInfo['init_mgid'], $userinfo['mgid'])) {
//                        return $this->error('抱歉，该报备记录已由其他组操作！');
//                    }
//                }
//
//                if(1 != sizeof($userinfo['mgid'])) {// 一个组员只有一个组
//                    return $this->error('抱歉，账号信息异常！');
//                }
//                $initMgid = $userinfo['mgid']['0'];
//            }

            //判断当前流程申请者角色
            switch ($reportedInfo['agent_type']) {
                //经纪人申请时
                case 0:
                case 1:
                    if (in_array($agent_type, $this->manager)) {
                        return $this->error('抱歉该记录为经纪人申请，是由您的下属工作人员进行操作');
                    }
                    break;
                //下属工作人员申请时
                case 2:
                    if ($agent_type == 2) {
                        return $this->error('抱歉您不是工作人员组长不可操作');
                    }
                    //找出申请者的上级组长
                    $mgid = explode(',', $reportedInfo['mgid']);
                    if (1 != sizeof($mgid)) {
                        return $this->error('抱歉,账号信息有误');
                    } else {
                        $mgid = $mgid['0'];
                    }
                    $parentinfo = $this->db->Name('xcx_store_agent')->select('agent_id')->where_express("FIND_IN_SET(:mgid, mgid)", [':mgid' => $mgid])->where_express("(type=:aType_1 or type=:aType_2)", [':aType_1' => 3, ':aType_2' => 4])->where_notEqualTo('agent_id', 0)->firstRow();
                    if ($parentinfo['agent_id'] != $agent_id) {
                        return $this->error('抱歉该工作人员不是您的下属人员不可操作');
                    }
                    break;
            }
        }

        //失效状态判断
//        $buildingRow['protect_set'] = json_decode($buildingRow['protect_set'],1);//楼盘设置的报备保护机制
        if ($reportedInfo['status_type'] >= 1 && $reportedInfo['status_type'] <= 3) {
            if ($reportedInfo['examine_type'] == -2) {
                return $this->error('该报备记录已经失效请联系后台人员');
            }
            $paramCheck = [
                'status'      => $reportedInfo['status_type'],
                'take_time'   => $reportedInfo['take_time'],
                'update_time' => $reportedInfo['update_time'],
                'protect_set' => $buildingRow['protect_set'],
            ];
            $checkProtect = $this->checkProtectTime($paramCheck);
            if (!$checkProtect) {
                return $this->error('该报备记录已经失效请联系后台人员');
            }
//            if(1 == $reportedInfo['status_type']) {// 如果是报备流程，则从预约带看时间开始算起
//                $baseTime = $reportedInfo['take_time'];
//            } else {//按最后的更新时间-小时
//                $baseTime = $reportedInfo['update_time'];
//            }
//            if($reportedInfo['examine_type']==-2){
//                return $this->error('该报备记录已经失效请联系后台人员');
//            }
//            //每个流程环节保护时间-规范到小时
//            $k = 'status'.$reportedInfo['status_type'].'_hours';
//            $protect_set_hours = intval($buildingRow['protect_set'][$k]);
//            if($protect_set_hours > 0) {// 如果有设置，才校验保护期
//                if(1 == $reportedInfo['status_type']) {
//                    $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
//                } else {
//                    $protect_time = $protect_set_hours * 3600;
//                }
//                $protectTimeEnd = $baseTime+$protect_time;
//                if($protectTimeEnd<=time()){
//                    return $this->error('该报备记录已经失效请联系后台人员');
//                }
//            }
        }

        $dbSaid = in_array($status_type, $this->chargeType) ? $leaderId : $saId;
        if (in_array($status_type, $this->chargeType)) {
            if (8 == $agent_type) {
                $dbSaid = $saId;
            } else {
                $dbSaid = $leaderId;
            }
        }

        try {
            DataBase::beginTransaction();

            if (!empty($status_type)) {
                // 环节变更判断
                if (6 == $status_type) {
                    $nextStatus = $status_type;
                    $examineType = $isPass;
                    $update = ['status_type' => $status_type, 'examine_type' => $isPass];
                } else {
                    $nextStatus = $status_type + 1;
                    switch ($isPass) {
                        // 驳回
                        case -1:
                            $nextStatus = $status_type;
                            $examineType = -1;
                            break;
                        // 重新申请
                        case 1:
                            $nextStatus = $status_type;
                            $examineType = 1;
                            break;
                        // 通过
                        case 2:
                            $nextStatus = $status_type + 1;
                            $examineType = 1;
                            // 如果是报备环节的申请中，只改状态为通过
                            if (1 == $status_type) {
                                if (1 == $reportedInfo['examine_type']) {
                                    $nextStatus = $status_type;
                                    $examineType = 2;
                                }
                            }
                            break;
                        default:
                            // 上面已做判断，is_pass的值只在-1和2
                            break;
                    }
                    $update = ['status_type' => $nextStatus, 'examine_type' => $examineType];

//                    // 如果是报备环节 本次审核为通过 且 数据库原有状态为申请中或驳回，只改状态为通过
//                    if(1 == $status_type && 2 == $isPass && 2 != $reportedInfo['examine_type']) {
//                        $update = ['status_type' => $status_type, 'examine_type' => $isPass];
//                    }
                }
                $update['examine_said'] = $saId;
                $update['examine_aid'] = $agent_id;
                $update['update_time'] = time();
                $update['init_mgid'] = $initMgid;
                $isupdate = $this->db->Name('xcx_building_reported')->update($update)->where_equalTo('id', $reported_id)->where_equalTo('status_type', $reportedInfo['status_type'])->execute();
                if (empty($isupdate)) {
                    DataBase::rollBack();
                    return $this->error('确认失败-1');
                }
            } else {
                $nextStatus = 0;
                $examineType = 0;
            }

            // 环节变更记录/日志添加
            $log = $this->db->Name('xcx_reported_log')->insert([
                'said'         => $reportedInfo['said'],
                'agent_id'     => $reportedInfo['agent_id'],
                'examine_said' => $dbSaid,
                'examine_aid'  => $agent_id,
                'report_id'    => $reportedInfo['id'],
                'status_type'  => $nextStatus,
                'examine_type' => $examineType,
                'agent_type'   => $userinfo['type'],
                'content'      => $content,
                'imgs'         => $imgs,
                'created_at'   => time(),
                'updated_at'   => time(),
            ])->execute();
            if (empty($log)) {
                DataBase::rollBack();
                return $this->error('确认失败-2');
            }

            // 如果是结佣申请（即开票通过），则插入结佣申请
            if (5 == $status_type && 2 == $isPass) {
                $settleInsert = [
                    'report_id'       => $reported_id,
                    'said'            => $dbSaid,
                    'agent_id'        => $agent_id,
                    'receiver_said'   => $reportedInfo['said'],
                    'commission_type' => $buildingRow['commission_type'],
                    'commission'      => $buildingRow['commission'],
                    'status'          => 1,
                    'created_at'      => time(),
                    'update_at'       => time(),
                ];
                $settle = $this->db->Name('xcx_reported_settlement')->insert($settleInsert)->execute();
                if (empty($settle)) {
                    DataBase::rollBack();
                    return $this->error('结佣申请失败');
                }
            }

            // 如果是结佣完成
            if (6 == $status_type) {
                $updateSettle = [
                    'admin_id'     => !empty($adminId) ? $adminId : 0,
                    'examine_said' => $saId,
                    'status'       => $examineType,
                    'update_at'    => time(),
                ];
                $settle = $this->db->Name('xcx_reported_settlement')->update($updateSettle)->where_equalTo("report_id", $reportedInfo['id'])->execute();
                if (empty($settle)) {
                    DataBase::rollBack();
                    return $this->error('结佣操作失败');
                }
            }

            DataBase::commit();

            // 报备环节变更推送
            if (!empty($status_type)) {
                $sendParam = [
                    'status_type'    => $nextStatus,
                    'examine_type'   => $examineType,
                    'building_id'    => $reportedInfo['building_id'],
                    'building_name'  => $buildingRow['name'],
                    'customer_name'  => $reportedInfo['user_name'],
                    'customer_phone' => $reportedInfo['user_phone'],
                    'said'           => $reportedInfo['said'],
                    'building_area'  => $buildingRow['city'],
                    'report_id'      => $reported_id,
                ];
                // $this->sendTmpMsg($sendParam);
                $this->sendWxApi($sendParam);
            }

            return $this->success();
        } catch (Exception $ex) {
            DataBase::rollBack();
            return $this->error('确认失败-3');
        }
    }

    //获取系统通知信息
    public function getSystenInforms()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        $page = Context::Post('page');
        $systemInfo = $this->db->Name('xcx_announcement_inform_user')->select('aiu.*,ai.inform_title,ai.inform_content,ai.release_time', 'aiu')->leftJoin('xcx_announcement_inform', 'ai', 'aiu.announcement_id=ai.id')->where_equalTo('aiu.username_type', 2)->where_equalTo('aiu.username_id', $agent_id)->where_equalTo('ai.if_revocation', 0)->page($page, self::MYLIMIT)->orderBy('ai.priority', 'desc')->orderBy('ai.release_time', 'desc')->execute();
        if (!empty($systemInfo)) {
            $systemInfo = array_reverse($systemInfo);
            foreach ($systemInfo as &$val) {
                $val['release_time'] = date('Y-m-d H:i:s', $val['release_time']);
            }
            echo json_encode(['success' => true, 'data' => $systemInfo]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    //获取系统头像与昵称
    public function getSystemInfo()
    {
        $data = [];
        $settingRow = $this->db->Name('xcx_setting')->select()->execute();
        if (!empty($settingRow)) {
            foreach ($settingRow as $val) {
                if ($val['key'] == 'system_name') {
                    $data['systemInfo']['system_name'] = $val['value'];
                }
                if ($val['key'] == 'system_logo') {
                    $data['systemInfo']['system_logo'] = $val['value'];
                }
            }
        }
        return $this->success($data);
//        echo json_encode(, JSON_UNESCAPED_UNICODE);
    }

    //修改系统消息为已读
    public function updateSystenRead()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        $this->db->Name('xcx_announcement_inform_user')->update(['if_read' => 1, 'read_time' => time()])->where_equalTo('username_id', $agent_id)->where_equalTo('username_type', 2)->where_equalTo('if_read', 0)->execute();
        echo json_encode(['success' => true]);
    }

    //获取经纪人与用户的聊天记录
    public function getChatMessage()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_id = Context::Post('user_id');    //用户id
        $page = Context::Post('page');
        $data = $this->db->Name('xcx_chat_record')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_status', '1')->orderBy('create_time', 'desc')->page($page, 20)->execute();
        if (!empty($data)) {
            $data = array_reverse($data);
            $earliestTime = intval($data[0]['create_time']);
            foreach ($data as $key => &$val) {
                $val['sender'] = $val['from_type'] == '2' ? 'self' : 'ta';
                $val['success'] = true;
                if ((intval($val['create_time']) - $earliestTime) > 1800 || $key == 0) {   //大于半小时显示时间
                    $earliestTime = intval($val['create_time']);
                    $val['create_time_name'] = date('Y-m-d H:i:s', $val['create_time']);
                } else {
                    $val['create_time_name'] = "";
                }
            }
            //将所有消息标记为已读
            $this->db->Name('xcx_chat_record')->update(['agent_read' => '1'])->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_status', '1')->execute();
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    //获取经纪人对应的消息列表
    public function getChatList()
    {
        $page = Context::Post('page');
        $agent_id = Session::get('agent_id');   //经纪人id
        $searchText = Context::Post('searchText');    //搜索内容
        $is_serach = empty(Context::Post('is_serach')) ? false : true;
        $nowDate = Context::Post('nowDate');
        $orderby = Context::Post('orderby');
        if (!in_array($orderby, ['desc', 'asc'])) {
            $orderby = 'desc';
        }
        if (empty($nowDate)) {
            $nowDate = date('Y-m-d', time());
        }

        //获取系统未读消息
        $systemNum = $this->db->Name('xcx_announcement_inform_user')->select('COUNT(*)')->where_equalTo('username_id', $agent_id)->where_equalTo('username_type', 2)->where_equalTo('if_read', 0)->firstColumn();
        $Db = $this->db->Name('xcx_chat_list')->select("cl.id,cl.agent_id,cl.user_id,cl.create_time,u.nickName,u.avatarUrl", "cl")->leftJoin('xcx_user', 'u', 'cl.user_id=u.id')->where_equalTo('cl.agent_id', $agent_id)->where_equalTo('cl.agent_status', '1')->page($page, self::MYLIMIT)->orderBy('cl.id', $orderby);
        if (!empty($searchText)) {
            $where_express = " (u.nickName like \"%" . $searchText . "%\") ";
            @$Db->where_express($where_express);
        }
        if (!empty($nowDate)) {
            $endDate = strtotime($nowDate . ' +1 day');
            $nowDate = strtotime($nowDate);
            if (!empty($nowDate)) {
                $where_express = " (cl.update_time >= \"" . $nowDate . "\" AND cl.update_time <= \"" . $endDate . "\") ";
                @$Db->where_express($where_express);
            }
        }
        $data = $Db->execute();

        if (empty($data)) {
            //echo json_encode(['success'=>false,'systemNum'=>$systemNum]);
            if (!empty($is_serach)) {
                //echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>[]]);
                return $this->success(['is_serach' => $is_serach, 'systemNum' => $systemNum, 'data' => [], 'agent_id' => $this->agentId]);
            } else {
                //echo json_encode(['success'=>false]);
                return $this->success(['systemNum' => $systemNum, 'data' => [], 'agent_id' => $this->agentId]);
            }
        } else {
            foreach ($data as &$val) {
                list($val['unread_num'], $val['unread_content'], $val['create_time']) = $this->getUnreadData($val['agent_id'], $val['user_id']);
            }
            //echo json_encode(['success'=>true,'data'=>$data,'systemNum'=>$systemNum]);
            return $this->success(['is_serach' => $is_serach, 'systemNum' => $systemNum, 'data' => $data, 'agent_id' => $this->agentId]);
        }
    }

    //获取经纪人未读数据
    private function getUnreadData($agent_id, $user_id)
    {
        $res[] = $this->db->Name('xcx_chat_record')->select('COUNT(*)')->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_read', '0')->where_equalTo('agent_status', '1')->firstColumn();
        $unread_content = $this->db->Name('xcx_chat_record')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_status', '1')->where_equalTo('message_type', '1')->orderBy('create_time', 'desc')->firstRow();
        if (empty($unread_content)) {
            $res[] = "";
        } else {
            $res[] = $unread_content['content'];
        }
        $res[] = $this->format_dates($unread_content['create_time']);
        return $res;
    }

    //修改消息列表为已读
    public function updateYd()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_id = Context::Post('user_id');    //用户id
        $res = $this->db->Name('xcx_chat_record')->update(['agent_read' => '1', 'update_time' => time()])->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_read', '0')->execute();
        if (!empty($res)) {
            return $this->success();
        } else {
            return $this->error();
        }
    }

    //删除消息列表及聊天内容
    public function delMessageList()
    {
        $agent_id = Session::get('agent_id');   //经纪人id
        $user_id = Context::Post('user_id');    //用户id
        $id = Context::Post('id');    //9h_xcx_chat_list id

        //隐藏聊天列表
        $res = $this->db->Name('xcx_chat_list')->update(['agent_status' => '0', 'update_time' => time()])->where_equalTo('id', $id)->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_status', '1')->execute();
        if (!empty($res)) {
            //修改聊天记录为删除状态
            $this->db->Name('xcx_chat_record')->update(['agent_status' => '0'])->where_equalTo('agent_id', $agent_id)->where_equalTo('user_id', $user_id)->where_equalTo('agent_status', '1')->execute();
            return $this->success();
        } else {
            return $this->error();
        }
    }

    //添加店员链接
    public function storeAgentAdd()
    {
        $data['store_id'] = Context::Post('store_id');
        $data['agent_id'] = 0;
        $data['type'] = 0;
        $data['valurl'] = "";
        $data['create_time'] = time();
        $data['update_time'] = time();
        $res = $this->db->Name('xcx_store_agent')->insert($data)->execute();
        $canshu = "?as=" . urlencode(Encryption::authcode($res, false));
        $data2['valurl'] = WX_HOST . "/xcxapi/userAjax/wxlogin" . $canshu;
        $res2 = $this->db->Name('xcx_store_agent')->update($data2)->where_equalTo('id', $res)->execute();
        if ($res && $res2) {
            //获取绑定经纪人的连接
            $bindingInfo = $this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id', $data['store_id'])->where_equalTo('agent_id', 0)->where_equalTo('type', 0)->orderBy('create_time', 'desc')->execute();
            if (empty($bindingInfo)) {
                $bindingInfo = [];
            } else {
                foreach ($bindingInfo as &$bindingVal) {
                    $bindingVal['create_time'] = date('Y.m.d H:i', $bindingVal['create_time'] + 86400);
                }
            }
            echo json_encode(['success' => true, 'bindingInfo' => $bindingInfo]);
        } else {
            echo json_encode(['success' => false, 'message' => "保存失败"]);
        }
    }

    //店员链接重置
    public function storeAgentReset()
    {
        $id = Context::Post('id');
        $store_id = Context::Post('store_id');
        $agRow = $this->db->Name('xcx_store_agent')->select()->where_equalTo('id', $id)->where_equalTo('store_id', $store_id)->where_equalTo('agent_id', 0)->where_equalTo('type', '0')->firstRow();
        if (!empty($agRow)) {
            $data['agent_id'] = 0;
            $canshu = "?as=" . urlencode(Encryption::authcode($id, false));
            $data['valurl'] = WX_HOST . "/xcxapi/userAjax/wxlogin" . $canshu;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $res = $this->db->Name('xcx_store_agent')->update($data)->where_equalTo('id', $id)->execute();
            if ($res) {
                $create_time = date('Y.m.d H:i', $data['create_time'] + 86400);
                echo json_encode(['success' => true, 'valurl' => $data['valurl'], 'create_time' => $create_time]);
            } else {
                echo json_encode(['success' => false, 'message' => '重置失败']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '重置失败']);
        }
    }

    //店员链接删除
    public function storeAgentDel()
    {
        $id = Context::Post('id');
        $store_id = Context::Post('store_id');
        $res = $this->db->Name('xcx_store_agent')->delete()->where_equalTo('id', $id)->where_equalTo('store_id', $store_id)->where_equalTo('agent_id', 0)->where_notEqualTo('type', 1)->execute();
        if ($res) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '删除失败']);
        }
    }

    //忽略店铺申请
    public function storeSqIgnore()
    {
        $agent_id = Session::get('agent_id');   //店长经纪人id
        $sqid = Context::Post('sqid');    //申请的经纪人id
        $storeInfo = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $agent_id)->where_equalTo('type', 1)->firstRow();
        if (!empty($storeInfo)) {
            $sqAgentInfo = $this->db->Name('xcx_agent_user')->select()->where_equalTo('id', $sqid)->where_equalTo('sq_store_id', $storeInfo['store_id'])->where_equalTo('sq_store_status', '1')->firstRow();
            if (!empty($sqAgentInfo)) {
                $res = $this->db->Name('xcx_agent_user')->update(['sq_store_status' => '3', 'sq_store_audittime' => time()])->where_equalTo('id', $sqid)->where_equalTo('sq_store_id', $storeInfo['store_id'])->where_equalTo('sq_store_status', '1')->execute();
                if ($res)
                    return $this->success();
//                    echo json_encode(['success' => true]);
                else
                    return $this->error('数据修改失败');
//                    echo json_encode(['success' => false, 'message' => '数据修改失败']);
            } else {
                return $this->error('请不要修改数据');
//                echo json_encode(['success' => false, 'message' => '请不要修改数据']);
            }
        } else {
            return $this->error('你还不是店长');
//            echo json_encode(['success' => false, 'message' => '你还不是店长']);
        }
    }

    //审核通过店铺申请
    public function storeSqThrough()
    {
        $agent_id = Session::get('agent_id');   //店长经纪人id
        $sqid = Context::Post('sqid');    //申请的经纪人id

        $storeInfo = $this->db->Name('xcx_store_agent')
            ->select()
            ->where_equalTo('agent_id', $agent_id)
            ->where_equalTo('type', 1)->firstRow();

        if (!empty($storeInfo)) {
            $sqAgentInfo = $this->db->Name('xcx_agent_user')
                ->select()
                ->where_equalTo('id', $sqid)
                ->where_equalTo('sq_store_id', $storeInfo['store_id'])
                ->where_equalTo('sq_store_status', '1')->firstRow();
            if (!empty($sqAgentInfo)) {

                //检测是否已为经纪人
                //$checkAgent=$this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id',$storeInfo['store_id'])->where_equalTo('agent_id',$sqid)->firstRow();
                $checkAgent = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $sqid)->firstRow();
                if (empty($checkAgent)) {
                    $res = $this->db->Name('xcx_agent_user')->update(['sq_store_status' => '2', 'sq_store_audittime' => time()])->where_equalTo('id', $sqid)->where_equalTo('sq_store_id', $storeInfo['store_id'])->where_equalTo('sq_store_status', '1')->execute();
                    $code_key = create_code_key('T2');
                    $res2 = $this->db->Name('xcx_store_agent')->insert([
                        'store_id'    => $storeInfo['store_id'],
                        'agent_id'    => $sqid,
                        'agent_openid'    => !empty($sqAgentInfo['openid']) ? $sqAgentInfo['openid'] : "",
                        'agent_name'    => !empty($sqAgentInfo['nickname']) ? $sqAgentInfo['nickname'] : "",
                        'agent_img'    => !empty($sqAgentInfo['headimgurl']) ? $sqAgentInfo['headimgurl'] : "",
                        'type'        => 0,
                        'create_time' => time(),
                        'update_time' => time(),
                        'code_key'    => $code_key,//必须值
                    ])->execute();
                    if ($res && $res2) {
                        /*$canshu="?as=".urlencode(Enrcyption::authcode($res2,false));
                        $parameter['valurl']=WX_HOST."/xcxapi/userAjax/wxlogin".$canshu;
                        $this->db->Name('xcx_store_agent')->update($parameter)->execute();*/
                        return $this->success();
                        echo json_encode(['success' => true]);
                    } else {
                        return $this->error('数据修改失败');
//                        echo json_encode(['success' => false, 'message' => '数据修改失败']);
                    }
                } else {
                    return $this->error('他已是经纪人了');
//                    echo json_encode(['success' => false, 'message' => '他已是经纪人了']);
                }
            } else {
                return $this->error('请不要修改数据');
//                echo json_encode(['success' => false, 'message' => '请不要修改数据']);
            }
        } else {
            return $this->error('你还不是店长');
//            echo json_encode(['success' => false, 'message' => '你还不是店长']);
        }
    }

    /**
     * 添加报备评论
     */
    public function addReportedComments()
    {
        $data['reported_id'] = Context::Post('reported_id');    //楼盘报备id
        $data['agent_id'] = Session::get('agent_id');   //经纪人id
        $data['send_from'] = '1';   //发送来源
        $data['content'] = Context::Post('content');   //评论内容
        $data['create_time'] = time();
        $data['update_time'] = time();
        if (empty($data['reported_id']) || empty($data['agent_id']) || empty($data['content'])) {
            echo json_encode(['success' => false, 'message' => '参数有误！']);
            exit;
        }
        $res = $this->db->Name('xcx_building_reported_comments')->insert($data)->execute();
        if ($res) {
            $res = $this->db->Name('xcx_building_reported_comments')->select('brc.*,au.name agent_name,au.nickname agent_nickname,au.headimgurl agent_headimgurl', 'brc')->leftJoin('xcx_agent_user', 'au', 'brc.agent_id=au.id')->where_equalTo('brc.id', $res)->firstRow();
            $res['nickname'] = empty($res['agent_name']) ? $res['agent_nickname'] : $res['agent_name'];
            $res['headimgurl'] = $res['agent_headimgurl'];
            unset($res['agent_name']);
            unset($res['agent_nickname']);
            unset($res['agent_headimgurl']);
            echo json_encode(['success' => true, 'data' => [$res]]);
        } else {
            echo json_encode(['success' => false, 'message' => '保存数据失败']);
        }
    }

    /**
     * 获取报备评论内容
     */
    public function getReportedComments()
    {
        $reported_id = Context::Post('reported_id');    //楼盘报备id
        $page = empty(Context::Post('page')) ? 1 : Context::Post('page');
        $data = $this->db->Name('xcx_building_reported_comments')->select('brc.*,au.name agent_name,au.nickname agent_nickname,au.headimgurl agent_headimgurl,a.name admin_name,a.img admin_img', 'brc')->leftJoin('xcx_agent_user', 'au', 'brc.agent_id=au.id')->leftJoin('admin', 'a', 'brc.admin_id=a.id')->where_equalTo('brc.reported_id', $reported_id)->page($page, self::MYLIMIT)->orderBy('brc.create_time', 'desc')->execute();
        if (!empty($data)) {
            foreach ($data as &$val) {
                $val['create_time'] = $this->format_dates($val['create_time']);
                if ($val['send_from'] == '1') {     //经纪人信息
                    $val['nickname'] = empty($val['agent_name']) ? $val['agent_nickname'] : $val['agent_name'];
                    $val['headimgurl'] = $val['agent_headimgurl'];
                } else {  //后台管理员信息
                    $val['nickname'] = $val['admin_name'];
                    $img = empty($val['admin_img']) ? '/upload/static/empty.png' : $val['admin_img'];
                    $val['headimgurl'] = WX_HOST . $img;
                }
                unset($val['agent_name']);
                unset($val['agent_nickname']);
                unset($val['agent_headimgurl']);
                unset($val['admin_name']);
                unset($val['admin_img']);
            }
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'data' => []]);
        }
    }

    // 经纪人/工作人员 选择列表
    public function getAgentList()
    {
        $date = !empty(Context::Post('nowDate')) ? date("Y-m-d", strtotime(Context::Post('nowDate'))) : date("Y-m-d", time());
        $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;
        $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : 10;
        $type = !empty(Context::Post('type')) ? Context::Post('type') : 1;
        $name = !empty(Context::Post('name')) ? Context::Post('name') : "";
        $usrInfo = $this->getUserInfo();
        $power = !empty($usrInfo['manageinfo']['auth_report_types']) ? $usrInfo['manageinfo']['auth_report_types'] : [];
        $saId = $this->saId;
        $agentId = $this->agentId;
        $adminId = $this->adminId;
        $data = [];

        $dateTime = strtotime($date);

        $startTime = $dateTime;
        $endTime = $dateTime + 86400;
        switch ($usrInfo['type']) {
            // 项目组员获取经纪人列表
            case 2:
                // 查找符合条件的经纪人
                $dbSQL = $this->db->Name('xcx_building_reported')->select("br.said, br.agent_id", "br")
                    ->leftJoin('xcx_reported_log', 'rl', "br.id=rl.report_id")
                    ->where_in('br.agent_type', $this->nomalAgent)
                    ->where_greatThanOrEqual('rl.updated_at', $startTime)
                    ->where_lessThanOrEqual('rl.updated_at', $endTime);

                if (-1 == $usrInfo['manageinfo']['building_ids']) {// 当前未绑定楼盘则取过去曾处理过的记录
                    $dbSQL->where_express('(rl.examine_said = :examineSaid or rl.examine_aid = :examineAid)', [':examineSaid' => $saId, ':examineAid' => $agentId]);
                } else {
                    if (0 != $usrInfo['manageinfo']['building_ids']) {
                        list($inPower, $valPower) = $this->buildWhereIn(':power', $power);
                        $build = explode(',', $usrInfo['manageinfo']['building_ids']);
                        list($inBuild, $valBuild) = $this->buildWhereIn(':build', $build);
                        // 只绑定部分楼盘:所辖楼盘的报备且环节处在当前账号的权限内 或 操作过的报备记录不论权限
                        $whereVal = [':examineSaid' => $saId, ':examineAid' => $agentId];
                        $whereVal = array_merge($whereVal, $valPower, $valBuild);
                        $dbSQL->where_express("((br.building_id in {$inBuild} and br.status_type in {$inPower}) or rl.examine_said = :examineSaid or rl.examine_aid = :examineAid)", $whereVal);
                    } else {
                        // 绑定所有楼盘:环节处在当前账号的权限内 或 操作过的报备记录不论权限
                        list($statusIn, $statusVal) = $this->buildWhereIn(':statusType', $power);
                        $whereVal = [
                            ':examineSaid' => $saId,
                            ':examineAid'  => $agentId,
                        ];
                        $whereVal = array_merge($whereVal, $statusVal);
                        $dbSQL->where_express("(br.status_type in {$statusIn} or (rl.examine_said = :examineSaid or rl.examine_aid = :examineAid))", $whereVal);
                    }
                }

                $resLog = $dbSQL->execute();

                //获取经纪人头像昵称今日报备数
                if (!empty($resLog)) {
                    $saIds = array_column($resLog, 'said');
                    $saIds = array_unique($saIds);
                    $dbSQL = $this->db->Name('xcx_store_agent')->select("sa.said, sa.agent_id, au.nickname, au.headimgurl, au.sex, sa.agent_name, sa.agent_img, count(br.id) as count", "sa")
                        ->leftJoin("xcx_agent_user", "au", "au.id=sa.agent_id")
                        ->leftJoin('xcx_building_reported', 'br', "(br.said=sa.said and br.create_time>={$startTime} and br.create_time<={$endTime})")
                        ->where_in('sa.said', $saIds)
                        ->where_notEqualTo('sa.agent_id', 0);

                    if (!empty($name)) {
                        $dbSQL->where_like('au.nickname', "%{$name}%");
                    }

                    $agent = $dbSQL->groupBy("sa.agent_id")->page($page, $pageSize)->execute();
                    if (!empty($agent)) {
                        foreach ($agent as $k => $v) {
                            $data[$k]['id'] = $v['agent_id'];
                            $data[$k]['nickname'] = empty($v['nickname']) ? !empty($v['agent_name']) ? $v['agent_name'] : "ID:{$v['said']}" : $v['nickname'];
                            $defaultHeadImg = $this->manImg;
                            if (isset($v['sex']) && 3 == $v['sex']) {
                                $defaultHeadImg = $this->womanImg;
                            }
                            $data[$k]['headimgurl'] = empty($v['headimgurl']) ? !empty($v['agent_img']) ? $v['agent_img'] : $defaultHeadImg : $v['headimgurl'];
                            $data[$k]['count'] = $v['count'];
                        }
                    }
                }
                break;
            // 渠道组员获取工作人员列表
            case 5:
                $storeIds = [];
                if (!empty($adminId)) {
                    $resStore = $this->db->Name('xcx_store_store')->select("id")->where_equalTo('aid', $adminId)->execute();
                    if (!empty($resStore)) {
                        $storeIds = array_column($resStore, 'id');
                    }
                }
                // 查找符合条件的经纪人
                $dbSQL = $this->db->Name('xcx_building_reported')->select("br.said, br.agent_id", "br")
                    ->innerJoin('xcx_store_agent', 'sa', 'sa.said=br.said')
                    ->leftJoin('xcx_reported_log', 'rl', "br.id=rl.report_id")
                    ->where_in('br.agent_type', $this->nomalAgent)
                    ->where_greatThanOrEqual('rl.updated_at', $startTime)
                    ->where_lessThanOrEqual('rl.updated_at', $endTime);

                /**
                 * 当前单
                 */
                $storeStr = "";
                $storeVal = [];
                if (!empty($storeIds)) {
                    list($strPower, $valPower) = $this->buildWhereIn(':power', $power);
                    list($strStore, $valStore) = $this->buildWhereIn(':store', $storeIds);
                    $storeStr = "(sa.store_id in {$strStore} and br.status_type in {$strPower}) or";
                    $storeVal = array_merge($valPower, $valStore);
                }

                /**
                 * 历史单
                 */
                $examineStr = "(rl.examine_said = :examineSaid or rl.examine_aid = :examineAid)";
                $examineVal = [':examineSaid' => $saId, ':examineAid' => $agentId];

                /**
                 * 合并
                 */
                $sqlStr = "({$storeStr} {$examineStr})";
                $sqlVal = array_merge($storeVal, $examineVal);

                $dbSQL->where_express($sqlStr, $sqlVal);

                $resLog = $dbSQL->execute();

                //获取经纪人头像昵称今日报备数
                if (!empty($resLog)) {
                    $saIds = array_column($resLog, 'said');
                    $saIds = array_unique($saIds);
                    $dbSQL = $this->db->Name('xcx_store_agent')->select("sa.said, sa.agent_id, au.nickname, au.headimgurl, au.sex, sa.agent_name, sa.agent_img, count(br.id) as count", "sa")
                        ->leftJoin("xcx_agent_user", "au", "au.id=sa.agent_id")
                        ->leftJoin('xcx_building_reported', 'br', "(br.said=sa.said and br.create_time>={$startTime} and br.create_time<={$endTime})")
                        ->where_in('sa.said', $saIds)
                        ->where_notEqualTo('sa.agent_id', 0);

                    if (!empty($name)) {
                        $dbSQL->where_like('au.nickname', "%{$name}%");
                    }

                    $agent = $dbSQL->groupBy("sa.agent_id")->page($page, $pageSize)->execute();
                    if (!empty($agent)) {
                        foreach ($agent as $k => $v) {
                            $data[$k]['id'] = $v['agent_id'];
                            $data[$k]['nickname'] = empty($v['nickname']) ? !empty($v['agent_name']) ? $v['agent_name'] : "ID:{$v['said']}" : $v['nickname'];
                            $defaultHeadImg = $this->manImg;
                            if (isset($v['sex']) && 3 == $v['sex']) {
                                $defaultHeadImg = $this->womanImg;
                            }
                            $data[$k]['headimgurl'] = empty($v['headimgurl']) ? !empty($v['agent_img']) ? $v['agent_img'] : $defaultHeadImg : $v['headimgurl'];
                            $data[$k]['count'] = $v['count'];
                        }
                    }
                }
                break;
            // 工作组长获取工作人员列表
            case 3:
            case 6:
                $mgid = $usrInfo['mgid'];
                $myWork = $this->db->Name('xcx_store_agent')->select("sa.said", "sa")
                    ->leftJoin('xcx_reported_log', 'rl', 'rl.examine_said=sa.said')
                    ->where_in('sa.mgid', $mgid)
                    ->where_express('(sa.type=:type_2 or sa.type=:type_5)', [':type_2' => 2, ':type_5' => 5])
                    ->where_greatThanOrEqual('rl.updated_at', $startTime)
                    ->where_lessThanOrEqual('rl.updated_at', $endTime)
                    ->groupBy('sa.said')
                    ->execute();

                if (!empty($myWork)) {
                    $workSaIds = array_column($myWork, 'said');
                    $workSaIds = array_unique($workSaIds);

                    $dbSQL = $this->db->Name('xcx_store_agent')->select("sa.said, sa.agent_id, au.nickname, au.headimgurl, au.sex, sa.agent_name, sa.agent_img, count(br.id) as count", "sa")
                        ->leftJoin('xcx_agent_user', 'au', 'sa.agent_id=au.id')
                        ->leftJoin('xcx_building_reported', 'br', "(br.said=sa.said and br.create_time>={$startTime} and br.create_time<={$endTime})")
                        ->where_in('sa.said', $workSaIds);

                    if (!empty($name)) {
                        $dbSQL->where_express("(au.nickname like :nick or sa.agent_name like :agname)", [':nick' => "%{$name}%", ':agname' => "%{$name}%"]);
                    }

                    $workData = $dbSQL->groupBy('sa.said')->page($page, $pageSize)->orderBy('br.update_time', "desc")->execute();

                    if (!empty($workData)) {
                        foreach ($workData as $k => $v) {
                            $data[$k]['id'] = $v['said'];
                            $data[$k]['nickname'] = empty($v['nickname']) ? !empty($v['agent_name']) ? $v['agent_name'] : "ID:{$v['said']}" : $v['nickname'];
                            $defaultHeadImg = $this->manImg;
                            if (isset($v['sex']) && 3 == $v['sex']) {
                                $defaultHeadImg = $this->womanImg;
                            }
                            $data[$k]['headimgurl'] = empty($v['headimgurl']) ? !empty($v['agent_img']) ? $v['agent_img'] : $defaultHeadImg : $v['headimgurl'];
                            $data[$k]['count'] = $v['count'];
                        }
                    }
                }
                break;
            // 区域负责人获取经纪人
            case 8:
                $area = $usrInfo['storeInfo']['city'];
                // 所辖区域内的楼盘
                $buildings = $this->db->Name("xcx_building_building")->select("id")->where_like("city", $area)->execute();
                if (!empty($buildings)) {
                    $buildingIds = array_column($buildings, "id");
                }
                // 楼盘相关报备单被那些人审核过
                $dbSQL = $this->db->name('xcx_building_reported')
                    ->select('rl.examine_said', 'br')
                    ->leftJoin('xcx_reported_log', 'rl', 'rl.report_id=br.id')
                    ->where_in('br.building_id', $buildingIds)
                    ->where_notEqualTo('rl.agent_type', 0)
                    ->where_notEqualTo('rl.agent_type', 1);

                // 时间范围
                if (!empty($startTime) || !empty($endTime)) {
                    $dbSQL->where_greatThanOrEqual('rl.updated_at', $startTime)->where_lessThanOrEqual('rl.updated_at', $endTime);
                }

                $brRes = $dbSQL->execute();

                // 获取今日审核过相关报备的人
                if (!empty($brRes)) {
                    $saIds = array_column($brRes, 'examine_said');
                    $saIds = array_unique($saIds);
                }

                // 查找审核人的信息
                if (!empty($saIds)) {
                    $workerDB = $this->db->Name('xcx_store_agent')
                        ->select('sa.said, au.nickname, au.headimgurl, au.name, sa.agent_name', 'sa')
                        ->leftJoin('xcx_agent_user', 'au', 'au.id=sa.agent_id')
                        ->where_in('sa.said', $saIds);
                    // 昵称搜索
                    if (!empty($name)) {
//                    $dbSQL->where_like('agent_name', "%{$name}%");
                        $workerDB->where_express('(au.nickname like :name or au.name like :name or sa.agent_name like :name)', [':name' => "%{$name}%"]);
                    }
                    $workers = $workerDB->execute();
                }

                // 信息处理
                if (!empty($workers)) {
                    foreach ($workers as $k => $v) {
                        $data[$k]['id'] = $v['said'];
                        $data[$k]['nickname'] = empty($v['nickname']) ? !empty($v['agent_name']) ? $v['agent_name'] : "ID:{$v['said']}" : $v['nickname'];
                        $defaultHeadImg = $this->manImg;
                        if (isset($v['sex']) && 3 == $v['sex']) {
                            $defaultHeadImg = $this->womanImg;
                        }
                        $data[$k]['headimgurl'] = empty($v['headimgurl']) ? !empty($v['agent_img']) ? $v['agent_img'] : $defaultHeadImg : $v['headimgurl'];
                    }
                }

                break;
            default:
                return $this->error('权限有误');
                break;
        }

        return $this->success($data);
    }

    // 经纪人/工作人员 选择列表-项目负责人
    public function getProjectAgentList()
    {
        $date = !empty(Context::Post('nowDate')) ? date("Y-m-d", strtotime(Context::Post('nowDate'))) : date("Y-m-d", time());
        $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;
        $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : 10;
        $name = !empty(Context::Post('name')) ? Context::Post('name') : "";
        $usrInfo = $this->getUserInfo();
//        $power = !empty($usrInfo['manageinfo']['auth_report_types']) ? $usrInfo['manageinfo']['auth_report_types'] : [];
        $power = [4];
        $saId = $this->saId;
        $agentId = $this->agentId;
        $leaderId = $this->builddingLeader;
        $adminId = $this->adminId;
        $data = [];

        $dateTime = strtotime($date);

        $startTime = $dateTime;
        $endTime = $dateTime + 86400;

        if (empty($leaderId) && empty($adminId)) {
            return $this->error('权限有误');
        }

        $adminBuild = $this->db->Name("xcx_building_building")->select("id")->where_equalTo("aid", $adminId)->execute();
        if (!empty($adminBuild)) {
            $buildingIds = array_column($adminBuild, "id");
        }

        $dbSQL = $this->db->Name('xcx_building_reported')->select("br.said, br.agent_id", "br")
            ->leftJoin('xcx_reported_log', 'rl', "br.id=rl.report_id")
            ->where_in('br.agent_type', $this->nomalAgent)
            ->where_greatThanOrEqual('rl.updated_at', $startTime)
            ->where_lessThanOrEqual('rl.updated_at', $endTime);

        /**
         * 当前单
         */
        $storeStr = "";
        $storeVal = [];
        if (!empty($buildingIds)) {
            list($strPower, $valPower) = $this->buildWhereIn(':power', $power);
            list($strBuild, $valStore) = $this->buildWhereIn(':build', $buildingIds);
            $storeStr = "(br.building_id in {$strBuild} and br.status_type in {$strPower}) or";
            $storeVal = array_merge($valPower, $valStore);
        }

        /**
         * 历史单
         */
        $examineStr = "(rl.examine_said = :examineSaid or rl.examine_aid = :examineAid)";
        $examineVal = [':examineSaid' => $saId, ':examineAid' => $agentId];

        /**
         * 合并
         */
        $sqlStr = "({$storeStr} {$examineStr})";
        $sqlVal = array_merge($storeVal, $examineVal);

        $dbSQL->where_express($sqlStr, $sqlVal);

        $resLog = $dbSQL->execute();

        //获取经纪人头像昵称今日报备数
        if (!empty($resLog)) {
            $saIds = array_column($resLog, 'said');
            $saIds = array_unique($saIds);
            $dbSQL = $this->db->Name('xcx_store_agent')->select("sa.said, sa.agent_id, au.nickname, au.headimgurl, au.sex, sa.agent_name, sa.agent_img, count(br.id) as count", "sa")
                ->leftJoin("xcx_agent_user", "au", "au.id=sa.agent_id")
                ->leftJoin('xcx_building_reported', 'br', "(br.said=sa.said and br.create_time>={$startTime} and br.create_time<={$endTime})")
                ->where_in('sa.said', $saIds)
                ->where_notEqualTo('sa.agent_id', 0);

            if (!empty($name)) {
                $dbSQL->where_like('au.nickname', "%{$name}%");
            }

            $agent = $dbSQL->groupBy("sa.agent_id")->page($page, $pageSize)->execute();
            if (!empty($agent)) {
                foreach ($agent as $k => $v) {
                    $data[$k]['id'] = $v['said'];
                    $data[$k]['nickname'] = empty($v['nickname']) ? !empty($v['agent_name']) ? $v['agent_name'] : "ID:{$v['said']}" : $v['nickname'];
                    $defaultHeadImg = $this->manImg;
                    if (isset($v['sex']) && 3 == $v['sex']) {
                        $defaultHeadImg = $this->womanImg;
                    }
                    $data[$k]['headimgurl'] = empty($v['headimgurl']) ? !empty($v['agent_img']) ? $v['agent_img'] : $defaultHeadImg : $v['headimgurl'];
                    $data[$k]['count'] = $v['count'];
                }
            }
        }

        return $this->success($data);
    }

    // 客户列表选择
    public function getUserList()
    {
        try {
            $name = Context::Post('name');    // 客户姓名
            $phone = Context::Post('phone');    // 客户手机
            $page = empty(Context::Post('page')) ? 1 : Context::Post('page');
            $pageSzie = empty(Context::Post('page_size')) ? self::MYLIMIT : Context::Post('page_size');

            $agentId = $this->agentId;

            $dbUser = $this->db->Name('xcx_agent_customer')->select("u.id, u.nickName, u.avatarUrl, u.gender, ac.id as acid, ac.user_name, ac.user_phone", 'ac')
                ->leftJoin('xcx_user', 'u', 'u.id=ac.user_id')
                ->where_equalTo("ac.agent_id", $agentId);

            if (!empty($name)) {
                $dbUser->where_express('(u.nickName like :nickname or ac.user_name like :username)', [':nickname' => "%{$name}%", ':username' => "%{$name}%"]);
            }

            if (!empty($phone)) {
                $dbUser->where_like('user_phone', "%{$phone}%");
            }

            $customers = $dbUser->orderBy('ac.create_time', 'desc')->page($page, $pageSzie)->execute();

            if (!empty($customers)) {
                foreach ($customers as &$v) {
                    $v['id'] = !empty($v['id']) ? $v['id'] : 0;
                    $v['nickName'] = !empty($v['nickName']) ? $v['nickName'] : "";
                    $defaultHeadImg = $this->manImg;
                    if (isset($v['gender']) && 3 == $v['gender']) {
                        $defaultHeadImg = $this->womanImg;
                    }
                    $v['avatarUrl'] = !empty($v['avatarUrl']) ? $v['avatarUrl'] : $defaultHeadImg;
                    $v['gender'] = isset($v['gender']) ? $v['gender'] : 0;
                }
            }

            return $this->success($customers);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 调用微信推送
     */
    protected function sendWxApi($sendParam)
    {
        // return ;
        // 微信推送
        // $wxApi = 'http://127.0.0.1:8085/agentapi/agentAjax/sendTmpMsg';
        $wxApi = 'http://chfx.999house.com/agentapi/agentAjax/sendTmpMsg';
        $client = new \GuzzleHttp\Client();
        $client->request('POST', $wxApi, [
            'form_params' => $sendParam
        ]);
    }

    protected function sendWxApi2($sendParam)
    {
        // return ;
        // 微信推送
        $data = [
            'data' => [
                'order_no'    => $sendParam['order_no'],
                'status_type' => $sendParam['status_type']
            ],
            'auth' => 'Nldo4g59sEkW2v7DCmIOruPc6FAMn'
        ];
        switch ($sendParam['type']) {
            // 环节变更
            case "change":
                $uri = '/999admin/Notify/DistributionWxMsg';
                $data['data']['do_type'] = $sendParam['do_type'];
                break;
            // 失效延时器
            case "delay":
                $uri = "/999admin/Notify/CheckProtection";
                $data['delay'] = $sendParam['delay_time'];
                break;
            default:
                return;
                break;
        }
        // var_dump($data);
        $url = $this->urlReported . $uri;
        $client = new \GuzzleHttp\Client(['timeout'  => 1.5]);
        $promise = $client->requestAsync('POST', $url, [
            'form_params' => $data
        ]);

        // $promise->then(
        //     function (\Psr\Http\Message\ResponseInterface $res) {
        //         echo $res->getStatusCode() . "\n";
        //     },
        //     function (\GuzzleHttp\Exception\RequestException $e) {
        //         echo $e->getMessage() . "\n";
        //     }
        // );

        $promise->wait(false);

        // var_dump($res->getBody()->getContents());
    }

    // 环节变更推送
    public function sendTmpMsg()
    {
        try {
            $sendParam = Context::Post();
            if (empty($sendParam)) {
                return;
            }
            $openIds = [];
            $phones = [];
            // 区域负责人必定推送，获取区域负责人
            $area = $sendParam['building_area'];
            $areaCharge = $this->db->Name('xcx_store_agent')->select('au.openid, au.phone', 'sa')->leftJoin('xcx_agent_user', 'au', 'au.id=sa.agent_id')->where_equalTo('sa.type', 8)->where_like('sa.city', "%{$area}%")->execute();
            if (!empty($areaCharge)) {
                $chargeOpenid = array_column($areaCharge, 'openid');
                $openIds = array_merge($openIds, $chargeOpenid);
                // 手机
                $chargePhone = array_column($areaCharge, 'phone');
                $phones = array_merge($phones, $chargePhone);
            }

            // 获取申请人信息
            $agent = $this->db->Name('xcx_store_agent')->select('sa.said, sa.agent_id, sa.type, sa.store_id, sa.mgid, au.openid, sa.agent_name, au.phone, ss.title', 'sa')
                ->leftJoin("xcx_agent_user", "au", "au.id=sa.agent_id")
                ->leftJoin("xcx_store_store", "ss", "ss.id=sa.store_id")
                ->where_equalTo('said', $sendParam['said'])->firstRow();
            if (!empty($agent)) {
                $sqlArr = [];
                /**
                 * 渠道必定推送
                 */
                // 找出该店铺的创建者
                $storeAdmin = $this->db->Name('xcx_store_store')
                    ->select("a.channel_id", "ss")
                    ->innerJoin('admin', 'a', "a.id=ss.aid")
                    ->where_equalTo("ss.id", $agent['store_id'])
                    ->firstRow();
                // 构造条件
                if (!empty($storeAdmin)) {
                    $sqlArr[] = "(sa.type=5 and sa.said={$storeAdmin['channel_id']})";// 合并渠道组员条件
                }

                $openIds[] = $agent['openid'];
                $phones[] = $agent['phone'];
                $agent2 = [];
                // 根据申请人身份,决定推送给哪些人
                if (in_array($agent['type'], $this->nomalAgent)) {// 普通经纪人
                    if (0 == $agent['type']) {// 店员要额外推送到店长
                        $sqlArr[] = "(sa.type=1 and sa.store_id={$agent['store_id']})";
                    }
                    // 根据权限查询工作组员
                    if (in_array($sendParam['status_type'], $this->projectType)) {// 项目组权限
                        $sqlArr[] = "(sa.type=2 and FIND_IN_SET({$sendParam['building_id']}, mb.building_ids))";
                        $sqlStr = implode('or', $sqlArr);
                        $agent2 = $this->db->Name('xcx_store_agent')
                            ->select('sa.agent_id, au.openid, au.phone, sa.type, sa.mgid', "sa")
                            ->innerJoin('xcx_agent_user', "au", "au.id=sa.agent_id")
                            ->leftJoin('xcx_manager_building', 'mb', "sa.said=mb.said")
                            ->where_express($sqlStr)
                            ->execute();
                    } elseif (in_array($sendParam['status_type'], $this->chargeType)) {// 项目负责人
                        // 找出该楼盘的创建者
                        $buildAdmin = $this->db->Name('xcx_building_building')
                            ->select("a.charge_id", "bb")
                            ->innerJoin('admin', 'a', "a.id=bb.aid")
                            ->where_equalTo("bb.id", $sendParam['building_id'])
                            ->firstRow();
                        // 构造条件
                        if (!empty($buildAdmin)) {
                            $sqlArr[] = "(sa.type=7 and sa.said={$buildAdmin['charge_id']})";
                        }
                        if (!empty($sqlArr)) {
                            $sqlStr = implode('or', $sqlArr);// 合并店长和负责人条件
                        } else {
                            $sqlStr = "sa.said=0";
                        }

                        $agent2 = $this->db->Name('xcx_store_agent')
                            ->select('au.openid, au.phone, sa.type, sa.mgid', "sa")
                            ->innerJoin('xcx_agent_user', "au", "au.id=sa.agent_id")
                            ->where_express($sqlStr)
                            ->execute();
                    } else {
                        if (!empty($sqlArr)) {
                            $sqlStr = implode('or', $sqlArr);// 合并条件
                        } else {
                            $sqlStr = "sa.said=0";
                        }

                        $agent2 = $this->db->Name('xcx_store_agent')
                            ->select('au.openid, au.phone, sa.type, sa.mgid', "sa")
                            ->innerJoin('xcx_agent_user', "au", "au.id=sa.agent_id")
                            ->where_express($sqlStr)
                            ->execute();
                    }
                } else {// 工作人员
                    if (2 == $agent['type']) {
                        $agent2[] = $agent;
                    }
                }

                //合并工作人员工作组长
                if (!empty($agent2)) {
                    $workerIds = [];
                    foreach ($agent2 as $k => $v) {
                        if (2 == $v['type'] || 5 == $v['type']) {
                            $mgid = explode(',', $v['mgid']);
                            $workerIds = array_merge($workerIds, $mgid);
                        }
                        $openIds[] = $v['openid']; //查找工作人员
                        $phones[] = $v['phone'];// 手机短信推送
                    }
                    if (!empty($workerIds)) {// 查找组长
                        $workerIds = array_unique($workerIds);
                        // 组装条件
                        $whereStr = [];
                        $whereMg = [];
                        foreach ($workerIds as $key => $val) {
                            $keyWhere = ":mgId_{$key}";
                            $whereStr[] = "FIND_IN_SET({$keyWhere}, sa.mgid)";
                            $whereMg[$keyWhere] = $val;
                        }
                        $sqlStr = implode(" or ", $whereStr);
                        $agent3 = $this->db->Name('xcx_store_agent')
                            ->select('sa.agent_id, au.openid, au.phone, sa.type, sa.mgid', "sa")
                            ->leftJoin('xcx_agent_user', "au", "au.id=sa.agent_id")
                            ->where_express("((sa.type=3 or sa.type=6) and ({$sqlStr}))", $whereMg)
                            ->where_equalTo('sa.status', 1)
                            ->where_equalTo('sa.is_delete', 0)
                            ->execute();
                        if (!empty($agent3)) {
                            foreach ($agent3 as $kk => $vv) {
                                if (!empty($vv['openid'])) {
                                    $openIds[] = $vv['openid'];
                                    $phones[] = $vv['phone'];
                                }
                            }
                        }
                    }
                }
            }

            // var_dump($openIds);
            $statusStr = "{$sendParam['status_type']}|{$sendParam['examine_type']}";
            $statusStr = $this->getReportStatus()[$statusStr];
            if (!empty($openIds)) {
                if (in_array($agent['type'], $this->nomalAgent)) {
                    $organ = !empty($agent['title']) ? $agent['title'] : '店铺';
                } else {
                    $organ = !empty($agent['agent_name']) ? $agent['agent_name'] : '组员';
                }
                $organStr = "九房网({$organ})";
                // $statusStr = "{$sendParam['status_type']}|{$sendParam['examine_type']}";
                // $statusStr = $this->getReportStatus()[$statusStr];

                $openIds = array_unique($openIds);
                $openIds = array_filter($openIds);
//                file_put_contents('openid.txt', json_encode($openIds) . PHP_EOL, FILE_APPEND);
                foreach ($openIds as $ko => $vo) {
                    $rs = $this->sendWxMsgTpl([
                        "touser"      => $vo,
                        "template_id" => 'Tm8xwzV5Em7GI48FXN2J9WLih8S_Xarz1seXpPaNAaQ',
                        "url"         => WX_HOST . '/agentside/pages/customer/record_detail.html?id=' . $sendParam['report_id'],
                        "data"        => [
                            'first'    => ['value' => '报备通知', 'color' => '#173177'],
                            'keyword1' => ['value' => $agent['agent_name'], 'color' => '#173177'],
                            'keyword2' => ['value' => $agent['phone'], 'color' => '#173177'],
                            'keyword3' => ['value' => $sendParam['customer_name'], 'color' => '#173177'],
                            'keyword4' => ['value' => $sendParam['customer_phone'], 'color' => '#173177'],
                            'keyword5' => ['value' => $sendParam['building_name'], 'color' => '#173177'],
                            // 'keyword3'=>['value'=>$statusStr,'color'=>'#173177'],
                            // 'keyword3'=>['value'=>$organStr,'color'=>'#173177'],
                            'remark'   => ['value' => "状态：{$statusStr}", 'color' => '#173177']
                        ]
                    ]);
                }
            }

            // 短信推送
            if (!empty($phones)) {
                $phones = array_unique($phones);
                $phones = array_filter($phones);
                $list = [];
                foreach ($phones as $v) {
                    $list[] = [
                        'mobile' => $v,
                    ];
                }

                $content = "报备楼盘：{$sendParam['building_name']};\n客户姓名：{$sendParam['customer_name']}\n客户电话：{$sendParam['customer_phone']};\n经纪人姓名：{$agent['agent_name']};\n经纪人手机：{$agent['phone']};\n状态：{$statusStr};";

                $phoneData = [
                    'content' => $content,
                    'list'    => $list,
                ];

                // $smsApi = '127.0.0.1:9502/index/public/sendMsgApi';
                $smsApi = 'http://mo.999house.com/index/public/sendMsgApi';
                $client = new \GuzzleHttp\Client();
                $client->request('POST', $smsApi, [
                    'form_params' => $phoneData
                ]);
            }
        } catch (Exception $e) {
            $this->db->Name('log')->insert([
                'title'   => '报备模板通知异常',
                'content' => json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE),
            ])->execute();
        }
    }

    /**
     * 各环节报备单数量
     */
    public function getReportNum()
    {
        try {
            $searchIds = !empty(Context::Post('search_ids')) ? Context::Post('search_ids') : [];
            $name = !empty(Context::Post('name')) ? Context::Post('name') : [];// 客户/经纪人姓名/楼盘
            $nameType = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $date = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $startTime = 0;
            $endTime = 0;
            if (!empty(Context::Post('nowDate'))) {
                $nowDate = Context::Post('nowDate');  //搜查询的日期
                $startTime = strtotime($nowDate);    //当天开始时间戳
                $endTime = $startTime + 86400;      //当天结束时间戳
            }

            $userInfo = $this->getUserInfo();

            $type = $userInfo['type'];

            $saId = $this->saId;

            $defaultData = [];
            $statusArr = $this->getReportType();
            foreach ($statusArr as $key => $val) {
                $defaultData[] = ['type' => $key, 'type_name' => $val, 'num' => 0];
            }

            $leaderId = $this->builddingLeader;
            $adminId = $this->adminId;

            $fields = 'br.status_type, count(*) as num';
            switch ($type) {
                // 店长
                case 1:
                    $storeId = $userInfo['storeInfo']['store_id'];
                    $myDb = $this->db->Name('xcx_building_reported')
                        ->select($fields, 'br')
                        ->innerJoin('xcx_store_agent', 'sa', 'sa.said=br.said')
                        ->where_equalTo('sa.store_id', $storeId)
                        ->where_notEqualTo('br.examine_type', -2);
                    break;
                // 项目组员
                case 2:
                    $buildings = $userInfo['manageinfo']['building_ids'];// 绑定的楼盘
                    $authType = $userInfo['manageinfo']['auth_report_types'];// 审核权限
                    if (-1 != $buildings) {
                        $myDb = $this->db->Name('xcx_building_reported')
                            ->select($fields, 'br')
                            ->where_in('br.status_type', $authType)
                            ->where_notEqualTo('br.said', $saId)
                            ->where_notEqualTo('br.examine_type', -2);
                        if (0 != $buildings) {
                            $buildings = explode(',', $buildings);
                            $myDb->where_in('br.building_id', $buildings);
                        }
                    }
                    break;
                // 渠道组员
                case 5:
                    if (empty($adminId)) {
                        return $this->success($defaultData);
                    }
                    $authType = $userInfo['manageinfo']['auth_report_types'];// 审核权限
                    $myDb = $this->db->Name('xcx_building_reported')
                        ->select($fields, 'br')
                        ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                        ->leftJoin("xcx_store_store", "ss", "sa.store_id=ss.id")
                        ->where_in('br.status_type', $authType)
                        ->where_equalTo('ss.aid', $adminId)
                        ->where_notEqualTo('br.said', $saId)
                        ->where_notEqualTo('br.examine_type', -2);
                    break;
                // 项目组长
                case 3:
                    // 渠道组长
                case 6:
                    $authType = $userInfo['manageinfo']['auth_report_types'];// 审核权限
                    $group = $userInfo['mgid'];// 所辖工作组
                    // 组员
                    $groupWork = $this->db->Name('xcx_store_agent')
                        ->select('said')
                        ->where_in('mgid', $group)
                        ->execute();
                    if (empty($groupWork)) {
                        return $this->success($defaultData);
                    } else {
                        $saidArr = array_column($groupWork, 'said');
                    }
                    $myDb = $this->db->Name('xcx_building_reported')
                        ->select($fields, 'br')
                        ->where_in('br.status_type', $authType)
                        ->where_in('br.said', $saidArr)
                        ->where_notEqualTo('br.examine_type', -2);
                    break;
                // 区域负责人
                case 8:
                    $authType = $userInfo['manageinfo']['auth_report_types'];// 审核权限
                    // 根据区域获取所有该地区的楼盘
                    $area = $userInfo['storeInfo']['city'];
                    $buildings = $this->db->Name('xcx_building_building')->select("id")->where_like("city", "%{$area}%")->execute();
                    if (!empty($buildings)) {
                        $buildingIds = array_column($buildings, 'id');
                    }
                    if (empty($buildingIds)) {
                        $buildingIds = [0];
                    }
                    $myDb = $this->db->Name('xcx_building_reported')
                        ->select($fields, 'br')
                        ->where_in('br.status_type', $authType)
                        ->where_in('br.building_id', $buildingIds)
                        ->where_notEqualTo('br.examine_type', -2);
                    break;
                default:
                    return $this->success($defaultData);
                    break;
            }

            if (!empty($startTime)) {
                $myDb->where_greatThanOrEqual('br.update_time', $startTime);
            }
            if (!empty($endTime)) {
                $myDb->where_lessThanOrEqual('br.update_time', $endTime);
            }

            $res = $myDb->groupBy('br.status_type')->execute();

            if (!empty($res)) {
                $result = [];
                foreach ($res as $k => $v) {
                    $result[] = ['type' => (int)$v['status_type'], 'type_name' => $statusArr[$v['status_type']], 'num' => (int)$v['num']];
                }
                return $this->success($result);
            } else {
                return $this->success($defaultData);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * "确认业绩"环节报备单数量
     */
    public function getReportChargeNum()
    {
        try {
            $searchIds = !empty(Context::Post('search_ids')) ? Context::Post('search_ids') : [];
            $name = !empty(Context::Post('name')) ? Context::Post('name') : [];// 客户/经纪人姓名/楼盘
            $nameType = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $date = !empty(Context::Post('name_type')) ? Context::Post('name_type') : 1;// 1-客户/经纪人姓名 2-楼盘名称
            $startTime = 0;
            $endTime = 0;
            if (!empty(Context::Post('nowDate'))) {
                $nowDate = Context::Post('nowDate');  //搜查询的日期
                $startTime = strtotime($nowDate);    //当天开始时间戳
                $endTime = $startTime + 86400;      //当天结束时间戳
            }

            $leaderId = $this->builddingLeader;
            $adminId = $this->adminId;

            $defaultData[] = ['type' => 4, 'type_name' => "确认业绩", 'num' => 0];

            if (empty($leaderId) || empty($adminId)) {
                return $this->success($defaultData);
            }

//            $userInfo = $this->getUserInfo();

            $statusArr = $this->getReportType();

            $fields = 'br.status_type, count(*) as num';
            $myDb = $this->db->Name('xcx_building_reported')
                ->select($fields, 'br')
                ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                ->where_in('br.status_type', $this->chargeType)
                ->where_equalTo('bb.aid', $adminId)
                ->where_notEqualTo('br.examine_type', -2);

            if (!empty($startTime)) {
                $myDb->where_greatThanOrEqual('br.update_time', $startTime);
            }
            if (!empty($endTime)) {
                $myDb->where_lessThanOrEqual('br.update_time', $endTime);
            }

            $res = $myDb->groupBy('br.status_type')->execute();

            if (!empty($res)) {
                $result = [];
                foreach ($res as $k => $v) {
                    $result[] = ['type' => (int)$v['status_type'], 'type_name' => $statusArr[$v['status_type']], 'num' => (int)$v['num']];
                }
                return $this->success($result);
            } else {
                return $this->success($defaultData);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 校验保护期
     * @param $param
     * @return bool
     */
    protected function checkProtectTime($param)
    {
        $check = TRUE;
        $status = $param['status'];
        $takeTime = $param['take_time'];
        $updateTime = $param['update_time'];
        $protectSet = $param['protect_set'];

//        var_dump($param);

        // 带看的保护期从带看时间算起，其他环节保护期从状态变更那时算起
        // if (2 == $status) {
        //     $startTime = $takeTime;
        // } else {
        //     $startTime = $updateTime;
        // }
        $startTime = $updateTime;

        // 当前记录状态如果与所查询状态一致，则判断是否过期
        $protect = json_decode($protectSet, TRUE);
        if ($status >= 1 && $status <= 3) {
            //每个流程环节保护时间-规范到小时
            $keyStatus = 'status' . $status . '_hours';
            $protect_set_hours = intval($protect[$keyStatus]);
            // 是否设置保护期 是-使用设置的保护期 否-使用默认的保护期
            if ($protect_set_hours <= 0) {
                switch ($status) {
                    case 1:
                        $protect_set_hours = 3;
                        break;
                    case 2:
                    case 3:
                        $protect_set_hours = 30;
                        break;
                    default:
                        $protect_set_hours = 0;
                        break;
                }
            }
            if (!empty($protect_set_hours)) {
                $protect_time = $protect_set_hours * 86400;
                $protectTimeEnd = $startTime + $protect_time;//按最后的更新时间-小时
                if ($protectTimeEnd <= time()) {
                    $check = FALSE;
                }
            }
        }

        return $check;
    }

    protected function getProtectTime($protectSet, $statusType)
    {
        $protectTime = 0;
        if (!empty($protectSet)) {
            $protectSet = json_decode($protectSet, TRUE);
            $key = "status{$statusType}_hours";
            $protectTime = !empty($protectSet[$key]) ? (int)$protectSet[$key] : 0;
            // 是否设置保护期 是-使用设置的保护期 否-使用默认的保护期
            if ($protectTime <= 0) {
                switch ($statusType) {
                    case 1:
                        $protectTime = 3;
                        break;
                    case 2:
                    case 3:
                        $protectTime = 30;
                        break;
                    default:
                        $protectTime = 0;
                        break;
                }
            }
            $protectTime *= 86400;
        }
        return $protectTime;
    }

    protected function sendParamToWx($data)
    {
        $orderNo = !empty($data['order_no']) ? $data['order_no'] : '';// 单号
        $statusType = !empty($data['status_type']) ? $data['status_type'] : 0;// 操作前的状态 用于判断do_type模板类型
        $nextStatus = !empty($data['next_status']) ? $data['next_status'] : 1;// 操作后的状态 用于找人
        $protectSet = !empty($data['protect_set']) ? $data['protect_set'] : "";// 保护期
        $isPass = !empty($data['is_pass']) ? $data['is_pass'] : 0;// 审批状态

        $sendParam = [
            'order_no'    => $orderNo,
            'status_type' => $nextStatus,
        ];
        // 报备环节变更推送
        if (!empty($statusType)) {// 获取do_type
            if (6 == $statusType) {
                $sendParam['do_type'] = 'commission';
            } else {
                $sendParam['do_type'] = 'examine';
            }
        } else {
            $sendParam['do_type'] = 'log';
        }
        if(-1 == $isPass) {
            $sendParam['do_type'] = 'reject';
        }
        $sendParam['type'] = 'change';
        $this->sendWxApi2($sendParam);
        // 延时任务
        if (in_array($nextStatus, [1, 2, 3])) {// 有保护期的1到3环节
            unset($sendParam['do_type']);
            $delayTime = $this->getProtectTime($protectSet, $nextStatus);
            if ($delayTime) {
                $sendParam['type'] = 'delay';
                $sendParam['delay_time'] = $delayTime;
                $this->sendWxApi2($sendParam);
            }
        }

    }

    //获取店铺列表
    public function getStoreName()
    {

        if (!empty($this->saId)) {
            if (is_array($this->saId)) {
                $storeId = array_keys($this->saId);
            } else {
                $storeId = [$this->saId];
            }
        }

        if (empty($storeId)) {
            return [];
        }
        $res = $this->db->name('xcx_store_agent')->select('store_id')->where_in('said', $storeId)->execute();

        $storeData = [];
        foreach ($res as $key => $value) {
            $data = explode(',', $value['store_id']);
            $storeData = array_merge($storeData, $data);
        }
        $res = $this->db->name('xcx_store_store')->select('id,title as name')->where_in('id', $storeData)->execute();

        return $this->success($res);
    }

    //获取绑定的用户列表
    public function getBindStoreName()
    {
        $type = Context::Post('type'); // 类型 1为自己绑定的店铺 2为下级绑定的店铺
        $name = Context::Post('name'); // 类型 1为自己绑定的店铺 2为下级绑定的店铺
        if (empty($type)) {
            return $this->error('类型有误');
        }
        $userInfo = $this->getUserInfo();
        $trans = $this->transforAuthToStatus($userInfo['type_auth']['duplicate']);
        $keys = array_keys($trans);
        if ($type == 1) {//判断
            $aidArray = [];
            if (!in_array('create-store', $keys)) {
                return $this->success([]);
            }

            foreach ($userInfo['said'] as $v) {
                if (!empty($v['aid'])) {
                    array_push($aidArray, $v['aid']);
                }
            }

            if (empty($aidArray)) {
                return $this->success([]);
            }

            $list = $this->db->name('xcx_store_store')
                ->select('ss.id,ss.aid,ss.title,count(r.store_id) as reported_count', 'ss')
                ->leftJoin('xcx_building_reported', 'r', 'r.store_id = ss.id AND r.examine_type =  1 
	AND r.status_type !=  6 ')
                ->where_in('ss.aid', $aidArray);
            if (!empty($name)) {
                $list = $list->where_like("ss.title", '%' . $name . '%');
//                $list = $list->where_equalTo('ss.title',$name);
            }

            $list = $list->groupBy('ss.id')
                ->execute();

        } else {
            if (!in_array('subordinate-work', $keys)) {
                return $this->success([]);
            }
            $mAid = [];
            $sAid = [];
            $list = [];
            foreach ($userInfo['said'] as $value) {
                if (!empty($value['group'])) {
                    foreach ($value['group'] as $v) {
                        array_push($mAid, $v['id']);
                    }
                }
            }

            $mySaid = array_keys($userInfo['said']);
            $mySaidStr = implode(',', $mySaid);

            foreach ($mAid as $v) {

                $data = $this->db->name('xcx_store_agent');
                if (!empty($name)) {
                    $data = $data->where_equalTo('agent_name', $name);
                }
                $data = $data->select('said,agent_name,agent_img')->find_in_set('mgid', $v)->where_express("said not in ({$mySaidStr})")->execute();
                if (empty($data)) {
                    return $this->success([]);
                }
                $list = array_merge($list, $data);
                $data = array_column($data, 'said');
                $sAid = array_merge($sAid, $data);

            }

            $aidData = $this->db->name('admin')->select('id,channel_id')->where_in('channel_id', $sAid)->execute();
            if (empty($list)) {
                return $this->success([]);
            }

            $aidData = array_column($aidData, 'id', 'channel_id');
            foreach ($list as $listKey => &$listValue) {
                if (empty($aidData[$listValue['said']])) {
                    $listValue['store_list'] = [];
                } else {
                    $storeList = $this->db->name('xcx_store_store')->select('id,title')
                        ->where_equalTo('aid', $aidData[$listValue['said']])->execute();
                    $listValue['store_list'] = empty($storeList) ? [] : $storeList;
                }

            }
        }

        return $this->success($list);
    }

    //店员列表
    public function bindStoreInfo()
    {
        $storeId = Context::Post('store_id');
        $name = Context::Post('name');
        if (empty($storeId)) {
            return $this->error('缺少店铺id');
        }
        $list = $this->db->name('xcx_store_agent')->where_equalTo('sa.store_id', $storeId);
        if (!empty($name)) {
            $list = $list->where_like("sa.agent_name", '%' . $name . '%');
//            $list = $list->where_equalTo('sa.agent_name',$name);
        }
        $list = $list->select('sa.said,sa.agent_name,sa.store_id,sa.agent_img,count(r.id) as reported_count', 'sa')
            ->leftJoin('xcx_building_reported', 'r', 'r.store_id = sa.store_id and r.examine_type = 1 
    AND r.status_type != 6 ')
            ->where_notEqualTo('sa.agent_id', 0)
            ->groupBy('sa.said')
            ->execute();

        return $this->success($list);

    }

    /**
     * 跟进记录新
     */
    public function getFollowDataNew()
    {
        try {
            $id = Context::Post('id');    //楼盘报备id
            $saIdData = $this->saId;
            $said = array_keys($saIdData);
            $agentId = $this->agentId;

            if (empty($said)) {
                return $this->error('您没有权限');
            }

            $detail = $this->db->Name('xcx_building_reported')
                ->select("br.id, br.agent_id, br.said, br.user_id, br.user_name, br.user_phone, br.building_id, br.status_type, br.examine_type, br.commission as iniCommission, br.user_gender, br.take_time, br.update_time, u.nickName, u.avatarUrl, bb.name as bname, bb.pic, bb.house_type, bb.commission,bb.commission_type,bb.fold,bb.store_manager_commission,bb.team_member_commission ,bb.city, bb.area, bb.protect_set, bb.aid", "br")
                ->leftJoin('xcx_user', 'u', "u.id=br.user_id")
                ->leftJoin('xcx_building_building', 'bb', "bb.id=br.building_id")
                ->where_equalTo("br.id", $id)
                ->firstRow();

            if (empty($detail)) {
                return $this->error('未获取到数据');
            }

            // 权限校验
            $isSelf = false;// 是否自己提交的単
            $isLog = false;// 是否能添加材料
            $isExamine = false;// 是否能审批
            if (!in_array($detail['said'], $said)) {
                foreach ($saIdData as $sk => $s) {
                    /**
                     * 当前用户是否与该单有关联
                     * 只要某个said在某个环节有权限即可
                     */
                    // $duplicate = $this->RoleAuth[$s['type']]['duplicate'];
                    // $duplicate = $this->transforAuthToStatus($duplicate);
                    // $duplicate = !empty($duplicate) ? array_keys($duplicate) : [];
                    // $examine = $this->RoleAuth[$s['type']]['examine'];
                    // $examine = $this->transforAuthToStatus($examine);
                    // $examine = !empty($examine) ? array_keys($examine) : [];
                    // $auth = array_unique(array_merge($duplicate, $examine));
                    // if(!empty($auth)) {
                    //     foreach($auth as $a) {
                    //         $can = $this->processing($id, $detail['said'], $sk, $a);
                    //         if($can) {
                    //             break 2;// 直接退出两重循环
                    //         }
                    //     }
                    // }
                    /**
                     * 当前用户是否能添加材料
                     */
                    $log = !empty($this->RoleAuth[$s['type']]['log'][$detail['status_type']]) ? $this->RoleAuth[$s['type']]['log'] : [];
                    if (!empty($log) && !$isLog) {
                        foreach ($log as $l) {
                            $isLog = $this->processing($id, $detail['said'], $sk, $l);
                            if ($isLog) {
                                break;
                            }
                        }
                    }
                    /**
                     * 当前用户是否能审批
                     */
                    $examine = !empty($this->RoleAuth[$s['type']]['examine'][$detail['status_type']]) ? $this->RoleAuth[$s['type']]['examine'] : [];
                    if (!empty($examine) && !$isExamine) {
                        foreach ($examine as $e) {
                            $isExamine = $this->processing($id, $detail['said'], $sk, $e);
                            if ($isExamine) {
                                break;
                            }
                        }
                    }
                }
            } else {
                // 是自己提交的単
                $isSelf = true;
                $isLog = true;
            }

            //失效状态判断
            if ($detail['status_type'] >= 1 && $detail['status_type'] <= 3) {
                $paramCheck = [
                    'status'      => $detail['status_type'],
                    'take_time'   => $detail['take_time'],
                    'update_time' => $detail['update_time'],
                    'protect_set' => $detail['protect_set'],
                ];
                $checkProtect = $this->checkProtectTime($paramCheck);
                if (!$checkProtect) {
                    $detail['examine_type'] = -2;
                }
            }

            // 上一次访问时间
            $last_visit_time = '';
            if (!empty($detail['user_id'])) {
                $historyData = $this->db->Name('xcx_user_browsing_history')->select('start_time')->where_equalTo('browse_type', '1')->where_equalTo('user_id', $detail['user_id'])->where_equalTo('agent_id', $agentId)->orderBy('start_time', 'desc')->firstRow();
                if (!empty($historyData)) {
                    $last_visit_time = date("Y.m.d H:i", $historyData['start_time']);
                }
            }

            // 佣金
            switch ($detail['commission_type']) {
                // 固定金额
                case 1:
                    $commission = $detail['commission'] . "元";
                    break;
                // 百分比
                case 2:
                    $commission = $detail['commission'] . "%";
                    break;
                default:
                    $commission = 0;
                    break;
            }

            // 项目驻场电话
            $data['agent_phone'] = '';
            $ab = $this->db->Name('xcx_manager_building')->select("au.phone", 'mb')->leftJoin('xcx_store_agent', 'sa', 'sa.said=mb.said')->leftJoin('xcx_agent_user', 'au', 'au.id=sa.agent_id')->where_express('FIND_IN_SET(:build, mb.building_ids)', [':build' => $detail['building_id']])->firstRow();
            if (!empty($ab['phone'])) {
                $data['agent_phone'] = $ab['phone'];
            }

            // 报备单基础信息
            $data['name'] = empty($detail['user_name']) ? empty($detail['nickName']) ? '' : $detail['nickName'] : $detail['user_name'];
            $defaultHeadImg = $this->manImg;
            if (isset($detail['user_gender']) && 3 == $detail['user_gender']) {
                $defaultHeadImg = $this->womanImg;
            }
            $data['headimgurl'] = empty($detail['avatarUrl']) ? $defaultHeadImg : $detail['avatarUrl'];
            $data['phone'] = empty($detail['user_phone']) ? '' : $detail['user_phone'];
            $data['last_visit_time'] = $last_visit_time;
            $data['status_type'] = $detail['status_type'];
            $data['examine_type'] = $detail['examine_type'];
            $keyType = "{$data['status_type']}|{$data['examine_type']}";
            $data['type_str'] = $this->getReportStatus()[$keyType];
            $data['building_name'] = empty($detail['bname']) ? "" : $detail['bname'];
            $data['building_cover'] = empty($detail['pic']) ? "" : $detail['pic'];
            $data['house_type'] = $detail['house_type'];
            $data['city'] = $detail['city'];
            $data['area'] = $detail['area'];
            $data['commission'] = $commission;
            $data['is_log'] = !empty($isLog) ? 1 : 0;
            $data['is_examine'] = !empty($isExamine) ? 1 : 0;
            $data['is_self'] = !empty($isSelf) ? 1 : 0;

            // 获取详细进程
            $list = $this->db->Name('xcx_reported_log')->select("rl.content, rl.imgs, rl.status_type, rl.agent_type, rl.examine_type, rl.is_admin, rl.updated_at, au.nickname, au.headimgurl, au.sex, au.phone", "rl")
                ->leftJoin('xcx_agent_user', 'au', 'au.id=rl.examine_aid')
                ->where_equalTo('rl.report_id', $detail['id'])
                ->orderBy('rl.updated_at', 'desc')
                ->execute();
            if (!empty($list)) {
                foreach ($list as &$v) {
                    $v['nickname'] = !empty($v['nickname']) ? $v['nickname'] : '';
                    $defaultImg = $this->manImg;
                    if (isset($v['sex']) && 3 == $v['sex']) {
                        $defaultImg = $this->womanImg;
                    }
                    $v['headimgurl'] = !empty($v['headimgurl']) ? $v['headimgurl'] : $defaultImg;
                    $v['position'] = isset($this->RoleAuth[$v['agent_type']]['name']) ? $this->RoleAuth[$v['agent_type']]['name'] : '未知身份';

                    $v['imgs'] = json_decode($v['imgs'], TRUE);
                    if (!empty($v['status_type'])) {
                        $key = "{$v['status_type']}|{$v['examine_type']}";
                        $v['status_str'] = $this->getReportStatus2()[$key];// 状态描述
                    }
                    $v['time_day'] = date("d", $v['updated_at']);
                    $v['time_year_month'] = date("Y / m", $v['updated_at']);
                }
            } else {
                $list = [];
            }
            $data['list'] = $list;

            // 不是自己的提交的报备单时，检查/标记已读记录
            if (!in_array($detail['said'], $said)) {
                $readLog = $this->db->Name('xcx_report_read_log')
                    ->select('id, is_read')
                    ->where_equalTo('agent_id', $agentId)
                    ->where_equalTo('report_id', $id)
                    ->firstRow();
                if (!empty($readLog)) {
                    if (0 == $readLog['is_read']) {
                        $resLog = $this->db->Name('xcx_report_read_log')->update(['is_read' => 1, 'updated_at' => time()])->where_equalTo('id', $readLog['id'])->execute();
                    }
                } else {
                    $inserData = [
                        'report_id'  => $id,
                        'said'       => 0,// 同一个agent_id可能对应多个said，故以agent_id为准
                        'agent_id'   => $agentId,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $resLog = $this->db->Name('xcx_report_read_log')->insert($inserData)->execute();
                }
            }

            return $this->success($data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}