<?php
// 应用公共文件
use think\facade\Config;
error_reporting(E_ERROR | E_PARSE );//关闭警告错误

/**
 * /*
 * 密码加密生成
 * @param string $password //要加密的原始密码
 * @param string $salt //的密码盐值之一（6位）
 * @return string
 * @throws \think\Exception
 */
function password_encrypt($password = '', $salt = '')
{
    if (empty($password) || empty($salt)) {
        throw new \think\Exception('密码参数错误');
    }

    $salt_auth = 'Yasj3eK80HnkC795Mr';//密码盐值之一
    $result = "###" . md5(sha1($salt_auth . $password) . $salt);
    return $result;
}

/**
 * 验证对比密码
 * @param $password 请求的password
 * @param $salt //数据库密码盐值之一
 * @param $passwordInDb //数据库密码
 * @return bool
 * @throws \think\Exception
 */
function password_compare($password, $salt, $passwordInDb)
{
    if (empty($password) || empty($salt) || empty($passwordInDb)) {
        throw new \think\Exception('参数错误');
    }
    return password_encrypt($password, $salt) === $passwordInDb;
}

/**
 * 设置或者获取用户信息
 * @param array $userInfo 要设置的值
 * @param int $isappend 是否是追加数据模式
 * @return array|mixed
 * @throws \think\Exception
 */
function getUserInfo($userInfo = [], $isappend = 0)
{
    if (!is_array($userInfo)) {
        throw new \think\Exception('参数类型错误');
    }
    if (!empty($userInfo)) {
        if ($isappend == 1) {
            $userInfo2 = [];
            if (session('_userInfo')) {
                $userInfo2 = session('_userInfo');
            }
            $userInfo = array_merge($userInfo2, $userInfo);
        }
        session('_userInfo', $userInfo);

    } else {
        $userInfo = session('_userInfo');
    }

    return $userInfo;
}

/**
 * 用户登陆时的一些初始化标识存储与获取，主要包含token信息和其他初始化信息
 * @param array $userInts
 * @param int $isappend //是否是追加数据模式
 * @param int $auto_update_expireTime //是否进行自动更新过期时间
 * @return array|mixed
 * @throws \think\Exception
 */
function getUserInts($userInts = [], $isappend = 0, $auto_update_expireTime = 0)
{
    if (!is_array($userInts)) {
        throw new \think\Exception('参数类型错误');
    }
    $new_expireTime = time() + 7200;// 设置新的_expireTime

    if (!empty($userInts)) {
        if ($isappend == 1) {//追加数据
            $userInts2 = [];
            if (session('_userInts')) {
                $userInts2 = session('_userInts');
            }
            if ($auto_update_expireTime == 1) {
                $userInts['_expireTime'] = '';
            }
            if (empty($userInts['_expireTime'])) {//更新_expireTime
                $userInts['_expireTime'] = $new_expireTime;
            }

            $userInts = array_merge($userInts2, $userInts);
            session('_userInts', $userInts);
        } else {
            //进行初始赋值
            if (!isset($userInts['_hasDbCheckedToken']) || $userInts['_hasDbCheckedToken'] != 0) { //标识用于下次是否从数据库验证token
                throw new \think\Exception('缺少必要参数');
            }
            if (empty($userInts['_expireTime'])) {//标识过期时间
                throw new \think\Exception('缺少必要的过期时间参数');
            }
            if (!isset($userInts['_userType']) || $userInts['_userType'] != 'admin') {
                $userInts2['_userType'] = 'user';//区别身份是admin还是用户会员
            }
            session('_userInts', $userInts);

            $sid = getSessionId();
            if (empty($sid)) {
                throw new \think\Exception('缺少必要参数');
            }
            $userInts['_sid_encrypt'] = sessiondId_encrypt($sid);//存储加密后的sessionid
            session('_userInts', $userInts);
        }
    } else {
        $userInts = session('_userInts');
        //判断是否过期
        if (empty($userInts['_expireTime']) || (int)$userInts['_expireTime'] < time()) {

            $userInts = [];
        } else {
            session('_userInts._expireTime', $new_expireTime); //更新_expireTime
            $userInts['_expireTime'] = $new_expireTime;
        }
    }

    return $userInts;
}

/**
 * 获取sessionid与设置方法 
 * @param string $val
 * @return string
 */
function getSessionId($val = ''){
    if(!empty($val)){
        think\facade\Session::setId($val);
        return  $val;
    }else{
        return think\facade\Session::getId();
    }
}
/**
 * 删除对应打开的session信息，移除此次session_id所对应的数据
 */
function clearSession()
{
    session('_userInfo', null);
    session('_userInts', null);
    session(null);
}
/**
 * 对session_id进行加密，用于后续存储，再次求情时用于检验session_id是否正确
 */
function sessiondId_encrypt($sessionid = '')
{
    if (empty($sessionid)) {
        return;
    }
    $salt = mb_substr($sessionid, 0, 6);
    return md5(md5($salt) . sha1($sessionid));
}
/**
 * 验证请求的session_id是否正确
 * @param string $sessionid 请求获取的session_id
 * @param string $sessionidDb 存储的session_id
 * @return bool
 * @throws \think\Exception
 */
function sessiondId_compare($sessionid = '', $sessionidDb = '')
{

    if (empty($sessionid)) {
        return false;
    }
    if (empty($sessionidDb)) {//默认读取session缓存
        if ($sessionid) {
            getSessionId($sessionid);
            $sessionidDb = getUserInts()['_sid_encrypt'];
        }
    }
    if (empty($sessionidDb)) {
        return false;
    }
    $sid = sessiondId_encrypt($sessionid);

    return $sid === $sessionidDb;
}


/**
 * 创建token
 * @param string $salt
 * @return string
 */
function creatToken($salt = '')
{
    if(empty($salt)){
        $salt = str_rand(8);
    }
    return  md5($salt.'_'.md5(uniqid(mt_rand(10000, 99999), true)).'&'.$salt);
}

function create_order_no($type='')
{
    return $type.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).str_pad(strval(mt_rand(1, 99999)), 5, '0', STR_PAD_LEFT);
}

/**
 * 二维数组根据某个键值去重
 * @param $arr
 * @param $key //指定某个键值或者键值组
 * @return mixed
 */
function assoc_unique($arr, $key)
{
    $tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (is_array($key)) {
            foreach ($key as $j) {
                if (in_array($v[$j], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                    unset($arr[$k]);
                } else {
                    $tmp_arr[] = $v[$j];
                }
            }
        } else {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
    }
    sort($arr); //sort函数对数组进行排序
    return $arr;
}

/**
 * 二位数组内部的一维数组不能完全相同，而删除重复项
 * @param $array2D
 * @return array
 */
function array_unique_fb($array2D)
{
    foreach ($array2D as $v) {
        $v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[] = $v;
    }
    $temp = array_unique($temp);//去掉重复的字符串,也就是重复的一维数组

    foreach ($temp as $k => $v) {
        $temp[$k] = explode(",", $v);//再将拆开的数组重新组装
    }

    return $temp;
}

/**
 *  获取完整静态资源路径
 */
function getRealStaticPath($path){
    if(!empty($path)&&strpos($path,'http')!==false){
        $path = config('app.imageHost').$path;
    }
    return $path;
}


/**
 * 微信设置token时的应答验证
 * @param $data
 */
function validateWxTonkenConfig()
{
    $signature = $_GET['signature'];
    $nonce = $_GET['nonce'];
    $timestamp = $_GET['timestamp'];
    $token = \think\facade\Config::get('wxWebconfig')['token'];

    if ($signature && $timestamp && $nonce) {
        $arr = [$timestamp, $nonce, $token];
        sort($arr);

        $tmpstr = implode('', $arr);
        $tmpstr = sha1($tmpstr);

        if ($tmpstr == $signature) {
            echo $_GET['echostr'];
            exit;
        }
    }

}

/**
 * //删除所有空格
 * @param $str
 * @return mixed
 */
function trim_all($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

/**
 * 生成随机字符串
 * @param int $length 生成随机字符串的长度
 * @param string $char 组成随机字符串的字符串
 * @return string $string 生成的随机字符串
 */
function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if (!is_int($length) || $length < 0) {
        return false;
    }

    $string = '';
    for ($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }

    return $string;
}

/**
 * 加密id
 * @param 可多个数字参数
 * @return int|string
 */
function hashids_encode(){
    //https://hashids.org/php/
    $numbers = func_get_args();
    if(empty($numbers)){
        return 0;
    }

    $salt = 'sLOBjLP9';
    $hashids = new Hashids\Hashids($salt,6);
    return $id = $hashids->encode(...$numbers);
}

/**
 * 解密id
 * @param $id
 * @param int $getall //是否获取所有返回，加密时多个数字参数时
 * @return array|int|mixed
 */
function hashids_decode($id,$getall=0){
    //https://hashids.org/php/
    if(empty($id)){
        return 0;
    }

    $salt = 'sLOBjLP9';
    $hashids = new Hashids\Hashids($salt,6);
    if($getall == 0){
        return $numbers = $hashids->decode($id)[0];
    }
    return $numbers = $hashids->decode($id);
}


/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv  是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = true)
{
    return request()->ip($type, $adv);
}


/**
 * //函数encode_pass($string, $operation, $key, $expiry)中的$string：字符串，明文或密文；$operation：decode表示解密，其它表示加密；$key：密匙；$expiry：密文有效期。
 * 加解密函数
 * @param $string
 * @param string $operation //encode,decode
 * @param string $key
 * @param int $expiry //有效时间
 * @return false|string
 */

function encode_pass($string, $operation = 'encode', $key = '', $expiry = 0) {
    /*$str = 'abcdef';
    $key = 'aaaa';
    echo $str = encode_pass($str,'encode',$key,0); //加密
    echo encode_pass($str,'decode',$key,0); //解密  */

    if($operation == 'encode'&&is_array($string)){
        $string = json_encode($string,JSON_UNESCAPED_UNICODE);
    }

    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙    
    $ckey_length = 6;
    $prefix = 'whichKey_';//key前缀
    // 密匙    
    $key = md5($key ? $prefix.$key : $prefix.'whereis_myauth@_key');

    // 密匙a会参与加解密    
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证    
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文    
    $keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙    
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，  
    //解密时会通过这个密匙验证数据完整性    
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确    
    $string = $operation == 'decode' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿    
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度    
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分    
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符    
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'decode') {
        // 验证数据有效性，请看未加密明文的格式    
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            //return substr($result, 26);
            $str = substr($result, 26);
            $rs = json_decode($str,true);
            return is_null($rs)? $str:$rs;
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因    
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码    
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 * @Description: 无限极分类一
 * @Author: Yang
 * @param $data  数据
 * @param int $parent_id   父级ID
 * @return array
 */
function getTree($data, $parent_id = 0)
{
    $tree = array();
    foreach ($data as $k => $v) {
        if ($v["pid"] == $parent_id) {
            unset($data[$k]);
            if (!empty($data)) {
                $children = getTree($data, $v["id"]);
                if (!empty($children)) {
                    $v["children"] = $children;
                }
            }
            $tree[] = $v;
        }
    }
    return $tree;
}

//协程请求
function doCoHttp($config = []){
    if(strpos($config['url'], 'http')===false){
        throw new Exception('请带上http格式');
    }

    $data = $config['data']??[];
    unset($config['data']);
    $http_headers = $config['headers'];
    $http_type = $config['http_type']?$config['http_type']:'POST';//请求类型 get,post
    $url_arr = parse_url($config['url']);//解析URL

    $file = $config['file']??[];//文件操作
    $fileString = $config['fileString']??[];//文件操作
    unset($config);

    $host = $url_arr['host']; //请求的host地址
    $method_url = strpos($url_arr['path'],'/')===0?substr($url_arr['path'],1):$url_arr['path'];//请求方法
    $http_type = strtoupper($http_type);
    if($url_arr['query']){//url存在请求参数时候合并请求参数
        //parse_str($url_arr['query'],$url_data);
        //$data = array_merge($url_data,$data);
        $method_url = $method_url.'?'.$url_arr['query'];
    }

    $port = 80;
    $isHttps = false;
    if($url_arr['scheme']=='https') {
        $port = 443;
        $isHttps = true;
    }
    unset($url_arr);

    $client = new \Swoole\Coroutine\Http\Client($host, intval($port), boolval($isHttps));
    $client->set(['timeout' => 3.5]);//3.5秒请求超时

    if(!empty($data)){
        $decodeData = json_decode($data,true);
        if(!is_null($decodeData)){//是json格式
            $http_headers['Content-Type'] = 'application/json';
            $data = $decodeData;
        }
    }
    if(!empty($http_headers)){
        $client->setHeaders($http_headers);
        if($http_headers['Content-Type'] == 'application/json'){
            $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        }
    }

    $client->setMethod($http_type);
    !empty($data)&&$client->setData($data);//请求数据操作
    if(!empty($file)){
        $client->addFile($file['path'],$file['name']);//进行文件上传操作
    }
    if(!empty($fileString)){
        $client->addData($file['data'],$file['name']);//进行文件上传操作
    }

    $status = $client->execute('/'.$method_url);

    //echo socket_strerror($client->errCode);
    $body = $client->getBody();
    $client->close();

    if(!empty($body)){
        $arr = json_decode($body,true);
        $body = $arr? $arr: $body;
    }
    return [
        'status'=> $status,
        'body'=> $body
    ];
}


//$data表示二维数组，结构比如说从数据读取出来的多行表结构，'date'表示每行里面的一个字段，通过这个字段排序
//SORT_DESC表示降序排列，SORT_STRING表示设置'date'字段的比较以字符串方式进行
//doSortArr($data,'date',SORT_DESC,SORT_STRING);
function doSortArr($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    $key_arrays =array();
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}


//二维数组多键值排序
//$data表示二维数组，结构比如说从数据读取出来的多行表结构
//'date'表示每行里面的一个字段，通过这个字段排序,然后通过'time'字段排序
//SORT_DESC,SORT_ASC分别对应'date'，'time'两个字段的排序方式
//效果类似于数据库中order by data desc,time asc。
//doSortManyArr($data,'date','time',SORT_DESC,SORT_ASC);
function doSortManyArr($arrays,$sort_key,$sort_key1,$sort_order=SORT_ASC,$sort_order1=SORT_DESC){
    $key_arrays = $key_arrays1 = array();
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
                $key_arrays1[] = $array[$sort_key1];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$key_arrays1,$sort_order1,$arrays);
    return $arrays;
}

//对象无侵入拓展操作
/**
 * @param $obj
 * @param callable|null $fun
 * $fun = function (){//匿名函数中的$this，是传入的$obj的$this作用域
 *  $this->aa;
 *  $this->bb();
 *};
 *
 * @return mixed
 */
function ObjTransform($obj,callable $fun = null){
    return $fun->call($obj);
}

/**
 * 图片路径是不是 404 ；
 * @param $img_path
 */
function urlIs404($img_path){
    if($img_path){
        $domin        = Config::get('app.domain_name').$img_path;
        $img_status   = get_headers($domin);
        if(strpos($img_status[0],'404')){
            $img_path = '';
        }
    }else{
            $img_path =  '';
    }

    return $img_path;
}