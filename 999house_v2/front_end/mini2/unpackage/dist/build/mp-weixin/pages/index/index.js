(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/index/index"],{"105d":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o=getApp(),i=function(){e.e("components/common/header").then(function(){return resolve(e("c8d9"))}.bind(null,e)).catch(e.oe)},c=function(){e.e("components/common/banner").then(function(){return resolve(e("81a0"))}.bind(null,e)).catch(e.oe)},u=function(){e.e("components/common/nav").then(function(){return resolve(e("a325"))}.bind(null,e)).catch(e.oe)},r=function(){e.e("components/common/speedy").then(function(){return resolve(e("24b1"))}.bind(null,e)).catch(e.oe)},a=function(){e.e("components/common/activity").then(function(){return resolve(e("e020"))}.bind(null,e)).catch(e.oe)},s=function(){e.e("components/common/news").then(function(){return resolve(e("0623"))}.bind(null,e)).catch(e.oe)},h={components:{headerindex:i,bannerindex:c,navindex:u,speedyindex:r,activityindex:a,news:s},data:function(){return{webviewStyles:"false",topIsFixed:!1,locationShow:!1,columns:[],bannerList:[],activityList:[],city_no:"",city_name:""}},onLoad:function(){this.getUserLocation()},onShow:function(){this.city_no=o.globalData.city_no},methods:{handleScroll:function(){document.documentElement.scrollTop},getUserLocation:function(){this.city_no=getApp().getCurrentCity().city_no,this.city_name=getApp().getCurrentCity().city_name,this.getColumnList(),this.getAdvs()},getAdvs:function(){var t=this,n={falg:["h5_home_top1","h5_home_top2"],city_no:this.city_no};this.$http.post("/adv/getAdvByFlag",n).then((function(n){var e=n.data,o=[];for(var i in e.h5_home_top1[0].img)o.push({img:e.h5_home_top1[0].img[i],href:e.h5_home_top1[0].href,info:e.h5_home_top1[0].info,cover:e.h5_home_top1[0].cover});t.bannerList=o;var c=[];for(var u in e.h5_home_top2)e.h5_home_top2[u]&&e.h5_home_top2[u].img&&c.push({img:e.h5_home_top2[u].img[0],href:e.h5_home_top2[u].href,info:e.h5_home_top2[u].info,cover:e.h5_home_top2[u].cover,title:e.h5_home_top2[u].title,sub_title:e.h5_home_top2[u].sub_title});t.activityList=c}))},getColumnList:function(){var t=this,n={group_flag:"h5_home_icons"};this.$http.post("/news/getColumnList",n).then((function(n){var e=n.data;t.columns=e}))}}};n.default=h},2807:function(t,n,e){"use strict";e.r(n);var o=e("105d"),i=e.n(o);for(var c in o)"default"!==c&&function(t){e.d(n,t,(function(){return o[t]}))}(c);n["default"]=i.a},8164:function(t,n,e){"use strict";var o;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return c})),e.d(n,"a",(function(){return o}));var i=function(){var t=this,n=t.$createElement;t._self._c;t._isMounted||(t.e0=function(n){t.topIsFixed=n.isFixed})},c=[]},9762:function(t,n,e){"use strict";(function(t){e("e878");o(e("66fd"));var n=o(e("fc25"));function o(t){return t&&t.__esModule?t:{default:t}}t(n.default)}).call(this,e("543d")["createPage"])},fc25:function(t,n,e){"use strict";e.r(n);var o=e("8164"),i=e("2807");for(var c in i)"default"!==c&&function(t){e.d(n,t,(function(){return i[t]}))}(c);var u,r=e("f0c5"),a=Object(r["a"])(i["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],u);n["default"]=a.exports}},[["9762","common/runtime","common/vendor"]]]);