<?php


namespace app\server\user;


use app\common\base\ServerBase;
use app\common\MyConst;

class Attention extends ServerBase
{
    /**
     * 我的房源关注
     * @param $userId 用户id
     * @param int $pageSize 页面分页
     * @return array
     */
    public function myListings($userId)
    {
        try {
            $time = time();
            $saleStatusArray = MyConst::ESTATESNEW_SALE_STATUS;

            $where = [
                ['l.user_id', '=', $userId],
                ['e.status', '=', 1],
                ['e.is_delete', '=', 0]
            ];

            $list = $this->db->name('estates_new_attention')->alias('l')
                ->join('estates_new e', 'e.id = l.building_id')
                ->leftJoin('estates_has_tag eht', 'e.id=eht.estate_id and eht.type=1')
                ->field(' l.id,
                               e.id as estates_id,
                               e.sale_status,
                               e.house_purpose,
                               GROUP_CONCAT(eht.tag_id) as feature_tag,
                               e.discount,
                               l.name,
                               l.area,
                               l.price,
                               e.business_area_str,
                               e.sale_status,
                               e.house_purpose,
                               e.list_cover,
                               e.logo,
                               e.detail_cover,
                               e.status,
                               e.is_delete,
                               l.city
                               ')
                ->where($where)
                ->group('e.id')
                ->order('e.id', 'desc')
                ->select();


            if ($list->isEmpty()) {
                $result = [];
            } else {
                $list = $list->toArray();
                foreach ($list as $key => &$value) {
                    //特色标签处理
                    if (empty($value['feature_tag'])) {
                        $value['feature_tag'] = [];
                    } else {
                        $value['feature_tag'] = explode(',', $value['feature_tag']);
                    }
                    $value['city'] = empty($value['city']) ? '' : $value['city'];
                    /**
                     * 卖点
                     */
                    $sellingPoint = [];
                    $discount = json_decode($value['discount'], TRUE);
                    if (!empty($discount)) {
                        foreach ($discount as $dis) {// 找出时间范围内
                            $startTime = strtotime($dis['start_time']);
                            $endTime = strtotime($dis['end_time']);
                            if ($startTime <= $time && $endTime >= $time) {
                                $sellingPoint[] = ['title' => $dis['title'], 'type' => 'discount'];
                            }
                        }
                    }

                    $value['selling_point'] = $sellingPoint;

                    //结构重新处理
                    $lableData = [$saleStatusArray[$value['sale_status']]];
                    $lable = (new BrowseRecords())->lable();

                    if (!empty($value['house_purpose']) && isset($lable['house_purpose'][$value['house_purpose']])) {
                        array_push($lableData, $lable['house_purpose'][$value['house_purpose']]);
                    }
                    if (!empty($value['feature_tag'][0]) && isset($lable['feature_tag'][$value['feature_tag'][0]])) {
                        array_push($lableData, $lable['feature_tag'][$value['feature_tag'][0]]);
                    }
                    if (!empty($value['feature_tag'][1]) && isset($lable['feature_tag'][$value['feature_tag'][1]])) {
                        array_push($lableData, $lable['feature_tag'][$value['feature_tag'][1]]);
                    }

                    if (!empty($value['detail_cover']) && !empty($value['logo'])) {
                        $coverType = 1;
                    } else {
                        $coverType = 0;
                    }
                    if ($value['is_delete'] == 0 && $value['status'] == 1) {
                        $statusDelete = 0;
                    } else {
                        $statusDelete = 1;//被下架删除了
                    }

                    $sellingData = [];
                    if (!empty($value['selling_point'])) {
                        foreach ($value['selling_point'] as $sv) {
                            $sellingData[] = [
                                'type' => $sv['type'] == 'hot' ? 0 : 1,
                                'name' => $sv['title'],
                            ];
                        }
                    }

                    $data[$key] = [
                        'id'            => $value['estates_id'],
                        'type'          => 8,// 8-新房
                        'info'          => [
                            'name'  => $value['name'],
                            'tip'   => $lableData,
                            'price' => $value['price'],
                            'site'  => $value['city'] . '' . $value['business_area_str'],
                            'area'  => $value['area'],
                            'lab'   => $sellingData
                        ],
                        'cover'         => $coverType ?? 0,
                        'status_delete' => $statusDelete,
                        'img'           => [$value['list_cover']]
                    ];

                }

            }
//            $result[] = [
//                'title' => '房源',
//                'list'  => $data,
//            ];
//            $result[] = [
//                'title' => '资讯',
//                'list'  => [],
//            ];


            return $this->responseOk($data);
        } catch (Exception $exception) {
            return $this->responseFail($exception);
        }
    }

    /**
     * 咨询关注
     * @param $userId
     * @param int $pageSize
     * @return array
     */
    public function myAdvisory($userId)
    {
        try {
            $data = [];
            $list = $this->db->name('article_attention')
                ->where('user_id', $userId)
                ->field('id,article_id,name,img,author_avatar,author_name')
                ->select();


            if ($list->isEmpty()) {
                $result = [];
            } else {
                $list = $list->toArray();
                foreach ($list as $key => $value) {

                    $data[$key] = [
                        'id'     => $value['article_id'],
                        'type'   => 1,
                        "title"  => $value['name'],
                        "img"    => empty($value['img']) ? [] : (empty(json_decode($value['img'], true)) ? [] : [json_decode($value['img'], true)] ),
                        "author" => [
                            "name" => '九房网',
                            "head" => $value['author_avatar']
                        ],

                    ];
                }

            }
//            $result[] = [
//                'title' => '房源',
//                'list'  => [],
//            ];
//            $result[] = [
//                'title' => '资讯',
//                'list'  => $data,
//            ];
            return $this->responseOk($data);
        } catch (Exception $exception) {
            return $this->responseFail($exception->getMessage());
        }
    }

    /**
     * 房源关注
     * @param $params
     * @return array
     */
    public function attentionListings($params)
    {
        try {
            $time = time();

            $buInfo = $this->db->name('estates_new_attention')->where([
                ['user_id', '=', $params['user_id']],//用户id
                ['building_id', '=', $params['id']] //楼栋的id
            ])->find();

            if (empty($buInfo)) { //关注
                $info = $this->db->name('estates_new')
                    ->where('id', $params['id'])
                    ->field('id,name,built_area,price,business_area_str,area_str')
                    ->find();
                $data = [
                    'building_id'       => $params['id'],
                    'user_id'           => $params['user_id'],
                    'name'              => $info['name'],
                    'area'              => $info['built_area'],
                    'price'             => $info['price'],
                    'business_district' => $info['business_area_str'],
                    'city'              => $info['area_str'],
                    'create_time'       => $time,
                    'update_time'       => $time
                ];
                $this->db->name('estates_new_attention')->insert($data);
            } else { //取消关注
                $where = [
                    ['user_id', '=', $params['user_id']],//用户id
                    ['building_id', '=', $params['id']] //楼栋的id
                ];
                $this->db->name('estates_new_attention')->where($where)->delete();
            }

            return $this->responseOk();
        } catch (\Exception $exception) {
            return $this->responseFail($exception->getMessage());
        }
    }

    /**
     * 关键楼盘id获取关注楼盘用户列表
     */
    public function getFollowUserListByEstatesId($e_id){
        if(empty($e_id)){
            return [];
        }

        $list  = $this->db->name('estates_new_attention')->where('building_id','=',$e_id)->field('user_id as id')->select();

        return  empty($list) ? [] : $list->toArray();

    }

    /**
     * 根据用id 获取楼盘状态
     */
    public function getFollowUserEstatesByListId($user_id){
        if(empty($user_id)){
            return [];
        }

        $list  = $this->db->name('estates_new_attention')->where('user_id','=',$user_id)->field('user_id as id')->select();

        return  empty($list) ? [] : $list->toArray();

    }


}