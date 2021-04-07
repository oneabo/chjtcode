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
include System . DS . 'Encryption.php';
include System . DS . 'Session.php';
include System . DS . 'Upload.php';

include System . DS . 'RedisBase.php';
class Common extends Controller{
    const MYLIMIT=10;

    //控制器/方法登录白名单
    private $login_white_routes = [
        'UserAjax/wxlogin',
        'UserAjax/getinfo',
        'userAjax/agentCustomer',
        'articleAjax/getArticleDetailData',
        'articleAjax/getArticleHome',
        'articleAjax/getAgentInfo',
        'articleAjax/getReply',
        'articleAjax/getDataInfo',
        'userAjax/addMember',
        'agentAjax/sendTmpMsg',
    ];
    protected $agentId = 0;
    protected $saId = 0;
    protected $openId='';
    protected $db = null;
    protected $redis = null;
    protected $adminId = 0;//有后台角色的后台账号id
    protected $builddingLeader = 0;//负责人id

    protected $defaultHeadImg = "/upload/default/default_head.png";
    protected $manImg = "/upload/default/man_head.png";
    protected $womanImg = "/upload/default/woman_head.png";
    protected $urlReported = 'http://192.168.1.83:9501';

    /**
     * 角色与权限
     */
    protected $RoleAuth = [
        /**
         * 与报备单的联系
         * self:自己 subordinate:下级店员 building:绑定的楼盘 subordinate-building：下级绑定的楼盘 create-store:创建的店铺 subordinate-store:下级绑定的店铺 create-building:创建的楼盘 city:城市 subordinate-work:下级管理
         */
        // 店员（经纪人）
        '0' => [
            'name'=> '店员',
            'duplicate'=> [], //抄送
            'examine' => [],//待处理
            'log'=>[
                1 => ['self'], 
                2 => ['self'], 
                3 => ['self'], 
                4 => ['self'], 
                5 => ['self'], 
                6 => ['self'],
            ],//日志追加
            'add'=> [1],//添加操作 [报备]
        ],
        // 店长（经纪人）
        '1' => [
            'name'=> '店长',
            'duplicate'=> [
                1 => ['subordinate'], 
                2 => ['subordinate'], 
                3 => ['subordinate'], 
                4 => ['subordinate'], 
                5 => ['subordinate'], 
                6 => ['subordinate'],
            ], //抄送
            'examine' => [],//待处理
            'log'=>[
                1 => ['self'], 
                2 => ['self'], 
                3 => ['self'], 
                4 => ['self'], 
                5 => ['self'], 
                6 => ['self'],
            ],//日志追加
            'add'=> [1],//添加操作 [报备]
            'store_manager' => true  //标识店长
        ],
        // 项目经理（原项目经理）
        '2' => [
            'name'=> '项目经理',
            'duplicate'=> [], //抄送
            'examine' => [
                1 => ['building'], 
                2 => ['building'], 
                3 => ['building'],
            ],//待处理
            'log'=>[
                1 => ['building'], 
                2 => ['building'], 
                3 => ['building'],
            ],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
        // 项目主管（原项目组长）
        '3' => [
            'name'=> '项目主管',
            'duplicate'=> [
                1 => ['subordinate-work'], 
                2 => ['subordinate-work'], 
                3 => ['subordinate-work'],
            ], //抄送
            'examine' => [],//待处理
            'log'=>[],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
        // 渠道专员（原渠道组员）
        '5' => [
            'name'=> '渠道专员',
            'duplicate'=> [
                1 => ['create-store'], 
                2 => ['create-store'], 
                3 => ['create-store'], 
                4 => ['create-store'], 
            ], //抄送
            'examine' => [
                5 => ['create-store'], 
                6 => ['create-store'],
            ],//待处理
            'log'=>[
                5 => ['create-store'], 
                6 => ['create-store'],
            ],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
        // 渠道总监（原渠道组长）
        '6' => [
            'name'=> '渠道总监',
            'duplicate'=> [
                1 => ['create-store'], 
                2 => ['create-store'], 
                3 => ['create-store'], 
                4 => ['create-store'], 
                5 => ['subordinate-work'], 
                6 => ['subordinate-work'],
            ], //抄送
            'examine' => [
                5 => ['create-store'], 
                6 => ['create-store'],
            ],//待处理
            'log'=>[
                5 => ['create-store'], 
                6 => ['create-store'],
            ],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
        // 项目总监（原项目负责人）
        '7' => [
            'name'=> '项目总监',
            'duplicate'=> [], //抄送
            'examine' => [
                4 => ['create-building']
            ],//待处理
            'log'=>[
                4 => ['create-building']
            ],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
        // 总负责人（原区域负责人）
        '8' => [
            'name'=> '总负责人',
            'duplicate'=> [
                1 => ['city'], 
                2 => ['city'], 
                3 => ['city'], 
                4 => ['city'], 
                5 => ['city'], 
                6 => ['city'],
            ], //抄送
            'examine' => [],//待处理
            'log'=>[],//日志追加
            'add'=> [],//添加操作 [报备]
        ],
    ];

    public function __construct(){

        $uid = Context::post('uid', 94);
        Session::set('agent_id', $uid);
// Session::set('said',143);
//        Session::destory();

        $controller = Context::$controller;
        $action = Context::$action;
        $route = strtolower($controller.'/'.$action);
        $this->agentId = Session::get('agent_id');
        $this->openId = Session::get('openid');
        if(!$this->isWhiteRoutes($route,$this->login_white_routes)&&empty($this->agentId)){
            echo json_encode(['ajaxerror'=>true]);
            exit;
        }
        $this->db = new Query();
        $this->db2 = new Query(new DataBase2());

        $this->redis = RedisBase::getInstance();

//        $this->redis->set('abc', '234');

        if(!empty($this->agentId)){
            $this->saId = $this->getUserInfo()['said']; //38;//
        }
    }

    //检测是否是白名单中
    protected function isWhiteRoutes($route,$white_routes){
        foreach ($white_routes as $item){
            if(strtolower($item)==$route){
                return true;
            }
        }
        return false;
    }

    // 报备环节
    protected function getReportType()
    {
        return [
            1 => '报备',
            2 => '带看',
            3 => '成交',
            4 => '确认业绩',
            5 => '开票',
            6 => '结佣',
        ];
    }

    public function clearSession(){
        Session::destory();
    }

    // 报备状态
    protected function getReportStatus()
    {
        return [
            '1|-2' => '报备失效',
            '1|-1' => '报备驳回',
            '1|1' => '报备中',
            '1|2' => '报备完成',
            '2|-2' => '带看失效',
            '2|-1' => '带看驳回',
            '2|1' => '带看中',
            '2|2' => '带看完成',
            '3|-2' => '成交失效',
            '3|-1' => '成交驳回',
            '3|1' => '成交中',
            '3|2' => '成交完成',
            '4|-2' => '确认业绩失效',
            '4|-1' => '确认业绩驳回',
            '4|1' => '确认业绩中',
            '4|2' => '确认业绩完成',
            '5|-2' => '开票失效',
            '5|-1' => '开票驳回',
            '5|1' => '开票中',
            '5|2' => '开票完成',
            '6|-2' => '结佣失效',
            '6|-1' => '结佣驳回',
            '6|1' => '结佣中',
            '6|2' => '结佣完成',
        ];
    }

    protected function getReportStatus2()
    {
        return [
            '1|-2' => '报备失效',
            '1|-1' => '报备驳回',
            '1|1' => '报备中',
            '1|2' => '报备完成',
            '2|-2' => '带看失效',
            '2|-1' => '带看驳回',
            '2|1' => '报备完成，带看中',
            '2|2' => '带看完成',
            '3|-2' => '成交失效',
            '3|-1' => '成交驳回',
            '3|1' => '带看完成，待成交',
            '3|2' => '带看完成',
            '4|-2' => '确认业绩失效',
            '4|-1' => '确认业绩驳回',
            '4|1' => '成交完成，待确认业绩',
            '4|2' => '成交完成',
            '5|-2' => '开票失效',
            '5|-1' => '开票驳回',
            '5|1' => '确认业绩完成，待开票',
            '5|2' => '确认业绩完成',
            '6|-2' => '结佣失效',
            '6|-1' => '结佣驳回',
            '6|1' => '开票完成，待结佣',
            '6|2' => '结佣完成',
        ];
    }

    // 账号身份
    protected function getStoreType()
    {
        return [
            0 => '店员',
            1 => '店长',
            2 => '项目组员',
            3 => '项目组长',
            4 => '财务',
            5 => '渠道组员',
            6 => '渠道组长',
            7 => '项目负责人',
            8 => '区域负责人',
        ];
    }

    protected function getAgentType(){
        return $this->getUserInfo()['type'];
    }

    protected function getAgentId(){
        if(empty(Session::get('agent_id'))){
            throw new Exception('用户未登录');
        }
        return $this->agentId = Session::get('agent_id');
    }
    protected function getOpenId(){
        if(empty(Session::get('openid'))){
            throw new Exception('用户未登录');
        }
        return $this->openId = Session::get('openid');
    }

    //计算发布时间据当前时间 如1秒前 1分钟前 1小时 1天 1个星期 1个人月 1年
    protected function format_dates($time) {
        if($time <= 0) return '刚刚';
        $nowtime = time();
        if ($nowtime <= $time) {
            return "刚刚";
        }
        $t = $nowtime - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            $c = floor($t/$k);
            if ($c > 0) {
                return $c . $v . '前';
            }
        }
    }
    //格式化数字转为nk或nw
    function formatting_number($num,$decimal=1){
        $res=0;
        if($num>=10000){
            $res=sprintf("%.".$decimal."f",$num/10000).'w';
        }else if($num>=1000){
            $res=sprintf("%.".$decimal."f",$num/1000).'k';
        }else{
            $res=$num;
        }
        return $res;
    }
    /**
     * get请求
     * @param $url
     * @return mixed
     */
    protected function sendGet($url=''){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    /**
     * post请求
     * @param $url
     * @param $data
     * @return mixed
     */
    protected function sendPost($url='',$data=[]){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    protected function success($data=[],$mag='操作成功'){
        $userInfo = $this->getUserInfo();
        if($data["_userInfo"]){
            $userInfo=$data["_userInfo"];
            unset($data["_userInfo"]);
        }
        echo json_encode([
            'data'=> $data,
            'success'=> true,
            'message'=> $mag,
            'code' => '1',
            'mestatus' => $userInfo['mestatus'],
            '_auth' => $userInfo['list_auth']
        ],JSON_UNESCAPED_UNICODE);
    }
    protected function error($msg='操作失败',$code=0,$data=[]){
        if(is_array($msg)){
            $data = !empty($msg['data'])?$msg['data']:[];
            $code = !empty($msg['code'])?$msg['code']:0;
            $msg = $msg['msg'];
        }
        $userInfo = $this->getUserInfo();
        if($data["_userInfo"]){
            $userInfo=$data["_userInfo"];
            unset($data["_userInfo"]);
        }

        echo json_encode([
            'data'=> $data,
            'success'=> false,
            'message'=> $msg,
            'code' => $code,
            'mestatus' => $userInfo['mestatus'],
            '_auth' => $userInfo['list_auth']
        ],JSON_UNESCAPED_UNICODE);
    }
    /*=============================================== 内部接口 ====================================================*/
    //获取用户信息-弃用
    protected function getUserInfoDrop($refresh=0,$id=0){
        $id=$id?$id:$this->agentId;
        $data=[];
        if(empty($id)){
            return $data;
        }

        $userinfo = Session::get('_userinfo');
        /*if($refresh==0&&!empty($userinfo)&&$userinfo['expire_time']>time()){
            return $userinfo;
        }*/
        //获取用户信息
        //$agentRow=$this->db->Name('xcx_agent_user')->select()->where_equalTo('id',$id)->firstRow();
        $agentRow = $this->db->Name('xcx_agent_user')->select()->where_equalTo('id',$id)->firstRow();
        $leaderId = 0;
        if(empty($agentRow)){
            return [];
        }

        //查询是否是经纪人
        $sainfoList = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id',$id)->where_equalTo('is_delete', 0)->execute();
        $data = [
            'storeInfo' => [
                'store_id'=> 0,
                'storename'=>"暂无店铺",//所属店铺
                'province'=> '', //省份
                'city'=> '', //城市
                'area'=> '', //区域
                'status'=> -1, //-1禁用 0申请中 1开通
            ],
            'type'=> -1,
            'typename'=> '', //所属职位
            'phone'=> $agentRow['phone'], //电话
            'signature'=> $agentRow['signature'],  //个性签名
            'special_label'=> explode(',',$agentRow['special_label']), //标签
            'headimgurl'=> $agentRow['headimgurl'], //头像
            'mestatus'=> '-2',  //经纪人综合性整体状态,是否可以进行报备操作，-2还不是经纪人
            'status'=>'',
            'name'=> $agentRow['name'], //姓名
            'nickname'=> $agentRow['nickname'], //微信昵称
            'uname'=> empty($agentRow['name'])?$agentRow['nickname']:$agentRow['name'],//姓名
            'said'=> 0,//成员id
            'mgid'=> [],// 所在组(工作人员/组长)
            'expire_time'=>time()+30*60,
            'manageinfo'=>[
                'building_ids'=> -1, //未绑定任何楼盘
                'auth_report_types'=> [] //可操作的报备流程环节
            ]
        ];

        if(empty($sainfoList)){
            return $data;
        }
        $sainfo =[];
        $len = count($sainfoList);
        if($len==1){
            $sainfo = $sainfoList[0];
            if($sainfo['type']=='7'){
                $leaderId = intval($sainfoList[0]['said']);//负责人said
                if(1 == $sainfo['status'] && 0 == $sainfo['is_delete']) {
                    $data['storeInfo']['status'] = 1;
                }
            }
        }else{
            foreach ($sainfoList as $item){
                if($item['type']!='7'){
                    $sainfo = $item;
                }
                if($item['type']=='7'){
                    $leaderId = $item['said'];//负责人said
                    $leaderInfo = $item;
                }
            }
        }
        unset($sainfoList);
        //@todo $adminId
        if(in_array($sainfo['type'],['5','6'])){//渠道
            $admininfo = $this->db->Name('admin')->select('id')->where_equalTo('channel_id',$sainfo['said'])->firstRow();
            $this->adminId = intval($admininfo['id']);
        }elseif(!empty($leaderId)){//负责人
            $admininfo = $this->db->Name('admin')->select()->where_equalTo('charge_id',$leaderId)->firstRow();
            $this->adminId = intval($admininfo['id']);
        }

        $data['said'] = intval($sainfo['said']);//成员id
        if(($sainfo['type']=='0'||$sainfo['type']=='1')){
            //店员信息
            $storeData=$this->db->Name('xcx_store_store')->select()->where_equalTo('id',$sainfo['store_id'])->firstRow();
            if(!empty($storeData)){
                $data['storeInfo'] = [
                    'store_id'=> $storeData['id'], //店铺id
                    'storename'=> $storeData['title'], //所属店铺
                    'province'=> $storeData['province'], //省份
                    'city'=> $storeData['city'], //城市
                    'area'=> $storeData['area'], //区域
                    'status'=> $storeData['status'], //店铺状态
                ];
            }
        }

        if(in_array($sainfo['type'],['2','3','4'])){//工作人员
            if(2 == $sainfo['type']) {
                // 组员判断所在组状态
                $gx = $this->db->Name('xcx_manager_user_gx')
                    ->select('id')
                    ->where_equalTo('id', $sainfo['mgid'])
                    ->where_equalTo('status', 1)
                    ->where_equalTo('is_delete', 0)
                    ->firstRow();
                if(!empty($gx)) {
                    $data['storeInfo']['status'] = 1;
                } else {
                    $data['storeInfo']['status'] = 0;
                }
            } else {
                $data['storeInfo']['status'] = 1;
            }
            if($len==2){
                if(1 == $leaderInfo['status'] && 0 == $leaderInfo['is_delete']) {
                    $data_status = 1;
                }
                if(!empty($data['storeInfo']['status']) || !empty($data_status)) {
                    $data['storeInfo']['status'] = 1;
                } else {
                    $data['storeInfo']['status'] = 0;
                }
            }

            //工作人员信息
            $mgData=$this->db->Name('xcx_manager_building')->select('id,building_ids,is_delete,auth_report_types')->where_equalTo('said',$sainfo['said'])->firstRow();
            if(!empty($mgData['id'])&&$mgData['is_delete']==0){
                $data['manageinfo'] = [
                    'building_ids'=> $mgData['building_ids'],
                    //'building_list'=>$building_list,
                    //'auth_report_types'=> $mgData['auth_report_types']?explode(',',$mgData['auth_report_types']):$mgData['auth_report_types'],
                    'auth_report_types'=> ['1','2','3'] //@todo auth_report_types 返回权限id集合
                ];
            }

//            $data['manageinfo'] = [
//                'auth_report_types'=> ['1','2','3'] //@todo auth_report_types 返回权限id集合
//            ];

            $data['storeInfo']['storename'] = '工作人员';
            // 获取其所在组
            $mgid = explode(',', $sainfo['mgid']);
            $data['mgid'] = $mgid;
        }

        if(in_array($sainfo['type'],['5','6'])){//渠道
            if(5 == $sainfo['type']) {
                // 组员判断所在组状态
                $gx = $this->db->Name('xcx_manager_user_gx')
                    ->select('id')
                    ->where_equalTo('id', $sainfo['mgid'])
                    ->where_equalTo('status', 1)
                    ->where_equalTo('is_delete', 0)
                    ->firstRow();
                if(!empty($gx)) {
                    $data['storeInfo']['status'] = 1;
                } else {
                    $data['storeInfo']['status'] = 0;
                }
                //渠道组员权限信息
                $data['manageinfo'] = [
                    'auth_report_types'=> ['1','2','3', '4', '5','6'] // auth_report_types 返回权限id集合
                ];
            } else {
                //渠道组长权限信息
                $data['manageinfo'] = [
                    'auth_report_types'=> ['5','6'] // auth_report_types 返回权限id集合
                ];
                $data['storeInfo']['status'] = 1;
            }


            $data['storeInfo']['storename'] = '渠道人员';
            // 获取其所在组
            $mgid = explode(',', $sainfo['mgid']);
            $data['mgid'] = $mgid;
        }

        // 区域负责人
        if(8 == $sainfo['type']) {
            $data['storeInfo']['status'] = 1;
            // 权限信息
            $data['manageinfo'] = [
                'auth_report_types'=> ['1','2','3', '4', '5','6']
            ];
            // 区域信息
            $data['storeInfo']['province'] = $sainfo['province'];// 省份
            $data['storeInfo']['city'] = $sainfo['city'];// 城市
            $data['storeInfo']['area'] = $sainfo['area'];// 区域
        }

        if(!empty($leaderId)){//有项目负责人权限
//            $data['manageinfo']['auth_report_types'] =  array_merge($data['manageinfo']['auth_report_types'],['7']);
        }

//        $typename_list = [
//            0 => '店员',
//            1 => '店长',
//            2 => '项目组员',
//            3 => '项目组长',
//            4 => '财务人员',
//            5 => '渠道组员',
//            6 => '渠道组长',
//            7 => '项目负责人'
//        ];
        $typename_list = $this->getStoreType();

        $data['buildding_leader'] = $leaderId;
        $this->builddingLeader = $leaderId;
        $data['type'] = $sainfo['type'];//所属职位
        $data['typename'] = $typename_list[$sainfo['type']];//所属职位

        $data['status'] = $sainfo['status'];//该账号状态
        $mestatus = $sainfo['status'];//该账号的整体综合状态是否可以操作报备

        if($mestatus!=0&&(empty($data['name']||$data['phone']))){//非待审核状态时
            $mestatus = -3;// 个人信息未填写完整
        }
        //店铺禁用时 //软删除时
        if($data['storeInfo']['status']!=1||$storeData['is_delete']==1||$sainfo['is_delete']==1){
            $mestatus = -1;
        }

        $data['mestatus'] = $mestatus;//经纪人综合性整体状态，是否可以进行报备操作

        if($refresh!=2){
            Session::set('_userinfo',$data);
        }

        return $data;
    }

    // 获取用户信息
    public function getUserInfo($refresh=0,$id=0) {
        $id = $id ? $id : $this->agentId;
        $data = [];
        if(empty($id)){
            return $data;
        }

        $userinfo = Session::get('_userinfo');

        /*if($refresh==0&&!empty($userinfo)&&$userinfo['expire_time']>time()){
            return $userinfo;
        }*/
        
        //获取用户信息
        $agentRow = $this->db->Name('xcx_agent_user')->select()->where_equalTo('id', $id)->firstRow();
        if(empty($agentRow)){
            return [];
        }

        //查询是否是经纪人
        $sainfoList = $this->db->Name('xcx_store_agent')->select()->where_equalTo('agent_id', $id)->where_equalTo('is_delete', 0)->execute();

        $data = [
            // 基础信息
            'uname' => empty($agentRow['name']) ? $agentRow['nickname'] : $agentRow['name'],//姓名
            'name' => $agentRow['name'], //姓名
            'openid' => $agentRow['openid'], //openid
            'nickname' => $agentRow['nickname'], //微信昵称
            'headimgurl' => $agentRow['headimgurl'], //头像
            'phone' => $agentRow['phone'], //电话
            'signature' => $agentRow['signature'],  //个性签名
            'special_label' => !empty($agentRow['special_label']) ? explode(',', $agentRow['special_label']) : [], //标签
            // 工作账号信息
            'mestatus'=> '-2',  // 状态 -3：个人信息未填写 -2：账号未启用 -1：权限未开通 1：启用
            'type' => [], // 类型 0-店员 1-店长 2-项目经理 4-项目总监 5-渠道专员 6-渠道总监 7-区域负责人
            'typename' => [], // 类型描述
            'said' => [], // said对应信息，包括店铺、工作组、楼盘等信息
            'list_auth' => [], // 列表权限，包括列表入口，环节权限
            'type_auth' => [
                'duplicate' => [],
                'examine' => [],
                'log' => [],
                'add' => [],
                'store_manager' => false,
            ], // 合并角色权限
            // 缓存时间
            'expire_time' => time()+30*60,
            'storeInfo' => [], // 商店信息
            'groupInfo' => [], // 组别信息
        ];

        if(empty($sainfoList)) {
            return $data;
        }

        foreach($sainfoList as $sa) {
            if(!empty($this->RoleAuth[$sa['type']])) {
                $auth = $this->RoleAuth[$sa['type']];
                // 类型赋值
                $data['type'][] = $sa['type'];
                $data['typename'][] = $auth['name'];
                // 合并角色权限
                $data['type_auth'] = $this->typeAuthMerge($data['type_auth'], $auth);
                // 获取角色的一些对应数据，如绑定的店铺、楼盘、工作组、后台账号
                $data['said'][$sa['said']] = $this->getSaidData($auth, $sa);
            }
        }
        // 列表权限
        $data['list_auth'] = $this->getListAuth($data['type_auth']);

        // 提取店铺和工作组信息
        $data['storeInfo'] = array_column($data['said'], 'store');
        $data['groupInfo'] = array_column($data['said'], 'group');

        // 状态判断
        $data['mestatus'] = 1;
        if(empty($data['name']||$data['phone'])) {
            $data['mestatus'] = -3;
        }
        $relation = array_column($data['said'], 'relation');
        if(empty($data['storeInfo']) && empty($data['groupInfo']) && !empty(array_intersect($relation, ['create-building', 'city']))) {
            $data['mestatus'] = -2;
        }
        $mestatus = array_column($sainfoList, 'status');
        if(!in_array(1, $mestatus)) {
            if(in_array(0, $mestatus)) {
                $data['mestatus'] = 0;
            } else {
                $data['mestatus'] = -1;
            }
        }

        if($refresh!=2){
            Session::set('_userinfo',$data);
        }

        return $data;
    }

    /**
     * 权限映射环节
     */
    protected function transforAuthToStatus($typeAuth = [])
    {
        try {
            $authType = [];
            if(!empty($typeAuth)) {
                foreach($typeAuth as $status => $auth) {
                    if(!empty($auth)) {
                        foreach($auth as $a) {
                            $authType[$a][] = $status;
                            $authType[$a] = array_unique($authType[$a]);
                        }
                    }
                }
            }
            return $authType;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 合并角色权限
     */
    protected function typeAuthMerge($main, $auth)
    {
        if(empty($auth)) {
            return $main;
        }
        foreach($auth as $key => $val) {
            if(!empty($val)) {
                switch($key) {
                    case 'duplicate':
                    case 'examine':
                    case 'log':
                        foreach($val as $k => $v) {
                            if(!empty($main[$key][$k])) {
                                $main[$key][$k] = array_merge($main[$key][$k], $v);
                            } else {
                                $main[$key][$k] = $v;
                            }
                            $main[$key][$k] = array_unique($main[$key][$k]);
                        }
                        break;
                    case 'add':
                        $main[$key] = array_merge($main[$key], $auth[$key]);
                        $main[$key] = array_unique($main[$key]);
                        break;
                    case 'store_manager':
                        if(!empty($auth[$key])) {
                            $main[$key] = true;
                        }
                        break;
                }
            }
        }
        return $main;
    }

    /**
     * 获取列表入口及对应权限
     * self:自己 subordinate:下级店员 building:绑定的楼盘 subordinate-building：下级绑定的楼盘 create-store:创建的店铺 subordinate-store:下级绑定的店铺 create-building:创建的楼盘 city:城市
     * type 1-发起 2-待处理 3-已处理 4-抄送
     */
    protected function getListAuth($auth)
    {
        $main = [];
        if(empty($auth)) {
            return $main;
        }
        // 发起入口判断
        if(!empty($auth['add'])) {
            $main[] = [
                'name' => '已发起',
                'status' => [1, 2, 3, 4, 5],
                'type' => 1,
            ];
        }
        // 待处理入口判断
        if(!empty($auth['examine'])) {
            $status = array_keys($auth['examine']);
            $main[] = [
                'name' => '待处理',
                'status' => $status,
                'type' => 2,
            ];
        }
        // 已处理入口必有
        $main[] = [
            'name' => '已处理',
            'status' => [],
            'type' => 3,
        ];
        // 抄送入口判断
        $duplicate = $this->transforAuthToStatus($auth['duplicate']);
        if(!empty($duplicate)) {
            foreach($duplicate as $key => $val) {
                switch($key) {
                    case 'subordinate':
                        $main[] = [
                            'name' => '抄送',
                            'status' => $val,
                            'type' => 4,
                            'select' => "",
                            'cond_type' => 1,// 店员
                        ];
                        break;
                    case 'create-store':
                        $main[] = [
                            'name' => '我的店铺',
                            'status' => $val,
                            'type' => 4,
                            'select' => "my-store",
                            'cond_type' => 2,// 店铺
                        ];
                        break;
                    case 'subordinate-work':
                        $main[] = [
                            'name' => '我的专员',
                            'status' => $val,
                            'type' => 4,
                            'select' => "subordinate-work",
                            'cond_type' => 3,// 专员
                        ];
                        break;
                }
            }
        }
        // 店铺入口
        if(!empty($auth['store_manager'])) {
            $main[] = [
                'name' => '网店',
                'status' => [],
                'type' => 5,
            ];
        }
        return $main;
    }

    /**
     * 获取said绑定的相关数据
     */
    protected function getSaidData($auth, $saInfo)
    {
        $relation = [];
        $resData = [
            'type' => (int)$saInfo['type'],
        ];
        if(!empty($auth)) {
            foreach($auth as $key => $val) {
                if(!empty($val) && in_array($key, ['duplicate', 'examine', 'log'])) {
                    $rlData = $this->transforAuthToStatus($val);
                    $rlData = !empty($rlData) ? array_keys($rlData) : [];
                }
                if(!empty($rlData)) {
                    $relation = array_merge($relation, $rlData);
                }
            }
        }
        if(!empty($relation)) {
            $relation = array_unique($relation);
            $resData['relation'] = $relation;
            foreach($relation as $rVal) {
                switch($rVal) {
                    // 需要店铺信息
                    case "self":
                    case "subordinate-store":
                        if(!empty($saInfo['store_id'])) {
                            $store = $this->db->Name('xcx_store_store')->select('id, title')->where_equalTo('id', $saInfo['store_id'])->where_equalTo('status', 1)->execute();
                            if(!empty($store['0'])) {
                                $store = $store['0'];
                                $resData['store'] = [
                                    'id' => $store['id'], 
                                    'name' => $store['title'],
                                ];
                            }
                        }
                        break;
                    // 需要楼盘信息
                    case "building":
                        $mb = $this->db->Name('xcx_manager_building')->select('building_ids')->where_equalTo('said', $saInfo['said'])->execute();
                        if(!empty($mb['building_ids'])) {
                            $resData['bind_building'] = explode(',', $mb['building_ids']);
                        }
                        break;
                    // 需要后台账号
                    case "create-store":
                        $admin = $this->db->Name('admin')->select('id')->where_equalTo('channel_id', $saInfo['said'])->execute();
                        if(!empty($admin['0']['id'])) {
                            $resData['aid'] = $admin['0']['id']; 
                        }
                        break;
                    case "create-building":
                        $admin = $this->db->Name('admin')->select('id')->where_equalTo('charge_id', $saInfo['said'])->execute();
                        if(!empty($admin['0']['id'])) {
                            $resData['aid'] = $admin['0']['id']; 
                        }
                        break;
                }
            }
            // 需要组
            if(array_intersect($relation, ['building', 'create-store', 'subordinate-work'])) {// 工作人员，找组
                if(!empty($saInfo['mgid'])) {
                    $mgid = explode(',', $saInfo['mgid']);
                    $mg = $this->db->Name('xcx_manager_user_gx')->select('id, title')->where_in('id', $mgid)->where_equalTo('status', 1)->execute();
                    if(!empty($mg)) {
                        foreach($mg as $m) {
                            $resData['group'][] = [
                                'id' => $m['id'],
                                'title' => $m['title'],
                            ];
                        }
                    }
                }
            }
        }
        return $resData;
    }

    /**
     * 发送微信模板消息
     */
    protected function sendWxMsgTpl($parameter = []){
        if(empty($parameter)){
            return '参数缺失';
        }
        $accessToken=$this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$accessToken;
        /*$openid=$this->db->Name('xcx_agent_user')->select()->where_equalTo('id',$data['agent_id'])->firstRow();
        $parameter=[
            "touser"=>$openid['openid'],
            "template_id"=>'M1KZ_G98NTROZRIMYd-M9MssPl76FbmCK3GGkhsfy1E',
            "url"=>WX_HOST.'/agentside/index.html',
            "data"=>[
                'first'=>['value'=>'已成功报备'.count($resultSuccess).'个楼盘','color'=>'#173177'],
                'keyword1'=>['value'=>$data['user_name'],'color'=>'#173177'],
                'keyword2'=>['value'=>implode(',',$resultSuccess),'color'=>'#173177'],
                'keyword3'=>['value'=>date('Y-m-d H:i',$data['take_time']),'color'=>'#173177'],
                'keyword4'=>['value'=>'仅在'.date('Y年m月d日',$data['take_time']).'带看有效','color'=>'#173177'],
                'remark'=>['value'=>'恭喜您报备楼盘成功！多多带看，继续成交！','color'=>'#173177']
            ]
        ];*/
        $red=$this->sendPost($url,json_encode($parameter,JSON_UNESCAPED_UNICODE));
        $red=json_decode($red, TRUE);
        if(!empty($red['errcode'])){
            $this->db->Name('log')->insert([
                'title'=> '报备模板通知失败',
                'content'=>json_encode($red,JSON_UNESCAPED_UNICODE),
                'request'=> json_encode($parameter,JSON_UNESCAPED_UNICODE),
            ])->execute();
        }
    }

    //获取公众号的access_toke
    protected function getAccessToken($reflash=0){
//        $access_token_time = $this->db->Name('xcx_setting')->select()->where_equalTo('`key`','ACCESSTOKENTIME')->firstRow()['value'];
        // $key = 'AccessTokenH5';
        $appid = WXAPPID;
        $secret = WXSECRET;
        $key = "wxfwaccesstokens_{$appid}";
        $accessTokenData = $this->redis->get($key);
        // if(!$accessTokenData) {
        //     $expires_time = null;
        // } else {
        //     $accessTokenData = json_decode($accessTokenData, TRUE);
        //     $access_token_time = $accessTokenData['expires_in'];
        // }
        if($reflash==1|| empty($accessTokenData)){
            // $appid = WXAPPID;
            // $secret = WXSECRET;
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
            $token = $this->sendPost($url);
            //打印获得的数据
            $arr=json_decode($token,true);
            $access_token = $arr['access_token'];
            $expires_in = $arr['expires_in']-3600+time();
//            (new Query())->Name('xcx_setting')->update(['value'=>$access_token])->where_equalTo('`key`','ACCESSTOKEN')->execute();
//            (new Query())->Name('xcx_setting')->update(['value'=>$expires_in])->where_equalTo('`key`','ACCESSTOKENTIME')->execute();
            // $accessTokenData = [
            //     'access_token' => $access_token,
            //     'expires_in' => $expires_in,
            // ];
            $this->redis->set($key, $accessTokenData);
            $this->redis->expireAt($key, $expires_in);
            return $access_token;
        }else{
//            return $this->db->Name('xcx_setting')->select()->where_equalTo('`key`','ACCESSTOKEN')->firstRow()['value'];
            // return $accessTokenData['access_token'];
            return $accessTokenData;
        }
    }

    //获取小程序接口所需的的access_token
    protected function getAccessToken2(){
        $access_token="";
//        $expires_time=$this->db->Name('xcx_setting')->select()->where_equalTo('`key`','expires_time')->firstRow();
        $key = 'AccessTokenMini';
        $accessTokenData = $this->redis->get($key);
        if(!$accessTokenData) {
            $expires_time = null;
        } else {
            $accessTokenData = json_decode($accessTokenData, TRUE);
            $expires_time = $accessTokenData['expires_in'];
        }

        if(!empty($expires_time) && time()<intval($expires_time)){
//            $access_token=$this->db->Name('xcx_setting')->select()->where_equalTo('`key`','access_token')->firstRow()['value'];
            $access_token = $accessTokenData['access_token'];
        }else{
            //防止本地请求token，使其失效
            $getAccessToken=$this->sendPost("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".SECRET);
            //$getAccessToken=$this->getJson("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx77d12f2497be2502&secret=88579921c6cea5a386e96b1373e1d6bd");
            $getAccessToken=json_decode($getAccessToken,true);
            if(empty($getAccessToken['errcode'])){
                $access_token=$getAccessToken['access_token'];
//                $this->db->Name('xcx_setting')->update(['value'=>$getAccessToken['access_token']])->where_equalTo('`key`','access_token')->execute();
//                $this->db->Name('xcx_setting')->update(['value'=>time()+$getAccessToken['expires_in']-200])->where_equalTo('`key`','expires_time')->execute();
                $expires_in = time() + $getAccessToken['expires_in'] - 200;
                $accessTokenData = [
                    'access_token' => $access_token,
                    'expires_in' => $expires_in,
                ];
                $this->redis->set($key, json_encode($accessTokenData));
                $this->redis->expireAt($key, $expires_in);
            }

        }
        return $access_token;
    }

    //字符串转十六进制
    protected function strToHex($str){
        $hex="";
        for($i=0;$i<strlen($str);$i++){
            $hex.=dechex(ord($str[$i]));
        }
        $hex=strtoupper($hex);
        return $hex;
    }
    //十六进制转字符串
    protected function hexToStr($hex){
        $str="";
        for($i=0;$i<strlen($hex)-1;$i+=2){
            $str.=chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $str;
    }

}