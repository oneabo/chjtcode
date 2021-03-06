<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace app\common\lib\wxapi\module;

use app\common\lib\TraitInstance;
use Nette\Utils\Image;
use think\App;
use think\Exception;
use think\facade\Config;

class  WxMini extends WxBase
{
//    use TraitInstance;

    public function __construct($config = []) //$dotype = 'wxH5'
    {
        $this->appid     = $config['appid'] ?? ''; //微信支付申请对应的公众号的APPID
        $this->appSecret = $config['secret'] ?? ''; //微信支付申请对应的公众号的APP Key
//        $this->cityCode = $config['h5']['city_code'] ?? 0;
//
//
//        //新增--订阅号-一些类型
//        $this->is_open = $config['h5']['is_open']; //网页授权回调地址
//        $this->wxType = $config['h5']['type']; //网页授权回调地址
//        $this->subscribeJson = empty($config['h5']['subscribe']) ? [] : $config['h5']['subscribe']; //订阅号appid等
        if (empty($this->appid) || empty($this->appSecret)) {
            throw new Exception('请进行微信配置');
        }
    }
    /**
     * 小程序授权登陆
     * @param array $dataArr
     * @return mixed
     * @throws \Throwable
     */
    public function getOauthLogin($dataArr=[]){
        try {
            $rs = $this->oauthLogin($dataArr['code'],$dataArr['decryptArray']);
            $this->getSession_WxAuth($rs);
            return $rs;
        } catch (\Throwable $throwable) {
            throw $throwable;
        }
    }

    /**
     * 小程序的授权登陆
     * @param string $code //小程序客户端授权码
     * @param array $decryptArray [ encryptedData,iv ] //小程序客户端用户加密数据
     * @return array 返回用户信息
     * @throws \Exception
     */
    private function oauthLogin($code='',$decryptArray=[]) {
        if(empty($code) || empty($decryptArray)){
            throw  new \Exception("缺少参数");
        }
        $appid=$this->appid;
        $appsecret=$this->appSecret;
        $url="https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$appsecret."&js_code=".$code."&grant_type=authorization_code";
        /*$content = @file_get_contents ( $url );//ssl链接时需要开启相关配置
        $content = json_decode ( $content, true );*/
        $content = self::curlGet( $url );
        //进行解密操作
        $errCode =$this->doDecryptDataForWxApp([
            'session_key' => $content['session_key'],
            'encryptedData' => $decryptArray['encryptedData'],
            'iv' => $decryptArray['iv'],
        ], $data);

        if ($errCode == 0) {
            $data=json_decode($data, true);
            unset($data['watermark']);
            $data['session_key']=$content['session_key'];
        } else {
            $data=[];
        }

        if(empty($data)){
            throw  new \Exception("登陆凭证校验失败".$errCode);
        }
        return $data;
    }

    /**
     * 进行解密小程序授权数据
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $baseparms  array 加密的用户数据 iv与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return int 成功0，失败返回对应的错误码
     */
    public function doDecryptDataForWxApp( $baseparms=[], &$data )
    {
        
        if (strlen($baseparms['session_key']) != 24) {
            return self::ErrorCode()['IllegalAesKey'];
        }
        $aesKey=base64_decode($baseparms['session_key']);//授权请求返回的session_key

        if (strlen($baseparms['iv']) != 24) {
            return self::ErrorCode()['IllegalIv'];
        }

        $aesIV=base64_decode($baseparms['iv']);//小程序返回的初始向量
        $aesCipher=base64_decode($baseparms['encryptedData']);//小程序返回的加密数据

        if(!function_exists('openssl_decrypt')){
            throw new Exception('请开启openssl_decrypt');
        }
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);//使用openssl解密
        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return self::ErrorCode()['IllegalBuffer'];
        }
        if( $dataObj->watermark->appid != $this->appid ) //验证解密后的水印是否正确
        {
            return self::ErrorCode()['IllegalBuffer'];
        }
        $data = $result;
        return self::ErrorCode()['OK'];
    }

    /**
     * 小程序全局唯一后台接口调用凭据 access_token
     * @return mixed
     */
    public function getAccessToken(){
        $session_AccessToken=$this->getCache_AccessToken();
        if(empty($session_AccessToken)){
            $appid =  $this->appid;
            $appSecret =  $this->appSecret;

            $rs = self::curlGet('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appSecret);
            if(!empty($rs['access_token'])){
                $session_AccessToken = $this->getCache_AccessToken($rs['access_token']);
            }
        }
        return $session_AccessToken;
    }

    /**
     * 获取小程序二维码
     */
    public function getWxAppEwcode($dataArr){
        $access_token = $this->getAccessToken();

        $postData = [
            'scene'=> $dataArr['scene'],
        ];
        if(!empty($dataArr['page'])){
            $postData['page']= $dataArr['page'];
        }
        if(!empty($dataArr['width'])){
            $postData['width']= $dataArr['width'];
        }

        $postData = json_encode($postData);
        $rs = self::curlPost('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token,$postData);
        if(!empty($rs['errcode'])){
            throw  new \Exception("接口错误：".$rs['errcode'].":".$rs['errmsg']);
        }

        //将返回的Buffer存到服务器
        $qr_path = WEB_ROOT.'/upload/user/';
        if(!file_exists($qr_path)){
            mkdir($qr_path, 0755,true);//判断保存目录是否存在，不存在自动生成文件目录
        }
        $filename = md5($this->createUuid()).'.png';
        $file = $qr_path.$filename;
        $res = file_put_contents($file,$rs);//将微信返回的图片数据流写入文件

        if($res===false){
            throw  new \Exception("生成二维码失败");
        }

        $file_path = '/upload/user/'.$filename;
        return $file_path; //返回图片地址链接给前端
    }

    //进行缓存
    private function getCache_AccessToken($data=null){
        if(!empty($data)){
            cache('_wxmini_accesstoken', $data, 5400); //缓存1个半小时
        }
        return cache('_wxmini_accesstoken');
    }



    /*生成唯一标志
    *标准的UUID格式为：xxxxxxxx-xxxx-xxxx-xxxxxx-xxxxxxxxxx(8-4-4-4-12)
    * //Returns like 'dba5ce3e-430f-cf1f-8443-9b337cb5f7db'
    */
    function  createUuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return $uuid ;
    }

    /**
     * 小程序授权后解密用户信息的error code 说明.
     * <ul>

     *    <li>-41001: encodingAesKey 非法</li>
     *    <li>-41003: aes 解密失败</li>
     *    <li>-41004: 解密后得到的buffer非法</li>
     *    <li>-41005: base64加密失败</li>
     *    <li>-41016: base64解密失败</li>
     * </ul>
     */
    private static function ErrorCode(){
        return [
            'OK' =>0,
            'IllegalAesKey' =>-41001,
            'IllegalIv' =>-41002,
            'IllegalBuffer' => -41003,
            'DecodeBase64Error' =>-41004,
        ];
    }

    public function checkWxText($text){

        if(empty($text)){
            throw  new \Exception("请输入需要校验的内容");
        }
        $data =[
          'content' => trim_all($text),
        ];
        $access_token = $this->getAccessToken();
        //$rs = self::curlPost('https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$access_token,json_encode($data,JSON_UNESCAPED_UNICODE));
        $rs = doCoHttp([
            'url' => 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$access_token,
            'data' => json_encode($data,JSON_UNESCAPED_UNICODE),
        ]);

        if(empty($rs['body'])||!empty($rs['body']['errcode'])){
            if($rs['body']['errcode']=='87014'){
                throw new \Exception("抱歉，内容含有违法违规内容");
            }else{
                throw new \Exception("接口错误：".$rs['errcode'].":".$rs['errmsg']);
            }
        }

        return $rs;
    }

    public function checkWxImg($img_url){
        if(empty($img_url)){
            throw  new \Exception("请输入需要校验的内容");
        }
        $access_token = $this->getAccessToken();
        $img_arr  = explode('/',$img_url);
        $img_path = App::getInstance()->getRuntimePath().end($img_arr);
        $file = file_get_contents($img_url);
        if(empty($file)){
            throw  new \Exception("请输入正确的图片地址");
        }
        file_put_contents($img_path, $file);
//        $imgaes = \think\Image::open($img_path);
//        if($imgaes->size() > 1024){
//            throw  new \Exception("图片超过大小");
//        }
        $cfile = new \CURLFile($img_path,'image/jpeg',end($img_arr));
        $data =[
            'media' => $cfile
        ];
        $rs = self::post_file('https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$access_token,$data);
        unlink($img_path);

        if(!empty($rs['errcode'])){
            if($rs['errcode']=='87014'){
                unlink($img_url);
                throw new \Exception("抱歉，内容含有违法违规内容");
            }else{
                unlink($img_url);
                throw new \Exception("接口错误：".$rs['errcode'].":".$rs['errmsg']);
            }
        }

        return $rs;
    }


    public function checkWxImgNew($img_url){
        if(empty($img_url)){
            throw  new \Exception("请输入需要校验的内容");
        }
        $access_token = $this->getAccessToken();
//        $img_arr  = explode('/',$img_url);
//        $img_path = App::getInstance()->getRuntimePath().end($img_arr);
//        $file = file_get_contents($img_url);
//        if(empty($file)){
//            throw  new \Exception("请输入正确的图片地址");
//        }
//        file_put_contents($img_path, $file);
//        $imgaes = \think\Image::open($img_path);
//        if($imgaes->size() > 1024){
//            throw  new \Exception("图片超过大小");
//        }
        $img_path  = App::getInstance()->getRootPath().'public'.$img_url;
        $disk_img_movpath = App::getInstance()->getRootPath().'public/upload/_check';
        if (!is_dir($disk_img_movpath)){
            $mk = mkdir($disk_img_movpath,0775,true);
            if($mk===false){
                throw new \Exception('文件目录创建失败');
            }
        }
        if(!is_file($img_path)){
            throw new \Exception('文件数据出错');
        }

        $thumb_img_path = $disk_img_movpath.'/'.basename($img_path);
        $imgObj = \think\Image::open($img_path);
        $imgObj->thumb(150, 150)->save($thumb_img_path,null,100);

        //curl请求方式
//        $cfile = new \CURLFile($thumb_img_path,'image/jpeg',end($img_arr));
//        $data =[
//            'media' => $cfile
//        ];
//        $rs = self::post_file('https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$access_token,$data);

        //协程请求方式
        $rs = doCoHttp([
            'url' => 'https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$access_token,
            'file' => [
                'path' => $thumb_img_path,
                'name' => 'media'
            ],
        ]);
        unset($imgObj);

        if(!empty($rs['errcode'])){
            if($rs['errcode']=='87014'){
                unlink($img_url);
                throw new \Exception("抱歉，内容含有违法违规内容");
            }else{
                unlink($img_url);
                throw new \Exception("接口错误：".$rs['errcode'].":".$rs['errmsg']);
            }
        }
        unlink($thumb_img_path);

        return $rs;
    }

}



