(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["uview-ui/components/u-tag/u-tag"],{"6e14":function(t,e,o){"use strict";o.d(e,"b",(function(){return n})),o.d(e,"c",(function(){return i})),o.d(e,"a",(function(){return r}));var r={uIcon:function(){return o.e("uview-ui/components/u-icon/u-icon").then(o.bind(null,"6c52"))}},n=function(){var t=this,e=t.$createElement,o=(t._self._c,t.show?t.__get_style([t.customStyle]):null),r=t.show&&t.closeable?t.__get_style([t.iconStyle]):null;t.$mp.data=Object.assign({},{$root:{s0:o,s1:r}})},i=[]},7655:function(t,e,o){"use strict";o.r(e);var r=o("6e14"),n=o("b9cc");for(var i in n)"default"!==i&&function(t){o.d(e,t,(function(){return n[t]}))}(i);o("9d74");var l,u=o("f0c5"),c=Object(u["a"])(n["default"],r["b"],r["c"],!1,null,"d45ea6be",null,!1,r["a"],l);e["default"]=c.exports},8444:function(t,e,o){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={name:"u-tag",props:{type:{type:String,default:"primary"},disabled:{type:[Boolean,String],default:!1},size:{type:String,default:"default"},shape:{type:String,default:"square"},text:{type:[String,Number],default:""},bgColor:{type:String,default:""},color:{type:String,default:""},borderColor:{type:String,default:""},closeColor:{type:String,default:""},index:{type:[Number,String],default:""},mode:{type:String,default:"light"},closeable:{type:Boolean,default:!1},show:{type:Boolean,default:!0}},data:function(){return{}},computed:{customStyle:function(){var t={};return this.color&&(t.color=this.color),this.bgColor&&(t.backgroundColor=this.bgColor),"plain"==this.mode&&this.color&&!this.borderColor?t.borderColor=this.color:t.borderColor=this.borderColor,t},iconStyle:function(){if(this.closeable){var t={};return"mini"==this.size?t.fontSize="20rpx":t.fontSize="22rpx","plain"==this.mode||"light"==this.mode?t.color=this.type:"dark"==this.mode&&(t.color="#ffffff"),this.closeColor&&(t.color=this.closeColor),t}},closeIconColor:function(){return this.closeColor?this.closeColor:this.color?this.color:"dark"==this.mode?"#ffffff":this.type}},methods:{clickTag:function(){this.disabled||this.$emit("click",this.index)},close:function(){this.$emit("close",this.index)}}};e.default=r},"9d74":function(t,e,o){"use strict";var r=o("ac6e"),n=o.n(r);n.a},ac6e:function(t,e,o){},b9cc:function(t,e,o){"use strict";o.r(e);var r=o("8444"),n=o.n(r);for(var i in r)"default"!==i&&function(t){o.d(e,t,(function(){return r[t]}))}(i);e["default"]=n.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'uview-ui/components/u-tag/u-tag-create-component',
    {
        'uview-ui/components/u-tag/u-tag-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("7655"))
        })
    },
    [['uview-ui/components/u-tag/u-tag-create-component']]
]);
