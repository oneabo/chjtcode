import {getToken,getSid} from '../http.interceptor.js';

//需要加载配置项时
const loadSetting = function(app = getApp()){
	var promise = new Promise((resolve, reject) => {
		requests({
			host: app.globalData.host,
			url: 'home/getSys',
			data: {},
			success: function (res) {
				uni.hideLoading();
				//console.log(">>>获取配置数据");
				//console.log(res);
				resolve(res)
			},fail: function (e) {
				uni.hideLoading();
				reject("网络异常");
			}
		})
	});
	return promise;
}

//使用时在app.js通过封装wxAuthLogin调用(因app.js已引用该文件,只能通过全局方法调用)//
//==========app.js中示例代码=========//
/* wxAuthLogin(){//用于全局调用
	return WxAuth.authLogin(this)
	} */
	/*requests.js 
	app.wxAuthLogin() */
//=========================//

/**
 * /**
 * 授权登陆
 * 
 * @param {*} appObj 从app.js调用时,appObj需要传this
 * @param {*} postdata 额外的请求数据
 */
const authLogin = function( appObj = getApp(), postdata={} ){ 
	if(!appObj){
		console.log('请设置appObj')
		return
	}

	console.log("=======授权登陆检测========");
    var promise = new Promise((resolve, reject) => {
		uni.showLoading();
		uni.login({
			success: function (res) {
				if(res.code){
					var code=res.code;
					//获取微信用户信息
					authLoginForUserInfo(appObj,code, postdata).then(function(rs){
						console.log('rs.data',rs.data)
						if(rs.code==1){
							if(rs.data.token){
								//appObj.globalData.token = rs.data.token;
								getToken(rs.data.token);
							}
							if(rs.data.sid){
								//appObj.globalData.sid = rs.data.sid;
								getSid(rs.data.sid);
							}
							//appObj.globalData.userInfo = rs.data.userinfo.info
							
							resolve(rs.data);
						}else{
							resolve('');
							console.log('登录授权失败');
							//reject('登录授权失败');
						}
					}).catch((err)=>{
						console.log(err);
						resolve('');
						//reject(err);
					})
				}else{
					resolve('');
					console.log('登录授权失败');
					//reject('登录授权失败');
				}
			},
			fail: function (res) {
				resolve('');
				console.log('登录授权失败');
				//reject('登录授权失败');
			},
			complete: function (res) {
				// complete
				uni.hideLoading();
			}
		});
    });
    return promise;
}
//跳转用户信息授权页面
var showAuthLoginModal_flag = false
const showAuthLoginModal=function(){
	let userCenterPage = 'pages/my/index';
	if(showAuthLoginModal_flag == true){
		return
	}
	
	var pages = getCurrentPages()
	var prepage;
	if (pages[pages.length - 2]){
		prepage = pages[pages.length - 2].route
	}
	
	if(['pages/authorize/index','pages/index/index'].includes(pages[pages.length - 1].route)){
		return
	}
	showAuthLoginModal_flag = true
	
	let launchPath= uni.getLaunchOptionsSync().path
	if(launchPath!='pages/index/index'||prepage==userCenterPage){
		//不是从首页启动,或者从个人中心进
		uni.redirectTo({
			url: '/pages/authorize/index',//跳转用户信息授权页面
		})
	}else{//从其他启动
		uni.switchTab({
			url: '/'+userCenterPage,//跳转用户中心
			fail: res=>{
				if(res){
					uni.redirectTo({
						url: '/pages/authorize/index',//跳转用户信息授权页面
					})
				}
			}
		})
	}
	showAuthLoginModal_flag = false
}

/**
 * 授权登陆获取微信授权用户信息,进行用户注册
 * //根据code//授权码,获取用户信息 
 * @param {*} appObj 
 * @param {*} code 
 */
const authLoginForUserInfo=function(appObj = getApp(), code, postdata={} ){
	var url = 'public/oauthLogin'
	if(postdata.url){
		url = postdata.url
		delete postdata.url
	}
	if(!postdata.hasOwnProperty('_$resetSid')){
		postdata._$resetSid = 1//是否重置sid tonken标识
	}
	//console.log('httplogin', appObj, code, postdata={})
    return new Promise((resolve, reject) => {
			//////////////
			if(!code){
				reject('缺少code')
				return
			}
			
			if(postdata.encryptedData&&postdata.iv){
				if(postdata._$resetSid==1){
					//重置头部携带的token和sid
					getToken(null)
					getSid(null)
				}
				
				appObj.$http.post(url,{
					code: code,
					...postdata
				}).then((res)=>{
						uni.hideLoading();
						console.log(">>>>>>>>>授权登陆返回",res);
						resolve(res)
				}).catch((err)=>{
					uni.hideLoading();
					reject(err)
				})
				return;
			}
			
			uni.getSetting({
				success: function(resp){
					if(resp.authSetting['scope.userInfo']) {
						uni.getUserInfo({
              				lang: "zh_CN",
							success: function (r) {
								//console.log(r)
								if(postdata._$resetSid==1){
									//重置头部携带的token和sid
									getToken(null)
									getSid(null)
								}
								
								appObj.$http.post(url,{
									code: code,
									encryptedData: r.encryptedData,
									iv: r.iv,
									...postdata
								}).then((res)=>{
										uni.hideLoading();
										console.log(">>>>>>>>>授权登陆返回",res);
										resolve(res)
								}).catch((err)=>{
									uni.hideLoading();
									reject(err)
								})
							},
							fail:function(res){
								reject(res)
								//人工授权
								showAuthLoginModal();
							}
						})  
					}else{
						reject('用户未点击授权')
						showAuthLoginModal(); //用户信息授权
					}
				},
				fail:function(resp){
					reject(resp)
					showAuthLoginModal(); //用户信息授权
				}  
			})
      //////////////
    })
  }

  //是否授权过一次
  var hasOnceLoginAuth =  function(){
    return new Promise(function(resolve){
		uni.getSetting({
			success: function(resp){
				if(resp.authSetting['scope.userInfo']) {
					resolve(true)
				}else{
					resolve(false)
				}
			},fail:function(){
				resolve(false)
			}
		})
	})
  }

/**
 * 获取授权手机号
 * @param {*} post 
 */
 const getMobile = function(post,app = getApp()){
	return new Promise((resolve, reject) => { 
		app.$http.post(post.url,{
			encryptedData: post.encryptedData,
			iv: post.iv,
			change: post.change ? post.change : ''
		}).then((res)=>{
				uni.hideLoading();
				console.log(">>>>>>>>>授权手机返回",res);
				if(res.code==1){
					resolve(res.data)
				}else{
					reject(res.msg)
				}				
		}).catch((err)=>{
			uni.hideLoading();
			reject(err)
		});
	})
}
/**
 * 跳转到手机登录页面
 * 
 */
const hrefMobileLoginPage = function(){
	var pageobj = getCurrentPages()
	var nowpageobj=pageobj[pageobj.length-1]
	//console.log(nowpageobj)
	var param=''
	for(var i in nowpageobj.options){
		var item= nowpageobj.options[i]?nowpageobj.options[i].trim():''
		param+= '&'+i+ '='+ item
	}
	if(param){
		param='?'+ param.substr(1)
	}
	uni.setStorageSync('prepage_forMobileLoginPage',nowpageobj.route+param);
	
	uni.redirectTo({
        url: '/pages/authorize/mobile' 
    })
}
/**
 * 跳转回之前发起手机登录的页面
 * 
 */
const hrefPrepage_forMobileLoginPage = function(){
	var preurl=uni.getStorageSync('prepage_forMobileLoginPage');
	if(preurl){
		uni.removeStorageSync('prepage_forMobileLoginPage')
	}else{
		console.log('url参数缺失')
	}

	preurl= '/'+ preurl.replace('.html','')
	switch (preurl) {
		case '/pages/my/my':
			uni.reLaunch({
				url: preurl
			})
			break;

		default:
			uni.redirectTo({
				url: preurl
			})
			break;
	}
}


/**
 * 进行手机登陆
 * 
 */
const toMobileLogin = function(app = getApp()){
	if(!app.globalData.user_id){
		hrefMobileLoginPage()
	}
}


const loginByAccount = function(appObj = getApp(), post={}){
	if(!post.url){ post.url = 'public/doLogin' }
	if(!post.hasOwnProperty('_$resetSid')){
		post._$resetSid = 1
	}
	if(post._$resetSid==1){
		//重置头部携带的token和sid
		getToken(null)
		getSid(null)
	}
	return new Promise((resolve,reject)=>{
		requests('post',{
			host: appObj.globalData.host,
			url: 'public/doLogin',
			data: post.data,
			success: function(rs){
				resolve(rs.data)
			},
			fail:function(){
				reject()
			}
		})
	})
	
}

module.exports = {
	loginByAccount,
	authLogin,
	loadSetting,
	getMobile,
	//hrefMobileLoginPage,
	hrefPrepage_forMobileLoginPage,
	toMobileLogin,
	hasOnceLoginAuth
}