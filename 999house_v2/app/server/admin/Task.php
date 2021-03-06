<?php

namespace app\server\admin;

use app\common\traits\TraitInstance;
use app\common\base\ServerBase;
use app\server\estates\Estatesnew;
use think\Db;
use think\Exception;
use think\facade\Db as FacadeDb;

/**
 * 任务相关
 */
class Task extends ServerBase
{
    /**
     * 新旧库地区ID映射
     */
    public $areaIds = [
        '9' => ['type' => '1', 'id' => '350000', 'oid' => '0', 'name' => '福建省'],// 福建省

        '10' => ['type' => '2', 'id' => '350200', 'oid' => '9', 'name' => '厦门市'],// 厦门市
        '11' => ['type' => '2', 'id' => '350600', 'oid' => '9', 'name' => '漳州市'],// 漳州市
        '12' => ['type' => '2', 'id' => '350500', 'oid' => '9', 'name' => '泉州市'],// 泉州市
        '13' => ['type' => '2', 'id' => '350800', 'oid' => '9', 'name' => '龙岩市'],// 龙岩市
        '591' => ['type' => '2', 'id' => '350100', 'oid' => '9', 'name' => '福州市'],// 福州市

        '1' => ['type' => '3', 'id' => '350203', 'pid' => '350200', 'oid' => '10', 'name' => '思明区'],// 思明
        '2' => ['type' => '3', 'id' => '350206', 'pid' => '350200', 'oid' => '10', 'name' => '湖里区'],// 湖里
        '3' => ['type' => '3', 'id' => '350205', 'pid' => '350200', 'oid' => '10', 'name' => '海沧区'],// 海沧
        '4' => ['type' => '3', 'id' => '350211', 'pid' => '350200', 'oid' => '10', 'name' => '集美区'],// 集美
        '5' => ['type' => '3', 'id' => '350212', 'pid' => '350200', 'oid' => '10', 'name' => '同安区'],// 同安
        '6' => ['type' => '3', 'id' => '350213', 'pid' => '350200', 'oid' => '10', 'name' => '翔安区'],// 翔安
        '7' => ['type' => '3', 'id' => '350280', 'pid' => '350200', 'oid' => '10', 'name' => '漳州港'],// 漳州港
        '8' => ['type' => '3', 'id' => '350666', 'pid' => '350200', 'oid' => '10', 'name' => '厦门周边'],// 厦门周边

        '14' => ['type' => '3', 'id' => '350503', 'pid' => '350500', 'oid' => '12', 'name' => '丰泽区'],// 丰泽区
        '15' => ['type' => '3', 'id' => '350502', 'pid' => '350500', 'oid' => '12', 'name' => '鲤城区'],// 鲤城区
        '16' => ['type' => '3', 'id' => '350504', 'pid' => '350500', 'oid' => '12', 'name' => '洛江区'],// 洛江区
        '17' => ['type' => '3', 'id' => '350582', 'pid' => '350500', 'oid' => '12', 'name' => '晋江市'],// 晋江市
        '18' => ['type' => '3', 'id' => '350583', 'pid' => '350500', 'oid' => '12', 'name' => '南安市'],// 南安市
        '19' => ['type' => '3', 'id' => '350581', 'pid' => '350500', 'oid' => '12', 'name' => '石狮市'],// 石狮市
        '20' => ['type' => '3', 'id' => '350521', 'pid' => '350500', 'oid' => '12', 'name' => '惠安县'],// 惠安县
        '25' => ['type' => '3', 'id' => '350584', 'pid' => '350500', 'oid' => '12', 'name' => '台商'],// 台商
        '26' => ['type' => '3', 'id' => '350524', 'pid' => '350500', 'oid' => '12', 'name' => '安溪县'],// 安溪县
        '1000' => ['type' => '3', 'id' => '350585', 'pid' => '350500', 'oid' => '12', 'name' => '清濛区'],// 清濛区
        '1011' => ['type' => '3', 'id' => '350505', 'pid' => '350500', 'oid' => '12', 'name' => '泉港区'],// 泉港区
        '1012' => ['type' => '3', 'id' => '350525', 'pid' => '350500', 'oid' => '12', 'name' => '永春县'],// 永春县
        '1013' => ['type' => '3', 'id' => '350526', 'pid' => '350500', 'oid' => '12', 'name' => '德化县'],// 德化县

        '32' => ['type' => '3', 'id' => '350602', 'pid' => '350600', 'oid' => '11', 'name' => '芗城区'],// 芗城区
        '33' => ['type' => '3', 'id' => '350685', 'pid' => '350600', 'oid' => '11', 'name' => '其他'],// 其他
        '40' => ['type' => '3', 'id' => '350603', 'pid' => '350600', 'oid' => '11', 'name' => '龙文区'],// 龙文区
        '41' => ['type' => '3', 'id' => '350683', 'pid' => '350600', 'oid' => '11', 'name' => '漳州港'],// 漳州港
        '42' => ['type' => '3', 'id' => '350684', 'pid' => '350600', 'oid' => '11', 'name' => '角美'],// 角美
        '43' => ['type' => '3', 'id' => '350681', 'pid' => '350600', 'oid' => '11', 'name' => '龙海市'],// 龙海市
        '44' => ['type' => '3', 'id' => '350623', 'pid' => '350600', 'oid' => '11', 'name' => '漳浦县'],// 漳浦县
        '45' => ['type' => '3', 'id' => '350625', 'pid' => '350600', 'oid' => '11', 'name' => '长泰县'],// 长泰县
        '46' => ['type' => '3', 'id' => '350682', 'pid' => '350600', 'oid' => '11', 'name' => '高新区'],// 高新区
        '59115' => ['type' => '3', 'id' => '350624', 'pid' => '350600', 'oid' => '11', 'name' => '诏安县'],// 诏安县
        '59116' => ['type' => '3', 'id' => '350628', 'pid' => '350600', 'oid' => '11', 'name' => '平和县'],// 平和县
        '59117' => ['type' => '3', 'id' => '350627', 'pid' => '350600', 'oid' => '11', 'name' => '南靖县'],// 南靖县
        '59118' => ['type' => '3', 'id' => '350622', 'pid' => '350600', 'oid' => '11', 'name' => '云霄县'],// 云霄县
        '59119' => ['type' => '3', 'id' => '350626', 'pid' => '350600', 'oid' => '11', 'name' => '东山县'],// 东山县
        '59120' => ['type' => '3', 'id' => '350629', 'pid' => '350600', 'oid' => '11', 'name' => '华安县'],// 华安县
        
        '999' => ['type' => '0', 'id' => '', 'pid' => ''],// 其他

        '1004' => ['type' => '3', 'id' => '350802', 'pid' => '350800', 'oid' => '13', 'name' => '新罗区'],// 龙岩大道-新罗区
        '1004' => ['type' => '3', 'id' => '350821', 'pid' => '350800', 'oid' => '13', 'name' => '长汀县'],// 龙腾路-长汀县 ? 
        '1006' => ['type' => '3', 'id' => '350821', 'pid' => '350800', 'oid' => '13', 'name' => '长汀县'],// 东山-长汀县 ?
        '1007' => ['type' => '3', 'id' => '350802', 'pid' => '350800', 'oid' => '13', 'name' => '新罗区'],// 东肖-新罗区
        '1008' => ['type' => '3', 'id' => '350881', 'pid' => '350800', 'oid' => '13', 'name' => '漳平县'],// 城北-漳平县 ? 
        '1009' => ['type' => '3', 'id' => '350802', 'pid' => '350800', 'oid' => '13', 'name' => '新罗区'],// 紫金山-新罗区 ? 
        '1010' => ['type' => '3', 'id' => '350882', 'pid' => '350800', 'oid' => '13', 'name' => '县城'],// 县城 

        '59101' => ['type' => '3', 'id' => '350102', 'pid' => '350100', 'oid' => '591', 'name' => '鼓楼区'],// 鼓楼区
        '59102' => ['type' => '3', 'id' => '350103', 'pid' => '350100', 'oid' => '591', 'name' => '台江区'],// 台江区
        '59103' => ['type' => '3', 'id' => '350104', 'pid' => '350100', 'oid' => '591', 'name' => '仓山区'],// 仓山区
        '59104' => ['type' => '3', 'id' => '350111', 'pid' => '350100', 'oid' => '591', 'name' => '晋安区'],// 晋安区
        '59105' => ['type' => '3', 'id' => '350105', 'pid' => '350100', 'oid' => '591', 'name' => '马尾区'],// 马尾区
        '59106' => ['type' => '3', 'id' => '350182', 'pid' => '350100', 'oid' => '591', 'name' => '长乐区'],// 长乐区
        '59107' => ['type' => '3', 'id' => '350181', 'pid' => '350100', 'oid' => '591', 'name' => '福清市'],// 福清市
        '59108' => ['type' => '3', 'id' => '350121', 'pid' => '350100', 'oid' => '591', 'name' => '闽侯县'],// 闽侯县
        '59109' => ['type' => '3', 'id' => '350122', 'pid' => '350100', 'oid' => '591', 'name' => '连江县'],// 连江县
        '59110' => ['type' => '3', 'id' => '350123', 'pid' => '350100', 'oid' => '591', 'name' => '罗源县'],// 罗源县
        '59111' => ['type' => '3', 'id' => '350124', 'pid' => '350100', 'oid' => '591', 'name' => '闽清县'],// 闽清县
        '59112' => ['type' => '3', 'id' => '350125', 'pid' => '350100', 'oid' => '591', 'name' => '永泰县'],// 永泰县
        '59113' => ['type' => '3', 'id' => '350128', 'pid' => '350100', 'oid' => '591', 'name' => '平潭县'],// 平潭县
        '59114' => ['type' => '3', 'id' => '350183', 'pid' => '350100', 'oid' => '591', 'name' => '其他'],// 其他
    ];

    /**
     * 销售状态对应
     */
    public $saleStatus = ['待售' => 1, '在售' => 2, '尾盘' => 4, '售完' => 3];

    /**
     * 图片类型新旧映射 旧=>新
     */
    public $imgType = [
        '1' => '1',// 效果图
        '2' => '3',// 样板图
        '3' => '10',// 交通图
        '4' => '2',// 实景图
        '5' => '5',// 配套图
        '6' => '11',// 封面图
    ];

    /**
     * 户型类型新旧映射 旧=>新
     */
    public $houseType = [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '7',
    ];

    /**
     * 标签映射 旧=>新
     */
    public $flag = [
        'r' => '16',// 热销楼盘
        's' => '17',// 学区
        'g' => '18',// 刚需楼盘
        'j' => '19',// 地铁商业
        'h' => '20',// 低总价
        'n' => '21',// 带装修改善盘
        'a' => '22',// 品牌房企
        'w' => '23',// 网上售楼处
        'z' => '24',// 红色标识
        'i' => '25',// 首页
        'e' => '26',// 生态盘
        'd' => '27',// 电商优惠
    ];


    // 任务调度信息
    public function getTaskDispatch($where)
    {
        try {
            $task = $this->db->name('task_dispatch')->where($where)->select()->toArray();

            if(empty($task)) {
                $task = [];
            }

            return $task;
        } catch(Exception $e) {
            throw $e;
        }
    }

    // 任务加锁
    public function addTaskLock($where, $update)
    {
        try {
            $lock = $this->db->name('task_dispatch')->where($where)->update($update);

            return $lock;
        } catch(Exception $e) {
            throw $e;
        }
    }

    // 获取旧库楼盘数据
    public function getOldData($params)
    {
        try {
            $table = $params['table'] ?? [];
            $where = $params['where'] ?? [];
            $fields = $params['fields'] ?? '*';
            $offset = $params['offset'] ?? 0;
            $limit = $params['limit'] ?? 0;

            $DB = FacadeDb::connect('old9h')->table('9h_' . $table)->alias('m');

            if(!empty($where)) {
                $DB->where($where);
            }
            if(!empty($limit)) {
                $DB->limit($offset, $limit);
            }
            $DB->field($fields);

            $res = $DB->select()->toArray();
            
            if(empty($res)) {
                return [];
            }

            return $res;
        } catch(Exception $e) {
            throw $e;
        }
    }

    // 数据迁移
    public function transferData($data)
    {
        $redis = $this->getReids();

        try {
            extract($data);

            $esServer = new Estatesnew();
            // $redis = $this->getReids();

            $eid = 0;
            $position = '';

            if(!empty($estates)) {
                foreach($estates as $e) {
                    $eid = $e['id'];

                    $newData = $this->dealEstate($e);

                    // 楼盘信息
                    $esRes = $esServer->add($newData);
                    if(empty($esRes['code']) || 1 != $esRes['code']) {
                        $redis->hSet('estates:transfer:building', $e['id'], json_encode($esRes, JSON_UNESCAPED_UNICODE));// 记录失败的ID
                        continue;
                    }
                    $estateId = $esRes['result'];

                    // 图片
                    $position = 'pic';
                    $imgs = $this->dealImg($pic, $e['id'], $estateId);
                    if(!empty($imgs)) {
                        $res = $this->db->name('estates_buildingphotos')->insertAll($imgs);
                        if(empty($res)) {
                            $redis->sAdd('estates:transfer:pic', $e['id']);// 记录失败的ID
                        }
                    }

                    // 户型
                    $position = 'house';
                    $housesList = $this->dealHouses($houses, $e['id'], $estateId);
                    if(!empty($housesList)) {
                        $res = $this->db->name('estates_new_house')->insertAll($housesList);
                        if(empty($res)) {
                            $redis->sAdd('estates:transfer:house', $e['id']);// 记录失败的ID
                        }
                    }

                    // 历史价格
                    $position = 'price';
                    $priceList = $this->dealPrice($price, $e['id'], $estateId);
                    // var_dump($priceList);
                    if(!empty($priceList)) {
                        $res = $this->db->name('price_change_log')->insertAll($priceList);
                        if(empty($res)) {
                            $redis->sAdd('estates:transfer:price', $e['id']);// 记录失败的ID
                        }
                    }

                    // 特色标签
                    $position = 'tags';
                    $tags = $this->dealTags($e['flag'], $e['id'], $estateId);
                    if(!empty($tags)) {
                        $res = $this->db->name('estates_has_tag')->insertAll($tags);
                        if(empty($res)) {
                            $redis->sAdd('estates:transfer:tags', $e['id']);// 记录失败的ID
                        }
                    }
                }
            }

            $count = sizeof($estates);

            return $count;
        } catch(Exception $e) {
            $key = "estates:transfer:{$position}";
            $redis->hSet($key, $eid, $e->getMessage());
            
            throw $e;
        }
    }

    /**
     * 旧库数据触发同步-楼盘
     */
    public function triggerEstate($data, $action)
    {
        try {
            if(empty($data['id'])) {
                throw new Exception('缺少旧楼盘ID-10009');
            }
            $estateId = $data['id'];

            if(in_array($action, ['add', 'update'])) {
                if('update' == $action) {
                    $exist = $this->db->name('estates_new')->where(['old_id' => $estateId])->field('id, price')->find();
                    if(empty($exist)) {
                        $action = 'add';
                    }
                }
                $newData = $this->dealEstate($data);
            }

            $myDB = $this->db->name('estates_new');

            switch($action) {
                case 'add':
                    $newId = $myDB->insertGetId($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'update':
                    $filter = ['create_time', 'old_id'];
                    $newData = array_filter($newData, function($v) use ($filter) {
                        if(in_array($v, $filter)) {
                            return false;
                        }
                        return true;
                    }, ARRAY_FILTER_USE_KEY);
                    $myDB->where(['old_id' => $estateId])->update($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'dele':
                    $where = [
                        ['old_id', 'in', $estateId],
                    ];
                    $myDB->where($where)->update(['is_delete' => 1]);
                    break;
                case 'show':
                    $where = [
                        ['old_id', 'in', $estateId],
                    ];
                    $newData = ['status' => 1];
                    $myDB->where($where)->update($newData);
                    break;
                case 'hide':
                    $where = [
                        ['old_id', 'in', $estateId],
                    ];
                    $newData = ['status' => 0];
                    $myDB->where($where)->update($newData);
                    break;
            }

            // 标签处理
            $newId = $newId ?? 0;
            $tags = $this->dealTags($data['flag'], $data['id'], $newId);
            if('add' == $action) {
                if(!empty($tags)) {
                    $this->db->name('estates_has_tag')->insertAll($tags);
                }
                // 记录价格变化
                $price = [
                    'estate_id' => $newId,
                    'old_estate_id' => $data['id'],
                    'new_price' => $newData['price'],
                    'type' => 1,
                    'month_time' => strtotime(date('Y-m-d', time())),
                    'create_time' => time(),
                    'update_time' => time(),
                ];
                $this->db->name('price_change_log')->insert($price);
            } elseif('update' == $action) {
                $estate = $this->db->name('estates_new')->field('id')->where(['old_id' => $data['id']])->find();
                $newId = $estate['id'] ?? 0;
                $tagsId = array_column($tags, 'tag_id');
                list($newTags, $delTags) = $this->dealEditTag($data['id'], $tagsId);
                if(!empty($delTags)) {
                    $this->db->name('estates_has_tag')->delete($delTags);
                }
                if(!empty($newTags)) {
                    foreach($newTags as $v) {
                        $insertData[] = [
                            'type' => 1,
                            'tag_id' => $v,//标签ID
                            'estate_id' => $newId,// 楼盘ID
                            'old_estate_id' => $data['id'],// 旧楼盘ID
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    $this->db->name('estates_has_tag')->insertAll($insertData);
                }
                // 价格变化处理
                if($newData['price'] != $exist['price']) {// 价格发生变化
                    $priceHis = $this->db->name('price_change_log')->where([
                        ['month_time', '=', strtotime(date('Y-m-d', time()))],
                        ['old_estate_id', '=', $estateId],
                    ])->field('id, old_price')->find();
                    // throw new Exception($this->db->getLastSql());
                    if(!empty($priceHis)) {
                        $priceData = [
                            'old_price' => $priceHis['old_price'] ?? 0,
                            'new_price' => $newData['price'],
                            'update_time' => time(),
                        ];
                        $this->db->name('price_change_log')->where(['id' => $priceHis['id']])->update($priceData);
                    } else {
                        $priceData = [
                            'estate_id' => $newId,
                            'old_estate_id' => $data['id'],
                            'old_price' => $exist['price'] ?? 0,
                            'new_price' => $newData['price'],
                            'type' => 1,
                            'month_time' => strtotime(date('Y-m-d', time())),
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                        $this->db->name('price_change_log')->insert($priceData);
                    }
                }
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 旧库数据触发同步-图片
     */
    public function triggerPic($data, $action, $type)
    {
        try {
            if(!in_array($action, ['add'])) {
                $oldId = $data['id'] ?? 0;
                if(!$oldId) {
                    throw new Exception('旧ID缺失-10011');
                }
            }

            if(in_array($action, ['add', 'update'])) {
                if('update' == $action) {
                    $exist = $this->db->name('estates_buildingphotos')->where(['old_id' => $oldId])->count();
                    if(!$exist) {
                        $action = 'add';
                        $data = [$data];
                    }
                }

                $newData = $this->dealPic($data, $action);

                // 过滤字段
                $filter = ['old_id', 'old_estate_id', 'estate_id', 'create_time'];
            }

            $myDB = $this->db->name('estates_buildingphotos');

            switch($action) {
                case 'add':
                    $myDB->insertAll($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'update':
                    $newData = array_filter($newData, function($v) use ($filter) {
                        if(in_array($v, $filter)) {
                            return false;
                        }
                        return true;
                    }, ARRAY_FILTER_USE_KEY);
                    if(empty($newData['cover'])) {
                        unset($newData['cover']);
                    }
                    $myDB->where(['old_id' => $oldId])->update($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'dele':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $myDB->where($where)->delete();
                    break;
                case 'show':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $myDB->where($where)->update(['status' => 1]);
                    break;
                case 'hide':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $newData = ['status' => 0];
                    $myDB->where($where)->update($newData);
                    break;
                default:
                
                    break;
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 旧库数据触发同步-其他
     */
    public function triggerOther($data, $action, $type)
    {
        try {
            if(empty($data['id'])) {
                throw new Exception('旧ID缺失-10010');
            }
            $oldId = $data['id'];
            if(in_array($action, ['add', 'update'])) {
                if(empty($data['property_id'])) {
                    throw new Exception('旧楼盘ID缺失-10005');
                }
                $oldEstateId = $data['property_id'];
                
                $estate = $this->db->name('estates_new')->where(['old_id' => $oldEstateId])->find();
                if(empty($estate)) {
                    throw new Exception('无对应新楼盘-10006');
                }
                $data['estate_id'] = $estate['id'];

                // 过滤字段
                $filter = ['old_id', 'old_estate_id', 'estate_id'];
            }

            switch($type) {
                case 'house':
                    $table = 'estates_new_house';
                    if(in_array($action, ['add', 'update'])) {
                       $newData = $this->dealSingleHouses($data); 
                    }
                    break;
                case 'price':
                    $table = 'price_change_log';
                    $newData = $this->dealSinglePrice($data);
                    $filter[] = 'create_time';
                    break;
                default:
                    throw new Exception('错误修改类型-10007');
                    break;
            }

            if('update' == $action) {
                $exist = $this->db->name($table)->where(['old_id' => $oldId])->count();
                if(!$exist) {
                    $action = 'add';
                }
            }

            $myDB = $this->db->name($table);
            
            switch($action) {
                case 'add':
                    $myDB->insert($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'update':
                    $filter[] = 'create_time';
                    $newData = array_filter($newData, function($v) use ($filter) {
                        if(in_array($v, $filter)) {
                            return false;
                        }
                        return true;
                    }, ARRAY_FILTER_USE_KEY);
                    $myDB->where(['old_id' => $oldId])->update($newData);
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'dele':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $myDB->where($where)->delete();
                    // throw new Exception($this->db->getLastSql());
                    break;
                case 'show':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $myDB->where($where)->update(['status' => 1]);
                    break;
                case 'hide':
                    $where = [
                        ['old_id', 'in', $oldId]
                    ];
                    $newData = ['status' => 0];
                    $myDB->where($where)->update($newData);
                    break;
                default:
                
                    break;
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 转换楼盘字段
     */
    public function dealEstate($estates)
    {
        $newData = [
            'old_id' => $estates['id'] ?? -1,// 旧库ID -1是以防没有ID时标记该条记录来源
            'name' => $estates['name'] ?? '',// 楼盘名称
            'price_str' => $estates['fold'] ?? '',// 价格描述
            'price' => $estates['pfp'] ?? 0,// 均价-数值
            'opening_time' => !empty($estates['kaipandate']) ? strtotime($estates['kaipandate']) : 0,// 开盘日期
            'address' => $estates['address'] ?? '',// 地址
            // 'list_cover' => !empty($estates['pic']) ? '/' . $estates['pic'] : '',// 列表封面
            'developers' => $estates['developers'] ?? '',// 开发商
            'delivery_time' => !empty($estates['kaipandate']) ? strtotime($estates['kaipandate']) : 0,// 交房时间
            'sales_telephone' => $estates['telephone_sales'] ?? '',// 售楼电话
            'sizelayout' => $estates['sizelayout'] ?? '',// 大小户型
            'building_type' => $estates['building_type'] ?? '',// 建筑类型
            'total_area' => (float)$estates['total_area'] ?? '',// 占地总面积
            'total_construction_area' => $estates['total_construction_area'] ?? '',// 建筑总面积
            'progress_project' => $estates['progress_project'] ?? '',// 项目进度
            'floor_condition' => $estates['floor_condition'] ?? '',// 楼层状况
            'floor_height' => $estates['storey'] ?? '',// 层高
            'decoration' => $estates['decoration'] ?? '',// 装修
            'public_bear' => $estates['pool'] ?? '',// 公摊
            'property_company' => $estates['property_company'] ?? '',// 物业公司
            'property_charges' => $estates['property_charges'] ?? '',// 物业费
            'volume_rate' => $estates['volume_rate'] ?? '',// 容积率
            'greening_rate' => $estates['greening_rate'] ?? '',// 绿化率
            'planning_number' => !empty($estates['households']) ? (int)$estates['households'] : 0,// 规划户数
            'parking_space_number' => !empty($estates['parking']) ? (int)$estates['parking'] : 0,// 车位
            'parking_space_proportion' => $estates['parking_rate'] ?? '',// 车位比
            // 'built_area' => $estates['mainlayout'] ?? '',// 主力户型=>建面
        ];

        // 图片(1.0图片未做修改时，不会再传该字段)
        if(!empty($estates['pic'])) {
            $newData['list_cover'] = '/' . $estates['pic'];
        }

        // 经纬度处理
        if(!empty($estates['coordinate'])) {
            $coordinate = explode(',', $estates['coordinate']);
            $newData['lng'] = $coordinate['0'] ?? '';
            $newData['lat'] = $coordinate['1'] ?? '';
        }

        // 区域处理
        if(!empty($estates['area_id'])) {
            if(isset($this->areaIds[$estates['area_id']])) {
                $areaData = $this->areaIds[$estates['area_id']];
                switch($areaData['type']) {
                    // 省
                    case 1:
                        $newData['province'] = $areaData['id'];
                        $newData['province_str'] = $areaData['name'];
                        break;
                    // 市
                    case 2:
                        $newData['city'] = $areaData['id'];
                        $newData['city_str'] = $areaData['name'];
                        // 找其所属省
                        $newData['province'] = $this->areaIds[$areaData['oid']]['id'] ?? '';
                        $newData['province_str'] = $this->areaIds[$areaData['oid']]['name'] ?? '';
                        break;
                    // 区/县
                    case 3:
                        $newData['area'] = $areaData['id'];
                        $newData['area_str'] = $areaData['name'];
                        // 找其所属市
                        $newData['city'] = $this->areaIds[$areaData['oid']]['id'] ?? '';
                        $newData['city_str'] = $this->areaIds[$areaData['oid']]['name'] ?? '';
                        $ocityId = $this->areaIds[$areaData['oid']]['oid'] ?? '';
                        // 找其所属省
                        $newData['province'] = $this->areaIds[$ocityId]['id'] ?? '';
                        $newData['province_str'] = $this->areaIds[$ocityId]['name'] ?? '';
                        break;
                }
            }
        }

        // 销售状态
        $newData['sale_status'] = $this->saleStatus[$estates['sales_status']] ?? 1;

        // 地铁线
        if(!empty($estates['subways'])) {
            $subways = explode(',', $estates['subways']);
            if(!empty($subways)) {
                foreach($subways as $s) {
                    $subList[] = trim($s, '号线');
                }
            }
            if(!empty($subList)) {
                $newData['subways'] = implode(',', $subList);
            }
        }

        // 房屋用途
        if(!empty($estates['project_type'])) {
            $newData['house_purpose'] = $this->dealHousesType($estates['project_type']);
        }

        // 销售许可证
        if(!empty($estates['pre_sale_permit'])) {
            $newData['sales_license'] = $this->dealLicense($estates['pre_sale_permit']);
        }

        // 编辑时间
        $time = !empty($estates['addtime']) ? strtotime($estates['addtime']) : time();
        $newData['create_time'] = time();
        $newData['update_time'] = $time;

        return $newData;
    }

    // 图片字段转换
    public function dealImg($pic, $oldId = 0, $id = 0)
    {
        $res = [];

        if(!empty($pic[$oldId])) {
            foreach($pic[$oldId] as $p) {
                $res[] = [
                    'name' => $p['name'] ?? '',// 图片名称
                    'category_id' => $this->imgType[$p['type']] ?? 0,// 类别
                    'cover' => !empty($p['pic']) ? '/' . $p['pic'] : '',// 图片路径
                    'status' => $p['status'] ?? 0,// 状态
                    'estate_id' => $id,// 楼盘ID
                    'old_id' => $p['id'],// 旧ID
                    'old_estate_id' => $oldId,// 旧楼盘ID
                    'update_time' => time(),
                ];
            }
        }

        return $res;
    }
    protected function dealPic($data, $type = 'add')
    {
        $res = [];
        if('add' == $type) {
            // 通过旧楼盘找到新楼盘ID
            $newIds = [];
            $eIds = array_column($data, 'property_id');
            if(!empty($eIds)) {
                $eData = $this->db->name('estates_new')->where([
                    ['old_id', 'in', $eIds],
                ])->select()->toArray();
                if(!empty($eData)) {
                    foreach($eData as $e) {
                        $newIds[$e['old_id']] = $e['id'];
                    }
                }
            }

            if(!empty($data)) {
                foreach($data as $d) {
                    $d['estate_id'] = !empty($newIds[$d['property_id']]) ? $newIds[$d['property_id']] : 0;
                    $res[] = $this->dealSingleImg($d);
                }
            }
        } elseif('update' == $type) {
            $eData = $this->db->name('estates_new')->where([
                ['old_id', '=', $data['property_id']],
            ])->find();
            if(!empty($eData)) {
                $data['estate_id'] = $eData['id'];
            }

            $res = $this->dealSingleImg($data);
        }
        return $res;
    }
    protected function dealSingleImg($data)
    {
        $res = [
            'name' => $data['name'] ?? '',// 图片名称
            'category_id' => $this->imgType[$data['type']] ?? 0,// 类别
            'cover' => !empty($data['pic']) ? '/' . $data['pic'] : '',// 图片路径
            'status' => $p['status'] ?? 0,// 状态
            'estate_id' => $data['estate_id'] ?? 0,// 楼盘ID
            'old_id' => $data['id'] ?? 0,// 旧ID
            'old_estate_id' => $data['property_id'] ?? 0,// 旧楼盘ID
            'update_time' => !empty($data['addtime']) ? strtotime($data['addtime']) : time(),
        ];

        return $res;
    }

    // 户型字段转换
    protected function dealHouses($houses, $oldId = 0, $id = 0)
    {
        $res = [];
        if(!empty($houses[$oldId])) {
            foreach($houses[$oldId] as $h) {
                $res[] = [
                    'name' => $h['code'] . ' ' . $h['name'],// 户型名称
                    'rooms_str' => $h['room'] ?? '',// 户型几居室描述
                    'rooms' => $this->houseType[$h['type']] ?? 0,// 户型-几居室
                    'img' => !empty($h['pic']) ? '/' . $h['pic'] : '',// 图片
                    'sale_status' => $this->saleStatus[$h['sales_status']] ?? 1,// 销售状态
                    'price' => $h['fold'] ?? 0,// 均价
                    'price_total' => !empty($h['total_price']) ? bcdiv($h['total_price'], 10000, 2) : 0,// 总价
                    'built_area' => $h['built_up_area'] ?? 0,// 建面
                    'status' => $h['status'] ?? 1,// 状态
                    'estate_id' => $id,// 楼盘ID
                    'old_estate_id' => $oldId,// 旧楼盘ID
                    'old_id' => $h['id'],// 旧ID
                ];
            }
        }
        
        return $res;
    }
    protected function dealSingleHouses($data)
    {
        $res = [
            'rooms_str' => $data['room'] ?? '',// 户型几居室描述
            'rooms' => $this->houseType[$data['type']] ?? 0,// 户型-几居室
            // 'img' => !empty($data['pic']) ? '/' . $data['pic'] : '',// 图片
            'sale_status' => $this->saleStatus[$data['sales_status']] ?? 1,// 销售状态
            'price' => $data['fold'] ?? 0,// 均价
            'price_total' => !empty($data['total_price']) ? bcdiv($data['total_price'], 10000, 2) : 0,// 总价
            'built_area' => $data['built_up_area'] ?? 0,// 建面
            'status' => $h['status'] ?? 1,// 状态
            'old_id' => $data['id'] ?? 0,// 旧ID
        ];
        $res['old_estate_id'] = $data['property_id'] ?? 0;// 旧楼盘ID
        $res['estate_id'] = $data['estate_id'] ?? 0;// 楼盘ID

        // 图片-编辑时若没变化不会传该字段
        if(!empty($data['pic'])) {
            $res['img'] = '/' . $data['pic'];
        }

        // 编辑时间
        $time = !empty($data['addtime']) ? strtotime($data['addtime']) : time();
        $res['create_time'] = time();
        $res['update_time'] = $time;

        // 户型名称
        $name = [];
        if(!empty($data['code'])) {
            $name[] = $data['code'];
        }
        if(!empty($data['name'])) {
            $name[] = $data['name'];
        }
        $name = !empty($name) ? implode(' ', $name) : '';
        $res['name'] = $name;

        return $res;
    }

    // 历史价格处理
    protected function dealPrice($price, $oldId = 0, $id = 0)
    {
        $res = [];

        if(!empty($price[$oldId])) {
            foreach($price[$oldId] as $p) {
                $time = !empty($p['addtime']) ? strtotime($p['addtime']) : time();
                $res[] = [
                    'new_price' => $p['price'] ?? 0,// 价格
                    'month_time' => $p['months'] ?? '',// 月份
                    'type' => 1,// 类型
                    'create_time' => $time,// 创建时间
                    'update_time' => $time,// 修改时间
                    'estate_id' => $id,// 楼盘ID
                    'old_estate_id' => $oldId,// 旧楼盘ID
                    'old_id' => $p['id'],// 旧ID
                ];
            }
        }

        return $res;
    }
    protected function dealSinglePrice($data)
    {
        $time = !empty($data['addtime']) ? strtotime($data['addtime']) : time();
        $res = [
            'new_price' => $data['price'] ?? 0,// 价格
            'month_time' => !empty($data['riqi']) ? strtotime($data['riqi']) : 0,// 月份
            'type' => 1,// 类型
            'create_time' => time(),// 创建时间
            'update_time' => $time,// 修改时间
            'estate_id' => $data['estate_id'],// 楼盘ID
            'old_estate_id' => $data['property_id'] ?? 0,// 旧楼盘ID
            'old_id' => $data['id'] ?? 0,// 旧ID
        ];

        return $res;
    }

    // 处理标签
    protected function dealTags($flag, $oldId = 0, $id = 0)
    {
        $res = [];
        $time = time();

        $flag = !empty($flag) ? explode(',', $flag) : [];
        if(!empty($flag)) {
            foreach($flag as $f) {
                if(isset($this->flag[$f])) {
                    $fl = $this->flag[$f];
                    $res[] = [
                        'type' => 1,
                        'tag_id' => $fl,//标签ID
                        'estate_id' => $id,// 楼盘ID
                        'old_estate_id' => $oldId,// 旧楼盘ID
                        'create_time' => $time,
                        'update_time' => $time,
                    ];
                }
            }
        }

        return $res;
    }
    protected function dealEditTag($id, $tags)
    {
        try {
            $delTags = [];// 需要删除的标签的记录ID
            $newTags = [];// 需要增加的标签的ID
            $tmp = [];
            $where = [
                ['old_estate_id', '=', $id],
                ['type', '=', 1],
            ];
            $res = $this->db->name('estates_has_tag')->where($where)->field('id, tag_id')->select()->toArray();

            if (!empty($res)) {
                foreach ($res as $v) {
                    $tmp[] = $v['tag_id'];
                    if (!in_array($v['tag_id'], $tags)) {// 不在本次选中标签内的,删除
                        $delTags[] = $v['id'];
                    }
                }
                // 新增的标签
                $newTags = array_diff($tags, $tmp);// 在本次选中的标签内，但不在原有标签内
            } else {
                $newTags = $tags;
            }
            return [$newTags, $delTags];
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 处理预售许可证
    protected function dealLicense($licence)
    {
        $res = '';
        if(!empty($licence)) {
            if(strstr($licence, '，')) {
                $arr = explode('，', $licence);
            } elseif(strstr($licence, '；')) {
                $arr = explode('；', $licence);
            } elseif (strstr($licence, '、')) {
                $arr = explode('、', $licence);
            } else {
                $arr[] = $licence;
            }
            
            if(!empty($arr)) {
                foreach($arr as $a) {
                    $list[] = [
                        'license' => $a,
                        'time' => '',
                        'building' => '',
                    ];
                }
                $res = json_encode($list, JSON_UNESCAPED_UNICODE);
            }
        }
        return $res;
    }

    // 处理房屋用途
    protected function dealHousesType($type)
    {
        $res = '';
        if(!empty($type)) {
            $type = explode('、', $type);
            if(!empty($type)) {
                foreach($type as $t) {
                   if(strstr($t, '住宅')) {
                        $arr[] = 1;
                    }
                    if(strstr($t, '别墅')) {
                        $arr[] = 2;
                    }
                    if(strstr($t, '商住')) {
                        $arr[] = 3;
                    }
                    if(strstr($t, '写字楼')) {
                        $arr[] = 4;
                    }
                    if(strstr($t, '公寓')) {
                        $arr[] = 5;
                    }
                    if(strstr($t, '车位')) {
                        $arr[] = 6;
                    }
                    if(strstr($t, '花园住宅')) {
                        $arr[] = 7;
                    }
                    if(strstr($t, '商铺')) {
                        $arr[] = 8;
                    } 
                }
                if(!empty($arr)) {
                    $res = implode(',', $arr);
                }
            }
        }
        return $res;
    }

    public function setEstatesField($data)
    {
        try {
            // var_dump($data);
            if(!empty($data)) {
                foreach($data as $v) {
                    if(!empty($v['mainlayout']) && !empty($v['id'])) {
                        $this->db->name('estates_new')->where(['old_id' => $v['id']])->update(['built_area' => $v['mainlayout']]);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
