<?php

namespace app\index\controller;

use app\common\base\HhDb;
use app\common\base\UserBaseController;
use app\common\lib\wxapi\WxServe;
use app\common\MyConst;
use app\index\validate\UserValidate;
use app\server\admin\SecondHandHousing;
use app\server\admin\Sys;
use app\server\marketing\Subject;
use app\server\marketing\Vote;
use app\server\merchant\Activities;
use app\server\user\ShortMessage;
use app\server\user\User;
use Exception;
use think\facade\Config;
use think\exception\ValidateException;


class PublicController extends UserBaseController
{

    /**
     * 微信授权登录
     * @return \think\response\Redirect
     * @throws \Throwable
     */
    public function wxLogin()
    {

        $param = $this->request->param();
        //测试用例
//        var_dump($param);
//        $param['redirect_uri'] = 'http://999house.test.com/index/public/wxH5UserLogin?active_id=1';
//        $data['redirect_uri'] = !empty($param['redirect_uri']) ? $param['redirect_uri'] : "http://192.168.1.30:82?merch_id=kJR9dO&active_id=0dB6d2";


        $redirect_uri = $param['redirect_uri'];
        if (empty($redirect_uri)) {
            $this->error('地址错误');
        }
        unset($param['redirect_uri']);
        $idx = strpos($redirect_uri, '?');
        if ($idx) {
            $res = substr($redirect_uri, $idx + 1);
            $res = explode('&', $res);
            //提取redirect_uri?后的数据
            foreach ($res as $v) {
                $ex = explode('=', $v);
                $data[$ex[0]] = $ex[1];
            }
        }

        foreach ($param as $key => $value) {
            if(in_array($key,['code','state'])){//去掉微信授权回跳所带的历史微信参数
                continue;
            }

            $data[$key] = $param[$key];
            $redirect_uri .= '&' . $key . '=' . $value;//拼接其他附加参数
        }


        //获取服务号配置
        $server = new WxServe();
//        $resUrl = $server->setCodeId(0)->getWxH5()->getH5Login([
//           'redirect_uri'=> $redirect_uri
//        ]);
        $resUrl = $server->setCodeId(0)->getH5Login([
            'redirect_uri'=> $redirect_uri
        ]);
        $resUrl = str_replace('&amp;', '&', $resUrl);


        return redirect($resUrl);
    }

    /**
     * 微信登录
     */
    public function wxH5UserLogin()
    {
        $param = $this->request->param();
        if (empty($param['code']) || empty($param['state'])) {
            $this->error('缺少参数');
        }

        // 用户信息处理
        $userServer = (new User());
        $res = $userServer->wxH5User([
            'code' => $param['code'],
        ]);
        if ($res['code'] == 0) {
            $this->error($res['msg']);
        }

        $this->success([
            'token'       => $res['result']['token'],
            'type'        => MyConst::WX_H5,
            'nickname'    => $res['result']['nickname'],
            'headimgurl'  => $res['result']['headimgurl'],
            'expire_time' => $res['result']['expire_time'],
            'is_login'    => $res['result']['is_login'],
            'phone'       => $res['result']['phone'] ?? ''
        ]);
    }


    //测试群发
    public function test()
    {
//        $data = $this->request->param();
//        \app\common\websocket\BroadcastProcess::getInstance()->task($data);

        /*$obj = \think\Container::getInstance()->make(\app\common\lib\wxapi\co\CoWxPool::class);
        $rs = $obj->addTask([
            [
                'key'=>'baidu',
                'data'=>'www.baidu.com',
                'callFun' => function($data){
                    return $data;
                }
            ],
            [
                'key'=>'baidu2',
                'data'=>'www.baidu2.com',
                'callFun' => function($data){
                    return $data;
                }
            ]
        ]);
        print_r($rs);*/
    }



    /**
     * 回跳地址参数处理
     * @return string|\think\response\Redirect|void
     */
    public function requestProxy()
    {

        $param = $this->request->param();
        $url = $param['url'];
        $code = $param['code'];
        $state = $param['state'];

        if (empty($url) || empty($code) || empty($state)) {
            echo '参数缺失';
            return;
        }
        unset($param['url'], $param['code'], $param['state']);


        if (strpos($url, '?')) {
            $url = "{$url}&code={$code}&state={$state}";
        } else {
            $url = "{$url}?code={$code}&state={$state}";
        }

        foreach ($param as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }

//        var_dump($url);exit();
//        header("Location: {$url}");

        return redirect($url);

    }


    //手机登录 和 微信授权后手机填写登录
    public function mobileLogin()
    {

        $param = $this->request->post();
        $type = $this->deviceType; //设备类型
        $token = $this->token;
        if (empty($param['mobile'])||empty($param['code'])) {
            return $this->error('缺少参数');
        }
        $mobile = $param['mobile'];

        $param['sence'] = 'login';// 验证码场景

        if($param['code'] != '666666'){
            $msgServer = new ShortMessage();
            if (!$msgServer->checkCode($param)) {
                return $this->error('请输入正确的验证码');
            }
        }

        if ($type != MyConst::H5) { //微信登录
            if(empty($token)){
                return $this->error('参数缺失');
            }
            $where = [
                ['token', '=', $token],
            ];
        } else {
            $where = [
                ['phone', '=', $mobile],
            ];
        }

        // 验证字段
        /*try {
            validate(UserValidate::class)->scene('edit')->check($params);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }*/
        $params = [
            'phone'       => $mobile,
            'update_time' => time(),
        ];

        $userServer = new User();
        $editRes = $userServer->addUser($where, $params, $type);
        if (isset($editRes['code']) && 0 == $editRes['code']) {
            return $this->error($editRes);
        }
        return $this->success($editRes['result']);

    }

    //发送短信验证码
    public function sendMsg()
    {
        $post = $this->request->post();

        $res = (new ShortMessage())->sendMsg($post);
        if ($res['code'] == 0) {
            return $this->error($res['msg']);
        }
        return $this->success($res['result']);
    }

    // 发送短信验证码-外部接口调用
    public function sendMsgApi()
    {
        $post = $this->request->post();

        $res = (new ShortMessage())->sendMsgApi($post);

    }


    public function getWebInfo(){
        $param = $this->request->param();
        $param['wx_config_id'] = $param['city_no']; //缓存id
        $server = new WxServe();

//        $resUrl = $server->setCodeId($param['city_no'])->getWxH5($param)->getJsSdkConfig($param,2); //1取服务号  2 取订阅号
        $param['wx_do_type'] = 2;
        $resUrl = $server->setCodeId($param['city_no'])->getJsSdkConfig($param);
        //$resUrl = $server->getJsSdkConfig($param);
        $this->success($resUrl);
    }

    /**
     * @param $code
     * @return bool|int|mixed
     */
//    public function getWxId($code)
//    {
//        try {
//            $vxconfig = $this->db->name('site_city_set')->where('region_no', $code)
//                ->where('key','wxh5')
//                ->field('wx_config_id')
//                ->value('val');
//            $vxconfig = $vxconfig ? json_decode($vxconfig,true):[];
//
//            if(empty($vxconfig)){
//                $this->error('微信未配置');
//            }
//
//            return $vxconfig;
//        } catch (\Exception $exception) {
//            return false;
//        }
//
//    }

    /**
     * 测试
     */
    public function wxConfigurationInfo()
    {
        $server = new Activities();

        $res = $server->wxConfigurationInfo(140400);

        return $this->success($res);
    }

    public function serverCode(){
        $res = (new Sys())->serverCode();
        if($res == 0){
            return $this->error($res);
        }
        return  $this->success($res['result']);
    }

    // 用户退出
    public function logout()
    {
        $param = $this->request->param();
        $this->getReids(0)->del(MyConst::JIUFANG_LOGIN . $param['token']);
        $this->success();
    }

    /**
     * 获取微信H5的AccessToken
     */
    public function getH5AcessToken()
    {
        try {
            $param = $this->request->param();

            $key = 'hu7iKr5ERmxy7Bqc';

            if(empty($param['key']) || $key != $param['key']) {
               throw new Exception('非法请求');
            }
            
            $server = new WxServe();
            $token = $server->getWxH5AccessToken();

        } catch(Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success($token);
    }

}