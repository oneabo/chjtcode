<?php


namespace app\admin\controller;
use app\common\base\AdminBaseController;
use app\common\MyConst;
use app\server\admin\City;
use app\server\admin\Subway;
use app\server\estates\SelectLog;
use app\server\estates\Tag;

class SelectLogController extends AdminBaseController
{
    /**
     * 列表
     */
    public function getList(){
        $params = $this->request->param();

        $where = [];

        $regionRes = $this->getMyCity();

        // 城市
        if(empty($params['region_no']) || -1 == $params['region_no']) {// 搜索当前全部城市
            $cityIds = !empty($regionRes['data']) ? array_column($regionRes['data'], 'id') : [];
        } else {
            $cityIds = $params['region_no'];
        }

        $where[] = ['esl.city', 'in', $cityIds];
        if(!empty($params['user_id'])){
            $where[] = ['esl.user_id','=',$params['user_id']];
        }

        $data = [
            'where' => $where,
            'fields' => 'u.nickname, u.headimgurl, u.realname, u.phone, esl.*',
            'join' => [
                ['table' => 'user u', 'cond' => "u.id=esl.user_id", 'type' => 'left'],
            ],
            'page_size' => 20,
        ];

        $result = (new SelectLog())->getList($data);

        if(empty($result['code']) || 1 != $result['code']) {
            $this->error($result);
        }
        $result = $result['result'];

        if(!empty($result['list'])) {
            $cityServer = new City();

            // 标签
            $tagsList = (new Tag())->getTagList(); //特色标签
            // 城市
            $cityList = [];
            if(!empty($regionRes['data'])) {
                foreach($regionRes['data'] as $r) {
                    $cityList[$r['id']] = $r;
                }
            }
            // 区/县
            $areaList = [];
            $area = $cityServer->getSiteAreas(['pid' => $cityIds])['result'];
            if(!empty($area) && is_array($area)) {
                foreach($area as $a) {
                    $areaList[$a['id']] = $a;
                }
            }
            // 商圈
            $businessList = [];
            $business = $cityServer->getSiteBusinessAreas(['pid' => $cityIds])['result'];
            if(!empty($business) && is_array($business)) {
                foreach($business as $b) {
                    $businessList[$b['id']] = $b;
                }
            }
            // 地铁站点
            $siteList = [];
            $sites = (new Subway)->getSubwayList([
                ['region_no', 'in', $cityIds]
            ]);
            if(!empty($sites['code']) && 1 == $sites['code']) {
                $sites = $sites['result'];
            }
            if(!empty($sites)) {
                foreach($sites as $s) {
                    $siteList[$s['id']] = $s;
                }
            }

            foreach($result['list'] as &$v) {
                // 用户信息处理
                $v['nickname'] = $v['nickname'] ?? '';
                $v['headimgurl'] = $v['headimgurl'] ?? '';
                $v['realname'] = $v['realname'] ?? '';
                $v['phone'] = $v['phone'] ?? '';
                // 创建时间
                $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
                // 特色标签
                $v['feature_tag'] = $this->dealArray($v['feature_tag'], $tagsList);
                // 城市
                $v['region'][] = !empty($cityList[$v['city']]) ? $cityList[$v['city']]['cname'] : '';
                // 区/县
                $v['region'][] = $this->dealArray($v['area'], $areaList, 'cname');
                // 商圈
                $v['region'][] = $this->dealArray($v['business_area'], $businessList, 'cname');
                // 地铁线
                $v['region'][] = !empty($v['subway']) ? "{$v['subway']}号线" : '';
                // 地铁站点
                $v['region'][] = $this->dealArray($v['subway_sites'], $siteList, 'name', '地铁站');
                //想买区域集合
                $v['region'] = !empty($v['region']) ? trim(implode(',', $v['region']), ',') : '';
                // 几居室
                $v['rooms'] = MyConst::ROOMS[$v['rooms']] ?? '';
            }
        }

        $this->success($result);
    }

    // 处理逗号分隔的字段
    protected function dealArray($string, $data, $field = '', $after = '')
    {
        $result = [];

        $arr = !empty($string) ? explode(',', $string) : [];
        if(!empty($arr)) {
            foreach($arr as $id) {
                if(!empty($field)) {
                    if(isset($data[$id][$field])) {
                        $item = $data[$id][$field];
                    }
                } else {
                    if(isset($data[$id])) {
                        $item = $data[$id];
                    }
                }
                $result[] = $item . $after;
            }
        }

        if(!empty($result)) {
            $res = implode(',', $result);
        } else {
            $res = '';
        }

        return $res;
    }

    /**
     * 删除
     */
    public function delete()
    {
        $data = $this->request->param();

        if(empty($data['id'])) {
            $this->error('缺少必要参数');
        }
        $id = $data['id'];

        $res = (new SelectLog())->delete($id);

        if(empty($res['code']) || 1 != $res['code']) {
            $this->error($res);
        }

        $this->success($res);
    }

}