
// const domain = 'https://act.999house.com/';
const domain = 'https://mo.999house.com/';
// const domain = 'http://999house.test.com/';

const that = this;
var $wx_logining = false;

const $http = (function() {

	const request = (options)=>{
		options = options ||{};  //调用函数时如果options没有指定，就给它赋值{},一个空的Object
		options.method = (options.method || "GET").toUpperCase();  // 请求格式GET、POST，默认为GET
		options.dataType = options.dataType || "json";   //响应数据格式，默认json
		options.timeout = options.timeout || 10000;

		let xhr,
			params = formatParams(options.data);	//options.data请求的数据
		
		//考虑兼容性
		if(window.XMLHttpRequest){
			xhr=new XMLHttpRequest();
		}else if(window.ActiveObject){	//兼容IE6以下版本
			xhr=new ActiveXobject('Microsoft.XMLHTTP');
		}
		
		//启动并发送一个请求
		if(options.method=="GET"){
			xhr.open("GET", options.url+"?" + params, true);

			if( options.header ){
				for( let key in options.header ){
					xhr.setRequestHeader(key, options.header[key]);
				}
			}
	
			xhr.send(null);
		}else if(options.method=="POST"){
			xhr.open("post", options.url, true);

			// 设置表单提交时的内容类型
			// Content-type数据请求的格式
			xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

			if( options.header ){
				for( let key in options.header ){
					xhr.setRequestHeader(key, options.header[key]);
				}
			}

			xhr.send(params);
		}

		// 设置有效时间
		setTimeout(function(){
			if(xhr.readySate !=4 ){
				xhr.abort();
			}
		},options.timeout);

		// 接收
		// options.success成功之后的回调函数  options.error失败后的回调函数
		// xhr.responseText,xhr.responseXML  获得字符串形式的响应数据或者XML形式的响应数据
		xhr.onreadystatechange=function(){
			if(xhr.readyState == 4){
				let status = xhr.status;
				
				if(status >= 200 && status < 300 || status == 304){
					options.success && options.success(xhr.responseText,xhr.responseXML);
				}else{
					options.error&&options.error(status);
				}
			}
		}
	};
	
	
	const ajax = ( params )=>{
		return new Promise( (resolve,reject) =>{ 
			if($wx_logining==true&&!['/index/public/wxH5UserLogin'].includes(params.url)){console.log($wx_logining)
				//正在微信登录时不发起请求
				return;
			}

			let token = getLocal('token');
			let cityNo = getLocal('u-cityNo');
			
			let inWechat = isWechat().inWechat;
			let isMini = isWechat().isMini;
			let device_type = 'h5';
			if(true ==  inWechat&&isMini == false){	// h5微信客户端
				device_type = 'wxh5';
			}
			
			let { 
				url,
				method = 'POST', 
				data, 
				header = {
					'XX-CityNo': cityNo,
					'XX-Token': token,
					'XX-Device-Type': device_type
				},
				timeout = 10000
			} = params;

			url = domain + url;

			request({
				url: url,
				method: method,
				data: data,
				dataType:'json',
				timeout: timeout,
				header: header,
				contentType:"application/json",
				success:function(res){
					res = JSON.parse(res);

					if( res.code != 1 ){
						// console.log(res)
						if( res.code == '50008' ){
							localStorage.removeItem('token');
							localStorage.removeItem('is_login');
							
							if(true ==  inWechat){// 微信客户端
								wxLogin(1)
								return
							}else{
								hrefMobileLogin();
								return;
							}
						} else {
							//vant.Toast(res.msg)
							reject(res)
						}
					} else {
						//console.log(res,6666)
						// vant.Toast(res.msg)
						resolve(res);
					}
					
				},
				//异常处理
				error:function(res){
					//vant.Toast(res.msg)
					reject(JSON.parse(res));
				}
			})

		})
	};
	

	/**
	 * 微信登录
	 * @param {*} reflash 是否强制刷新登录
	 */
	const wxLogin = async ( reflash=0 ,call)=>{
		let inWechat = isWechat().inWechat
		let isMini = isWechat().isMini

		if( false == inWechat || true == isMini  ){	// 微信客户端
			return
		}

		let token = getLocal('token');
		if(reflash!=1&&token){//已经登录
			return;
		}

		let code 	= getUrlParamValue('code');
		let state 	= getUrlParamValue('state');
		if( reflash==1 || !code || !state ){
			let href =  window.location.href;
			window.location.href = domain+'/index/public/wxLogin'+'?redirect_uri='+encodeURI(href);
		}else{
			let sendData = {
				code: code,
				state: state,
			};
			
			ajax({
				method: 'GET',
				url: '/index/public/wxH5UserLogin',
				data: sendData,
			}).then( res=>{
				let data = res.data;
				let time = (data.expire_time -  Math.round(new Date().valueOf()/1000)) * 1000;
				setLocal('token',data.token,time ); //设置缓存
				setLocal('is_login',data.is_login,time);
				setLocal('user_info',{
					user_avatar: data.user_avatar,
					user_name: data.user_name,
					user_id: data.id
				});
				call&&call(data.is_login);
			}).catch( res=>{
				if(res.msg&&(res.msg.indexOf('40029-invalid')!=-1||res.msg.indexOf('40163-code')!=-1)){
					wxLogin(1)
					return;
				}
				vant.Toast('登录失败')
			})
		}

		
	};

	const mobileLogin = function(sendData,type=1,url,jumpDate){
		ajax({
			method: 'POST',
			url: '/index/public/mobileLogin',
			data: sendData,
		}).then( res=>{
			let data = res.data;
			let time = (data.expire_time -  Math.round(new Date().valueOf()/1000)) * 1000;
			setLocal('token',data.token,time ); //设置缓存
			setLocal('is_login',1,time ); //手机号已登录
			setLocal('user_info',{
				user_avatar: data.user_avatar,
				user_name: data.user_name,
				user_id: data.id
			})
			
			if(type ==1){
				$api.goPage('my/index.html');
			}else{
				//console.log(url)
				window.history.back(-1);
				//window.location.href = url;
				// $api.goPage(url,jumpDate);
			}

		}).catch( res=>{
			vant.Toast(res.msg)
		})
	}

	const sendMsg = function(sendData){
		ajax({
			method: 'POST',
			url: '/index/public/sendMsg',
			data: sendData,
		}).then( res=>{
			let data = res.data;
			console.log(res);
		}).catch( res=>{
			console.log(res)
		})
	}

	//格式化请求参数
	const formatParams = (data)=>{
		let arr=[];
		for(let name in data){
			if(typeof(data[name])=='object'){
				for(let j in data[name]){
					arr.push(encodeURIComponent(name)+"[]="+encodeURIComponent(data[name][j]));
				}
				
			}else{
				arr.push(encodeURIComponent(name)+"="+encodeURIComponent(data[name]));
			}
		}
		
		arr.push(("v="+Math.random()).replace(".",""));
		
		return arr.join("&");
	}
	
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
	
	const imgDirtoUrl = (url) =>{
		if(!url){
			return url;
		}

		if(url.indexOf('http:') ==-1 && url.indexOf('https:') ==-1){
			return  domain + url;
		}else{
			return  url;
		}
	};

	// 登录时调用共用接口存取
	const publicFun = (e) =>{
		const tag = $api.localGet('u-tag');
		let allTag_update = $api.localGet('u-tag-update');

		if( tag && allTag_update!=1){
			return true;
		}
		
		// 获取公共常用列表
		if( !e ){
			ajax({
				method: 'GET',
				url: '/index/estates/getConst',
			}).then( res=>{
				let data = res.data;
				$api.localSet('u-tag',data, 3600*2)
			})
		} else {
			return new Promise((resove,reject)=>{
				ajax({
					method: 'GET',
					url: '/index/estates/getConst',
				}).then( res=>{
					let data = res.data;
					$api.localSet('u-tag',data, 3600*2)
					$api.localSet('u-tag-update',0);
					resove(data);
				}).catch( rej=>{
					reject(rej);
				})
			})
		}
	}
	
	const setLocal =(key,value,expires)=>{
			let params = { key, value, expires };
			if (expires) {
				// 记录何时将值存入缓存，毫秒级
				var data = Object.assign(params, { startTime: new Date().getTime() });
				localStorage.setItem(key, JSON.stringify(data));
			} else {
				if (Object.prototype.toString.call(value) == '[object Object]') {
					value = JSON.stringify(value);
				}
				if (Object.prototype.toString.call(value) == '[object Array]') {
					value = JSON.stringify(value);
				}
				localStorage.setItem(key, value);
			}
	}

	const getLocal =(key)=>{
		let item = localStorage.getItem(key);
		// 先将拿到的试着进行json转为对象的形式
		try {
			item = JSON.parse(item);
		} catch (error) {
			// eslint-disable-next-line no-self-assign
			item = item;
		}
		// 如果有startTime的值，说明设置了失效时间
		if (item && item.startTime) {
			let date = new Date().getTime();
			// 如果大于就是过期了，如果小于或等于就还没过期
			if (date - item.startTime > item.expires) {
				localStorage.removeItem(name);
				return false;
			} else {
				return item.value;
			}
		} else {
			return item;
		}
	}
	
	//是否登录
	const isLogin = ()=>{
	  let token  = 	getLocal('is_login');
	  if(token){
	  	return true;
	  }else{
	  	return  false;
	  }
	}

	const hrefMobileLogin = (alerMsg=true)=>{
		if( !isLogin()){
			if(alerMsg==true){
				vant.Dialog.confirm({
					title: '提示',
					message: '请登录后再进行操作',
				}).then(() => {
					$api.localSet('pre-page',window.location.href);
					$api.goPage('my/login.html');
				}).catch(()=>{

				});
			}else{
				$api.localSet('pre-page',window.location.href);
				$api.goPage('my/login.html');
			}
		}
	}
	
	// 检测图片链接是否有域名
	const testUrl = ( img )=>{
		if( img ){
			if( img.indexOf('http') == -1 ){
				img = domain + img;
			}
			
			return img;
		} else {
			return '';
		}
	}
	
	// 点击显示图片
	const showImg = (item, index) => {
		let obj = {};
		const arr = [];
		
		if( typeof(item) != 'object' ){
			item = [item];
		}
		
		item.map( newItem=>{
			arr.push(testUrl(newItem));
		})
		
		obj = {
			images: arr,
			swipeDuration: 300,
			loop: false
		};
		
		if( index ){
			obj.startPosition = index;
		}
		
		vant.ImagePreview(obj);
	}
	
	
	//根据地图定位当前城市
	let is_alert_msg = 0;
	const getMapCity = ()=> {
		return new Promise((resolve,reject)=>{
			const map = new AMap.Map("container-user-site");
			const cityList = $api.localGet('city-list');
			if(!map){
				resolve('');
				this.$toast('抱歉地图定位失败，请检查相应权限');
				return
			}

			map.plugin('AMap.CitySearch', function () {
				console.log(2)
				var citySearch = new AMap.CitySearch()
				citySearch.getLocalCity(function (status, result) {
				  if (status === 'complete' && result.info === 'OK') {
					// 查询成功，result即为当前所在城市信息
					let city = getCity(result.city, cityList.all)
					console.log(city)
					resolve(city)
				  }else{
					if(status == 'error'){
						if(result.message&&result.message.indexOf('permission denied')!=-1){
							if(is_alert_msg==0){
								alert('抱歉地图定位失败，请检查相应权限，将切换为默认城市厦门');
								is_alert_msg = 1;
							}
							
							resolve('')
						}
					}
				  }
				})
			})
			
			function getCity(city,cityList){
				console.log(city,cityList)
				city = city.replace('市','');
				let has = 0;
				let obj = { }
				cityList.map( item=>{
					if( city&&city == item.cname ){
						obj = {
							city_no: item.id,
							city_name: item.cname
						}
						has = 1;
						return;
					}
				})
				
				if(has == 0){
					return '';
				}else{
					return obj
				}
			}
		})
	}
	
	//  从缓存获取当前用户城市
	const getCurrentCity = (val)=> {
		let key = 'current_city';

		if(isMini){
			let _city_no = getUrlParamValue('_city_no')//小程序时
			let _city_name = getUrlParamValue('_city_name')//小程序时
			console.log(_city_name)
			if(_city_no&&_city_name){
				return new Promise((resolve,reject)=>{
					let obj = {
						city_no: _city_no,
						city_name: _city_name
					}
					resolve(obj)
					$api.localSet(key, obj, 3600*2);
				})
			}
		}
		
		const that = this;
		const cityList = $api.localGet('city-list', 3600*2);
		if(val){
			//@todo 根据用户当前经纬获取
			if(!val.city_no||!val.city_name){
				throw('参数格式错误');
			}
			$api.localSet(key, val, 3600);
			return;
		}
		
		let res = $api.localGet(key);
		
		return new Promise((resolve,reject)=>{
			if(!res){
				if( !cityList ){
						ajax({
							url: '/index/City/getCityList',
						}).then( res=>{
							// console.log( res.data)
							let hotList = [];
							let allCity =[];
							let obj;
							
							res.data&&res.data.map((item)=>{
								item.cname = item.cname.replace('市','');
								item.is_hot&&hotList.push(item);
								allCity.push(item);
							});
							
							obj = {
								hot: hotList,
								all: allCity,
							};
							
							$api.localSet('city-list', obj, 3600*8);
							
							getMapCity().then( data=>{
								if(!data||!data.city_no){
									data = {
										city_no: 350200,
										city_name: '厦门'
									}
								}

								resolve(data)
								$api.localSet(key, data, 3600*8);
							}).catch( data=>{
								data = {
									city_no: 350200,
									city_name: '厦门'
								}

								resolve(data)
								$api.localSet(key, data, 3600*8);
							})
							
						})
				} else {
					getMapCity().then( data=>{
						if(!data||!data.city_no){
							data = {
								city_no: 350200,
								city_name: '厦门'
							}
						}

						resolve(data)
						$api.localSet(key, data, 3600*8);
					}).catch( data=>{
						data = {
							city_no: 350200,
							city_name: '厦门'
						};

						resolve(data)
						$api.localSet(key, data, 3600*8);
					})
				}
			}else{
				let update_local = 0;
				if(!res||!res.city_no){
					res = {
						city_no: 350200,
						city_name: '厦门'
					}
					update_local =1;
				}

				resolve(res)

				if(update_local==1){
					$api.localSet(key, res, 3600*8);
				}
			}
		})
		
	}
	
	return {
		ajax,
		mobileLogin,
		getLocal,
		setLocal,
		isLogin,
		hrefMobileLogin,
		wxLogin,
		isWechat,
		getUrlParamValue,
		sendMsg,
		imgDirtoUrl,
		showImg,
		publicFun,
		testUrl,
		getMapCity,
		// 获取当前城市
		getCurrentCity,
	};
}());

const http = {
	install: function(Vue) {
		Vue.prototype.$http = $http;
    },
}

// 登录混入
const loginMixin = {
	data(){
		return {
			mobile: '',
			code: '',
			city_no: 350200,
		}
	},
	created (){
		$http.publicFun();
		$http.getCurrentCity().then( data=>{
			if(data&&data.city_no){
				this.city_no = data.city_no;
			}
		}).catch(()=>{ });


		//微信h5微信没登录进行登录
		if(!$http.getLocal('token') ){
			let inWechat = $http.isWechat().inWechat
			let isMini = $http.isWechat().isMini
	
			if(false == inWechat|| true == isMini){
				return
			}

			$wx_logining = true;
			$http.wxLogin(0,(e)=>{
				$wx_logining = false;
				
				this.$options.created[1].call(this);
				
				if( e == 1 ){
					const url = this.$api.localGet('pre-page');
					this.$api.localDel('pre-page');
					
					if(url){
						window.location.href = url;
					}
				}
			});
		}
	},
	methods:{
		mobileLogin(type,url,data){
			let token = $http.getLocal('token');
			return $http.mobileLogin({mobile:this.mobile,code:this.code,token:token},type,url,data);
		},
		/**
		 *
		 * @param data obj
		 *
		 */
		wxShare( res , point ) {
			let inWechat = $http.isWechat().inWechat;
			let isMini = $http.isWechat().isMini;
			if(false==inWechat || true == isMini){
				return
			}
			const share = {
				title: res.data.share_title,
				desc: res.data.share_desc,
				img: res.data.share_ico,
				link: ''
			}
			
			if( point ) {
				share.link = domain + point;
			} else {
				share.link = window.location.href;
			}
			
			this.wxConfig().then( e=>{
				setTimeout(()=>{
					this.wxShareType(share);
				},300)
			});
		},
		wxConfig(){
			return new Promise((resolve,reject)=>{
				const data = {
					url: window.location.href,
					city_no: this.city_no,
				}
				
				this.$http.ajax({
					data: data,
					url:'/index/public/getWebInfo'
				}).then(res=>{
					if(res.code ==1){
						console.log(res);
						let config = res.data;
						
						wx.config({
							debug: false, // 是否开启调试模式
							appId: config.appid, //appid
							timestamp: config.timestamp, // 时间戳
							nonceStr: config.noncestr, //
							signature: config.signature, // 签名
							jsApiList: [
								'updateAppMessageShareData',
								'updateTimelineShareData'
							] // 需要使用的JS接口列表
						});
						
						resolve('OK');
					}else{
						res.msg&&this.$toast(res.msg);
					}
				}).catch(res=>{
					res.msg&&this.$toast(res.msg);
				});
			})
		},
		wxShareType(data) {
			let imgUrl =  this.$http.imgDirtoUrl(data.img);
			
			wx.ready(function () {
				wx.updateAppMessageShareData({
					title: data.title, // 分享标题
					desc: data.desc, // 分享描述
					link: data.link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
					imgUrl: imgUrl, // 分享图标
					success: function () {
						console.log('分享朋友设置成功')
					}
				})
				
				wx.updateTimelineShareData({
				    title: data.title, // 分享标题
				    desc: data.desc, // 分享描述
				    link: data.link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
				    imgUrl: imgUrl, // 分享图标
				    success: function () {
						console.log('分享朋友圈设置成功')
				    }
				 })
			});
		},
	}
}

export { http, loginMixin }; 
