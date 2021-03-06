<?php


namespace app\miniwechat\controller;


use app\common\base\UserBaseController;
use app\server\admin\Promotions;
use app\server\marketing\Vote;
use app\server\user\User;

class PromotionsController extends UserBaseController
{
    /**
     * 优惠活动-列表
     */
    public function getPromotionsList()
    {
        $param = $this->request->param(); //active_id
        $param['active_id'] = intval($param['active_id']);
        if (empty($param['active_id'])) {
            return $this->error('缺失参数');
        }
        $res = (new Promotions())->getPromotionsList([
            'id' => intval($param['active_id']),
        ]);
        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }

        return $this->success($res['result']);
    }

    /**
     * 投票列表页
     */
    public function voteActivityList()
    {
        $param = $this->request->param();
        $param['active_id'] = intval($param['active_id']);
        if (empty($param['active_id'])) {
            $this->error('缺失参数');
        }

        $res = (new Promotions())->voteActivityList([
            'id'      => intval($param['active_id']),
            'user_id' => $this->getUserId(false),
        ]);
        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }
        $res['result']['nowtime'] = time();
        $res['result']['share_title'] = $res['result']['share_title'];
        $res['result']['share_desc'] = $res['result']['share_desc'];
        $res['result']['share_ico'] = $res['result']['cover_url'];

        return $this->success($res['result']);
    }
    //投票详情
    public function voteInfo()
    {

        $param = $this->request->param();
        $param['vote_detail_id'] = intval($param['vote_detail_id']);
        if (empty($param['vote_detail_id'])) {
            $this->error('缺失参数');
        }

        $res = (new Promotions())->voteInfo([
            'vote_detail_id' => $param['vote_detail_id'],
        ]);
        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }

        return $this->success($res['result']);
    }

    //投票
    public function voteAddLog()
    {
        $param = $this->request->param();
        $param['vote_detail_id'] = intval($param['vote_detail_id']);
        if (empty($param['vote_detail_id'])) {
            $this->error('缺失参数');
        }

        $res = (new Vote())->addLog([
            'user_id'        => $this->getUserId(),
            'vote_detail_id' => $param['vote_detail_id'],
        ]);

        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }

        return $this->success($res['result']);
    }



    //楼房详情
    public function getEstatesNewInfo()
    {
        $params = $this->request->param();
        $res = (new Promotions())->getEstatesNewInfo($params);

        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }

        return $this->success($res['result']);
    }


}