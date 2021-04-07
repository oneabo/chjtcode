<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\validate\AccountValidate;
use app\admin\validate\ArticleDate;
use app\common\base\AdminBaseController;
use app\common\MyConst;
use app\server\admin\Admin;
use app\server\admin\ArticleTag;
use app\server\admin\ArticleTagBing;
use app\server\admin\Chat;
use app\server\admin\Column;
use app\server\admin\InformationVideo;
use app\server\admin\News;
use app\server\estates\Estatesnew;
use app\server\index\ArtColumn;
use app\server\user\Attention;
use think\Validate;


class NewsController extends AdminBaseController
{
    public function getCategoryList()
    {
        $data = $this->request->param();
        $where = [
            'status'      => $data['status'],
            'has_comment' => $data['has_comment'],
            'name'        => $data['name'],
            'pid'         => $data['pid'],
        ];

        $rs = (new News())->getCategoryList($where)['result'];
//       if(!isset($where['status']) || !isset($where['has_comment']) || !isset($where['name']) ){
//           $rs['list'] = getTree($rs['list'],0);
//       }

//        var_dump($rs);
        $this->success($rs);
    }


    /**
     * 状态开光
     */
    public function categoryEnable()
    {
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

    /**
     * 哦评论开光
     */
    public function commentEnable()
    {
        $id = $this->request->post('id');
        $has_comment = $this->request->post('has_comment');

        $info = $this->db->name('Column')->where('id', '=', $id)->find();
        if ($has_comment == 0 && $info && $info['pid'] == 0) {
            $cate_arr = $this->db->name('Column')->where('pid', '=', $id)->select();
            $id = [$id];
            foreach ($cate_arr as $k => $v) {
                $id[] = $v['id'];
            }
        }
        if (!in_array($has_comment, [1, 0])) {
            return $this->error('类型错误');
        }
        if ((new News())->commentEnable($id, $has_comment)) {
            return $this->success('状态更改成功');
        }
        return $this->error('状态更改失败');
    }

    /**
     * 排序修改
     *
     */
    public function categoryChangeSort()
    {
        $id = $this->request->post('id');
        $sort = $this->request->post('sort');
        if (empty($id) || empty($sort) || !is_numeric($sort)) {
            return $this->error('参数错误');

        }

        if ((new News())->categoryChangetCateListgeSort($id, $sort)) {
            return $this->success('状态更改成功');
        }
        return $this->error('状态更改失败');

    }

    /**
     *
     * 获取分类
     */
    public function getCateListCont()
    {
        $data = MyConst::CLOUMNFLAG;
        $this->success($data);
    }

    /**
     *
     *
     */
    public function getCateList()
    {
        $pid = $this->request->param('pid');
        $flag = $this->request->param('flag');
        $res = (new Column())->getListByPid($pid, $flag);
        if ($res['code'] != 1) {
            $this->success([]);
        }
        $this->success($res['result']);
    }

    /**
     * 获取树形等级
     */
    public function getCategoryListAll()
    {
        $pid = $this->request->post('pid');
//        $res = (new Column())->getCateListAll($pid); //之前用的是栏目，现在改用标签
        $res = (new Column())->getLabelListAll();
        $res = getTree($res);
        if (empty($res)) {
            $this->success([]);
        }
        $this->success($res);
    }

    public function getCategoryListAll1()
    {
        $pid = $this->request->post('pid');
        $res = (new Column())->getCateListAll($pid); //之前用的是栏目，现在改用标签
//        $res = (new Column())->getLabelListAll();
        $res = getTree($res);
        if (empty($res)) {
            $this->success([]);
        }
        $this->success($res);
    }

    /**
     * 分类修改
     */
    public function categoryEdit()
    {
        $post = $this->request->post();
        if (empty($post['name']) || empty($post['sort']) || empty($post['pid'])) {
            return $this->error('参数不能为空');
        }
        $data = [
            'id'          => $post['id'],
            'title'       => $post['name'],
            'sort'        => $post['sort'],
            'pid'         => $post['pid'],
            'has_comment' => $post['has_comment'] ?? 0,
            'status'      => $post['status'],
            'cover'       => $post['icon_category'],
        ];
        if (!empty($post['id'])) {
            $data['update_time'] = time();
            $res = (new News())->categoryEdit($data);
        } else {
            $data['update_time'] = time();
            $res = (new News())->categoryCreate($data);
        }

        if ($res['code'] == 1) {
            $this->success();
        }
        $this->error($res['msg']);


    }

    /**
     *  删除分类
     */
    public function categoryDel()
    {
        $id = $this->request->post('id');
        if (!$id) {
            return $this->error('缺少参数');
        }
        $server = new News();
        $info = $server->getInfo($id);

        if ($info['pid'] == 0) {
            $son_lsit = $server->getSonList($info['id']);
            if ($son_lsit) {
                return $this->error('需先删除子类我');
            }
        }
        $rs = $server->categoryDel($id);

        if ($rs) {
            $this->success();
        }
        $this->error();
    }


    /**
     * 资讯发布首页
     */
    public function getList()
    {
        $data = $this->request->param();
        $where = [
            'status'     => $data['status'],
            'cate_id'    => end($data['cate_id']),
            'name'       => $data['name'],
            'city'       => $data['city'],
            'start_date' => empty($data['startdate']) ? '' : strtotime($data['startdate']),
            'end_date'   => empty($data['enddate']) ? '' : strtotime($data['enddate'] . ' 23:59:59'),
            'region_no'  => empty($data['region_no']) ? '' : $data['region_no'],
            'forid'      => empty($data['forid']) ? '' : $data['forid'],
        ];

        $rs = (new News())->getList($where)['result'];
//        if (!isset($where['status']) || !isset($where['has_comment']) || !isset($where['name'])) {
//            $rs['list'] = getTree($rs['list'], 0);
//        }

        $this->success($rs);
    }


    /**
     * 视频上首页
     */
    public function getListVoide()
    {
        $data = $this->request->param();
        $where = [
            'status'  => $data['status'],
            'cate_id' => end($data['cate_id']),
            'name'    => $data['name'],

            'start_date' => empty($data['startdate']) ? '' : strtotime($data['startdate']),
            'end_date'   => empty($data['enddate']) ? '' : strtotime($data['enddate'] . ' 23:59:59'),
            'region_no'  => empty($data['region_no']) ? '' : $data['region_no'],
        ];

        $rs = (new InformationVideo())->getListVoide($where)['result'];
        if (!isset($where['status']) || !isset($where['has_comment']) || !isset($where['name'])) {
            $rs['list'] = getTree($rs['list'], 0);
        }

//        var_dump($rs);
        $this->success($rs);
    }

    /**
     * 新增文章
     */
    public function edit()
    {
        $data = $this->request->post();
        $lable = $data['lable'];
        $data1 = [
            'name'            => $data['name'],
            'id'              => $data['id'],
            'sort'            => $data['sort'],
            'status'          => $data['status'],
            'context'         => $data['context'],
            'keyword'         => $data['keyword'],
            'has_comment'     => $data['has_comment'],
            'num_read'        => $data['num_read'],
            'num_collect'     => $data['num_collect'],
            'num_share'       => $data['num_share'],
            'num_thumbup'     => $data['num_thumbup'],
            'release_time'    => strtotime($data['release_time']),
            'title'           => $data['title'],
            'is_top'          => in_array(1, $data['recommend_place']) ? 1 : 0,
            'top_time'        => strtotime($data['top_time']),
            'is_index'        => in_array(2, $data['recommend_place']) ? 1 : 0,
            'resource_type'   => $data['resource_type'],
            'forid'           => $data['forid'],
            'region_no'       => $data['region_no'],
            'is_propert_news' => $data['forid'] ? 1 : 0,
            'source_type'     => $data['source_type'] ?? 1,
//            'video_url' => $data['video_url'],
            'order_type'      => $data['order_type'],
            'city_list'       => $data['city_list'],
            'img_url'         => implode($data['img_ids'], ',') ?? '',
            'img_path'        => json_encode($this->getImgPath($data['img_ids'])),
            'is_original'     => $data['is_original'] ?? 0,
            'source_link'     => $data['source_link'] ?? '',
            'description'     => $data['description'] ?? '',
        ];
        if (empty($data1['description'])) {
            return $this->error('请输入描述内容用于分享');
        }

        if (mb_strlen($data1['description']) >= 255) {
            return $this->error('描述最多 255 个字');
        }
        $lable = explode(',', $lable);
        if (empty($lable)) {
            return $this->error('标签不能为空');
        }

//        var_dump($data1);
        if ($data1['source_type'] == 1) {
            $data1['source_id'] = $this->getUserId();
        }
//        var_dump($data1);

        $bing_model = new ArticleTagBing();
        $colService = new Column();
        $arr = $colService->getIsTrueTag();
        foreach ($lable as $k => $v) {
            if (!in_array($v, $arr)) {
                unset($lable[$k]);
            }
        }

        $data1['lable'] = implode($lable, ',');
        $data1['lable_string'] = json_encode((new ArticleTag())->getTagName(explode(',', $data1['lable'])) ?? []);
        $cate = $bing_model->getCateBytag($lable);//通过tag 获取tagid
        $data1['cate_id'] = json_encode($cate);
        $validate = new  ArticleDate();

        if ($data['id']) {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {

                $this->db->startTrans();
                $model = new News();
                $data1['update_time'] = time();
                //解除广联文章跟栏目id
                $result1 = $this->db->name('article_cloumn')->where('article_id','=',$data['id'])->delete();
                if($result1  ===false){
                    $this->db->rollback();
                    $this->error();
                }
                //接触广联文章和标签id
                $result2 = (new ArticleTagBing())->delArticleTagBing($data['id']);

                if ($result2 === false) {
                    $this->db->rollback();
                    $this->error();
                }


                if ($model->edit($data1)) {

                    //添加文章和栏目的关联
                    $col_arr  =  [];
                    foreach ($cate as $k=> $v){
                        $col_arr[] = [
                            'column_id'   => $v,
                            'article_id'  => $data['id'],
                            'create_time' =>time(),
                            'update_time' => time(),
                        ];
                    }
                    $result  = $this->db->name('article_cloumn')->insertAll($col_arr);

                    if($result === false){
                        $this->db->rollback();
                    }

                    //添加文章和标签的关联
                    $tag_arr = [];
                    foreach ($lable as $k => $v) {
                        $tag_arr[] = [
                            'tag_id'      => $v,
                            'article_id'  => $data['id'],
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    if (!empty($lable)) {
                        $result2 = (new ArticleTagBing())->addAllArticleTagBing($tag_arr);

                        if ($result2 === false) {
                            $this->db->rollback();
                        }
                    }

//                    $model->addOldNews($data,$data['id'],'edit');
                    $this->db->commit();

                    return $this->success();
                } else {
                    $this->db->rollback();
                    return $this->error();
                }
            }
        } else {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {
                $this->db->startTrans();
                $model = new News();
                $data1['update_time'] = time();
                $data1['create_time'] = time();
                if ($article_id = $model->add($data1)) {
                    //如果时楼盘消息触发推送消息
                    $server = new Estatesnew();
                    $chatServer = new Chat();
                    $info = $server->getInfo([['id','=',$data['forid']]])['result'];
                    if($data['is_propert_news'] && !empty($info)){
                            //todo
                            $msg_data = [
                                'title'           => '您关注的楼盘信息有变更，赶快去查看吧',
                                'contxt'          => '',
                                'status'          => '1',
                                'cover'          =>  $info['list_cover'] ?? 'upload/images/admin/admin/lp_list.png',
                                'chat_type'       => 4,
                                'estate_id'       => $info['forid'],
                                'sub_context'     => "您关注的楼盘{$info['name']}信息有变更，赶快去查看吧",
                                'name'              => $info['name'] ?? '',
                                'update_time'     => time(),
                                'create_time'     => time(),
                            ];

                            //获取楼盘关注列表
                            $follow_list  = (new Attention())->getFollowUserListByEstatesId($data['forid']);
                            if(!empty($follow_list)){
                                $chatServer->addSyetemMsg($msg_data,$follow_list);
                            }

                    }



//                    //添加 跟栏目关联
                    $col_arr  =  [];
                    foreach ($cate as $k=> $v){
                        $col_arr[] = [
                            'column_id'   => $v,
                            'article_id'  => $article_id,
                            'create_time' =>time(),
                            'update_time' => time(),
                        ];
                    }
//
//
                    $result  = $this->db->name('article_cloumn')->insertAll($col_arr);

                    //添加标签跟文章关联
                    $tag_arr = [];
                    foreach ($lable as $k => $v) {
                        $tag_arr[] = [
                            'tag_id'      => $v,
                            'article_id'  => $data['id'],
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }

                    $result2 = (new ArticleTagBing())->addAllArticleTagBing($tag_arr);
                    if ($result2 === false) {
                        $this->db->rollback();
                    }


//                    if($result === false){
//                        $this->db->rollback();
//                    }
                    //同步倒旧项目
//                    $model->addOldNews($data,$article_id,'add');
                    $this->db->commit();
                    return $this->success();
                } else {
                    $this->db->rollback();
                    return $this->error();
                }
            }
        }

    }

    public function edit1()
    {
        $data = $this->request->post();
        $lable = $data['lable'];
        $data1 = [
            'name'            => $data['name'],
            'id'              => $data['id'],
            'sort'            => $data['sort'],
            'status'          => $data['status'],
            'context'         => $data['context'],
            'keyword'         => $data['keyword'],
            'has_comment'     => $data['has_comment'],
            'num_read'        => $data['num_read'],
            'num_collect'     => $data['num_collect'],
            'num_share'       => $data['num_share'],
            'num_thumbup'     => $data['num_thumbup'],
            'release_time'    => strtotime($data['release_time']),
            'title'           => $data['title'],
            'is_top'          => in_array(1, $data['recommend_place']) ? 1 : 0,
            'top_time'        => strtotime($data['top_time']),
            'is_index'        => in_array(2, $data['recommend_place']) ? 1 : 0,
            'resource_type'   => $data['resource_type'],
            'forid'           => $data['forid'],
            'region_no'       => $data['region_no'],
            'is_propert_news' => $data['forid'] ? 1 : 0,
            'source_type'     => $data['source_type'] ?? 1,
//            'video_url' => $data['video_url'],
            'order_type'      => $data['order_type'],
            'city_list'       => $data['city_list'],
            'img_url'         => implode($data['img_ids'], ',') ?? '',
            'img_path'        => json_encode($this->getImgPath($data['img_ids'])),
            'is_original'     => $data['is_original'] ?? 0,
            'source_link'     => $data['source_link'] ?? '',
            'description'     => $data['description'] ?? '',
        ];
        if (empty($data1['description'])) {
            return $this->error('请输入描述内容用于分享');
        }

        if (mb_strlen($data1['description']) >= 255) {
            return $this->error('描述最多 255 个字');
        }
        $lable = explode(',', $lable);
        if (empty($lable)) {
            return $this->error('标签不能为空');
        }

//        var_dump($data1);
        if ($data1['source_type'] == 1) {
            $data1['source_id'] = $this->getUserId();
        }
//        var_dump($data1);

        $bing_model = new ArticleTagBing();
        $colService = new Column();
        $arr = $colService->getIsTrueTag();
        foreach ($lable as $k => $v) {
            if (!in_array($v, $arr)) {
                unset($lable[$k]);
            }
        }

        $data1['lable'] = implode($lable, ',');
        $data1['lable_string'] = json_encode((new ArticleTag())->getTagName(explode(',', $data1['lable'])) ?? []);
        $cate = $bing_model->getCateBytag($lable);//通过tag 获取tagid
        $data1['cate_id'] = json_encode($cate);
        $validate = new  ArticleDate();

        if ($data['id']) {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {

                $this->db->startTrans();
                $model = new News();
                $data1['update_time'] = time();
                //解除广联文章跟栏目id
//                $result1 = $this->db->name('article_cloumn')->where('article_id','=',$data['id'])->delete();
//                if($result1  ===false){
//                    $this->db->rollback();
//                    $this->error();
//                }
                //接触广联文章和标签id
                $result2 = (new ArticleTagBing())->delArticleTagBing($data['id']);

                if ($result2 === false) {
                    $this->db->rollback();
                    $this->error();
                }


                if ($model->edit($data1)) {

                    //添加文章和栏目的关联
//                    $col_arr  =  [];
//                    foreach ($cate as $k=> $v){
//                        $col_arr[] = [
//                            'column_id'   => $v,
//                            'article_id'  => $data['id'],
//                            'create_time' =>time(),
//                            'update_time' => time(),
//                        ];
//                    }
//                    $result  = $this->db->name('article_cloumn')->insertAll($col_arr);
//
//                    if($result === false){
//                        $this->db->rollback();
//                    }

                    //添加文章和标签的关联
                    $tag_arr = [];
                    foreach ($lable as $k => $v) {
                        $tag_arr[] = [
                            'tag_id'      => $v,
                            'article_id'  => $data['id'],
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    if (!empty($lable)) {
                        $result2 = (new ArticleTagBing())->addAllArticleTagBing($tag_arr);

                        if ($result2 === false) {
                            $this->db->rollback();
                        }
                    }


                    $this->db->commit();

                    return $this->success();
                } else {
                    $this->db->rollback();
                    return $this->error();
                }
            }
        } else {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {
                $this->db->startTrans();
                $model = new News();
                $data1['update_time'] = time();
                $data1['create_time'] = time();
                if ($article_id = $model->add($data1)) {

//                    //添加 跟栏目关联
//                    $col_arr  =  [];
//                    foreach ($cate as $k=> $v){
//                        $col_arr[] = [
//                            'column_id'   => $v,
//                            'article_id'  => $article_id,
//                            'create_time' =>time(),
//                            'update_time' => time(),
//                        ];
//                    }
//
//
//                    $result  = $this->db->name('article_cloumn')->insertAll($col_arr);

                    //添加标签跟文章关联
                    $tag_arr = [];
                    foreach ($lable as $k => $v) {
                        $tag_arr[] = [
                            'tag_id'      => $v,
                            'article_id'  => $data['id'],
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }

                    $result2 = (new ArticleTagBing())->addAllArticleTagBing($tag_arr);
                    if ($result2 === false) {
                        $this->db->rollback();
                    }


//                    if($result === false){
//                        $this->db->rollback();
//                    }

                    $this->db->commit();

                    return $this->success();
                } else {
                    $this->db->rollback();
                    return $this->error();
                }
            }
        }

    }

    /**
     * 获取推荐order
     * @return int
     */
    public function getArticleOrder()
    {
        $cate_id = $this->request->post('cate_id');
        return (new News())->getArticleOrder($cate_id);
    }

    /**
     *
     * 修改排序
     */
    public function changeSort()
    {
        $order = $this->request->post('sort');
        $id = $this->request->post('id');
//        $p_cate_id = $this->request->post('p_cate_id');
        if (!$order || !$id) {
            return $this->error();
        }

        if ((new News())->setColumnSort($id, $order, 'sort')) {
            return $this->success();
        }

        return $this->error();
    }


    /**
     *
     * 修改排序wer
     */
    public function changeSortVideo()
    {
        $order = $this->request->post('sort');
        $id = $this->request->post('id');
        $p_cate_id = $this->request->post('p_cate_id');
        if (!$order || !$id || !$p_cate_id) {
            return $this->error();
        }

        if ((new InformationVideo())->setColumnSort($id, $order, $p_cate_id, 'sort')) {
            return $this->success();
        }

        return $this->error();
    }


    /**
     *
     * 修改排序
     */
    public function enable()
    {
        $status = $this->request->post('status');
        $id = $this->request->post('id');
        $info = (new News())->getNewsInfo($id);
        if ($info['status'] == 2) {
            return $this->error('草稿状态下不可操作，请先保存文章');
        }
        if (!in_array($status, [1, 0]) || !$id) {

            return $this->error();
        }

        if ((new News())->setColumnSort($id, $status, 'status')) {

            return $this->success();
        }
        return $this->error();
    }

    /**
     *
     *
     */
    public function enableVideo()
    {
        $status = $this->request->post('status');
        $id = $this->request->post('id');

        $info = (new InformationVideo())->getNewsInfo($id);

        if ($info['status'] == 2) {
            return $this->error('草稿状态下不可操作，请先保存文章');
        }
        if (!in_array($status, [1, 0]) || !$id) {

            return $this->error();
        }

        if ((new InformationVideo())->setColumnSort($id, $status, 'status')) {

            return $this->success();
        }
        return $this->error();
    }

    /**
     * 获取文章列表
     */
    public function getInfo()
    {
        $id = $this->request->param('id');
        if (!$id) {
            return $this->error('参数错误');
        }

        $info = (new News())->getNewsInfo($id);
        $info['release_time'] = !empty($info['release_time']) ? date('Y-m-d H:i:s', $info['release_time']) : date('Y-m-d H:i:s');
        $info['top_time'] = $info['top_time'] ? date('Y-m-d H:i:s', $info['top_time']) : '';
        $info['img_ids'] = explode(',', $info['img_url']);
        $info['img_url'] = $this->getImgPath(explode(',', $info['img_url']));
        $info['cate_id'] = !empty($info['cate_id']) ? json_decode($info['cate_id'], true) : [];
        $info['is_propert_news'] = $info['is_propert_news'] == 1 ? true : false;
        $info['forname'] = $this->db->name('estates_new')->where(['id' => $info['forid']])->value('name');
        if (empty($info[0]['img_url'])) {
            $info['img_url'] = [];
        }
        if ($info['is_top']) {
            $info['recommend_place'][] = '1';
        }
        if ($info['is_index']) {
            $info['recommend_place'][] = '2';
        }
        $info['context'] = htmlspecialchars_decode($info['context']);

        return $this->success($info);
    }

    /**
     *  视频详情liveRoom
     */
    public function getInfoVideo()
    {
        $id = $this->request->param('id');
        if (!$id) {
            return $this->error('参数错误');
        }

        $info = (new InformationVideo())->getNewsInfo($id);
//        var_dump($info);
        $info['release_time'] = $info['release_time'] ? date('Y-m-d H:i:s') : '';
        $info['top_time'] = $info['top_time'] ? date('Y-m-d H:i:s') : '';
        $info['is_propert_news'] = $info['is_propert_news'] == 1 ? true : false;
        $info['forname'] = $this->db->name('estates_new')->where(['id' => $info['forid']])->value('name');
        $info['cate_id'] = !empty($info['cate_id']) ? json_decode($info['cate_id'], true) : [];
        $info['video_path'] = $this->getVoidePath($info['video_url']);
        if ($info['is_top']) {
            $info['recommend_place'][] = '1';
        }
        if ($info['is_index']) {
            $info['recommend_place'][] = '2';
        }
        return $this->success($info);

    }

    /**
     * 删除文章列表
     */
    public function del()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误213231');
        }
        if ((new News())->delNews($id)) {
            (new ArtColumn())->delColumnByArtId($id);
            return $this->success();
        }

        return $this->error();


    }


    /**
     * 删除文章列表
     */
    public function delVideo()
    {
        $id = $this->request->param('id');
        $p_cate_id = $this->request->param('p_cate_id');
        if (!$p_cate_id || !$id) {
            return $this->error('参数错误');
        }

        if ((new InformationVideo())->delVideo($id, $p_cate_id)) {

            return $this->success();
        }

        return $this->error();


    }

    public function delTag()
    {
        $id = $this->request->param('id');
        if (!$id) {
            return $this->error('参数错误');
        }
        if ((new ArticleTag())->del($id)) {

            return $this->success();
        }
    }

    /**
     * 新增标签
     * @throws \think\db\exception\DbException
     */
    public function editTag()
    {
        $post = $this->request->post();
        if (empty($post['name']) && (empty($post['pid'] || $post['pid'] !== 0))) {
            return $this->error('名称或分类不能为空');
        }

        $cover_imgs = $post['cover_url'] && $post['cover_url'][0] && $post['cover_url'][0]['url'] ? $post['cover_url'][0]['url'] : '';
        if (empty($cover_imgs)) {
            $this->error('请上传图片');
        }
        $data = [
            'id'          => $post['id'],
            'name'        => $post['name'],
            'pid'         => $post['pid'],
            'status'      => $post['status'],
            'cover'      => $cover_imgs,
            'update_time' => time()
        ];
        $server = new ArticleTag();
        if ($server->isColumn($post['name'], 'name')) {
            return $this->success('名称不能重复');
        }

        if ($data['id']) {
            $res = $server->edit($data);
        } else {
            $res = $server->add($data);
        }

        if ($res) {
            return $this->success();
        }

        return $this->error();

    }

    /**
     * 获得标签列表
     */
    public function getTagList()
    {
        $data = $this->request->param();
        $where = [
            'status' => $data['status'],
            'pid'    => $data['pid'],
            'name'   => $data['name'],
        ];

        $rs = (new ArticleTag())->getTagList($where)['result'];

        foreach ($rs as &$v) {
            //list($v['cover_id'], $v['cover_url']) = $this->getImgsIdAndUrl($v['cover']);
            $v['cover_url'] = !empty($v['cover']) ? $this->getFormatImgs($v['cover']) : [];
        }
//        var_dump($where);
        if ($where['status'] == '-1' && empty($where['name']) && $where['pid'] == '-1') {
            $rs = getTree($rs, 0);
        }

//        var_dump($rs);
//        var_dump($rs);
        $this->success($rs);
    }

    /**
     * 标签 开启关闭
     */
    public function enableTag()
    {
        $id = $this->request->post('id');
        $status = $this->request->post('status');
        if (!$id || !in_array($status, [0, 1])) {
            return $this->error('参数错误');
        }
        if ((new ArticleTag())->setTagColumn($id, 'status', $status)) {
            return $this->success();
        }
        return $this->error();

    }

    /**
     * 获取标签列表
     */
    public function getExtraList()
    {
        $p_cate_id = $this->request->post('p_cate_id');
        if (is_array($p_cate_id)) {
            $p_cate_id = $p_cate_id[0];
        }
        $where = ['p_cate_id' => $p_cate_id];
        $rs = (new ArticleTag())->getTagList($where)['result'];

        return $this->success($rs);

    }

    /**
     * 获取有跟栏目绑定的标签
     */
    public function getExtraIsTrueList()
    {
        $p_cate_id = $this->request->post('p_cate_id');
        if (is_array($p_cate_id)) {
            $p_cate_id = $p_cate_id[0];
        }
        $colService = new  Column();
        $arr = $colService->getIsTrueTag();
        if (empty($arr)) {
            return $this->error('没有可用标签,请先将标签和栏目关联');
        }

        $where = ['id' => $arr];
        $rs = (new ArticleTag())->getTagList($where)['result'];
        $rs = getTree($rs, 0);

        return $this->success($rs ?? []);

    }


    /**
     * 视频新增
     */
    public function videoEdit()
    {
        $data = $this->request->post();
        $cate = $data['cate_id'];
        $data1 = [
            'name'            => $data['name'],
            'id'              => $data['id'],
            'sort'            => $data['sort'],
            'status'          => $data['status'],
            'context'         => $data['context'],
            'keyword'         => $data['keyword'],
            'lable'           => $data['lable'],
            'lable_string'    => json_encode((new ArticleTag())->getTagName(explode(',', $data['lable'])) ?? []),
            'has_comment'     => $data['has_comment'],
            'num_read'        => $data['num_read'],
            'num_collect'     => $data['num_collect'],
            'num_share'       => $data['num_share'],
            'num_thumbup'     => $data['num_thumbup'],
            'release_time'    => strtotime($data['release_time']),
            'title'           => $data['title'],
            'is_top'          => in_array(1, $data['recommend_place']) ? 1 : 0,
            'top_time'        => strtotime($data['top_time']),
            'is_index'        => in_array(2, $data['recommend_place']) ? 1 : 0,
            'resource_type'   => $data['resource_type'],
            'source_type'     => $data['source_type'] ?? 2,
            'video_url'       => $data['video_url'],
            'video_path'      => $this->getVoidePath($data['video_url']),
            'order_type'      => $data['order_type'],
            'cate_id'         => json_encode($cate),
            'city_list'       => $data['city_list'],
            'forid'           => $data['forid'],
            'region_no'       => $data['region_no'],
            'is_propert_news' => $data['forid'] ? 1 : 0,
            'cover_url'       => $data['cover_url'],
            'cover'           => $data['cover'],
            'description'     => $data['description'] ?? '',
        ];
//var_dump($data1); 333
        $data1['source_id'] = $this->getUserId();
        $validate = new  ArticleDate();
        if ($data['id']) {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {
                $this->db->startTrans();
                $model = new InformationVideo();
                $data1['update_time'] = time();
//                $result1 = $this->db->name('video_cloumn')->where('article_id', '=', $data['id'])->delete();
                $result1 = $this->db->name('video_tag')->where('article_id', '=', $data['id'])->delete();

                if ($result1 === false) {
                    $this->db->rollback();
                    $this->error();
                }
                if ($data['id'] && $model->edit($data1)) {
                    $col_arr = [];
                    foreach ($cate as $k => $v) {
                        $col_arr[] = [
                            'tag_id'   => end($v),
                            'article_id'  => $data['id'],
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    $result = $this->db->name('video_tag')->insertAll($col_arr);

                    if ($result === false) {
                        $this->db->rollback();
                    }

                    $this->db->commit();

                    return $this->success();
                } else {
                    $this->error();
                    return $this->error();
                }


            }
        } else {
            if ($data['status'] != 2 && !$validate->scene('add')->check($data1)) {
                return $this->error($validate->getError());
            } else {
                $model = new InformationVideo();
                $data1['update_time'] = time();
                $data1['create_time'] = time();
                if ($article_id = $model->add($data1)) {
                    $col_arr = [];
                    foreach ($cate as $k => $v) {
                        $col_arr[] = [
                            'tag_id'   => end($v),
                            'article_id'  => $article_id,
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    $result = $this->db->name('video_tag')->insertAll($col_arr);

                    if ($result === false) {
                        $this->db->rollback();
                    }

                    $this->db->commit();

                    return $this->success();
                } else {
                    return $this->error();
                }
            }

        }

    }

    /**
     * 获取排行榜
     */
    public function getRank()
    {
        $region_id = $this->request->post('region_id');

        $redis = $this->getReids();
        $reids_key = MyConst::NEWS_HOS_LIST;

        $list = $redis->hGet($reids_key, $region_id);

        if (!empty($list)) {
            $list = json_decode($list, true) ?? [];
        } else {
          return  $this->success([]);
        }
        foreach ($list as $k => $v) {
            $list[$k]['rank'] = $k + 1;
        }

        $this->success($list);
    }

    /**
     * 设置排行榜
     */
    public function editRank1()
    {
        $region_id = $this->request->post('region_id');
        $deal = $this->request->post('deal');
        $id = $this->request->post('id');
        $rank = $this->request->post('rank');


        if (empty($rank) ||  $rank <= 0 || $rank > 8) {
            return $this->error('只能为1-7的排行');
        }
        if (empty($region_id) || empty($deal) || empty($id)) {
            return $this->error('参数错误');
        }

        $redis = $this->getReids();
        $reids_key = MyConst::NEWS_HOS_LIST;

        $list = $redis->hGet($reids_key, $region_id);

        $list = json_decode($list, true) ?? []  ;
        $is_array = false;

        $info = (new News())->getNewsInfo($id);

        $info = [
            'id'       => $info['id'],
            'num_read' => $info['num_read'] + $info['num_read_real'],
            'name'     => $info['name']
        ];

        foreach ($list as $k => $v) {
            if ($id == $v['id']) {
                $is_array = true;
                break;
            }
        }

        if ($deal == 'add' || $deal == 'edit') {

            //在榜单里面，先移除 ，在按对应位置加入
            if ($is_array) {
                foreach ($list as $k => $v) {
                    if ($id == $v['id']) {
                        array_splice($list, $k, 1);
                        break;
                    }

                }

                array_splice($list, $rank - 1, 0, json_encode($info));// 将新排名加入榜单内
            } else {
//                array_splice($list, $rank - 1, 6, json_encode($info));// 加入排行 去除最后一个
                array_splice($list, $rank - 1, 0, json_encode($info));
            }
        } else {
            //在榜单里面，先移除 ，在按对应位置加入
            if ($is_array) {
                foreach ($list as $k => $v) {
                    if ($id == $v['id']) {
                        array_splice($list, $k, 1);
                        break;
                    }

                }
            }
        }
        foreach ($list as $ks => $vs) {
            if (is_string($vs)) {
                $list[$ks] = json_decode($vs);
            }
        }

        $redis->hSet($reids_key, $region_id, json_encode($list));
        $this->success();
    }

    public function editRank()
    {
        $region_id = $this->request->post('region_id');
        $deal = $this->request->post('deal');
        $id = $this->request->post('id');
        $rank = $this->request->post('rank');


        if (empty($rank) ||  $rank <= 0 || $rank > 8) {
            return $this->error('只能为1-7的排行');
        }
        if (empty($region_id) || empty($deal) || empty($id)) {
            return $this->error('参数错误');
        }

        $redis = $this->getReids();
        $reids_key = MyConst::NEWS_HOS_LIST;

        $list = $redis->hGet($reids_key, $region_id);

        $list = json_decode($list, true) ?? []  ;
        $is_array = false;

        $info = (new News())->getNewsInfo($id);

        $info = [
            'id'       => $info['id'],
            'num_read' => $info['num_read'] + $info['num_read_real'],
            'name'     => $info['name']
        ];

        foreach ($list as $k => $v) {
            if ($id == $v['id']) {
                $is_array = true;
                break;
            }
        }

        if ($deal == 'add' || $deal == 'edit') {

            //判断是否取了同一篇文章
            if ($is_array) {
                foreach ($list as $k => $v) {
                    if ($id == $v['id']) {
                        array_splice($list, $k, 1);
                        break;
                    }
                }
            }

            if($deal == 'edit'){ 
                array_splice($list, $rank - 1, 1);
            }
            array_splice($list, $rank - 1, 0, json_encode($info));// 将新排名加入榜单内
        } else {
            //在榜单里面，先移除 ，在按对应位置加入
            if ($is_array) {
                foreach ($list as $k => $v) {
                    if ($id == $v['id']) {
                        array_splice($list, $k, 1);
                        break;
                    }

                }
            }
        }
        foreach ($list as $ks => $vs) {
            if (is_string($vs)) {
                $list[$ks] = json_decode($vs);
            }
        }

        $redis->hSet($reids_key, $region_id, json_encode($list));
        $this->success();
    }

    //批量操作
    public function batchEdit()
    {
        $params = $this->request->param();

        $res = (new News())->batchEdit($params);
        if ($res['cod'] != 1) {
            $this->error($res['msg']);
        }

        $this->success();
    }

}

