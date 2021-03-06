import Vue from 'vue'
import App from './App'

Vue.config.productionTip = false
App.mpType = 'app';

// #ifdef H5
	let mpShare = require('uview-ui/libs/mixin/mpShare.js');
	Vue.mixin(mpShare)
	
// #endif

import $api from '@/utils/util.js'
Vue.prototype.$api = $api;



const app = new Vue({
    ...App
})

import uView from "uview-ui";
Vue.use(uView);

import myMixin from '@/myMixin.js'
Vue.use(myMixin, app);

// http拦截器
import httpInterceptor from '@/utils/http.interceptor.js';
//httpInterceptor.js引入"app"对象(即页面的"this"实例)
Vue.use(httpInterceptor.install, app);


//拓展getApp中Vue新增的属性
let original_getApp =  getApp
getApp = function(){
	// return {
	// 	...original_getApp.apply(),
	// 	$api: Vue.prototype.$api,
	// 	$http: Vue.prototype.$http,
	// };
	let obj = original_getApp.apply();
	obj.$api = Vue.prototype.$api;
	obj.$http = Vue.prototype.$http;
	return obj
}

app.$mount();

