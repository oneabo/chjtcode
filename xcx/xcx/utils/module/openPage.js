//页面跳转
//bind:tap="openPage" data-page="{url:/pages/logs/logs,data:{aa:bb}}" data-hreftype

//import {Function,evaluate} from './eval5.min.js';

var btnOpenPage = false
var timer = null
const openPage = function(e={}){
	if(btnOpenPage==true){ return; }
	btnOpenPage = true
	//防止短时间内多次点击
	/* if(timer){
		clearTimeout(timer)
	} */
	timer = setTimeout(function () {
		btnOpenPage = false
	}, 300)
  
	var hreftype;
	//e是自动返回调用函数的目标对象 data-page
	if(e.currentTarget){
		var getpage = e.currentTarget.dataset.page;//目标页面
		var pagedata = e.currentTarget.dataset.pagedata;//目标页面数据,用于存储大数据时结合本地缓存
		hreftype = e.currentTarget.dataset.hreftype?e.currentTarget.dataset.hreftype:'';//跳转类型
	}
	if(e.url){//直接传入对象参数
		var getpage = e;
	}
	if(e.pagedata){
		var pagedata = e.pagedata;
	}
	if(e.hreftype){
		 hreftype = e.hreftype;
	}
	
	wx.removeStorageSync('thisPageData')
	if(pagedata){
		 wx.setStorageSync('thisPageData',pagedata);
	}
	
	var page_name='';//页面地址
	var parmsarr={};//页面参数对象
	if(typeof getpage=='object'){
		page_name = getpage.url
		if(!page_name){
			console.log('页面路径缺失')
			return
		}
		if(getpage.data){parmsarr = getpage.data;}//页面参数
	}else{
		getpage = getpage.replace(/\s*/g,"");
		if(getpage.indexOf('{url:')!='0'){
			//page_name = getpage;
			console.log('页面地址写法错误')
			return
		}	
		getpage = getpage.split(",data:{");
		page_name = getpage[0].replace(/{url:/, "")
		if(getpage.length==1){
			page_name = page_name.replace(",","");	
			page_name = page_name.replace("}","");	
		}
		if(getpage.length==2){
			var parms = getpage[1];
			if(parms.indexOf('}}')==-1){
				console.log('参数写法错误')
				return
			}
			//字符串页面参数转对象
			parms = parms.replace("}}","");	
			//console.log(parms)
			parms = parms.split(",");	
			for(var i in parms){
				var arr=parms[i].split(':');
				arr[0] = arr[0].replace(/(^\s*)|(\s*$)/g, "")
				arr[1] = arr[1].replace(/(^\s*)|(\s*$)/g, "")
				if (arr[1]){
					parmsarr[arr[0]]=arr[1];
				}
			}
		}
	}
	
	page_name = String(page_name)
	if(hreftype!='navigateBack'&&Object.keys(parmsarr).length>0){	
		parmsarr = urlEncode(parmsarr);	//转成url&参数	
		if(page_name.indexOf('?')!=-1){
				page_name = page_name+'&'+parmsarr;			
		}else{
			page_name = page_name+'?'+parmsarr;	
		}
	}
	
	//判断是否是pages/开头是的话去除
	if(hreftype!='navigateBack'){
		if(page_name.indexOf('pages/')=='0'){
			page_name = '/'+page_name
		}
		//判断是否从根开始找，若第一个是字符是/pages从根开始找
		if(page_name.indexOf('/pages')=='0'){
			var pageurl=`${page_name}`
		}else{
			var pageurl=`../${page_name}`
		}
	}

	//选择跳转类型
  	switch (hreftype) {
		case 'redirectTo':
			wx.redirectTo({
				url: pageurl
			})
			break;
		case 'reLaunch':
			wx.reLaunch({
				url: pageurl
			})
			break;
		case 'switchTab':
			wx.switchTab({
				url: pageurl
			})
			break;	
		case 'navigateBack':
			if(isNaN(page_name)||parseInt(page_name)>=0){
				console.log('页面返回层需要是小于0数字')
				return
			}else{
				page_name = parseInt(page_name);
				page_name = Math.abs(page_name);
			}			
			var pagelist = getCurrentPages();
			var prePage = pagelist[pagelist.length - (page_name +1)];
			//返回时的数据传参与上一页的调用函数navigatebackfun
     		if (prePage && typeof prePage.navigatebackfun=='function'){
				prePage.navigatebackfun(parmsarr);
			}
			
			wx.navigateBack({
				delta:page_name
			})
			break;		
		default:
			wx.navigateTo({
				url: pageurl
			});
			break;
	}
	
}

//获取页面跳转时先存的数据
const thisPageData= function(){	
	var thisPageData= wx.getStorageSync('thisPageData');
	//console.log(thisPageData)
	if(thisPageData){
	   if(isJSON(thisPageData)){
		 thisPageData=JSON.parse(thisPageData);
	   }else{
		 return thisPageData;
	   }
	   
	}else{
	  thisPageData='';
	}
	return thisPageData;
}
/**
 * 判断是否是json格式
 * @param {*} str 
 */
function isJSON(str) {
	if (typeof str == 'string') {
			try {
					var obj=JSON.parse(str);
					if(typeof obj == 'object' && obj ){
							return true;
					}else{
							return false;
					}

			} catch(e) {
					//console.log('error：'+str+'!!!'+e);
					return false;
			}
	}
	console.log('It is not a string!')
}

/** 
 * param 将要转为URL参数字符串的对象 
 * key URL参数字符串的前缀 
 * encode true/false 是否进行URL编码,默认为true 
 *  
 * return URL参数字符串 
 */
var urlEncode = function (param, key, encode = false) {
  if (param == null) return '';
  var paramStr = '';
  var t = typeof (param);
  if (t == 'string' || t == 'number' || t == 'boolean') {
    paramStr += '&' + key + '=' + ((encode == null || encode) ? encodeURIComponent(param) : param);
  } else {
    for (var i in param) {
      var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
      paramStr += urlEncode(param[i], k, encode);
    }

    //用于截取第一个&
    if (k) {
      paramStr = paramStr.substr(1);
    }
  }
  return paramStr;
};


// 获取url的参数
function getUrlParms(apiUrl) {
	var data = apiUrl
	var theRequest = {};//存储提取的参数
	var index_flag=data.indexOf("?")
	if (index_flag!= -1) {
			var str = data.substr(index_flag+1);
			str = str.split("&");
			for (var i = 0; i < str.length; i++) {
					theRequest[str[i].split("=")[0]] = decodeURIComponent(str[i].split("=")[1]);
			}
	} 
	console.log(theRequest)
	return theRequest;
}



module.exports = {openPage, thisPageData, urlEncode, getUrlParms}
