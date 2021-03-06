<?php


namespace app\index\controller;


use app\common\base\UserBaseController;
use app\server\admin\PublicArticles;

class PublicArticlesController extends UserBaseController
{
    public function list(){
        $data = $this->request->param();
        $res = (new PublicArticles())->getList($data);

        if($res['code'] == 0){
            return $this->error($res['msg']);
        }

        return $this->success($res['result']);
    }
}