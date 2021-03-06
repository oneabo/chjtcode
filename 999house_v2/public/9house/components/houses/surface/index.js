var housesSurface = (function() {
	const html = `<transition name="van-slide-down">
						<div
							class="surface" 
							:style="[ list && list.bg ? {backgroundImage: 'url('+ list.bg +')'} : {} ]" 
							v-show="show"
							@touchstart.stop="touchstart"
							@touchmove.prevent
							@touchend.stop="touchend"
							@click.stop
						>
							<top-bar type="1" :icon-style="{
								width: '.7rem',
								fontSize: '.6rem',
								color: 'rgba(255, 255, 255, 1)',
								marginTop: '.2rem'
							}">
							</top-bar>
							<div class="surface-right">
								<!-- <span><img src="../../static/houses/vr.png"></span> -->
								<!-- <span @click="videoPlay"><img src="../../static/houses/play.png"></span> -->
								<span  @click="goImage"><img src="../../static/houses/img.png"></span>
							</div>
							
							<img :src="list && list.logo ? list.logo : ''" class="surface-icon">
							
							<div class="surface-bottom">
								<div class="surface-info">
									<div class="surface-box-title">
										<div class="title">
											{{list && list.title ? list.title : ''}}
											
											<template v-if="list && list.tip">
												<template v-for="(item,index) in list.tip">
													<van-tag type="primary" :key="index" v-if="index < 2">{{item}}</van-tag>
												</template>
											</template>
										</div>
										<div class="price" v-if="list && list.price">
											{{list.price}}
											<span>元/m²</span>
										</div>
									</div>
									<div class="surface-info-box">
										<span class="van-ellipsis">楼盘地址：{{list && list.site ? list.site : ''}}</span>
										<span class="van-ellipsis">开盘时间：{{list && list.time ? list.time : ''}}</span>
										<span class="van-ellipsis">免费咨询：{{list && list.phone ? list.phone : ''}}</span>
										<a :href="'tel:'+list.phone" class="surface-phone" v-if="list && list.phone"><div class="iconfont icondianhua"></div></a>
									</div>
									
								</div>
								<div class="surface-hint">
									<span @click="$emit('hide')">向上滑动查看更多</span>
									<span class="iconfont iconshuangjiantouxia" @click="$emit('hide')"></span>
								</div>
							</div>
							
						</div>
					</transition>`;
				
	
	return {
		template: html,
		data: function(){
			return {
				startY: 0
			}
		},
		props: {
			show: {
				type: Boolean,
				default() {
					return true
				}
			},
			list: {
				type: Object,
				default() {
					return {}
				}
			}
		},
		created() {
			
		},
		mounted() {
			
		},
		methods: {
			touchstart(evt) {
				try{
					const touch = evt.changedTouches[0];
					const y = Number(touch.pageY); 
					//记录触点初始位置
					this.startY = y;
				}catch(e){
					console.log(e.message)
				}
			},
			touchend(evt) {
				try{
					const touch = evt.changedTouches[0];
					const y = Number(touch.pageY);

					// console.log(this.startY - y)
					if( this.startY - y > 40 ){
						// console.log(999)
						this.$emit('hide')
					}
				}catch(e){
					console.log(e.message)
				}
			},
			videoPlay() {
				this.$emit('hide','video');
			},
			goImage() {
				this.$emit('hide','img');
			},
		}
	}
}());