<?php


namespace app\admin\controller;
use app\common\base\AdminBaseController;
use app\server\marketing\LiveRoom;


class LiveRoomController extends AdminBaseController
{

    public function getList(){
        $data = $this->request->param();
        $where = [
            'status' => $data['status'],
            'start_time'=> !empty($data['startdate']) ? strtotime($data['startdate']) : '',
            'end_time'=> !empty($data['enddate']) ? strtotime($data['enddate'].' +1 day') : ''
        ];

        // 城市
        if(!empty($data['region_no'])) {
            if(-1 == $data['region_no']) {// 前搜索当全部城市
                $regionRes = $this->getMyCity();

                $cityIds = !empty($regionRes['data']) ? array_column($regionRes['data'], 'id') : [];

                $where['region_no'] = $cityIds;
            } else {
                $where['region_no'] = $data['region_no'];
            }
        }


        $rs = (new LiveRoom())->getList($where)['result'];
        if(empty($rs['list'])){
            $rs = [];
        }else{
            foreach ($rs['list'] as &$item){
                $item['cover_url'] = !empty($item['cover'])?$this->getFormatImgs($item['cover']):[];
            }
            unset($item);
        }
        $this->success($rs);
    }

    //删除
    public function del()
    {
        $data = $this->request->param();
        $rs = (new LiveRoom())->del(intval($data['id']));
        $this->success($rs);
    }

    public function edit(){
        $data = $this->request->param();
        $data['id'] = intval($data['id']);

        $data['room_name'] = trim_all($data['room_name']);
        if(empty($data['room_name'])){
            $this->error('请填写名称');
        }

        $data["start_time"] = strtotime($data["start_time"]);
        $data["end_time"] = strtotime($data["end_time"]);
        if(empty($data["start_time"])||empty($data["end_time"])){
            $this->error('请设置该广告的有效时间范围');
        }
        if($data["start_time"]>=$data["end_time"]){
            $this->error('开始时间超过结束时间');
        }

        $indata = [
            'room_name' => $data['room_name'],
            'cover'=> !empty($data['cover_url'][0]['url']) ? $data['cover_url'][0]['url'] : "",
            'share_img' => $data["share_img"],
            'start_time'=> $data["start_time"],
            'end_time'=> $data["end_time"],
            'status' => intval($data["status"]),
            "room_url"=> $data["room_url"],
            'sort' => intval($data["sort"]),
            'desc' => $data["desc"],
            'forid' => intval($data["forid"]),
            'region_no' => $data['region_no'] ?? '',
        ];

        if($data['id']){
            $rs = (new LiveRoom())->edit($data['id'],$indata);
        }else{
            $rs = (new LiveRoom())->add($indata);
        }
        if($rs['code']==1){
            $this->success();
        }else{
            $this->error();
        }
    }


    public function changeSort(){
        $data = $this->request->param();
        $rs = (new LiveRoom())->edit(intval($data['id']),['sort'=>$data['sort']]);
        if($rs['code'] == 1){
            $this->success();
        }else{
            $this->error('',$rs['msg']);
        }
    }
}