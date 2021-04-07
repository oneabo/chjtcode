import localStore from './module/localStore.js'

var $http = null;
var app = null;

const getCommonParms = function(key='',val='',expire=3600*1.5){
	let parms ='';
	// let local_key = 'http-'+key+'-key'
	let local_key = key

	if(val !== null){
		if(val){
			parms = val;
			localStore.localSet(local_key, parms, expire);
		}else{
			if(app&&app.globalData&&app.globalData[key]){
				parms = app.globalData[key];
			}else{
				let res_local = localStore.localGet(local_key)
				parms = res_local ? res_local : '';
			}
		}
	}else{//null为重置
		localStore.localDel(local_key);
	}

	app.globalData[key] = parms;
	return parms;
}
const getToken = (val='')=>{
	return getCommonParms('token', val, 3600*1.5);
}
const getSid = (val='')=>{
	return getCommonParms('sid', val, 3600*1.5);
}
const getUserInfoByCache = (val='')=>{
	return getCommonParms('userInfo', val, 3600*1.5);
}



let config = {
	baseUrl: '', // 请求的域名
	method: 'POST',
	// 设置为json，返回后会对数据进行一次JSON.parse()
	dataType: 'json',
	// showLoading: true, // 是否显示请求中的loading
	// loadingText: '请求中...', // 请求loading中的文字提示
	// loadingTime: 800, // 在此时间内，请求还没回来的话，就显示加载中动画，单位ms
	// originalData: false, // 是否在拦截器中返回服务端的原始数据
	// loadingMask: true, // 展示loading的时候，是否给一个透明的蒙层，防止触摸穿透
	// 配置请求头信息
	header: {
		'content-type': 'application/json;charset=UTF-8',
		'XX-Api-Version' : 1,
	},
}

//请求拦截
let request_interceptor = (config) => {
	//头部信息
	// #ifdef H5
		config.header['XX-Token'] = getToken()//sessionStorage.getItem('token');
		config.header['xx-device-type'] = 'h5'
	// #endif
	//#ifndef H5
		config.header['XX-Token'] = getToken();
		config.header['xx-device-type'] = 'mini'
	// #endif
	config.header['XX-Sid'] = getSid();
	//config.header['xx-device-type'] = "mini";
	return config; // 如果return一个false值，则会取消本次请求
	// if(config.url == '/user/rest') return false; // 取消某次请求
}
//请求返回拦截
let response_interceptor = (res, app)=>{
	if (res.code == '50008') {
		var pages = getCurrentPages();
		var currentPage = pages[pages.length-1];
		app.methods.logout()//账号登陆时退出登录
		localStore.localDel('token')
		//sessionStorage.removeItem("token")
		//白名单页面不进行弹窗
		if(app.globalData.whitePages.includes(currentPage.route)){
			uni.hideLoading()
			return res; 
		}
		//当前页面非白名单页面进行弹窗提醒//未登录提示操作
		uni.showModal({
			title: '提示',
			content: res.data.msg,
			showCancel: false,
			success (r) {
				if (r.confirm) { 
					//=========================//
					if(!app.wxAuthLogin){//未登录重新连接
						console.log('请在app.js封装wxAuthLogin用于调用WxAuth文件中的authLogin')
					}
					
					//微信登陆时执行用户久未操作断线重连
					/* app.wxAuthLogin(0).then(function(e){
						// 重新刷新此次失败的页面
						currentPage.onLoad(currentPage.options)
						currentPage.onShow()
					}) */
				 } 
			}
		})
		return false;
	} else{ 
		return res; //如果return false，则会调用Promise的reject回调，
	}
}

const install = (Vue, vm) => {
	
	let globalData = vm.$options.globalData;
	// #ifdef MP-WEIXIN
	config.baseUrl = globalData.host_api;
	// #endif
	// #ifdef H5
	config.baseUrl = globalData.host_h5_api;
	// #endif
	// config.baseUrl = globalData.host_api;
	//配置自定义参数
	Vue.prototype.$u.http.setConfig(config);
	
	Vue.prototype.$u.http.interceptor.request = request_interceptor; // 请求拦截
	Vue.prototype.$u.http.interceptor.response = (res)=>{ return response_interceptor(res, vm.$options)}; // 返回拦截
	
	Vue.prototype.$u.http.uploadFile = function(url, data, header){
		if(!data.file||!data.file.url||!data.file.name){
			console.error('file格式错误')
			return;
		}
		let obj = {
			url: config.baseUrl+url,
			filePath: data.file.url,
			name: data.file.name,
		}

		if(data.formData){
			obj.formData = data.formData;
		}

		obj.header = config.header;
		
		
		// #ifdef H5
			obj.header['XX-Token'] = sessionStorage.getItem('token');
		// #endif
		//#ifndef H5
			obj.header['XX-Token'] = getToken();
		// #endif
		obj.header['XX-Sid'] = getSid();
		if(header){
			obj.header = Object.assign(obj.header, header);
		}
		
		 uni.uploadFile(obj)
	}
	$http = Vue.prototype.$http = Vue.prototype.$u.http;
	
	app = vm.$options;
}

module.exports = {
	install,
	$http,
	getToken,
	getSid,
	getUserInfoByCache,
}