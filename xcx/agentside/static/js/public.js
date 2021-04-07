// const DOMAINNAME="http://192.168.1.22:8085/";
const DOMAINNAME="http://192.168.1.6:8092/"; // 接口页面地址
// const DOMAINNAME="http://chfx.999house.com/"; // 接口页面地址
const DOMAINIMAGE="http://www.999house.com";
const DOMAINSTATIC="http://192.168.1.6:8085/agentside/static/";
const DOMAINWEBSOCKET="wss://chat.999house.com:1201/";

var _auth = {}
var _mestatus = ''
function ajax(url,datas,fun){
    var type = 'POST';
    var api = 'agentapi/';
    if(typeof url == 'object'){
        if(url.api){
            api = url.api
        }
        if(url.data){
            datas = url.data
        }
        if(url.type){
            type = url.type
        }
        if(url.success){
            fun = url.success
        }
        if(url.error ){
            err_fun = url.error
        }
        var is_async = true;
        if(url.is_async ){
            is_async = url.is_async
        }

        url = url.url
    }else{
        var err_fun = arguments[3] ? arguments[3] : false;
        var is_async = arguments[4] ? arguments[4] : false;
    }
    datas['uid'] = 84
    console.log('datas',datas)
    url = DOMAINNAME + api + url
    $.ajax({
        url: url,
        type: type, //GET,POST
        async: is_async, //或false,是否异步
        data: datas,
        timeout: 50000,    //超时时间
        dataType: 'json',    //返回的数据格式：json/xml/html/script/jsonp/tex
        success:function(res) {
            if(res.ajaxerror){
                isLogin(false)
                return false;
            }else{
                if(res._auth){
                    _auth =  get_auth(res._auth)
                }else{
                    _auth =  get_auth()
                }
                if(res.mestatus){
                    _mestatus = res.mestatus
                }
                canLoad()
                fun(res);
            }
        },
        error:function(err){
            if(err_fun){
                err_fun(err);
            }
        }
    });
}

/*
* 分享次数纪录
* share_type:分享类型 1：名片  2：文章  3：楼盘
* */
function myAddShare(share_type,article_id,building_id){
    ajax('publicAjax/addShare',{share_type:share_type,article_id:article_id,building_id:building_id},function(res){})
}


//检查店铺状态
function checkStoreStatus(userinfo = _auth,show_message=false){
    console.log('_mestatus',_mestatus)
    console.log(_mestatus=='1')
    if(_mestatus=='1'){
        return true;
    }else{
        var error_msg = '您还不是经纪人请耐心等待开通';
        // if(!userinfo.store_status=='-1'){
        //     error_msg = '您所在店铺已被禁用'
        // }else
        console.log('mestatus',_mestatus)
        if(_mestatus=='-1'){
            error_msg = '您的账号被禁用'
        }else if(_mestatus=='0'){
            error_msg = '您的账号正在审核中请耐心等待开通'
        }else if(_mestatus=='-3'){
            error_msg = '请先完善个人信息';
        }else if(_mestatus=='-2'){
            error_msg = '您的账号未启用请联系管理员';
        }

        if(show_message){
            if(_mestatus=='-3'){
                mui.alert(error_msg, '提示', function() {
                    window.location.href = window.location.origin+'/agentside/pages/me/edit.html'
                });
            }else{
                mui.toast(error_msg);
            }
        }

        return error_msg;
    }
}
function isAuthUrl(url){
    var idx = url.indexOf('?')
    if(idx!='-1'){
        url = url.substr(0,idx)
    }

    var publicUrls = [
        'news.html',
        'pages/me/join_store.html',
        'pages/index/article_detail.html'
    ];
    if(publicUrls.includes(url)){
        return false
    }

    var authUrls = [
        'index.html',
        'me.html',
        'building.html',
        'pages/build/build_detail.html',
        'customer.html',
        'pages/me/report.html',
        'pages/build/house_info.html',
        'pages/build/building_list.html',
        'pages/index/erweima.html',
        'pages/me/chat.html',
    ]

    if(!_mestatus||_mestatus=='-2'||_mestatus=='0'){
        authUrls = authUrls.concat([
            'pages/me/disturb.html',
            'pages/me/my_message.html',
            'pages/me/my_report.html',
            'pages/customer/take_apply.html',
            'examine_reported.html',
            'pages/build/my_building.html',
            'pages/me/feedback.html'
        ])
    }
    if(authUrls.includes(url)){
        return true
    }
    return false
}
//页面跳转
function setLocation(url){
    if(isAuthUrl(url)&&checkStoreStatus(_auth,true)!==true){
        return
    }
    window.location.href=DOMAINNAME+'agentside/'+url;
}

var can_timer = null
//当前页面是否可以打开
function canLoad(){
    if(can_timer){clearTimeout(can_timer);can_timer=null}
    var pageUrl = window.location.pathname.replace('/agentside/','');
    var errmsg = checkStoreStatus()
    console.log('isAuthUrl(pageUrl)',isAuthUrl(pageUrl))
    console.log('errmsg',errmsg)
    if(isAuthUrl(pageUrl)&&errmsg!==true){
        console.log('进入判断')
        var href = window.location.origin+'/agentside/news.html'
        if(errmsg=='请先完善个人信息'){
            href = window.location.origin+'/agentside/pages/me/edit.html'
        }

        can_timer = setTimeout(function(){
            mui.alert(errmsg, '提示', function() {
                window.location.href = href
            });
        },500)

        if(!getQueryString('code_key')){
            isLogin(2)
            throw('页面无权限')
        }
    }
    if(_mestatus&&_mestatus!=-2){
        isLogin(1)
    }
    // if(get_auth()['mestatus']&&get_auth()['mestatus']!=-2){
    //     isLogin(1)
    // }
}

function getQueryString(name){
    if(!window.location.search){
        return  null
    }
    var parms = window.location.search.substr(1)
    if(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = parms.match(reg);
        return r ? decodeURIComponent(r[2]) : null;
    }else{
        parms = parms.replace(/amp;/g,'')
        parms = parms.split('&');
        var nparms= {}
        for(var i in parms){
            var pair = parms[i].split("=");
            nparms[pair[0]] = pair[1]
        }
        return nparms;
    }
}

function doMissId(val,errmsg='参数缺失'){
    if(!val){
        mui.alert(errmsg,'提示','确定',function(){
            if(window.history&&window.history.length > 1){
                window.history.go( -1 );
            }else{
                setLocation('index.html');
            }
        });
        throw errmsg
    }
}

function get_auth(auth={}) {
    if(Object.keys(auth).length>0){
        sessionStorage.setItem("_auth", JSON.stringify(auth));
        if(!auth.auth_report_types){auth.auth_report_types=[]}
        return auth
    }
    auth = sessionStorage.getItem("_auth")
    auth =  auth? JSON.parse(auth):{}
    if(!auth.auth_report_types){auth.auth_report_types=[]}
    return auth
}

function get_code_key(val=null) {
    if(val){
        sessionStorage.setItem("_code_key", JSON.stringify({code_key:val.code_key,is_use:1}));
    }else{
        var code_key= getQueryString('code_key')
        var codeobj = sessionStorage.getItem("_code_key")
        if(codeobj){
            codeobj = JSON.parse(codeobj)
        }else{
            codeobj={}
            codeobj.code_key = ''
        }
        if(code_key&&code_key!=codeobj.code_key){
            sessionStorage.setItem("_code_key", JSON.stringify({code_key:code_key,is_use:0}));
            return code_key
        }else{
            if(codeobj.is_use==1){ return '' }
            return codeobj.code_key
        }
    }
}
function isLogin(is_login=null){
	//  调试
	// is_login = {
	// 	expires: 7200000,
	// 	key: "is_login",
	// 	startTime: 1610526205015,
	// 	value: 1,
	// }
	// is_login = 1
    if(is_login!==null){
        sessionStorage.setItem("_isLogin",is_login)
        return is_login
    }
    is_login = sessionStorage.getItem("_isLogin")
    is_login = is_login&&is_login!=='false'?is_login : false
    return is_login
}
if(isLogin()!=false){
    var code_key = get_code_key()

    if(isLogin()==1&&code_key&&window.location.href.indexOf('agentside/index.html')!='-1'){
        ajax('userAjax/activeCode',{code_key:code_key},function(res){
            get_code_key({code_key:code_key,is_use:1})
            if(res.code==1){
                mui.toast('绑定成功');
            }else{
                mui.toast(res.message);
            }
        },function () {},true);
    }else if(isLogin()==2&&getQueryString('code_key')){
        doLogin(window.location.href,1)
        throw('重新登陆')
    }

    var agent_id = getQueryString('agent_id');
    var source = getQueryString('source');
    if(agent_id){
        ajax('userAjax/agentCustomer',{agent_id:agent_id,source:source},function(res){

        },function () {},true);
    }
}else{
    doLogin()
}

var timer = null
function doLogin(redirect_uri = window.location.href, refresh = 0){
    if(timer){
        clearTimeout(timer)
        timer = null
    }
    var code= getQueryString('code')

    timer = setTimeout(function(){
        if(refresh!=1&&code&&getQueryString('state')==='0'){
            //当前页面
            var postdata = { 'code': code }
            var currentUrl = window.location.origin + window.location.pathname
            var parms = getQueryString()
            if(parms){
                if(parms['code']){
                    delete parms['state']
                    delete parms['code']
                }
                if(Object.keys(parms).length>0){
                    var str = ''
                    for(var i in parms){
                        str+='&'+i+'='+parms[i]
                    }
                    currentUrl+='?'+str.substr(1)
                }
            }

            var code_key = getQueryString('code_key');
            if(code_key){postdata.code_key=code_key;}
            ajax({
                api: 'agentapi/',
                url: 'userAjax/getinfo',
                type: 'GET',
                data: postdata,
                success: function(rs){
                    if(rs.code==1){
                        if(code_key){
                            isLogin(1)
                        }else{
                            console.log('code_key',code_key)
                            isLogin(2)
                        }

                        window.location.href = currentUrl
                    }else if(rs.code==3001){
                        doLogin(currentUrl,1)
                    }else{
                        isLogin(false)
                        mui.toast(rs.message);
                    }
                }
            })
            return
        }

        isLogin(false)
        // window.location.href=DOMAINNAME+'agentapi/userAjax/wxlogin?redirect_uri='+encodeURIComponent(redirect_uri);
    },300)
}

