<?php


namespace app\server\admin;


use app\common\base\ServerBase;
use app\server\marketing\Subject;
use think\Exception;

class WorkWriteOff extends ServerBase
{
    /**
     * 会员信息
     * @param $params
     * @param $userId
     * @return array
     */
    public function staffMember($params, $userId)
    {
        try {
            if (empty($params['activity_id'])) {
                return $this->responseFail('活动id不能为空');
            }
            $where = ['id' => $params['activity_id'], 'status' => 1];
            $activity_info = (new Subject())->getInfo($where, 'region_no')['result'];

            $info = $this->db->name('user')->where('id', $userId)
                ->field('realname,phone,nickname,headimgurl,store_id')->find();
            $number = $this->db->name('write_off_information')->where('staff_member_id', $userId)->count();
            $couponInfo = $this->db->name('activity_coupon')->where('shop_id',$info['store_id'])->field('coupon_send_unm,coupon_surplus_num')->find();

            $info['nickname'] = empty($info['realname']) ? (empty($info['nickname']) ? '' : $info['nickname']) : $info['realname'];
            $info['number'] = empty($number) ? 0 : $number;
            $info['headimgurl'] = empty($info['headimgurl']) ? '' : $info['headimgurl'];
            $info['region_no'] = $activity_info['region_no'];
            $info['coupon_surplus_num'] = $couponInfo['coupon_surplus_num'] ?? 0;
            $info['coupon_send_unm'] = $couponInfo['coupon_send_unm'] ?? 0;

            unset($info['realname']);
            return $this->responseOk($info);
        } catch (\Exception $exception) {
            return $this->responseFail($exception->getMessage());
        }
    }

    /**
     * 核销列表
     * @param $params
     * @return array
     */
    public function list($params)
    {
        try {
            $where = [
                ['woi.staff_member_id', '=', $params['user_id']]
            ];
            if (!empty($params['name'])) {
                $where[] = ['woi.user_name', 'like', '%' . $params['name'] . '%'];
            }
            $res = $this->db->name('write_off_information')
                ->alias('woi')
                ->join('activity_coupon_shop acs', 'acs.id = woi.store_id')
                ->where($where)
                ->field('woi.id,
                    woi.write_off_time,
                    woi.user_name,
                    woi.user_headimgurl,
                    woi.user_type,
                    woi.subject_id,
                    woi.coupon_describe,
                    acs.shop_img,
                    acs.shop_name,
                    acs.shop_type_string,
                    acs.shop_lable_string
                ')
                ->paginate($params['page_size']);

            if ($res->isEmpty()) {
                $result['list'] = [];
            } else {
                $result['list'] = $res->items();
                foreach ($result['list'] as $key => &$value) {
                    $value['write_off_time'] = empty($value['write_off_time']) ? '' : date('Y-m-d H:i:s', $value['write_off_time']);
                }

            }
            $result['total'] = $res->total();
            $result['last_page'] = $res->lastPage();
            $result['current_page'] = $res->currentPage();
            return $this->responseOk($result);
        } catch (\Exception $exception) {
            return $this->responseFail($exception->getMessage());
        }
    }

    public function info($data)
    {
        try {
            $where = ['id' => $data['subject_id'], 'status' => 1];
            $activity_info = (new Subject())->getInfo($where, 'region_no')['result'];

            $info = $this->db->name('user')->where('id', $data['user_id'])
                ->field('id,nickname,headimgurl,unionid')->find();

            $userWhere = [
                ['unionid', '=', $info['unionid']],
                ['bind_wx_city_no', '=', $activity_info['region_no']]
            ];

            $user_is_exit = $this->db->name('user_association')
                ->where($userWhere)->field('subscribe')->find();

            $shopInfo = $this->db->name('activity_coupon_shop')
                ->where('id', $data['store_id'])->field('shop_img,shop_name,shop_lable_string,shop_type_string')->find();

            $coupon = $this->db->name('activity_coupon')->where('id', $data['coupon_id'])->field('coupon_send_unm,coupon_surplus_num,coupon_describe')->find();


            if (empty($info) || empty($shopInfo) || empty($coupon)) {
                $this->db->name('log')->insert([
                    'content' => json_encode([$info,$shopInfo,$coupon]),
                    'created_at' => 1,
                    'source' => 'hexiao',
                    'url' => '23'
                ]);
                return $this->responseFail('信息有误不对');
            }

            $time = time();
            $start = strtotime(date('Y-m-d', $time));
            $end = $start + 24 * 60 * 60 - 1;
            $where = [
                ['subject_id', '=', $data['subject_id']],
                ['user_id', '=', $data['user_id']],
                ['write_off_time', '>=', $start],
                ['write_off_time', '<=', $end],
            ];
            //判断审核人员是否有权限
            $is_exit = $this->db->name('write_off_information')->where($where)->find();
            if(empty($is_exit)){
                $info['type'] = empty($user_is_exit['subscribe']) ? 0 : 1;
            }else{
                $info['type'] = 0;
            }


            $info['shop_img'] = $shopInfo['shop_img'];
            $info['shop_name'] = $shopInfo['shop_name'];
            $info['shop_lable_string'] = $shopInfo['shop_lable_string'];
            $info['shop_type_string'] = $shopInfo['shop_type_string'];
            $info['coupon_describe'] = $coupon['coupon_describe'];

            return $this->responseOk($info);
        } catch (\Exception $exception) {
            return $this->responseFail($exception->getMessage());
        }
    }

    /**
     * 审核
     * @param $params
     *  $params['staff_member_id'] 审核人员id
     *  $params['user_id'] 用户id
     *  $params['sort_id]  店铺id
     *  $params['coupon_id'] 优惠券id
     * @return array
     */
    public function review($params)
    {
        try {
            $time = time();
            $start = strtotime(date('Y-m-d', $time));
            $end = $start + 24 * 60 * 60 - 1;
            $where = [
                ['subject_id', '=', $params['subject_id']],
                ['user_id', '=', $params['user_id']],
                ['write_off_time', '>=', $start],
                ['write_off_time', '<=', $end],
            ];
            //判断审核人员是否有权限
            $info = $this->db->name('user')->where('id', $params['staff_member_id'])->find();

            if ($info['store_id'] != $params['store_id']) {
                return $this->responseFail('工作人员只能审核自己所在的店铺');
            }

            //判断该用户今天是否有使用过优惠券
            $is_exit = $this->db->name('write_off_information')->where($where)->find();
            if (!empty($is_exit)) {
                return $this->responseFail('用户当天有参与过兑换');
            }
            //判断有没有过期
//            $storeInfo = $this->db->name('activity_coupon_shop')->where('id', $params['store_id'])->field('start_time,end_time')->find();


            //判断优惠券的数量是否到
            $couponInfo = $this->db->name('activity_coupon')->where('id', $params['coupon_id'])->find();

            if ($couponInfo['start_time'] > $time) {
                return $this->responseFail('活动还未开始');
            }
            if ($couponInfo['end_time'] < $time) {
                return $this->responseFail('活动已经结束');
            }
            if ($couponInfo['shop_id'] != $params['store_id']) {
                return $this->responseFail('该优惠券不属于该店，请到对应的店铺兑换');
            }
            if ($couponInfo['coupon_surplus_num'] <= 0) {
                return $this->responseFail('优惠券数量不足');
            }

            //获取用户信息
            $userInfo = $this->db->name('user')->where('id', $params['user_id'])->field('nickname,headimgurl')->find();

            $data = [
                'staff_member_id' => $params['staff_member_id'],
                'store_id'        => $params['store_id'],
                'user_id'         => $params['user_id'],
                'coupon_id'       => $params['coupon_id'],
                'write_off_time'  => time(),
                'remarks'         => $params['remarks'] ?? '',
                'create_time'     => $time,
                'update_time'     => $time,
                'user_name'       => $userInfo['nickname'],
                'user_headimgurl' => $userInfo['headimgurl'],
                'subject_id'      => $params['subject_id'],
                'coupon_describe' => $couponInfo['coupon_describe']
            ];
            $couponWhere = [
                ['id', '=', $params['coupon_id']],
                ['coupon_surplus_num', '>', 0],
            ];
            //开始其事务
            $this->db->startTrans();

            //扣除数量
            $res = $this->db->name('activity_coupon')
                ->where($couponWhere)
                ->dec('coupon_surplus_num', 1)->update();
            if (empty($res)) {
                throw new Exception('优惠券不足');
            }

            //插入
            $res = $this->db->name('write_off_information')->insert($data);
            if (empty($res)) {
                throw new Exception('审核失败');
            }

            $is_exit = $this->db->name('write_off_information')->where($where)->count();
            if ($is_exit > 1) {
                throw new Exception('用户当天有参与过兑换');
            }

            $this->db->commit();
            return $this->responseOk();
        } catch (\Exception $exception) {
            $this->db->rollback();
            return $this->responseFail($exception->getMessage());
        }
    }
}