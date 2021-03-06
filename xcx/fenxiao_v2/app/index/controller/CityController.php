<?php


namespace app\index\controller;


use app\common\base\UserBaseController;
use app\server\admin\City;
use app\common\traits\TraitEstates;
use app\server\estates\Estatesnew;

class CityController extends UserBaseController
{
    use TraitEstates;

    //site_city
    public function getCityList(){

        $param['status'] = 1;
        $res = (new City())->getSiteCitys($param,'id,cname,is_hot');

        if($res['code'] == 0){
            return $this->error($res['msg']);
        }
        return $this->success($res['result']);
    }

    /**
     * 区/县/商圈列表
     */
    public function getAreaList()
    {
        $params = $this->request->param();

        $type = $params['type'] ?? '';
        $isCount = $params['is_count'] ?? 0;// 是否计算楼盘

        switch($type) {
            // 区/县
            case 'area':
                if(empty($params['city_no'])) {
                    $this->error('缺少城市范围');
                }
                $search = ['pid' => $params['city_no'], 'status' => 1];
                $list = (new City())->getSiteAreas($search);
                $group = 'area';
                break;
            // 商圈
            case 'business':
                if(empty($params['city_no']) && empty($params['area_no']) && empty($params['business_no'])) {
                    $this->error('缺少区域范围');
                }
                $search = ['status' => 1];
                if(!empty($params['city_no'])) {
                    $search['city_no'] = $params['city_no'];
                }
                if(!empty($params['area_no'])) {
                    $search['area_no'] = $params['area_no'];
                }
                if(!empty($params['business_no'])) {
                    $search['id'] = $params['business_no'];
                }
                $list = (new City())->getSiteBusinessAreas($search);
                $group = 'business_area';
                break;
            default:
                $this->error('类型错误');
                break;
        }

        if(empty($list['code']) || 1 != $list['code']) {
            $this->error($list);
        }

        $result = [];
        if(!empty($list['result'])) {
            if($isCount) {
                $this->buildWhere($params, $data);
                $data['where'][] = [$group, '<>', ''];
                $data['fields'] = "count(en.id) as count, {$group}";
                $data['group'][] = $group;
                $res = (new Estatesnew())->getListByParams($data);
                if (empty($res['code'])) {
                    $this->error($res);
                }
                $estates = $res['result'];
            }

            foreach($list['result'] as $v) {
                $result[$v['cname']] = [
                    'code' => $v['id'],
                    'cname' => $v['cname'],
                    'lnglat' => !empty($v['lng']) && !empty($v['lat']) ? "{$v['lng']},{$v['lat']}" : "",
                    'count' => 0,
                ];
                if(!empty($estates)) {
                    foreach ($estates as $e) {
                        if($e[$group] == $v['id']) {
                            $result[$v['cname']]['count'] = $e['count'];
                            break;
                        }
                    }
                }
            }
        }

        $this->success($result);
    }
}