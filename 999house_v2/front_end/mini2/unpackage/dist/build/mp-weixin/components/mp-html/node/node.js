(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/mp-html/node/node"],{"2f3a":function(t,i,r){"use strict";var e=function(t){t.options.wxsCallMethods||(t.options.wxsCallMethods=[])};i["a"]=e},"3f057":function(t,i,r){"use strict";r.r(i);var e=r("5db8"),n=r("d6cb");for(var o in n)"default"!==o&&function(t){r.d(i,t,(function(){return n[t]}))}(o);r("481e");var s,a=r("f0c5"),c=r("2f3a"),u=Object(a["a"])(n["default"],e["b"],e["c"],!1,null,null,null,!1,e["a"],s);"function"===typeof c["a"]&&Object(c["a"])(u),i["default"]=u.exports},"481e":function(t,i,r){"use strict";var e=r("bc25b"),n=r.n(e);n.a},"5db8":function(t,i,r){"use strict";var e;r.d(i,"b",(function(){return n})),r.d(i,"c",(function(){return o})),r.d(i,"a",(function(){return e}));var n=function(){var t=this,i=t.$createElement;t._self._c},o=[]},bc25b:function(t,i,r){},d6cb:function(t,i,r){"use strict";r.r(i);var e=r("ec9d"),n=r.n(e);for(var o in e)"default"!==o&&function(t){r.d(i,t,(function(){return e[t]}))}(o);i["default"]=n.a},ec9d:function(t,i,r){"use strict";(function(t){Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var e=function(){Promise.resolve().then(function(){return resolve(r("3f057"))}.bind(null,r)).catch(r.oe)},n={name:"node",options:{virtualHost:!0},data:function(){return{ctrl:{}}},props:{name:String,attrs:{type:Object,default:function(){return{}}},childs:Array,opts:Array},components:{node:e},mounted:function(){for(this.root=this.$parent;"mp-html"!=this.root.$options.name;this.root=this.root.$parent);},beforeDestroy:function(){},methods:{toJSON:function(){},play:function(i){if(this.root.pauseVideo){for(var r=!1,e=i.target.id,n=this.root._videos.length;n--;)this.root._videos[n].id==e?r=!0:this.root._videos[n].pause();if(!r){var o=t.createVideoContext(e,this);o.id=e,this.root._videos.push(o)}}},imgTap:function(i){var r=this.childs[i.currentTarget.dataset.i].attrs;r.ignore||(r.src=r["data-src"]||r.src,this.root.$emit("imgtap",r),this.root.previewImg&&t.previewImage({current:parseInt(r.i),urls:this.root.imgList}))},imgLongTap:function(){},imgLoad:function(t){var i=t.currentTarget.dataset.i;this.childs[i].w?(this.opts[1]&&!this.ctrl[i]||-1==this.ctrl[i])&&this.$set(this.ctrl,i,1):this.$set(this.ctrl,i,t.detail.width)},linkTap:function(i){var r=this.childs[i.currentTarget.dataset.i].attrs,e=r.href;this.root.$emit("linktap",r),e&&("#"==e[0]?this.root.navigateTo(e.substring(1)).catch((function(){})):e.includes("://")?this.root.copyLink&&t.setClipboardData({data:e,success:function(){return t.showToast({title:"链接已复制"})}}):t.navigateTo({url:e,fail:function(){t.switchTab({url:e,fail:function(){}})}}))},mediaError:function(t){var i=t.currentTarget.dataset.i,r=this.childs[i];if("video"==r.name||"audio"==r.name){var e=(this.ctrl[i]||0)+1;if(e>r.src.length&&(e=0),e<r.src.length)return this.$set(this.ctrl,i,e)}else"img"==r.name&&this.opts[2]&&this.$set(this.ctrl,i,-1);this.root&&this.root.$emit("error",{source:r.name,attrs:r.attrs,errMsg:t.detail.errMsg})}}};i.default=n}).call(this,r("543d")["default"])}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/mp-html/node/node-create-component',
    {
        'components/mp-html/node/node-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("3f057"))
        })
    },
    [['components/mp-html/node/node-create-component']]
]);
