var commonTemplate = (function() {
	const html = `<span class="all-template">
						<div
							:class="[item.type != 6 ? 'template' : 'template2']" 
							v-for="(item,key) in list" 
							:key="key"
							@click.stop="goDetil(item)"
							v-if="key<limit_num"
						>
							<!-- 资讯 -->
							<template v-if="[0,1,2,3,4,5,7,9,10].includes(item.type)">
								<!-- 楼盘广告顶部 -->
								<template v-if="item.type == 4 || item.type == 7 || item.type == 9 || item.type == 10">
									<div class="template-top-houses" v-if="item.info.name">
										<div class="template-top-houses-name">
											<div class="templ_1">
												<p class="template-name">{{item.info.name}}</p>
												<template v-if="item.info.tip && item.info.tip.length > 0">
													<template v-for="(houseTip,houseKey) in item.info.tip" >
														<span 
															v-if="houseKey < 3 &&houseKey!=0"
														>
															{{houseTip}}
														</span>
													</template>
												</template>
											</div>
											
											<span
												v-if="item.info.tip"
												:class="[
													item.info.tip[0] == '在售' ? 'houses-bg-blue' : '',
													item.info.tip[0] == '待售' ? 'houses-bg-orange' : '',
													item.info.tip[0] == '售完' ? 'houses-bg-purple' : '',
													item.info.tip[0] == '尾盘' ? 'houses-bg-blue2' : '',
												]"
											>
												{{item.info.tip[0]}}
											</span>
										</div>
										<div class="template-top-houses-price">
											<div v-if="item.info.price>0">
												<span>{{item.info.price}}</span>元/m²
											</div>
											<div v-else>
												价格待定
											</div>
											
											<span class="template-top-houses-site" :class="[ item.info.area ? 'template3-site-line' : '']">{{item.info.site}}</span>
											<span  class="template3-site-area van-ellipsis" v-if="item.info.area">建面{{item.info.area}}</span>
										</div>
									</div>
								</template>
								<div 
									class="template-top" 
									:class="[ 
										item.type == 1 && item.img && item.img.length == 1 ? 'template-news-1' : '',
										
										(item.type == 1 || item.type == 2 || item.type == 4 || item.type == 10) && item.img && item.img.length > 1 ? 'template-news-3' : '',
										(item.type == 2 || item.type == 4 || item.type == 10) && item.img && item.img.length == 1 ? 'template-ad-1' : '',
									]"
								>
									<h4 class="template-title ">
										<span v-if="(item.type == 0 || item.type == 1) && ( item.hot == 1 || item.write == 1 ) && !item.html_title && haveTag && item.write == 1">
											<span>
												<img src="/9house/static/index/fire.png" v-if="item.hot == -999">
												<!--:class="[item.hot == 1 && item.write == 1 ? 'margin-left' : '']"--> 
												<span 
													class="template-title-write" 
													v-if="item.write == 1"
												>
													原创
												</span>
											</span>
										</span>
										<i v-if="item.html_title" v-html="item.html_title"></i>
										<i v-else>
											{{item.title}}
										</i>
									</h4>
									
									<!-- 资讯/广告有图 -->
									<span 
										:class="[ 
											item.type == 1 && item.img && item.img.length == 1 ? 'template-top-1-pic' : '',
										]"
										v-if="![0,3,5,7,9].includes(item.type) && item.img.length>0"
									>
										<template v-for="(url, newKey) in item.img">
											<img v-lazy="$http.imgDirtoUrl(url)" v-if="newKey < 3" :key="newKey">
										</template>
									</span>
									
									<!-- 广告视频占位 -->
									<template v-if="item.type == 3 || item.type == 5 || item.type == 7 || item.type == 9">
										<div class="template-ad-video">
											<img style='width:100%;height:100%;' v-lazy="$http.imgDirtoUrl(item.img)">
										</div>
									</template>
								</div>
								<div class="template-bottom" v-if="item.type != 9 && item.type != 10">
									<!-- 资讯 / 单视频 -->
									<template v-if="item.type == 0 || item.type == 1 || item.type == 5">
										<div class="template-bottom-left">
											<span v-if="haveTag">
												{{item.author.name}} &nbsp {{item.release_time}} | {{item.readNum}}次阅读
											</span>
											<span v-else>
												{{item.release_time||''}}
											</span>
										</div>
										<div class="template-bottom-news" v-if="haveTag">
											<template v-for="(newsTip, newsTipKey) in item.tip">
												<span class="template-bottom-tip" :key="newsTipKey" v-if="newsTipKey < 2">
													{{newsTip}}
												</span>
											</template>
											<i v-if="item.tip.length > 2">...</i>
										</div>
										
									</template>
									<!-- 资讯/视频广告 -->
									<template v-if="item.type == 2 || item.type == 3">
										<div class="template-bottom-tip">
											广告
										</div>
										<span class="template-bottom-ad-del" v-if="show_adclose" @click.stop="delAd(key)">
											<span class="iconfont iconlujing"></span>
										</span>
									</template>
									<!-- 楼盘广告 -->
									<template v-if="item.type == 4">
										<div class="template-bottom-left">
											<template v-for="(house,houseIndex) in item.info.lab">
												<span class="template3-bottom-tip" :key="houseIndex" v-if="houseIndex < 2 && house.type == 0">
													<img src="/9house/static/index/hot.png">
													<span class="van-ellipsis">{{house.name}}</span>
												</span>
												<!--template3-bottom-vr--> 
												<span class="template3-bottom-tip " :key="houseIndex" v-if="houseIndex < 2 && house.type == 1">
													<img src="/9house/static/index/sale.png">
													<span class="van-ellipsis">{{house.name}}</span>
												</span>
											</template>
										</div>
										<div class="template-bottom-right">
											<span class="template-bottom-tip">
												广告
											</span>
											<span class="template-bottom-ad-del" v-if="show_adclose" @click.stop="delAd(key)">
												<span class="iconfont iconlujing"></span>
											</span>
										</div>
									</template>
								</div>
							</template>
							<!-- <template v-else-if="item.type == 6">
								<h4 class="template-title template2-title" v-if="titleShow">精彩小视频</h4>
								<div class="template2-video">
									<div class="template2-video-item" v-for="(list,key) in item.list" :key="key" @click.stop="goSmallVideo(list.id)">
										<div class="template2-video-bg">
											<img style='width:100%;height:100%;' v-lazy="$http.imgDirtoUrl(list.img)">
										</div>
										<div class="template2-video-top">
											<span>{{list.tip[0]}}</span>
											<span>{{list.view}}人</span>
										</div>
										<div class="template2-video-bottom">
											{{list.title}}
										</div>
									</div>
						
									<div class="template2-video-place">
										<div>
											<span class="template2-video-place-box" @click.stop="moreVideo">
												<img src="/9house/static/index/more.png">
												<span>查看更多</span>
											</span>
										</div>
									</div>
								</div>
							</template> -->
							
							<!-- 新房/优惠楼盘 -->
							<template v-else-if="item.type == 8 || item.type == 11 || item.type == 12">
								<div class="template3">
									<div class="template3-top">
										<span :class="[ item.type == 11 ? 'template3-top-img-sale' : '' ]"><img v-lazy="$http.imgDirtoUrl(item.img[0])" class="template3-top-img" ></span>
										<div class="template3-top-right">
											<div class="template3-title">
												
												<template v-if=" item.info.tip[0] == '人气榜' || item.info.tip[0] == '热搜榜'">
													<img :src="item.info.tip[0] == '热搜榜' ? '../../static/new_house/hot.png' : '../../static/new_house/popular.png'">
												</template>
												{{item.info.name}}
												<span
													:class="[
														item.info.tip[0] == '在售' ? 'houses-bg-blue' : '',
														item.info.tip[0] == '待售' ? 'houses-bg-orange' : '',
														item.info.tip[0] == '售罄' ? 'houses-bg-purple' : '',
														item.info.tip[0] == '尾盘' ? 'houses-bg-blue2' : '',
													]"
													v-if=" item.info.tip[0] != '人气榜' && item.info.tip[0] != '热搜榜'"
												>
													{{item.info.tip[0]}}
												</span>
											</div>
											<div class="template-top-houses-price">
												<div v-if="item.info.price>0">
													<span>{{item.info.price}}</span>元/m²
												</div>
												<div v-else>
													价格待定
												</div>
											</div>
											<div class="template3-tip">
												<template v-for="(tip,num) in item.info.tip">
													<div v-if="tip&&!(['在售','待售','售罄','尾盘'].includes(tip))&&num<4" :key="num">
														{{tip}}
													</div>
												</template>
											</div>
											<div class="template3-site">
												<span class="template-top-houses-site" :class="[ item.info.area ? 'template3-site-line' : '']">{{item.info.site}}</span>
												<span  class="template3-site-area van-ellipsis" v-if="item.info.area">建面{{item.info.area}}</span>
											</div>
										</div>
									</div>
									<template v-if="item.type == 8">
										<div class="template3-bottom">
											<template v-for="(house,houseIndex) in item.info.lab">
												<span class="template3-bottom-tip" :key="houseIndex" v-if="houseIndex < 2 && house.type == 0">
													<img src="/9house/static/index/hot.png">
													<span class="van-ellipsis">{{house.name}}</span>
												</span>
												<!--template3-bottom-vr--> 
												<span class="template3-bottom-tip" :key="houseIndex" v-if="houseIndex < 2 && house.type == 1">
													<img src="/9house/static/index/sale.png">
													<span class="van-ellipsis">{{house.name}}</span>
												</span>
											</template>
										</div>
									</template>
									<template v-else-if="item.type == 11">
										<div class="template3-bottom-apply">
											<div class="template3-bottom-apply-box">
												<span class="template3-bottom-apply-info van-ellipsis"><i class="iconfont iconxingzhuang1"></i>{{ item.apply.title }}</span>
												<span class="template3-bottom-apply-people"><span>{{ item.apply.people }}</span>人已报名</span>
											</div>
											<van-button 
												round 
												type="info"
												:color="item.apply.state == 0 ? 'linear-gradient(90deg, #FFA640 0%, #FE8D35 100%)' : 'rgba(173, 173, 173, 1)' "
												:disabled ="item.apply.state == 0 ? false : true"
												@click="$emit('apply',item.id)"
											>
												{{ item.apply.state == 0 ? '立即报名' : '已报名' }}
											</van-button>
										</div>
									</template>
								</div>
							</template>
						</div>
					</span>`;
	
	return {
		data: function(){
			return {
				
			}
		},
		template: html,
		props: {
			list: {
				type: Array,
				default() {
					return []
				}
			},
			titleShow: {
				type: Boolean,
				default() {
					return true
				}
			},
			pid:{
				type:Number,
				default:9
			},
			cate_id:{
				type:Number,
				default:10
			},
			show_adclose:{
				type: Boolean,
				default() {
					return true
				}
			},
			limit_num:{//现在显示的条数
				type: Number,
				default() {
					return 999999
				}
			},
			haveTag: {
				type: Boolean,
				default() {
					return true
				}
			}
		},
		created(){
			//console.log('limit_num',this.limit_num)
		},
		methods: {
			goDetil( item ){
				if( item.type == 0 || item.type == 1 ){	//	文章资讯
				
					this.$api.goPage('discover/news_detail.html',{ id: item.id, pid: this.pid, cate_id: this.cate_id });
					
				}else if(  item.type == 2 ){		// 广告有图
					if( item.href && (item.href.indexOf('http://') ==0 || item.href.indexOf('https://') ==0 ) ){
						window.location.href = item.href;
					}
					// this.$api.goPage('discover/news_detail.html',{ id: id });
					
				}else if( item.type == 3 || item.type == 5 || item.type == 7 ){	//	3-广告视频 5-单独视频 7-楼盘视频
					
					this.$api.goPage('discover/video.html',{ id: item.id,pid:this.pid,cate_id:this.cate_id });
					
				}else if( [4,8,9,10].includes(item.type) ){	//	4-广告楼盘有图 8-新房 9-好房推荐楼盘视频 10-好房推荐楼盘有图
					if(item.type == 4){
						if(!$api.trim(item.href) && item.info){
							item.href = 'houses/index.html?id='+item.info.estate_id+'&cover='+item.info.cover;
						}
						if(!item.href){
							return
						}
						$api.goPage(item.href);
						return;
					}
					const obj = {
						id: item.id,
						cover: item.cover
					}
					// console.log(item)
					const active_id = this.$api.funcUrlDel().option.active_id;
					
					if(active_id){ 
						obj.active_id = active_id 
					}
					// console.log(obj)
					item.href = $api.trim(item.href)
					if(item.href){
						this.$api.goPage(item.href);//有设置跳转时
					}else{
						this.$api.goPage('houses/index.html',obj);
					}
				}
			},
			goSmallVideo( id ) {
				this.$api.goPage('discover/small_video.html',{id: id});
			},
			moreVideo() {
				this.$api.goPage('discover/small_video_list.html');
			},
			// 广告删除
			delAd( index ) {
				let list = this.$api.deepClone(this.list);
				
				list.splice(index,1);
				
				this.$emit('del', list);
			}
		},
	}
}());