<?php

/*
 * 报备流程相关
 */

/**
 * Description of main
 *
 * @author Goods0
 */
include 'Common.php';
include 'phpqrcode.php';
class ReportNew extends Common{

    protected $worker = [2, 3, 4, 5, 6, 7];// 工作人员类型
    protected $manager = [3, 6];// 组长类型（项目、渠道）
    protected $teamMember = [2, 5, 7, 8];// 可审批类型
    protected $nomalAgent = [0, 1];// 普通经纪人
    protected $chargeType = [4];// 项目负责人操作环节
    protected $projectType = [1, 2, 3];// 项目组员操作环节
    protected $channelType = [5, 6];// 渠道组员操作环节
    protected $channelUser = [5, 6];// 渠道组类型
    protected $statusDeal = 1;// 环节处理方式 1-待处理/抄送 2-已处理 3-已处理中的已审批 4-已处理中的已过期
    protected $listType = 1;// 1-使用审核权限 2-使用抄送权限
    protected $statusCond = [];// 状态筛选条件


    public function __construct()
    {
        parent::__construct();
        $userinfo = $this->getUserInfo();
        if($userinfo['mestatus']==-1){
            $this->error('权限未开通');
            exit();
        }elseif ($userinfo['mestatus']=='0'){
            $this->error('您的账号正在审核中请耐心等待开通');
            exit();
        }elseif ($userinfo['mestatus']=='-2'){
            $this->error('账号未启用');
            exit();
        }elseif ($userinfo['is_emptyinfo']=='-3'){
            $this->error('请先完善个人信息');
            exit();
        }
    }

    protected function redisLog($field, $data)
    {
        $saId = $this->saId;
        $agentId = $this->agentId;
        $key = 'fx_log';
        $redis = RedisBase::getInstance();
        if(49 == $agentId || 570 == $saId) {
            $save['said'] = $saId;
            $save['agent_id'] = $agentId;
            $save['data'] = $data;
            $redis->hset($key, $field, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }

    // 构建wherein
    protected function buildWhereIn($key = ':where', $value = [])
    {
        $in = '';
        $whereValue = [];
        if(empty($value)) {
            $value[]=0;
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
    protected function getAgentImgName($agent_id){
        $agentInfo=$this->db->Name('xcx_agent_user')->select()->where_equalTo('id',$agent_id)->firstRow();
        $data[]=empty($agentInfo['name'])?$agentInfo['nickname']:$agentInfo['name'];
        $data[]=$agentInfo['headimgurl'];
        return $data;
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
        // if(2 == $status) {
        //     $startTime = $takeTime;
        // } else {
        //     $startTime = $updateTime;
        // }
        $startTime = $updateTime;

        // 当前记录状态如果与所查询状态一致，则判断是否过期
        $protect = json_decode($protectSet, TRUE);
        if($status >=1 && $status <= 3) {
            //每个流程环节保护时间-规范到小时
            $keyStatus = 'status' . $status . '_hours';
            $protect_set_hours = intval($protect[$keyStatus]);
            if($protect_set_hours > 0) {// 有设置才判断保护期
                if(1 == $status) {
                    $protect_time = $protect_set_hours * 60;// 报备保护期按分钟算
                } else {
                    $protect_time = $protect_set_hours * 3600;
                }
                $protectTimeEnd = $startTime + $protect_time;//按最后的更新时间-小时
                if($protectTimeEnd <= time()){
                    $check = FALSE;
                }
            }
        }

        return $check;
    }

    /**
     * 获取报备列表的数据库语句公共部分
     */
    protected function getReportDB($field = "br.*")
    {
        $myDB = $this->db->Name('xcx_building_reported')
                        ->select($field, "br")
                        ->innerJoin("xcx_building_building", "bb", "bb.id=br.building_id")
                        ->innerJoin("xcx_store_agent", "sa", "sa.said=br.said")
                        ->leftJoin("xcx_agent_user", "au", "sa.agent_id=au.id")
                        ;

        return $myDB;
    }

    /**
     * 接收参数处理
     */
    protected function filterParam()
    {
        $page = !empty(Context::Post('page')) ? Context::Post('page') : 1;// 页码
        $pageSize = !empty(Context::Post('page_size')) ? Context::Post('page_size') : self::MYLIMIT;// 每页记录条数
        $store = !empty(Context::Post('store')) ? Context::Post('store') : [];// 店铺选择
        $agent = !empty(Context::Post('agent')) ? Context::Post('agent') : [];// 店员选择
        $isRead = !empty(Context::Post('is_read')) ? Context::Post('is_read') : null;// 是否已读
        $listType = !empty(Context::Post('list_type')) ? Context::Post('list_type') : 1;// 1-待处理 2-抄送 3-已处理
        $copyType = !empty(Context::Post('copy_type')) ? Context::Post('copy_type') : '';// 抄送时有效
        $searchWord = !empty(Context::Post('search_word')) ? Context::Post('search_word') : '';// 搜索关键词
        if($pageSize > 100) {
            return $this->error('请求数据超出限制');
        }

        $type = !empty(Context::Post('type')) ? Context::Post('type') : 0;// 环节
        
        $orderBy = !empty(Context::Post('order_by')) ? Context::Post('order_by') : "";
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
        if(!empty(Context::Post('nowDate'))) {
            $nowDate = Context::Post('nowDate');  //搜查询的日期
            $startTime = strtotime($nowDate);    //当天开始时间戳
            $endTime = $startTime + 86400;      //当天结束时间戳
        }

        return [
            'page' => $page,
            'pageSize' => $pageSize,
            'type' => $type,
            'orderBy' => $orderBy,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'store' => $store,
            'agent' => $agent,
            'is_read' => $isRead,
            'listType' => $listType,
            'copyType' => $copyType,
            'searchWord' => $searchWord,
        ];
    }


    /**
     * 获取发起单
     */
    public function getLaunch()
    {
        try {
            $param = $this->filterParam();

            extract($param);

            $saId = array_keys($this->saId);

            // 获取当前账号信息
            $userInfo = $this->getUserInfo();
            // 报备权限
            $auth = !empty($userInfo['type_auth']) ? $userInfo['type_auth'] : [];

            if(empty($auth['add'])) {// 没有发起报备单的能力
                return $this->error('您无报备楼盘的权限');
            }

            $field = "br.id, br.said, br.agent_type, br.status_type, br.examine_said, br.examine_type, br.create_time, br.update_time, br.user_name, br.user_gender, br.take_time, br.user_phone, sa.agent_name as sa_name, sa.agent_img as sa_img, au.nickname as agent_nickname, au.headimgurl, au.name as agent_name, au.sex, u.nickName as customer_nickname, u.avatarUrl, bb.name as building_name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission, bb.store_manager_commission, bb.team_member_commission, bb.protect_set";

            $myDB = $this->getReportDB($field);
            $myDB->leftJoin("xcx_user", "u", "br.user_id=u.id");
            // 自己提交的单
            if(is_array($saId)) {
                $myDB->where_in('br.said', $saId);
            } else {
                $myDB->where_equalTo('br.said', $saId);
            }
            // 状态筛选
            if(!empty($type)) {
                $myDB->where_in('status_type', $type);// 指定状态
            } else {
                // 发起流程不包括已处理
                $myDB->where_greatThan('status_type', 0);
                $myDB->where_lessThan('status_type', 6);
            }
            $myDB->where_in('examine_type', [1]);
            // 搜索关键词
            if(!empty($searchWord)) {
                $myDB->where_like('br.user_name', "%{$searchWord}%");
            }
            // 时间筛选
            if(!empty($startTime) && !empty($endTime)) {
                $myDB->where_greatThanOrEqual('br.update_time', $startTime)->where_lessThanOrEqual('br.update_time', $endTime);
            }
            // 排序
            if(!empty($orderBy)) {
                $myDB->orderBy('br.update_time', $orderBy);
            }
            // 分页
            $myDB->page($page, $pageSize);
            // 执行
            $list = $myDB->execute();
            // 数据处理
            $data = [];
            if(!empty($list)) {
                foreach($list as $k => $v) {
                    $data[$k]['id'] = $v['id'];
                    // 状态信息
                    $data[$k]['status_type'] = $v['status_type'];
                    $data[$k]['examine_type'] = $v['examine_type'];
                    $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                    $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];
                    // 客户信息
                    $defaultCustomerImg = $this->manImg;// 默认头像
                    if(isset($v['user_gender']) && 2 == $v['user_gender']) {
                        $defaultCustomerImg = $this->womanImg;
                    }
                    $data[$k]['customer_name'] = empty($v['user_name']) ? empty($v['customer_nickname']) ? "" : $v['customer_nickname'] : $v['user_name'];
                    $data[$k]['customer_img'] = !empty($v['avatarUrl']) ? $v['avatarUrl'] : $defaultCustomerImg;
                    $data[$k]['customer_phone'] = !empty($v['user_phone']) ? $v['user_phone'] : '';
                    // 楼盘信息
                    $data[$k]['name'] = $v['building_name'];
                    $data[$k]['cover'] = $v['cover'];
                    $data[$k]['house_type'] = $v['house_type'];
                    $data[$k]['city'] = $v['city'];
                    $data[$k]['area'] = $v['area'];
                    $data[$k]['sales_status'] = $v['sales_status'];
                    $data[$k]['fold'] = $v['fold'];
                    $data[$k]['commission'] = $v['commission'];
                    $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);

                    $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);

                    $day = 0;
                    // 当前记录状态如果与所查询状态一致，则判断是否过期
                    if($v['status_type'] >=1 && $v['status_type'] <= 3) {
                        $paramCheck = [
                            'status' => $v['status_type'],
                            'take_time' => $v['take_time'],
                            'update_time' => $v['update_time'],
                            'protect_set' => $v['protect_set'],
                        ];
                        $checkProtect = $this->checkProtectTime($paramCheck);
                        if(!$checkProtect) {
                            $data[$k]['examine_type'] = -2;
                        }

                        if(!empty($v['update_time'])) {
                            $time = time() - $v['update_time'];
                            if($time > 0 && $time >= 86400) {
                                $day = bcdiv($time, 86400);
                            }
                        }
                    }
                    $data[$k]['msg'] = (int)$day;
                }
            }
            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 抄送列表/待处理
     */
    public function getList()
    {
        try {
            $param = $this->filterParam();

            extract($param);

            // 获取当前账号信息
            $agentId = $this->agentId;
            $userInfo = $this->getUserInfo();

            // 报备权限
            $auth = !empty($userInfo['type_auth']) ? $userInfo['type_auth'] : [];

            if(1 == $listType) {// 待处理
                if(empty($auth['examine'])) {
                    return $this->error('您没有权限查看');
                }
                $authStatus = $this->transforAuthToStatus($auth['examine']);
            } elseif(2 == $listType) {// 抄送
                if(empty($auth['duplicate'])) {
                    return $this->error('您没有权限查看');
                }
                $this->listType = 2;
                $authStatus = $this->transforAuthToStatus($auth['duplicate']);
                if(!empty($copyType)) {
                    if('my-store' == $copyType) {
                        $copyType = 'create-store';// create这个单词作为参数时会被过滤
                    }
                    foreach($authStatus as $ask => $asv) {
                        if($ask != $copyType) {
                            unset($authStatus[$ask]);
                        }
                    }
                }
            }

            $authStatus = !empty($authStatus) ? array_keys($authStatus) : [];

            if(!empty($type)) {
                $this->statusCond = $type;
            }

            if(!empty($authStatus)) {
                foreach($authStatus as $val) {
                    $sqlStr = $this->getAuthSql($val);
                    if(!empty($sqlStr)) {
                        $sql[] = $sqlStr;
                    }
                }
            }
            
            $isStore = false;
            if(!empty(array_intersect(['create-store', 'subordinate-work'], $authStatus))) {// 数组交集 是否是跟店铺相关的抄送
                $isStore = true;
            }

            $fields = "br.id, br.said, br.agent_type, br.status_type, br.examine_type, br.create_time, br.update_time, br.take_time, br.store_id, bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission, br.user_name, br.user_phone, br.user_gender, sa.agent_name";

            if(2 == $listType) {// 抄送列表需要
                $fields .= ', rrl.is_read';
            }

            $myDB = $this->getReportDB($fields);

            if(2 == $listType) {// 抄送列表需要
                $myDB->leftJoin("xcx_report_read_log", 'rrl', "(br.id=rrl.report_id and rrl.agent_id={$agentId})");
                if(in_array('subordinate-work', $authStatus)) {
                    $myDB->leftJoin("xcx_reported_log", "rl", "br.id=rl.report_id");
                    $myDB->groupBy('br.id');
                }
            }

            if(!empty($sql)) {
                $sqlFinal = implode('or', $sql);
                $myDB->where_express($sqlFinal);
            }

            // // 状态筛选
            // if(!empty($type)) {
            //     $myDB->where_in('br.status_type', $type);
            // }
            // 店铺筛选
            if(!empty($stores)) {
                $myDB->where_in('br.store_id', $stores);
            }
            // 已读未读
            if(!empty($isRead) && 2 == $listType && 1 == sizeof($isRead)) {// 参数数组不为空 抄送列表 数组只有一个元素
                $isRead = $isRead[0];
                if($isRead) {
                    $myDB->where_equalTo('rrl.is_read', $isRead);
                } else {
                    $myDB->where_isNULL('rrl.is_read', $isRead);
                }
            }
            // 店员筛选
            if(!empty($agent)) {
                $myDB->where_in('br.said', $agent);
            }
            // 搜索关键词
            if(!empty($searchWord)) {
                $myDB->where_like('br.user_name', "%{$searchWord}%");
            }
            // 时间筛选
            if(!empty($startTime) && !empty($endTime)) {
                $myDB->where_greatThanOrEqual('br.update_time', $startTime)->where_lessThanOrEqual('br.update_time', $endTime);
            }
            // 排序
            if(!empty($orderBy)) {
                $myDB->orderBy('br.update_time', $orderBy);
            }
            // 分页
            $myDB->page($page, $pageSize);
            // 执行
            $list = $myDB->execute();

            $data = [];
            if(!empty($list)) {
                // 是否店铺相关
                if($isStore) {
                    $storeIds = array_unique(array_column($list, 'store_id'));
                    if(!empty($storeIds)) {
                        $sDB = $this->db->Name('xcx_store_store');
                        $fields = 'ss.id, ss.title';
                        if(in_array('subordinate-work', $authStatus)) {// 下级关联需找出下级信息
                            $fields .= ', sa.agent_name, sa.agent_img';
                            $sDB->leftJoin('admin', 'a', 'ss.aid=a.id')->leftJoin('xcx_store_agent', 'sa', 'sa.said=channel_id');
                        }
                        $sDB->select($fields, 'ss');
                        $sDB->where_in('ss.id', $storeIds);
                        $stores = $sDB->execute();

                        $storeRes = [];
                        if(!empty($stores)) {
                            foreach($stores as $s) {
                                $storeRes[$s['id']] = $s;
                            }
                        }
                    } 
                }
                // 最后的数据
                $paramRes = [
                    'type' => $type,
                    'listType' => $listType,
                    'isStore' => $isStore,
                    'storeRes' => $storeRes,
                ];
                $data = $this->dealRes($list, $paramRes);
            }
            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 已处理列表
     */
    public function getDealList()
    {
        try {
            $param = $this->filterParam();

            extract($param);

            // 获取当前账号信息
            $userInfo = $this->getUserInfo();

            $this->statusDeal = 2;

            // 报备权限
            $auth = !empty($userInfo['type_auth']) ? $userInfo['type_auth'] : [];
            
            if(!empty($auth['add'])) {// 店员看已被处理
                $said = array_keys($this->saId);
                if(!empty($said)) {
                    $saidStr = implode(',', $said);
                    $sqlAdd =  " and ((br.status_type>=6 and br.examine_type=2) or br.examine_type=-2 or br.examine_type=-1)";
                    if(!empty($type)) {
                        if(!is_array($type)) {
                            $types = [$type];
                        } else {
                            $types = $type;
                        }
                        $sqlAddArr = [];
                        foreach($types as $type) {
                            switch($type) {
                                // 已审批
                                case "examine":
                                    $sqlAddArr[] = "(br.status_type>=6 and br.examine_type=2)";
                                    break;
                                // 已失效
                                case "invalid":
                                    $sqlAddArr[] = " (br.examine_type=-2 or br.examine_type=-1)";
                                    break;
                                // 全部/默认
                                case "all":
                                default:
                                    return $this->error('状态选择错误');
                                    break;
                            }
                        }
                        if(!empty($sqlAddArr)) {
                            $sqlAdd = implode('or', $sqlAddArr);
                            $sqlAdd = " and {$sqlAdd}";
                        }
                    }
                    $sql[] = "br.said in ({$saidStr}) {$sqlAdd}";
                }
            } else {// 工作人员看自己已处理
                // 状态筛选
                if(!empty($type)) {
                    if(is_array($type)) {
                        if(1 == sizeof($type)) {
                            $type = $type[0];
                        }
                    }

                    switch($type) {
                        // 全部
                        case "all":
                            $this->statusDeal = 2;
                            break;
                        // 已审批
                        case "examine":
                            $this->statusDeal = 3;
                            break;
                        // 已失效
                        case "invalid":
                            $this->statusDeal = 4;
                            break;
                    }
                }

                $authStatus = $this->transforAuthToStatus($auth['examine']);
                $authStatus = array_keys($authStatus);
                if(!empty($authStatus)) {
                    foreach($authStatus as $val) {
                        $sqlStr = $this->getAuthSql($val);
                        if(!empty($sqlStr)) {
                            $sql[] = $sqlStr;
                        }
                    }
                }
            }

            $fields = "br.id, br.said, br.agent_type, br.status_type, rl.examine_said, br.examine_type, br.update_time, br.take_time, br.store_id, bb.name, bb.pic as cover, bb.house_type, bb.city, bb.area, bb.sales_status, bb.flag, bb.fold, bb.commission, br.user_name, br.user_phone, br.user_gender, sa.agent_name";

            $myDB = $this->getReportDB($fields);

            $myDB->leftJoin('xcx_reported_log', 'rl', "br.id=rl.report_id");

            if(!empty($sql)) {
                $sqlFinal = implode('or', $sql);
                $myDB->where_express($sqlFinal);
            }
            
            // 店铺筛选
            if(!empty($stores)) {
                $myDB->where_in('br.store_id', $stores);
            }
            // 店员筛选
            if(!empty($agent)) {
                $myDB->where_in('br.said', $agent);
            }
            // 搜索关键词
            if(!empty($searchWord)) {
                $myDB->where_like('br.user_name', "%{$searchWord}%");
            }
            // 时间筛选
            if(!empty($startTime) && !empty($endTime)) {
                $myDB->where_greatThanOrEqual('br.update_time', $startTime)->where_lessThanOrEqual('br.update_time', $endTime);
            }
            // 分组
            $myDB->groupBy("br.id");
            // 排序
            if(!empty($orderBy)) {
                $myDB->orderBy('br.update_time', $orderBy);
            }
            // 分页
            $myDB->page($page, $pageSize);
            // 执行
            $list = $myDB->execute();

            // 最后的数据
            $paramRes = [
                'type' => $type,
                'listType' => $listType,
                'isStore' => $isStore,
                'storeRes' => $storeRes,
            ];
            $data = $this->dealRes($list, $paramRes);

            return $this->success($data);
        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 结果处理
     */
    protected function dealRes($list, $param)
    {
        $data = [];

        $type = !empty($param['type']) ? $param['type'] : 0;
        $listType = !empty($param['listType']) ? $param['listType'] : 1;
        $isStore = !empty($param['isStore']) ? $param['isStore'] : 0;
        $storeRes = !empty($param['storeRes']) ? $param['storeRes'] : [];

        if(!empty($list)) {
            foreach($list as $k => $v) {
                // 当前记录状态如果与所查询状态一致，则判断是否过期
                if($v['status_type'] == $type && $v['status_type'] >=1 && $v['status_type'] <= 3) {
                    $paramCheck = [
                        'status' => $v['status_type'],
                        'take_time' => $v['take_time'],
                        'update_time' => $v['update_time'],
                        'protect_set' => $v['protect_set'],
                    ];
                    $checkProtect = $this->checkProtectTime($paramCheck);
                    if(!$checkProtect) {
                        if(in_array($this->statusDeal, [1, 3])) {
                            continue;
                        }
                        $invail = true;
                    }
                }

                $data[$k]['id'] = $v['id'];
                $data[$k]['status_type'] = $v['status_type'];
                $data[$k]['examine_type'] = $v['examine_type'];
                $statusKey = "{$data[$k]['status_type']}|{$data[$k]['examine_type']}";
                $data[$k]['status_str'] = $this->getReportStatus()[$statusKey];
                $data[$k]['is_read'] = !empty($v['is_read']) ? 1 : 0;
                $data[$k]['update_time'] = date("Y.m.d", $v['update_time']);
                // 状态标识 已处理
                $data[$k]['progress'] = 1;// 处理中
                if(1 != $this->statusDeal) {
                    if(-1 == $v['examine_type'] || $invail) {
                        $data[$k]['progress'] = -1;// 已失效
                    } else {
                        $data[$k]['progress'] = 2;// 已处理
                    }
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
                $data[$k]['flag'] = empty($v['flag']) ? [] : explode(',', $v['flag']);
                $data[$k]['is_read'] = !empty($v['is_read']) ? 1 : 0;
                // 客户信息
                $data[$k]['customer_img'] =  2 == $v['user_gender'] ? $this->womanImg : $this->manImg;
                $data[$k]['customer_name'] = $v['user_name'];
                $data[$k]['customer_phone'] = $v['user_phone'];
                // 店铺信息
                $data[$k]['agent_name'] = $v['agent_name'];// 经纪人姓名
                $data[$k]['store_name'] = "";// 店铺名
                $data[$k]['work_name'] = "";// 渠道专员名
                if($isStore) {
                    $data[$k]['store_name'] = !empty($storeRes[$v['store_id']]['title']) ? $storeRes[$v['store_id']]['title'] : "";
                    $data[$k]['work_name'] = !empty($storeRes[$v['store_id']]['agent_name']) ? $storeRes[$v['store_id']]['agent_name'] : "";
                }
                // 根据状态得到提示信息
                $data[$k]['msg'] = '';
                if(3 >= $v['status_type'] && !empty($v['update_time'])) {
                    $time = time() - $v['update_time'];
                    if($time > 0 && $time >= 86400) {
                        $day = bcdiv($time, 86400);
                        $data[$k]['msg'] = (int)$day;
                    }
                }
            }

        }

        return $data;
    }

    /**
     * 根据权限生成条件
     */
    protected function getAuthSql($key)
    {
        try {
            $sqlStr = "";
            switch($key) {
                // 自己
                case "self":

                    break;
                // 下级店铺
                case 'subordinate':
                    $saidData = $this->getSaid($key);
                    $ids = array_keys($saidData);
                    // 找出店铺
                    $sa = $this->db->Name('xcx_store_agent')->select('said, store_id')->where_in('said', $ids)->execute();
                    if(!empty($sa)) {
                        $join = "";
                        foreach($sa as $sVal) {
                            $join = $this->getConnect($sqlStr, 'or');
                            if(!empty($sVal['store_id']) && !empty($saidData[$sVal['said']]['status'])) {
                                $statusSql = $this->getStatusSql($saidData[$sVal['said']]['status'], 'and', $sVal['said']);
                                $sqlStr .= " {$join} (br.store_id in ({$sVal['store_id']}) {$statusSql})";
                            }
                        }
                        // 筛除自己
                        if(!empty($sqlStr)) {
                            $join = " and ";
                        }
                        $idsStr = implode(',', $ids);
                        $sqlStr .= " {$join} (br.said not in ({$idsStr}))";
                    }
                    break;
                // 绑定楼盘相关
                case 'building':
                    $saidData = $this->getSaid($key);
                    $ids = array_keys($saidData);
                    // 找出绑定的楼盘
                    $builds = $this->db->Name('xcx_manager_building')->select('said, building_ids')->where_in('said', $ids)->execute();
                    if(!empty($builds)) {
                        $join = "";
                        foreach($builds as $bVal) {
                            $join = $this->getConnect($sqlStr, 'or');
                            if(!empty($bVal['building_ids']) && !empty($saidData[$bVal['said']]['status'])) {
                                $statusSql = $this->getStatusSql($saidData[$bVal['said']]['status'], 'and', $bVal['said']);
                                $sqlStr .= " {$join} (br.building_id in ({$bVal['building_ids']}) {$statusSql})";
                            }
                        }
                    }
                    break;
                // 创建的店铺
                case 'create-store':
                    $saidData = $this->getSaid($key);
                    if(!empty($saidData)) {
                        foreach($saidData as $sk => $s) {
                            if(!empty($saidData[$sk]['aid'])) {
                                $aid = $saidData[$sk]['aid'];
                                $aidToSaid[$aid]['status'] = $saidData[$sk]['status'];
                                $aidToSaid[$aid]['said'] = $sk;
                            }
                        }
                    }
                    $ids = array_column(array_values($saidData), 'aid');
                    $stores = $this->db->Name('xcx_store_store')->select('aid, group_concat(id) as ids')->where_in('aid', $ids)->groupBy('aid')->execute();
                    if(!empty($stores)) {
                        $newData = [];
                        // 店铺数据整理
                        foreach($stores as $csVal) {
                            $newData[$csVal['aid']] = $csVal['ids'];
                        }
                        $join = "";
                        foreach($aidToSaid as $sk => $sv) {
                            $join = $this->getConnect($sqlStr, 'or');
                            if(!empty($sv['status']) && !empty($newData[$sk])) {
                                $storeIds = $newData[$sk];
                                $statusSql = $this->getStatusSql($sv['status'], 'and', $sv['said']);
                                $sqlStr .= " {$join} (br.store_id in ({$storeIds}) {$statusSql})";
                            }
                        }
                    }
                    break;
                // 下级绑定的店铺
                case 'subordinate-store':
                    $saidData = $this->getSaid($key);
                    $said = array_keys($saidData);
                    $sa = $this->db->Name('xcx_store_agent')->select('said, mgid')->where_in('said', $said)->execute();
                    $mgidArr = [];
                    $mgidToSaid = [];
                    if(!empty($sa)) {
                        foreach($sa as $s) {
                            if(!empty($s['mgid'])) {
                                $mgid = explode(',', $s['mgid']);
                                $saidToMgid[$s['said']] = $mgid;
                                $mgidArr = array_merge($mgidArr, $mgid);
                                if(!empty($mgid)) {
                                    foreach($mgid as $m) {
                                        $mgidToSaid[$m] = $s['said'];
                                    }
                                }
                            }
                        }
                        $saidStr = implode(',', $said);
                        $mgData = $this->db->Name('xcx_store_agent')->select('GROUP_CONCAT(ss.id) as storeIds, sa.mgid', 'sa')
                                            ->leftJoin('admin', 'a', "a.channel_id=sa.said")
                                            ->leftJoin('xcx_store_store', 'ss', "ss.aid=a.id")
                                            ->where_in('sa.mgid', $mgidArr)
                                            ->where_express("sa.said not in ({$saidStr})")
                                            ->groupBy('sa.mgid')
                                            ->execute();
                        if(!empty($mgData)) {
                            $saidToStoreId = [];
                            foreach($mgData as $md) {
                                $storeIds = !empty($md['storeIds']) ? explode(',', $md['storeIds']) : [];
                                $said1 = !empty($mgidToSaid[$md['mgid']]) ? $mgidToSaid[$md['mgid']] : 0;
                                $saidToStoreId[$said1] = !empty($saidToStoreId[$said1]) ? array_merge($saidToStoreId[$said1], $storeIds) : $storeIds;
                            }
                            $join = "";
                            if(!empty($saidToStoreId)) {
                                foreach($saidToStoreId as $sak => $sav) {
                                    if(!empty($sav) && !empty($saidData[$sak]['status'])) {
                                        $join = $this->getConnect($sqlStr, 'or');
                                        $statusSql = $this->getStatusSql($saidData[$sak]['status'], 'and', $sak);
                                        $storeStr = implode(',', $sav);
                                        $sqlStr .= " {$join} (br.store_id in ({$storeStr}) {$statusSql})";
                                    }
                                }
                            }
                        }
                    }
                    break;
                // 下级绑定的楼盘
                case "subordinate-building":
                    $saidData = $this->getSaid($key);
                    $said = array_keys($saidData);
                    $sa = $this->db->Name('xcx_store_agent')->select('said, mgid')->where_in('said', $said)->execute();
                    $mgidArr = [];
                    $mgidToSaid = [];
                    if(!empty($sa)) {
                        foreach($sa as $s) {
                            if(!empty($s['mgid'])) {
                                $mgid = explode(',', $s['mgid']);
                                $mgidArr = array_merge($mgidArr, $mgid);
                                if(!empty($mgid)) {
                                    foreach($mgid as $m) {
                                        $mgidToSaid[$m] = $s['said'];
                                    }
                                }
                            }
                        }
                        $saidStr = implode(',', $said);
                        $mgData = $this->db->Name('xcx_store_agent')->select('GROUP_CONCAT(ss.building_ids) as buildIds, sa.mgid', 'sa')
                                            ->leftJoin('xcx_manager_building', 'mb', "sa.said=mb.said")
                                            ->where_in('sa.mgid', $mgidArr)
                                            ->where_express("sa.said not in ({$saidStr})")
                                            ->groupBy('sa.mgid')
                                            ->execute();
                        if(!empty($mgData)) {
                            // 整理 组长-属下所绑定楼盘 数据
                            $saidToBuilding = [];
                            foreach($mgData as $md) {
                                $said1 = !empty($mgidToSaid[$md['mgid']]) ? $mgidToSaid[$md['mgid']] : 0;
                                $buildingIds = !empty($md['buildIds']) ? $md['buildIds'] : "";
                                $saidToBuilding[$said1] = !empty($saidToBuilding[$said1]) ? array_merge($saidToBuilding[$said1], $buildingIds) : $buildingIds;
                            }
                            $join = "";
                            if(!empty($saidToBuilding)) {
                                foreach($saidToBuilding as $sbk => $sbv) {
                                    if(!empty($sbv) && !empty($saidData[$sbk]['status'])) {
                                        $join = $this->getConnect($sqlStr, 'or');
                                        $statusSql = $this->getStatusSql($saidData[$sbk]['status'], 'and', $sbk);
                                        $buildingStr = implode(',', $sbv);
                                        $sqlStr .= " {$join} (br.building_id in ({$buildingStr}) {$statusSql})";
                                    }
                                }
                            }
                        }
                    }
                    break;
                // 创建的楼盘
                case "create-building":
                    $saidData = $this->getSaid($key);
                    if(!empty($saidData)) {
                        foreach($saidData as $sk => $s) {
                            if(!empty($saidData[$sk]['aid'])) {
                                $aid = $saidData[$sk]['aid'];
                                $aidToSaid[$aid]['status'] = $saidData[$sk]['status'];
                                $aidToSaid[$aid]['said'] = $sk;
                            }
                        }
                    }
                    $ids = array_column(array_values($saidData), 'aid');
                    $buildData = $this->db->Name('xcx_building_building')->select('group_concat(id) as ids, aid')->where_in('aid', $ids)->groupBy('aid')->execute();
                    if(!empty($buildData)) {
                        $newData = [];
                        // 楼盘数据整理
                        foreach($buildData as $cbVal) {
                            $newData[$cbVal['aid']] = $cbVal['ids'];
                        }
                        $join = "";
                        foreach($aidToSaid as $ak => $av) {
                            $join = $this->getConnect($sqlStr, 'or');
                            if(!empty($av['status']) && !empty($newData[$ak])) {
                                $buildIds = $newData[$ak];
                                $statusSql = $this->getStatusSql($av['status'], 'and', $av['said']);
                                $sqlStr .= " {$join} (br.building_id in ({$buildIds}) {$statusSql})";
                            }
                        }
                    }
                    break;
                // 城市
                case "city":
                    $saidData = $this->getSaid($key);
                    $saids = array_keys($saidData);
                    $sa = $this->db->Name("xcx_store_agent")->select('said, city')->where_in('said', $saids)->execute();
                    if(!empty($sa)) {
                        $join = "";
                        foreach($sa as $scVal) {
                            if(!empty($scVal['city']) && !empty($saidData[$scVal['said']]['status'])) {
                                $join = $this->getConnect($sqlStr, 'or');
                                $statusSql = $this->getStatusSql($saidData[$scVal['said']]['status'], 'and', $scVal['said']);
                                $sqlStr .= " {$join} (bb.city={$scVal['city']} {$statusSql})";
                            }
                        }
                    }
                    break;
                // 下级管理
                case "subordinate-work":
                    $saidData = $this->getSaid($key);
                    $said = array_keys($saidData);
                    $sa = $this->db->Name('xcx_store_agent')->select('said, mgid')->where_in('said', $said)->execute();
                    $mgidArr = [];
                    $mgidToSaid = [];
                    if(!empty($sa)) {
                        foreach($sa as $s) {
                            if(!empty($s['mgid'])) {
                                $mgid = explode(',', $s['mgid']);
                                $mgidArr = array_merge($mgidArr, $mgid);
                                if(!empty($mgid)) {
                                    foreach($mgid as $m) {
                                        $mgidToSaid[$m] = $s['said'];
                                    }
                                }
                            }
                        }
                        $saidStr = implode(',', $said);
                        $mgData = $this->db->Name('xcx_store_agent')->select('sa.said, sa.mgid', 'sa')
                                            ->where_in('sa.mgid', $mgidArr)
                                            ->where_express("sa.said not in ({$saidStr})")
                                            ->execute();
                        if(!empty($mgData)) {
                            $saidToSaid = [];
                            foreach($mgData as $md) {
                                $said1 = !empty($mgidToSaid[$md['mgid']]) ? $mgidToSaid[$md['mgid']] : 0;
                                if(!empty($md['said'])) {
                                    if(!empty($saidToSaid[$said1])) {
                                        array_push($saidToSaid[$said1], $md['said']);
                                    } else {
                                        $saidToSaid[$said1][] = $md['said'];
                                    }
                                }
                            }
                            $join = "";
                            if(!empty($saidToSaid)) {
                                foreach($saidToSaid as $sskey => $ssid) {
                                    if(!empty($ssid) && !empty($saidData[$sskey]['status'])) {
                                        $ssidStr = implode(',', $ssid);
                                        $join = $this->getConnect($sqlStr, 'or');
                                        $status = $saidData[$sskey]['status'];
                                        if(!empty($this->statusCond)) {
                                            $arrInter = array_intersect($this->statusCond, $status);
                                            if(!empty($arrInter)) {
                                                $status = $$arrInter;
                                            } else {
                                                continue ;
                                            }
                                        }

                                        $statusStr = implode(',', $status);
                                        $statusSql = "(rl.status_type in ({$statusStr}))";

                                        $sqlStr .= " {$join} (rl.examine_said in ({$ssidStr}) and {$statusSql})";
                                    }
                                }
                            }
                        }
                    }
                    break;
            }

            return $sqlStr;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取条件连接符
     */
    protected function getConnect($sql = "", $join = "")
    {
        $connect = "";
        if($sql) {
            $connect = " {$join} ";
        }
        return $connect;
    }

    /**
     * 根据是已处理还是未处理列表，获取环节条件
     */
    protected function getStatusSql($status = [], $join = "", $said = 0)
    {
        $resSql = "";
        if(!empty($status)) {
            $replenish = "";
            if(!empty($said)) {
                $replenish = "and rl.examine_said = {$said}";
            }
            switch($this->statusDeal) {
                case 1:
                    if(!empty($this->statusCond)) {
                        if(is_array($this->statusCond)) {
                            $arrInter = array_intersect($this->statusCond, $status);
                        } else {
                            if(in_array($this->statusCond, $status)) {
                                $arrInter = [$this->statusCond];
                            } else {
                                $arrInter = [];
                            }
                        }
                        if(!empty($arrInter)) {
                            $status = implode(',', $arrInter);
                        } else {
                            return " and br.status_type > 999";// 如果筛选的状态不在该said的权限内，通过远超实际状态的条件SQL语句使其搜索结果为空
                        }
                    } else {
                        $status = implode(',', $status);
                    }
                    $resSql = "{$join} (br.status_type in ({$status}) and br.examine_type=1)";
                    break;
                // 已处理（包括已审批和已失效）
                case 2:
                    $max = max($status);
                    $statusStr = implode(',', $status);
                    $resSql = "{$join} ((rl.status_type = {$max} and rl.examine_type = 2  {$replenish}) or (br.status_type in ({$statusStr}) and (br.examine_type=-2 or br.examine_type=-1)))";
                    break;
                // 已审批
                case 3:
                    $max = max($status);
                    $resSql = "{$join} (rl.status_type = {$max} and rl.examine_type = 2  {$replenish})";// 已审批：批过最大权限的通过
                    break;
                // 已失效
                case 4:
                    $statusStr = implode(',', $status);
                    $resSql = "{$join} (br.status_type in ({$statusStr}) and (br.examine_type=-2 or br.examine_type=-1))";// 已失效：权限内，过期-2和驳回-1
                    break;
            }
        }
        return $resSql;
    }

    /**
     * 获取SAID对应的有权限的环节和绑定的后台账号ID
     */
    protected function getSaid($key)
    {
        try {
            $saId = $this->saId;
            if(empty($saId) || !is_array($saId)) {
                return [];
            }

            if(2 == $this->listType) {
                $field = 'duplicate';
            } else {
                $field = 'examine';
            }

            $res = [];
            foreach($saId as $id => $idData) {
                if(empty($idData['type'])) {
                    continue;
                }
                $type = $idData['type'];
                // 获取不同type的权限
                if(!empty($this->RoleAuth[$type][$field])) {
                    $duplicate = $this->RoleAuth[$type][$field];
                    foreach($duplicate as $status => $dup) {
                        if(in_array($key, $dup)) {
                            $res[$id]['status'][] = $status;
                            $res[$id]['aid'] = !empty($idData['aid']) ? $idData['aid'] : 0;
                        }
                    }
                }
            }

            return $res;
        } catch(Exception $e) {
            throw $e;
        }
    }

    
}