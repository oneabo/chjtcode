(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/common/nav"],{"0c5a":function(n,t,e){"use strict";e.r(t);var a=e("4d36"),r=e.n(a);for(var i in a)"default"!==i&&function(n){e.d(t,n,(function(){return a[n]}))}(i);t["default"]=r.a},"4d36":function(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={data:function(){return{newNav:[]}},props:["nav"],created:function(){this.newNav=this.divideNav()},mounted:function(){},methods:{divideNav:function(){var n=this,t=[];if(n.nav.length>0){var e=this.$api.deepClone(n.nav);return a(e),t}function a(n){n.length>0&&(t.push(n.slice(0,8)),n.splice(0,8),n.length>0&&a(n))}},goPages:function(n){var t="";-1!=n.indexOf(".html")&&(t=n.replace(".html",""),console.log("url",t),this.$api.trim(t)&&this.goPage(t))}}};t.default=a},"653e":function(n,t,e){"use strict";var a=e("e908"),r=e.n(a);r.a},a325:function(n,t,e){"use strict";e.r(t);var a=e("f59d"),r=e("0c5a");for(var i in r)"default"!==i&&function(n){e.d(t,n,(function(){return r[n]}))}(i);e("653e");var o,u=e("f0c5"),c=Object(u["a"])(r["default"],a["b"],a["c"],!1,null,null,null,!1,a["a"],o);t["default"]=c.exports},e908:function(n,t,e){},f59d:function(n,t,e){"use strict";var a;e.d(t,"b",(function(){return r})),e.d(t,"c",(function(){return i})),e.d(t,"a",(function(){return a}));var r=function(){var n=this,t=n.$createElement,e=(n._self._c,n.__map(n.newNav,(function(t,e){var a=n.__get_orig(t),r=n.__map(t,(function(t,e){var a=n.__get_orig(t),r=n.$api.imgDirtoUrl(t.cover);return{$orig:a,g0:r}}));return{$orig:a,l0:r}})));n.$mp.data=Object.assign({},{$root:{l1:e}})},i=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/common/nav-create-component',
    {
        'components/common/nav-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("a325"))
        })
    },
    [['components/common/nav-create-component']]
]);
