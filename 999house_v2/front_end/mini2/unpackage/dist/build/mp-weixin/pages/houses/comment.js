(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/houses/comment"],{"1f02":function(t,e,n){"use strict";n.r(e);var o=n("5efe"),i=n.n(o);for(var a in o)"default"!==a&&function(t){n.d(e,t,(function(){return o[t]}))}(a);e["default"]=i.a},"2f5a":function(t,e,n){"use strict";var o=n("ab2c"),i=n.n(o);i.a},"5efe":function(t,e,n){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=function(){n.e("components/houses/comment").then(function(){return resolve(n("20dd"))}.bind(null,n)).catch(n.oe)},i={data:function(){return{safeBottom:0,page:0,total_page:1,loading:!1,showComment:!1,estate_id:0,color:[],type:0,list:{tip:[{type:0,name:"全部评论"},{type:2,name:"有图"}],list:[]}}},components:{housesComment:o},onLoad:function(e){this.safeBottom=Math.abs(t.getSystemInfoSync().safeAreaInsets.bottom),console.log(t.getSystemInfoSync().safeAreaInsets),this.estate_id=e.id},onShow:function(){this.getTalk(1)},onReachBottom:function(){this.getTalk()},methods:{changeTag:function(t){this.type=this.list.tip[t].type,this.getTalk(1)},getTalk:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0,n=this.page+1;if(1==e&&(n=1,this.showComment=!1,this.$set(this.list,"list",[])),!(n>this.total_page)&&1!=this.loading){this.loading=!0;var o=this.estate_id,i=this.type,a={id:o,is_img:i,page:n,pageSize:100};this.$http.post("/comment/propertyReviewsList",a).then((function(e){var o=e.data.list?e.data.list:[],i=[];o.map((function(e){var n={};n.id=e.id,n.head=t.imgDirtoUrl(e.user_avatar),n.name=e.user_name,n.say=e.content,n.time=t.$api.timeFormat(e.create_time,"yyyy年mm月dd日"),n.img=e.img,i.push(n)})),t.$set(t.list,"list",i),t.showComment=!0,t.page=n,t.total_page=e.data.last_page?e.data.last_page:1,t.loading=!1})).catch((function(){t.showComment=!0,t.loading=!1}))}},goComment:function(){this.isLogin()&&this.goPage("houses/send_comment",{id:this.estate_id})}}};e.default=i}).call(this,n("543d")["default"])},ab2c:function(t,e,n){},c35c:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){return o}));var o={uTag:function(){return n.e("uview-ui/components/u-tag/u-tag").then(n.bind(null,"7655"))},uButton:function(){return n.e("uview-ui/components/u-button/u-button").then(n.bind(null,"ad7e"))}},i=function(){var t=this,e=t.$createElement;t._self._c},a=[]},c4e4:function(t,e,n){"use strict";(function(t){n("e878");o(n("66fd"));var e=o(n("f589"));function o(t){return t&&t.__esModule?t:{default:t}}t(e.default)}).call(this,n("543d")["createPage"])},f589:function(t,e,n){"use strict";n.r(e);var o=n("c35c"),i=n("1f02");for(var a in i)"default"!==a&&function(t){n.d(e,t,(function(){return i[t]}))}(a);n("2f5a");var s,u=n("f0c5"),c=Object(u["a"])(i["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],s);e["default"]=c.exports}},[["c4e4","common/runtime","common/vendor"]]]);