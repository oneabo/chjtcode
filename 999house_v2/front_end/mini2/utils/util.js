//const wxAuth = require('/module/WxAuth.js');

const localStore = require('./module/localStore.js');
const mapLocation = require('./module/mapLocation.js');
import {openPage} from './module/openPage.js';
import {authLogin as wxMiniAuthLogin,getMobile as getMiniMobile} from './module/WxMiniAuth.js';
import {getSid, getToken, getUserInfoByCache,} from './http.interceptor.js';
import httpInterceptor from '@/utils/http.interceptor.js';
const getTagsText = function(e){
	let app = getApp();
	const allTags = app.globalData.constList;
	let update =0;
	let list_obj = {
		estatesnew_sale_status: [],
		house_purpose: [],
		feature_tag: [],
	}
	
	for(let i in e){
		let item = e[i]
		if(!item){ item = [];}
		let res = formatItem(allTags,i,item, update);
		list_obj[i] = res.list
		if(res.update==1){
			update = 1;
		}
	}
	
	let tagarr = [];
	if(list_obj.estatesnew_sale_status.length){
		tagarr = tagarr.concat(list_obj.estatesnew_sale_status);
	}
	if(list_obj.house_purpose.length){
		tagarr = tagarr.concat(list_obj.house_purpose);
	}
	if(list_obj.feature_tag.length){
		tagarr = tagarr.concat(list_obj.feature_tag);
	}
	
	if(update==1){
		app.getConst(1)
	}
	return tagarr;
	
	function formatItem(tag, key, list, update){
		let arr = []
		// console.log(tag, key, list,9999)
		if(typeof(list)!='object'){
			list = String(list)
			list = list.split(',')
		}
		
		for(let i in list){
			let item = list[i]
			if(tag[key]&&tag[key]){
				if(tag[key][item]){
					arr.push(tag[key][item]);
				}else{
					update = 1
				}
			}
		}
		return {
			list: arr,
			update
		};
	}
}
// 8-新房列表数据处理
const createHouseList = ( data, type )=>{
	const list = data.list;
	let app = getApp();
	const tag = app.globalData.constList;
	let allTag_update = 0;//标识是否存在新的用于下次标签更新
	let arr = [];
	
	for(let i in list){
		let item = list[i];
		const cover = ( item.detail_cover && item.logo ) ? 1 : 0;

		const obj = {
			id: item.id,
			type: 8,
			info: {
				id: item.id,
				name: item.name,
				tip: [],
				price: item.price,
				site: item.area_str + ' ' + item.business_area_str,
				area: item.built_area,
				lab: [],
				lng: item.lng,
				lat: item.lat,
			},
			cover: cover,
			img: [item.list_cover]
		};

		if(tag&&tag.estatesnew_sale_status&&tag.estatesnew_sale_status[item.sale_status]){
			obj.info.tip.push( tag.estatesnew_sale_status[item.sale_status] );
		}else{
			allTag_update = 1;
		}
		
		let house_purpose_arr = [];
		if(typeof(item.house_purpose)!='object'){
			item.house_purpose&&house_purpose_arr.push(item.house_purpose)
		}else{
			house_purpose_arr = item.house_purpose
		}
		house_purpose_arr.map( tip=>{
			if(tag&&tag.house_purpose&&tag.house_purpose[tip]){
				obj.info.tip.push( tag.house_purpose[tip] );
			}else{
				allTag_update = 1;
			}
		})
		item.feature_tag.map( tip=>{
			if(tag&&tag.feature_tag&&tag.feature_tag[tip]){
				obj.info.tip.push( tag.feature_tag[tip] );
			}else{
				allTag_update = 1;
			}
		})
		
		item.selling_point.map( point=>{
			let objP = {
				name: point.title
			}
			
			objP.type = point.type == 'hot' ? 0 : 1;
			
			obj.info.lab.push(objP);
		})
		
		arr.push(obj);
	}
	

	if( type == 1 && data.adv_list && data.adv_list.length > 0 ) {
		const ad = data.adv_list;
		let arr2 = [];
		let adIndex = 0;
		
		arr.map( (item,index)=>{
			arr2.push(item)
			
			let condition;
			
			if( (index+1)%6 == 3 ){
				condition = true;
			} else {
				condition = false;
			}
			// console.log(index+1,condition)
			
			if( ad[adIndex] && condition ){
				let adItem = ad[adIndex];
				
				if( ad[adIndex].type == 4 ) {
					let house_purpose = [];
					let obj = {
						id: adItem.id,
						type: adItem.type,
						info: {
							name: adItem.info.name,
							tip: [],
							estate_id:adItem.info.estate_id,
							cover:adItem.info.cover,
							price: adItem.info.price,
							site: adItem.info.site,
							area: adItem.info.area,
							lab: []
						},
						title: adItem.title,
						img:  adItem.img
					} 
					
					if(adItem.info && adItem.info.sale_status){
						if(tag&&tag.estatesnew_sale_status&&tag.estatesnew_sale_status[adItem.info.sale_status]){
							house_purpose.push( tag.estatesnew_sale_status[adItem.info.sale_status] );
						}else{
							allTag_update = 1;
						}
					}
					
					if( adItem.info.house_purpose.length > 0 ) {
						adItem.info.house_purpose.map( tip=>{
							if(tag&&tag.house_purpose&&tag.house_purpose[tip]){
								house_purpose.push( tag.house_purpose[tip] );
							}else{
								allTag_update = 1;
							}
						})
					}
					
					if( adItem.info.tip.length > 0 ) {
						adItem.info.tip.map( tip=>{
							if(tag&&tag.feature_tag&&tag.feature_tag[tip]){
								house_purpose.push( tag.feature_tag[tip] );
							}else{
								allTag_update = 1;
							}
						})
					}
					
					obj.info.tip = house_purpose;
					
					if( adItem.info.lab.length > 0 ){
						adItem.info.lab.map( point=>{
							let objP = {
								name: point.title
							}
							
							objP.type = point.type == 'hot' ? 0 : 1;
							
							obj.info.lab.push(objP);
						})
					}
					
					arr2.push( obj );
				} else {
					arr2.push( ad[adIndex] );
				}
				
				adIndex += 1;
			}
		});
		
		arr = arr2;
	}

	
	if(allTag_update==1){
		app.getConst(1)
	}

	return arr;
}


// 深度克隆
const deepClone = (obj) => {
	if (typeof obj !== "object" && typeof obj !== 'function') {
		//原始类型直接返回
		return obj;
	}
	var o = isArray(obj) ? [] : {};
	for (let i in obj) {
		if (obj.hasOwnProperty(i)) {
			o[i] = typeof obj[i] === "object" ? deepClone(obj[i]) : obj[i];
		}
	}
	return o;
}

const isArray = (arr) => {
	return Object.prototype.toString.call(arr) === '[object Array]';
}

// 判断是否为空
const isEmpty = (value) => {
	switch (typeof value) {
		case 'undefined':
			return true;
		case 'string':
			if (value.replace(/(^[ \t\n\r]*)|([ \t\n\r]*$)/g, '').length == 0) return true;
			break;
		case 'boolean':
			if (!value) return true;
			break;
		case 'number':
			if (0 === value || isNaN(value)) return true;
			break;
		case 'object':
			if (null === value || value.length === 0) return true;
			for (var i in value) {
				return false;
			}
			return true;
	}
	return false;
}

// 文字去除空格
const trim = (str, pos = 'both') => {
	if(!str){
		return '';
	}
	if (pos == 'both') {
		return str.replace(/^\s+|\s+$/g, "");
	} else if (pos == "left") {
		return str.replace(/^\s*/, '');
	} else if (pos == 'right') {
		return str.replace(/(\s*$)/g, "");
	} else if (pos == 'all') {
		return str.replace(/\s+/g, "");
	} else {
		return str;
	}
}

// html特殊字符转换
const htmlEscape = (val) => {
	return val.replace(/[<>&"]/g, function(c) {
		return {
			'<': '&lt;',
			'>': '&gt;',
			'&': '&amp;',
			'"': '&quot;'
		} [c];
	});
}
// 删除url某项参数 返回 新url + 参数对象
	const funcUrlDel = (names) => {
		if (typeof(names) == 'string') {
			names = [names];
		}
	
		const loca = window.location;
		const obj = {}
		const arr = loca.search.substr(1).split("&");
		const newData = {};
	
		//获取参数转换为object
		for (var i = 0; i < arr.length; i++) {
			arr[i] = arr[i].split("=");
			obj[arr[i][0]] = arr[i][1];
		};
	
		//删除指定参数
		if (names) {
			for (let i = 0; i < names.length; i++) {
				delete obj[names[i]];
			}
		}
	
		// 排序
		Object.keys(obj).sort().map(key => {
			newData[key] = decodeURI(obj[key])
		})
	
		//重新拼接url
		const url = loca.origin + loca.pathname + "?" + JSON.stringify(newData).replace(/[\"\{\}]/g, "").replace(/\:/g,
			"=").replace(/\,/g, "&");
	
		return {
			url: url,
			option: newData
		};
	}
// 验证手机格式
function isMobile(value) {
	return /^1[23456789]\d{9}$/.test(value)
}

// 手机号加密
const encryptPhone = (phone) => {
	if(!phone){
		return '';
	}
	phone = String(phone);

	return phone.replace(phone.substring(3, 7), "****");
}

/**
 * 时间格式化
 * timestamp时间戳
 * fmt时间格式，可选。
 * 默认为yyyy-mm-dd，年为"yyyy"，月为"mm"，日为"dd"，时为"hh"，分为"MM"，秒为"ss"，格式可以自由搭配
 * 如： yyyy:mm:dd，yyyy-mm-dd，yyyy年mm月dd日，yyyy年mm月dd日 hh时MM分ss秒，yyyy/mm/dd/，MM:ss等组合
 * 
 */
const timeFormat = (timestamp = null, fmt = 'yyyy-mm-dd') => {
	// 如果为null,则格式化当前时间
	if (!timestamp){
		timestamp = Number(new Date())
	}else{
		// 其他更多是格式化有如下:
		// yyyy:mm:dd|yyyy:mm|yyyy年mm月dd日|yyyy年mm月dd日 hh时MM分等,可自定义组合
		if (!(typeof timestamp === 'number' && !isNaN(timestamp))){return timestamp};
		timestamp = parseInt(timestamp);
	}
	
	// 判断用户输入的时间戳是秒还是毫秒,一般前端js获取的时间戳是毫秒(13位),后端传过来的为秒(10位)
	if (timestamp.toString().length == 10) timestamp *= 1000;
	let date = new Date(timestamp);
	let ret;
	let opt = {
		"y+": date.getFullYear().toString(), // 年
		"m+": (date.getMonth() + 1).toString(), // 月
		"d+": date.getDate().toString(), // 日
		"h+": date.getHours().toString(), // 时
		"M+": date.getMinutes().toString(), // 分
		"s+": date.getSeconds().toString() // 秒
		// 有其他格式化字符需求可以继续添加，必须转化成字符串
	};
	for (let k in opt) {
		ret = new RegExp("(" + k + ")").exec(fmt);
		if (ret) {
			fmt = fmt.replace(ret[1], (ret[1].length == 1) ? (opt[k]) : (opt[k].padStart(ret[1].length, "0")))
		};
	};
	return fmt;
}
//防抖
const debounce = (func, wait = 500, immediate = false)=>{
	// 清除定时器
	if (timeout !== null) clearTimeout(timeout);
	// 立即执行，此类情况一般用不到
	if (immediate) {
		var callNow = !timeout;
		timeout = setTimeout(function() {
			timeout = null;
		}, wait);
		if (callNow) typeof func === 'function' && func();
	} else {
		// 设置定时器，当最后一次操作后，timeout不会再被清除，所以在延时wait毫秒后执行func回调方法
		timeout = setTimeout(function() {
			typeof func === 'function' && func();
		}, wait);
	}
}

/**
 * 点击显示图片(小程序拿不到值，需在相应vue文件中编写函数)
 * showImg(item, index){
		return this.$api.showImg(item, index);
	}
 */
const showImg = (item, index=0) => {
	let obj = {};
	const arr = [];
	console.log(item)
	if( typeof(item) != 'object' ){
		item = [item];
	}
	console.log(item);
	item.map( newItem=>{
		const img = imgDirtoUrl(newItem);
		
		arr.push(img);
	})

	obj = {
		urls: arr,
		current: 0,
	};
	
	if( index ){
		obj.current = index;
	}

	uni.previewImage(obj)
}

/**
 * type -> 不传(网络图)/ 1(静态)
 */
const imgDirtoUrl = function(url,type){
	let app = getApp();
	if(!url){
		return url;
	}
	
	if(url.indexOf('http') == '0'){
		return url;
	}else{
		if( url && url[0] != '/' ){
			url = '/' + url;
		}
		if(type == 2 && url.indexOf('/9house/static') != -1){
			return app.globalData.host+url
		}
		if( type == 1 || url.indexOf('upload') == -1){
			return app.globalData.imgHost + url;
		}
		
		return app.globalData.host + url;
	}
}


/**
 * 获取微信启动时候的信息
 */
function getLaunchData() {
	let launchObj = wx.getLaunchOptionsSync() //小程序启动时的参数
	let $url = '/' + launchObj.path //开始启动时要打开的页面
	let page_parms = '' //开始启动时要打开的页面的参数
	for (let i in launchObj.query) {
	  page_parms = page_parms + [i] + '=' + launchObj.query[i] + '&'
	}
	if (page_parms) {
	  page_parms = page_parms.substr(0, page_parms.length - 1)
	  page_parms = '?' + page_parms
	}
	$url = $url + page_parms
	return {
	  ...launchObj,
	  $url: $url
	}
  }
 
/**
 * 小程序获取分享信息(接口获取)
 */
function shareInfo(res) {
	const share = {
		title: res.data.share_title,
		imageUrl: imgDirtoUrl(res.data.share_ico),
	}
	
	console.log(imgDirtoUrl(res.data.share_ico))
	
	return share;
	
}
const getUrlParamValue = (name)=>{
		if (name == null || name == 'undefined') { return null };
		//
		var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);//search,查询？后面的参数，并匹配正则
		// console.log(r)
		if(r!=null){
			return decodeURIComponent(r[2])
			//return  unescape(r[2]);
		}else{
			return null;
		}
	};
var isMini = getUrlParamValue('_isMini')?true : false;
//判断是否在微信中
const isWechat = ()=>{
	const ua = window.navigator.userAgent.toLowerCase();

	if (ua.match(/micromessenger/i) == 'micromessenger') {
		// console.log('是微信客户端')
		return{
			inWechat: true,
			isMini: isMini //true 小程序,false h5
		}
	} else {  
		// console.log('不是微信客户端')  
		return{
			inWechat: false, 
			isMini: false
		}
	}
};
/**
 * 微信h5登录
 * @param {*} reflash 是否强制刷新登录
 */
let loging = false;

const wxLogin = async ( reflash=0 ,call)=>{
	let inWechat = isWechat().inWechat
	let isMini = isWechat().isMini

	if( false == inWechat || true == isMini  ){	// 微信客户端
		return
	}
	let token = localStore.localGet('token');
	
	//let token = sessionStorage.getItem('token');
	if(reflash!=1&&token){//已经登录
		return;
	}
	let href =  window.location.href;
	
	let code 	= getUrlParamValue('code');
	let state 	= getUrlParamValue('state');
	console.log('reflash',reflash)
	console.log('code',code)
	console.log('state',state)
	

	if( reflash==1 || !code || !state ){
		if(loging==true){
			return
		}
		loging = true;
		
		let href =  window.location.href;
		window.location.href = getApp().globalData.host+'/index/public/wxLogin'+'?redirect_uri='+encodeURI(href);
		
	}else{
		let sendData = {
			code: code,
			state: state,
		};
		uni.request({
			method: 'GET',
			url: getApp().globalData.host+'/index/public/wxH5UserLogin',
			data: sendData,
			success:(res)=>{
				let data = res.data.data;
				let time = (data.expire_time -  Math.round(new Date().valueOf()/1000)) * 1000;
				
				sessionStorage.setItem('token',data.token)
				localStore.localSet('token',data.token,time ); //设置缓存
				localStore.localSet('is_login',data.is_login,time);
				localStore.localSet('user_info',{
					user_avatar: data.user_avatar,
					user_name: data.user_name,
					user_id: data.id
				});
				uni.$emit('h5login')
				call&&call(data.is_login);
			}
		})
	}

	
};

module.exports = {
	getTagsText,
	createHouseList,
	mapLocation,
	localStore,
	deepClone,
	isArray,
	isEmpty,
	trim,
	htmlEscape,
	funcUrlDel,
	isMobile,
	encryptPhone,
	timeFormat,
	debounce,
	showImg,
	imgDirtoUrl,
	openPage,
	wxMiniAuthLogin,
	getLaunchData,
	getToken,
	getSid,
	getUserInfoByCache,
	wxLogin,
	getMiniMobile,
	shareInfo,
}