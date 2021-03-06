var commonArea = (function() {
	const html =
		`<div class="site">
					<div class="content" :style="{ height: height + 'rem' }">
						<div class="content-left">
							<div 
								class="content-left-item" 
								:class="choosing.left == index ? 'content-active' : ''"
								v-for="(item,index) in list" 
								:key="index"
								@click="choosingSite('left',index)"
							>
								<div class="content-left-item van-ellipsis" >
									{{ item.title }}
								</div>
							</div>
						</div>
						<div class="content-center">
							<template v-if="list[choosing.left].list && list[choosing.left].list.length > 0">
								<template v-for="(item,index) in list[choosing.left].list" >
									<div
										class="content-left-item" 
										:class="[
											choosing.center == index ? 'content-active' : '',
											(Object.keys(choosing.right).indexOf(String(index)) != -1 && choosing.right[index] && choosing.right[index].length > 0) ? 'content-active' : ''
										]"
										:key="index"
										@click="choosingSite('center',index)"
									>
										<div class="content-left-item van-ellipsis" >
											{{ item.name }}
										</div>
									</div>
								</template>
							</template>
						</div>
						<div class="content-right">
							<template v-if="list[choosing.left].list[choosing.center].list && list[choosing.left].list[choosing.center].list.length > 0">
								<template v-for="(item,index) in list[choosing.left].list[choosing.center].list" >
									<div
										class="content-left-item" 
										:class="(choosing.right[choosing.center] && choosing.right[choosing.center].indexOf(index) != -1) ? 'content-active' : ''"
										:key="index"
										@click="choosingSite('right',index)"
									>
										<div class="content-left-item-right">
											<div class="content-left-item-right van-ellipsis" >
												{{ item.name }}
											</div>
											<span 
												class="iconfont"
												:class="(choosing.right[choosing.center] && choosing.right[choosing.center].indexOf(index) != -1) ? 'iconnewxuanzhongduoxuan' : 'iconweixuanzhong'"
											>
											</span>
										</div>
										
									</div>
								</template>
							</template>
						</div>
					</div>
					<div class="location-btn">
						<template v-if="!clear">
							<van-button
								class="location-btn-close" 
								type="default" 
								plain 
								@click="close"
							>
								{{ clear == true ? '??????' : '??????' }}
							</van-button>
						</template>
						<template v-else>
							<div class="location-btn-icon" @click="close">
								<i class="iconfont iconqingchu"></i>
								<span>??????</span>
							</div>
						</template>
						
						<van-button 
							class="location-btn-sure" 
							type="default"
							color="rgba(254, 130, 30, 1)"
							@click="sure"
						>
							??????
						</van-button>
					</div>
				</div>`;

	return {
		// inject: ['default_data'],
		data: function() {
			return {
				choosing: {
					left: 0,
					center: 0,
					right: {}
				},
				lastChoose: {
					left: 0,
					center: 0,
					right: {}
				}
			}
		},
		template: html,
		props: {
			list: {
				type: [Array],
				default () {
					return []
				}
			},
			height: {
				type: [String, Number],
				default () {
					return '7.16'
				}
			},
			clear: {
				type: Boolean,
				default () {
					return true
				}
			},
			default_data: {
				type: [Object],
				default () {
					return ''
				}
			}
		},
		watch: {
			default_data: {
				handler(val) {
					console.log(999, val)
				},
				deep: true,
			}
		},
		created() {
			if (this.default_data && Object.keys(this.default_data).length) {
				//console.log('default_data',this.default_data)
				let list = [];
				//???????????????
				if (this.default_data.site_center.type == 'area') {
					list = this.list[0].list
					this.choosingSite('left', 0) //????????????
				} else if (this.default_data.site_center.type == 'subway') {

					list = this.list[1].list
					this.choosingSite('left', 1) //????????????
				} else {
					return
				}
				//???????????????
				this.setColumnById(this.default_data.site_center.pid, list, 'center')

				//???????????????
				if (typeof(this.default_data.id) == 'object') { //??????
					this.default_data.id.map((item) => {
						this.setColumnById(item, list, 'right')
					})
				} else { //??????
					this.setColumnById(this.default_data.id, list, 'right')
				}
				this.lastChoose = this.$api.deepClone(this.choosing);
			}

		},
		methods: {
			setColumnById(id, list, type = 0) {
				let index1 = 0;
				let index2 = 0;
				if (type == 'center') {
					id && list && list.map((item, idx) => {
						if (item.id == id) {
							index1 = idx
							return;
						}
					})
					this.choosingSite('center', index1)
				}
				if (type == 'right') {
					id && list && list.map((item) => {
						item.list && item.list.map((item2, idx2) => {
							if (String(id).indexOf('p_') != -1) { //???????????????
								return
							}
							if (item2.id == id) {
								index2 = idx2
								return;
							}
						})
					})

					index2 && this.choosingSite('right', index2)
				}
			},
			choosingSite(key, index) {
				const el = this.choosing[key];

				switch (key) {
					case 'left':

						this.choosing = {
							left: 0,
							center: 0,
							right: {},
						}

						this.choosing[key] = index;
						break;
					case 'center':
						// // ??????
						// if( index == 0 ){
						// 	this.choosing.right = {};
						// }

						// ??????????????????
						this.$set(this.choosing, 'right', {});
						this.choosing[key] = index;

						this.$set(this.choosing['right'], this.choosing['center'], [0]);
						const id = this.list[this.choosing['left']].list[this.choosing['center']].id;
						// console.log(id)
						// this.$emit('sure',this.list[this.choosing['left']].list[this.choosing['center']].name,id);
						break;
					case 'right':

						let len = 0;

						for (let key in el) {
							len += el[key].length;
						}

						// ???????????????
						if (el[this.choosing['center']]) {

							const num = el[this.choosing['center']].indexOf(index);

							// ????????????
							if (num == -1) {

								// ???????????????
								if (index != 0) {

									// ??????????????????
									if (el[this.choosing['center']].indexOf(0) != -1) {
										el[this.choosing['center']].splice(el[this.choosing['center']].indexOf(0), 1);
									}

									// ??????<6
									if (len < 5) {
										this.choosing[key][this.choosing['center']].push(index);
									} else {
										this.$toast('??????????????????5????????????')
									}

								} else {
									if (len < 5) {
										this.$set(this.choosing[key], this.choosing['center'], []);
										this.choosing[key][this.choosing['center']].push(index);

									} else {
										this.$toast('??????????????????5????????????')
									}
								}
							} else {
								el[this.choosing['center']].splice(num, 1);
							}

						} else {
							if (len < 5) {
								this.$set(this.choosing[key], this.choosing['center'], [index]);
							} else {
								this.$toast('??????????????????5????????????')
							}
							// console.log(this.choosing)
						}

						return;
				}
			},
			reset() {
				this.choosing = this.$api.deepClone(this.lastChoose);
				// console.log(this.choosing)
			},
			clearSite() {
				this.choosing = {
					left: 0,
					center: 0,
					right: {}
				};

				this.lastChoose = this.$api.deepClone(this.choosing);

				this.$emit('sure', '??????');
				this.$emit('close');
			},
			close() {
				if (this.clear == true) {
					this.clearSite();
				} else {
					this.reset();
					this.$emit('close');
				}
			},
			sure() {
				let text = '';
				let len = 0;
				let id = [];
				let center = {
					pid: 0,
					type: ''
				}; //?????????

				this.lastChoose = this.$api.deepClone(this.choosing);

				for (let k in this.choosing.right) {
					len += Number(this.choosing.right[k].length);
				}

				if (len == 0) {
					text = '??????';
				} else {
					const list = this.list[this.choosing.left].list;
					for (let key in this.choosing.right) {
						if (this.choosing.right[key].indexOf(0) != -1) { //	??????
							text += list[key].name + ',';
							id = 'p_' + list[key].id; //??????p???????????????id
							center.pid = list[key].id;
						} else {
							this.choosing.right[key].map(item => {
								text += list[key].list[item].name + ',';
								id.push(list[key].list[item].id);
								center.pid = list[key].id;
							})
						}
					}
					if (center.pid != 0) {
						if (this.choosing.left == '0') {
							center.type = 'area'
						} else {
							center.type = 'subway'
						}
					} else {
						center.type = ''
					}
				}

				console.log(text, id, center)
				this.$emit('sure', text, id, center);
				this.$emit('close');
			},
		},
	}
}());
