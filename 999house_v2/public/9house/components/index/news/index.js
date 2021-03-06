var indexNews = (function() {
	const html = `<div class="info" id="info" ref="contentBox">
						<van-tabs
							v-model="active"
							color="rgba(254, 130, 30, 1)"
							title-active-color="rgba(254, 130, 30, 1)"
							:sticky="true"
							@change="tabsChange"
							
						>
							<van-tab v-for="(items,index) in tabList" :title="items.name" :key="index" >
								<template v-if="items.type == 1">
									<template v-if="activeList.length > 0">
										<van-list
										  v-model="loading"
										  :finished="finished"
										  finished-text="没有更多了"
										  @load="onLoad"
										>
											<common-template :list="activeList" @del="(e)=>{ activeList = e }"></common-template>
										</van-list>
									</template>
									<template v-else>
										<van-empty image="search" description="暂无数据" v-show="no_data"/>
									</template>
								</template>
								<template v-else>
									<div class="house_wrap" @touchstart="start" @touchmove="move" @touchend="end"  v-if="activeList.length > 0">
										<div class="new-house-activity" v-if="items.activityList && items.activityList.length > 0">
											<div 
												class="new-house-activity-item" 
												v-for="(item,index) in items.activityList" 
												:key="index"
												@click="goActive(item)"
											>
												<div>{{item.title}}</div>
												<img v-if="item.img&&item.img[0]" :src="$http.imgDirtoUrl(item.img[0])">
											</div>
										</div>
										
										<div 
											class="house_wrap_content" 
											:style="{ transform: 'translateY(' + (moveY + 'px') + ')'}"
										>
											<common-template :list="activeList"  @del="(e)=>{ activeList = e }"></common-template>
										</div>
										<div class="house_wrap_bottom" :style="{ transform: 'translateY(' + (moveY + 'px') + ')'}">{{ moreText }}</div>
									</div>
									<template v-else>
										<van-empty image="search" description="暂无数据" v-show="no_data"/>
									</template>
								</template>
							</van-tab>
						</van-tabs>
					</div>`;
	
	return {
		data: function(){
			return {
				active: 0,
				activeList: [],
				loading: true,
				finished: false,
				page: 0,
				maxPage: 1,
				city_no: 0,
				no_data: false,
				/**
				 * 
				 * 广告涉及删除，所有数据需要id
				 * 
				 * type
				 * 	
				 * 0-资讯无图 / 1-资讯有图(1~3)
				 * 2-广告有图(1~3) / 3-广告视频 / 4-广告楼盘有图
				 * 5-单独视频 / 6-精彩小视频 / 7-楼盘视频
				 * 8-新房
				 * 
				 */
				tabList: [
					{ 
						type: 1,
						name: '资讯',
					},
					{
						type: 2,
						name: '新房',
						activityList: []
					}
				],
				startY: 0,
				moveY: 0,
				moreText: '再往上拉查看更多~'
			}
		},
		template: html,
		created() {
			this.$http.getCurrentCity().then( data=>{
				this.city_no = data.city_no;
				this.getNewsList();
			})
		},
		methods: {
			// 切换资讯/新房
			tabsChange(e) {
				this.page = 0;
				this.maxPage = 1;
				this.loading = true;
				this.finished = false;
				this.activeList = [];
				this.no_data = false;

				this.onLoad();
			},
			onLoad() {
				if( this.active == 0 ) {
					this.getNewsList();
				} else {
					this.getHouseActivty();
					this.getHouseList();
				}
			},
			getNewsList() {
				if( this.page >= this.maxPage ) {
					this.loading = false;
					this.finished = true;
					return;
				}
				
				let page = this.page;
				const haveVideo = ( page == 1 || page%3 == 0 ) ? 1 : 0;
				
				page++;
				
				const data = {
					pid: 9,
					is_index: 1,
					city_no: this.city_no,
					page: page,
					is_get_small_video: haveVideo
				};
				
				// console.log(data)
				
				this.$http.ajax({
					url: '/index/news/getNewsList',
					data: data
				}).then( res=>{
					const data = res.data;
					const arr = [];
					let adIndex = 0;
					
					this.maxPage = data.last_page;
					this.page = data.current_page;
					
					if( page == 1 && (!data.list || data.list.length == 0)){
						this.no_data = true;
					}
					
					data.list.map( (item,index)=>{
						arr.push(item);
						
						if( (index+1)%6 == 3 ){
							let ad = data.ad_lsit[adIndex];
							ad = this.formatAdv(ad);
							if( ad ){
								arr.push( data.ad_lsit[adIndex] );
								adIndex++;
							}
						}
					})
					
					if( haveVideo == 1 ) {
						( data.small_voide && Object.keys(data.small_voide).length > 0 ) && arr.push( data.small_voide );
					}
					
					// console.log(res)
					// console.log(data)
					// console.log(arr)
					this.activeList = [...this.activeList,...arr];
					
					if( this.page >= this.maxPage ) {
						this.finished = true;
					}
					
					this.loading = false;
				})
			},
			getHouseActivty() {
				this.$http.ajax({
					url: '/index/adv/getAdvByFlag',
					data: {
						falg: 'h5_home_estates',
						city_no: this.city_no,
						limit: 999
					}
				}).then( res=>{
					const data = res.data;
					
					// console.log(res)
					// console.log(data)
					this.$set( this.tabList[1], 'activityList', data );
				})
			},
			getHouseList() {
				const data = {
					city_no: this.city_no,
					recommend: 1
				};
				
				// console.log(data)
				
				this.$http.ajax({
					url: '/index/estates/getEstatesList',
					data: data
				}).then( res=>{
					const data = res.data;
					
					this.activeList = this.$api.createHouseList( data, 1 );
					// console.log(res)
					// console.log(data)
					if( !this.activeList || this.activeList.length == 0 ) {
						this.no_data = true;
					}
				})
			},
			goActive(e) {
				if(!$api.trim(e.href)&&e.info){
					e.href = 'houses/index.html?id='+e.info.estate_id+'&cover='+e.cover;
				}
				if(!e.href){
					return
				}
				$api.goPage(e.href)
			},
			// 查看更多跳转
			start( e ) {
				// console.log(this.$refs)
				let el = this.$refs.contentBox.parentElement;
				// console.log(el.scrollHeight,'el.scrollHeight---',this.$refs.contentBox.scrollHeight)
				// console.log(el.scrollTop,'el.scrollTop---',this.$refs.contentBox.scrollTop)
				// console.log(el.clientHeight,'el.clientHeight---',this.$refs.contentBox.clientHeight)
				// console.log(el.scrollHeight - el.scrollTop - el.clientHeight)
				if( el.scrollHeight - el.scrollTop - el.clientHeight < 1){
					this.startY = Number(e.changedTouches[0].clientY.toFixed(2));
					console.log('到底了',this.startY)
				}
			},
			move( e ) {
				if( this.startY == 0 ){
					return;
				}
				
				let move = Number(e.changedTouches[0].clientY.toFixed(2));
				
				if( move < this.startY ) {
					const num = Number((this.startY - move).toFixed(2));
					if( num < 60 ){
						this.moveY = -num;
					}
					
					if(  Math.abs(num) < 40 ){
						this.moreText = '再往上拉查看更多~'
					} else {
						this.moreText = '跳转新房~'
					}
				}
			},
			end( e ) {
				if( this.startY == 0 ){
					return;
				}
			
				if(  Math.abs(this.moveY) > 40 ){
					this.$api.goPage('new_house/index.html');
				}
				
				this.startY = 0;
				this.moveY = 0;
				this.moreText = '再往上拉查看更多~';
			},


			formatAdv(advlist){
				if(advlist &&  !advlist.href&&advlist.info){
					advlist.href = 'houses/index.html?id='+advlist.info.estate_id+'&cover='+advlist.cover;
				}
				
				let tips = [];	
				let new_lab = [];	
				if(advlist && advlist.info){
					let adv_info = advlist.info															
					tips = tips.concat($api.getTagsText('estatesnew_sale_status',advlist.info.sale_status));
					tips = tips.concat($api.getTagsText('house_purpose',advlist.info.house_purpose));
					if(advlist.info.feature_tag){
						tips = tips.concat($api.getTagsText('feature_tag',advlist.info.feature_tag));
					}
					advlist.info.tip = tips;

					if(advlist.info.lab){
						let lab = advlist.info.lab
						
						for(let i in lab){
							let item = lab[i]
							if(item.type == 'discount'){
								item.type = 1;
								new_lab.push({
									name: item.title,
									type: item.type,
								})
							}
							if(item.type == 'hot'){
								item.type = 0;
								new_lab.push({
									name: item.title,
									type: item.type,
								})
							}
						}
					}
					advlist.info.lab = new_lab;
				}
				
				
				return advlist;
			},
		}
	}
}());