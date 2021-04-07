<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of main
 *
 * @author Goods0
 */
include 'Common.php';
include Lib . DS . 'JWT.php';

class UserAjax extends Common{
    //获取首页数据信息
    public function getHomeData(){
        $data=[];
        //获取统计信息
        $agent_id=Session::get('agent_id');
        $buildingNum=$this->db->Name('xcx_agent_building')->select('count(*)')->where_equalTo('agent_id',$agent_id)->firstColumn();
        $data['buildingNum']=empty($buildingNum)?0:$buildingNum;
        $customerNum=$this->db->Name('xcx_agent_customer')->select('count(*)')->where_equalTo('agent_id',$agent_id)->firstColumn();
        $data['customerNum']=empty($customerNum)?0:$customerNum;
        //获取最新的浏览记录
        $historyRow=$this->db->Name('xcx_user_browsing_history')->select("ubh.start_time,u.nickName,u.avatarUrl","ubh")->leftJoin("xcx_user","u","ubh.user_id=u.id")->where_equalTo('ubh.browse_type','1')->where_equalTo('ubh.agent_id',$agent_id)->orderBy('ubh.start_time','desc')->firstRow();
        if(empty($historyRow)){
            $historyRow=[];
        }else{
            $historyRow['start_time']=$this->format_dates($historyRow['start_time']);
        }
        $data['historyRow']=$historyRow;
        //获取新闻资讯
        /*$max_article=$this->db->Name('xcx_setting')->select()->where_equalTo('`key`','max_article')->firstRow()['value'];
        $articleInfo=$this->db->Name('xcx_article_article')->select()->where_equalTo('is_hot',1)->where_equalTo('status',1)->orderBy('create_time','desc')->page(1,$max_article)->execute();
        if(empty($articleInfo)){$articleInfo=[];}*/
        //九房网新闻资讯获取
        $articleInfo=$this->db2->Name('news')->select('id,title,wap_pic,addtime')->where_equalTo('flag','t,i')->where_equalTo('belong',15)->orderBy('addtime','desc')->orderBy('orders')->limit(0,10)->execute();
        foreach ($articleInfo as $key=>$value){
            $article[$key]['id']=$value['id'];
            $article[$key]['cover']='http://www.999house.com/'.$value['wap_pic'];
            $article[$key]['title']=$value['title'];
        }
        if(empty($article)){$article=[];}

        $data['articleInfo']=$article;
        $data['agent_id']=$agent_id;
        $data=array_merge($data,['userInfo'=>$this->getUserInfo()]);

        return $this->success($data);
    }
    //获取我的个人数据信息
    public function getMeData(){
        return $this->success(['userInfo'=>$this->getUserInfo()]);
    }
    //获取经纪人编辑页面数据
    public function getMeEditData(){
        return $this->success(['userInfo'=>$this->getUserInfo()]);
    }
    //修改经纪人用户信息
    public function editAgent(){
        $parameter['name']=Context::Post('name');
        $parameter['phone']=Context::Post('phone');
        $parameter['signature']=Context::Post('signature');
        $parameter['special_label']=trim(Context::Post('special_label'),',');
        $parameter['update_time']=time();
        $res=$this->db->Name('xcx_agent_user')->update($parameter)->where_equalTo('id',Session::get('agent_id'))->execute();
        if($res)
            return $this->success();
        // echo json_encode(['success'=>true]);
        else
            return $this->error();
        // echo json_encode(['success'=>false]);
    }



    //=========公众号用户登录=========//
    public function wxlogin(){
        $appid = WXAPPID;
        $redirect_uri = Context::Get('redirect_uri');
        $redirect_uri = urlencode($redirect_uri);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
        Context::Redirect($url);
        exit;
    }
    //获取公众号粉丝信息进行的登录操作
    public function getinfo(){
        $code_key = Context::Get('code_key');//经纪人绑定激活用于区分是否是经纪人激活
        $appid = WXAPPID;
        $secret = WXSECRET;
        $domain_name = WX_HOST;
        $code = Context::Get('code');
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $oauth2 = $this->sendPost($oauth2Url);
        $oauth2 = json_decode($oauth2, true);

        if(isset($oauth2['unionid']) && !empty($oauth2['unionid'])){
            $resData = $this->sendPost("https://api.weixin.qq.com/sns/userinfo?access_token=".$oauth2['access_token']."&openid=".$appid."&lang=zh_CN");

            $resData = json_decode($resData, true);

            $userInfo=(new Query())->Name('xcx_agent_user')->select()->where_equalTo('unionid',$oauth2['unionid'])->firstRow();
            if(!empty($userInfo)){
                $agent_id = $userInfo['id'];
                $openid = $resData['openid'];
                (new Query())->Name('xcx_agent_user')->update(['`openid`'=>$openid])->where_equalTo('id',$userInfo['id'])->execute();

                $jwtToken=JWT::encode(['from_type'=>'2','agent_id'=>$agent_id,'user_id'=>0,'create_time'=>time()],'9hhouse');
            }else{

                if(!empty($resData)){
                    $data['nickname']=$resData['nickname'];
                    $data['headimgurl']=$resData['headimgurl'];
                    $data['sex']=$resData['sex'];
                    $data['language']=$resData['language'];
                    $data['country']=$resData['country'];
                    $data['province']=$resData['province'];
                    $data['city']=$resData['city'];
                    $data['openid']=$resData['openid'];
                    $data['unionid']=$resData['unionid'];
                    $data['create_time']=time();
                    $data['update_time']=time();

                    if(!empty($code_key)){//经纪人登陆
                        $resId=$this->db->Name('xcx_agent_user')->insert($data)->execute();
                        if(empty($resId)){
                            return $this->error('用户添加失败！');
                        }
                        $agent_id = $resId;
                        $openid = $resData['openid'];

                        $jwtToken=JWT::encode(['from_type'=>'2','agent_id'=>$agent_id,'user_id'=>0,'create_time'=>time()],'9hhouse');
                    }else{//普通用户登陆
                        $is_user = 1;
                        $has = $this->db->Name('xcx_user')->select('id,unionId')->where_equalTo('unionId',$resData['unionid'])->firstRow();
                        if(empty($has)){
                            $resId=$this->db->Name('xcx_user')->insert([
                                'nickName'=>$resData['nickname'],
                                'avatarUrl'=>$resData['headimgurl'],
                                'language'=> $resData['language'],
                                'gender'=> $resData['sex'],
                                'country'=> $resData['country'],
                                'province'=> $resData['province'],
                                'city'=> $resData['city'],
                                'openId'=> '',
                                'unionId'=> $resData['unionid'],
                                'create_time'=>time(),
                                'update_time'=>time(),
                            ])->execute();
                            if(empty($resId)){
                                return $this->error('用户添加失败！');
                            }
                            $unionid = $resData['unionid'];
                            $user_id = $resId;
                        }else{
                            $user_id = $has['id'];
                            $unionid = $has['unionId'];
                        }

                        /*$p_agent_id = Context::Get('agent_id');
                        if(!empty($p_agent_id)){
                            //形成客户关系
                            $has2 = $this->db->Name('xcx_agent_customer')->select('id')->where_equalTo('agent_id',$p_agent_id)->where_equalTo('user_id',$user_id)->firstRow();
                            if(empty($has2)){
                                $source = intval(Context::Get('source')); //来源 0自己关注，1：经纪人名片 2：文章 3：楼盘
                                if(!in_array($source,['0','1','2','3'])){
                                    $source = 0;
                                }
                                $this->db->Name('xcx_agent_customer')->insert(['agent_id'=>$p_agent_id,'user_id'=>$user_id,'source'=>$source,'agent_status'=>1,'user_status'=>1,'create_time'=>time(),'update_time'=>time()])->execute();
                            }
                        }*/
                        $jwtToken='';
                    }
                }else{
                    return $this->error('获取用户信息失败！');
                }
            }

            if($is_user!=1){//经纪人时
                Session::set('agent_id',$agent_id);
                Session::set('openid',$openid);
                Session::del('customer_user_id');
                $this->agentId = $agent_id;
                $this->getUserInfo(1);//刷新经纪人信息
                Session::set('token',$jwtToken);//用于聊天
            }else{
                //普通用户时
                Session::set('customer_user_id',$user_id);
                Session::del('openid');
                Session::del('agent_id');
                Session::del('token');
                Session::set('unionid',$unionid);
            }

            return $this->success(['token'=> $jwtToken,'nice_name'=>$resData['nickname'],'headimgurl'=>$resData['headimgurl'] ]);
        }else{
            return $this->error([
                'msg'=> '授权登录失败',
                'code' => '3001',
            ]);
        }
    }

    //经纪人身份激活绑定，通过后台激活码
    public function activeCode(){
        $code_key = Context::Post('code_key');

        if(!empty($code_key)){

            //激活码是否可以激活
            $asRow=$this->db->Name('xcx_store_agent')->select('said,store_id,agent_id,type,create_time,type')->where_equalTo('code_key',$code_key)->firstRow();

            if(empty($asRow['said'])||$asRow['is_delete']==1){
                //未存在或者软删除
                return $this->error('未存在该验证信息！');
            }
            if(($asRow['create_time']+86400)<time()){
                return $this->error('该验证信息已过期！');
            }
            if(!empty($asRow['agent_id'])){
                return $this->error('该验证信息已被绑定！');
            }
            if($asRow['status']=='-1'){
                return $this->error('该验证信息已被禁用！');
            }

            //该人员是否绑定过
            $agent_id = $this->getAgentId();
            $asRowold=$this->db->Name('xcx_store_agent')->select('said,type')->where_equalTo('agent_id',$agent_id)->where_equalTo('is_delete', 0)->execute();

            //获得该用户所有绑定类型
            $asRowold = array_column($asRowold,'type');

            if(in_array($asRow['type'], [0, 1])) {// 经纪人
                $craftsman = array_diff($asRowold,[0,1]);
                if(!empty($craftsman)){
                    return $this->error('该角色已经绑定过工作人员类型，不可再绑定其他类型！');
                }
            } else {// 工作人员
                $craftsman = array_diff($asRowold,[2,3,5,6,7,8]);
                if(!empty($craftsman)){
                    return $this->error('该角色已经绑定过经纪人类型，不可再绑定其他类型！');
                }
            }


            $userInfo = $this->getUserInfo();

            try {
                $pdo = new DataBase();
                $pdo->beginTransaction();

                if($asRow['type']==0){//店员的时候切换为申请中
                    $asRow['status']=0;
                }
                $parameter['agent_id'] = $agent_id;//绑定人员信息
                $parameter['agent_openid'] = $userInfo['openid'];//openid
                $parameter['agent_name'] = !empty($userInfo['nickname']) ? $userInfo['nickname'] : '';// 冗余绑定者昵称，方便在解绑后查找最后绑定者昵称
                $parameter['agent_img'] = !empty($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '';// 冗余绑定者头像，方便在解绑后查找最后绑定者头像
                $parameter['update_time'] = time();
                $res1=$this->db->Name('xcx_store_agent')->update($parameter)->where_equalTo('said',$asRow['said'])->execute();
                if(empty($res1)){
                    $pdo->rollBack();
                    throw new PDOException('绑定店铺数据插入失败！');
                }

                // 经纪人申请状态变更
                $update = [
                    'sq_store_status' => 2,
                ];
                $res2 = $this->db->Name('xcx_agent_user')->update($update)->where_equalTo('id', $agent_id)->execute();
//                if(empty($res2)){
//                    $pdo->rollBack();
//                    throw new PDOException('申请状态变更失败！');
//                }

                $pdo->commit();

                $this->getUserInfo(1);//刷新经纪人信息

                return $this->success([],'绑定成功');
            } catch (PDOException $e) {
                $pdo->rollBack();
                return $this->error($e->getMessage());
            }
        }
    }


    //加入成员，通过店铺二维码
    public function addMember(){
        $store_id = Context::Post('store_id');
        try {
            if(empty($store_id)){
                throw new Exception('参数有误！');
                return;
            }
            $store_id = Encryption::authcode($store_id,true);

            $agent_id = $this->agentId;

            if(empty($agent_id)){
                //throw new Exception('用户没有注册成功！');
                $unionid=Session::get('unionid');
                //没注册用户注册
                $has = $this->db->Name('xcx_agent_user')->select('id')->where_equalTo('unionId',$unionid)->firstRow();
                if(!empty($has)) {
                    $agent_id=$has['id'];
                }else{
                    $resData=$this->db->Name('xcx_user')->select()->where_equalTo('unionId',$unionid)->firstRow();
                    $resId = $this->db->Name('xcx_agent_user')->insert([
                        'nickname' => $resData['nickName'],
                        'headimgurl' => $resData['avatarUrl'],
                        'language' => $resData['language'],
                        'sex' => $resData['gender'],
                        'country' => $resData['country'],
                        'province' => $resData['province'],
                        'city' => $resData['city'],
                        'openId' => '',
                        'unionid' => $resData['unionId'],
                        'create_time' => time(),
                        'update_time' => time(),
                    ])->execute();
                    if (empty($resId)) {
                        return $this->error('用户添加失败');
                        throw new Exception('用户添加失败！');
                    }
                    $agent_id = $resId;
                }

            }

            //自己不能申请自己
            $Info=$this->db->Name('xcx_store_agent')->select()->where_equalTo('store_id',$store_id)->where_equalTo('type',1)->firstRow();
            if(empty($Info)){
                return $this->error('没有店长');
                throw new Exception('没有店长！');
                return;
            }

            if($Info['agent_id']==$agent_id){
                return $this->error('自己不可申请自己');
                throw new Exception('自己不可申请自己！');
                return;
            }


            $userInfo=$this->db->Name('xcx_agent_user')->select()->where_equalTo('id',$agent_id)->firstRow();


            if(!empty($userInfo)){
                //已有申请
                if($userInfo['sq_store_status']==1){
                    $time = $userInfo['sq_store_addtime'] + (3 * 24 * 3600);
                    if($time > time()) {
                        if($userInfo['sq_store_id'] == $store_id) {
                            $str = '已经提交过申请！等待审核中！';
                        } else {
                            $str = '已经在其他店铺提交过申请！等待审核中！';
                        }
                        return $this->error($str);
                    }
                }
                elseif($userInfo['sq_store_status']==3){
                    return $this->error('已经提交过申请！等待审核中！');
                }
                elseif($userInfo['sq_store_status']==2){
                    return $this->error('您已经申请成功！！');
                }

                $res=$this->db->Name('xcx_agent_user')
                    ->update(['sq_store_id'=>$store_id,'sq_store_status'=>'1', 'sq_store_addtime'=>time()])
                    ->where_equalTo('id',$agent_id)->execute();
                if($res){
                    echo $this->success(["_userInfo"=>["mestatus"=>0],"massage"=>"正在申请中，等待通过!"]);
                    return;
                }else{
                    throw new Exception('申请失败！');
                    return;
                }
            }else{
                throw new Exception('账号还未登录！');
                return;
            }
        }catch (Exception $e){
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
            return;
        }
    }

    //客户关系形成
    public function agentCustomer(){
        $p_agent_id = intval(Context::Post('agent_id'));
        $user_id = Session::get('customer_user_id');
        if(!empty($p_agent_id)&&!empty($user_id)){
            //形成客户关系
            $agentinfo = $this->db->Name('xcx_agent_user')->select('id')->where_equalTo('id',$p_agent_id)->firstRow();
            if($agentinfo){
                $has2 = $this->db->Name('xcx_agent_customer')->select('id')->where_equalTo('agent_id',$p_agent_id)->where_equalTo('user_id',$user_id)->firstRow();
                if(empty($has2)){
                    $source = intval(Context::Post('source')); //来源 0自己关注，1：经纪人名片 2：文章 3：楼盘
                    if(!in_array($source,['0','1','2','3'])){
                        $source = 0;
                    }
                    $this->db->Name('xcx_agent_customer')->insert(['agent_id'=>$p_agent_id,'user_id'=>$user_id,'source'=>$source,'agent_status'=>1,'user_status'=>1,'create_time'=>time(),'update_time'=>time()])->execute();
                }
            }
        }
        return $this->success();
    }

    //=========公众号用户登录end=========//

    //获取经纪人名片信息
    public function getCardData(){
        $id = intval(Context::Post('id'));
        if(empty($id)){$id = $this->getAgentId();}
        //$id = urlencode(Encryption::authcode($this->getAgentId(),false));
        //$id = $this->getAgentId();
        $data=[];
        $access_token=$this->getAccessToken2();
        $parameter=["scene"=>$id,"page"=>"pages/store/store_detail/store_detail"];
        $qrCode=$this->sendPost('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token,json_encode($parameter));
        $qrarray = json_decode($qrCode,1);
        if($qrarray&&!empty($qrarray['errmsg'])){
            return $this->error('生成失败');
        }
        $qrCode=$this->data_uri($qrCode,'image/png');

        $data=array_merge($data,['userInfo'=>$this->getUserInfo(2,$id)],["qrCode"=>$qrCode,"agent_id"=>$id]);
        return $this->success($data);
    }
    //二进制转图片image/png
    private function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }

    public function getChatToken(){
        $agent_id = $this->getAgentId();
        $jwtToken=JWT::encode(['from_type'=>'2','agent_id'=>$agent_id,'user_id'=>0,'create_time'=>time()],'9hhouse');
        return $this->success(['token'=>$jwtToken]);
    }

    public function kefu(){
        $rs= $this->db->Name('xcx_setting')->select()->where_in('`key`', ['wechat_logo','system_company','system_wechat'])->execute();
        $rs = array_column($rs,null,'key');
        return $this->success([
            "agent_id"=>$this->getAgentId(),
            "wechat_logo"=>$rs['wechat_logo']['value'],
            "system_company"=>$rs['system_company']['value'],
            "system_wechat"=>$rs['system_wechat']['value'],
        ]);
    }
}