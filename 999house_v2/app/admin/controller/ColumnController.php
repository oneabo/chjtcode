<?php


namespace app\admin\controller;
use app\common\base\AdminBaseController;
use app\common\MyConst;
use app\server\admin\ArticleTag;
use app\server\admin\Column;
use app\server\admin\News;

class ColumnController extends AdminBaseController
{

    public function getList(){
        $data = $this->request->param();
        $where = [
            'status' => $data['status'],
            'type'   => $data['type'],
            'pid'    => $data['pid']
        ];
        $rs = (new Column())->getList($where)['result'];
        if(empty($rs['list'])){
            $rs = [];
        }
       if($where['status'] != -1 ||  $where['type'] != -1 || empty($where['pid']) )
        $rs['list'] = getTree($rs['list'],0);
        $this->success($rs);

    }
    //修改状态
    public function enable(){
        $id = $this->request->post('id');
        $status = $this->request->post('status');
        if (!in_array($status, [1, 0])) {
            return $this->error('类型错误');
        }

        $info = $this->db->name('column')->where('id', '=', $id)->find();
        if ($status == 0 && $info && $info['pid'] == 0) {
            $cate_arr = $this->db->name('column')->where('pid', '=', $id)->select();
            $id = [$id];
            foreach ($cate_arr as $k => $v) {
                $id[] = $v['id'];
            }
        }


        if ((new News())->categoryEnable($id, $status)) {
            return $this->success('状态更改成功');
        }
        return $this->error('状态更改失败');
    }
    //删除
    public function del()
    {
        $data = $this->request->param();
        $rs = (new Column())->del(intval($data['id']));
        $this->success($rs);
    }

    public function edit(){
        $data = $this->request->param();
        $data['id'] = intval($data['id']);
        $data['title'] = trim_all($data['title']);

        if(empty($data['title'])){
            $this->error('请填写栏目名称');
        }
        $pid    =$data['pid'];
        $indata = [
            'cover' => $data['cover'],
            'status' => intval($data["status"]),
            'type' => $data["type"],
            'sort' => intval($data["sort"]),
            'place' => $data['place'],
            'href' => htmlspecialchars_decode($data["href"]),
            'title' => $data['title'],
            'page_title' => $data["page_title"],
            'page_keywords' => $data["page_keywords"],
            'page_desc' => $data["page_desc"],
            'pid' => $pid,
            'tags'=> $data['tags']
        ];
        //资讯判断是否选择标签,其他分类直接我给空
        if(($indata['pid'] !=0  && $indata['place'] =='h5_fx_home') || ($data['id'] ==13  && $indata['place'] =='h5_fx_home')){
            if( empty($indata['tags']) ){
                return $this->error('资讯儿类须选择至少一个标签');
            }
        }else{
            $indata['tags'] ='';
        }
        if($data['id']){
            if( $data['id'] == $indata['pid'] ){
               return $this->error('上级不能是自己');
            }
            $rs = (new Column())->edit($data['id'],$indata);
        }else{
            $rs = (new Column())->add($indata);
        }

        if($rs['code']==1){
            //增加有跟栏目关联的id
            $this->success();
        }else{
            $this->error();
        }
    }
    /**
     *
     * 获取顶级分类列表
     */
    public function getCateList(){

        $pid  = $this->request->param('pid');
        $res  =  (new Column())->getListByPid($pid);
        if( $res['code'] !=1 ){
            $this->success([]);
        }
        $this->success($res['result']);
    }

    /**
     * 获取标识列表
     */
    public function getFlagList(){
        $this->success(MyConst::CLOUMNFLAG);
    }

    /**
     * 获取标识对应 数组
     */
    public function getCateListByFlag(){
       $info  = MyConst::CLOUMNFLAG;
       $arr   = array();
       foreach ($info as $k => $v) {
           $arr[$v['id']]  = (new Column())->getListByPid($v['col_id'])['result'];
       }
       $this->success($arr);
    }

    /**
     * 获取树形等级
     */
    public function getCategoryListAll()
    {
        $pid = $this->request->post('pid');
        $res = (new Column())->getCateListAll($pid);
        $res = getTree($res);
        $obj  = [
          'id'      => '0',
          'title'   => '顶级模块'
        ];
        array_unshift($res,$obj);
        if (empty($res)) {
            $this->success([]);
        }
        $this->success($res);
    }


}