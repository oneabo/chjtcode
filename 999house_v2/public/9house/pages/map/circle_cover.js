let Circle_zIndex = 2;
// 自定义区饼图 - 继承DOMOverlay
function Donut(options) {
	TMap.DOMOverlay.call(this, options);
}

Donut.prototype = new TMap.DOMOverlay();

// 初始化
Donut.prototype.onInit = function(options) {
	this.map = options.map;
	this.position = options.position;
	this.content = options.content || '请输入content内容';
	this.count = options.count || '';
};

// 销毁时需解绑事件监听
Donut.prototype.onDestroy = function() {
	if (this.onClick) {
		this.dom.removeEventListener('click',this.onClick);
	}
};

// 创建DOM元素，返回一个DOMElement，使用this.dom可以获取到这个元素
Donut.prototype.createDOM = function() {
	let circle = document.createElement("div");
	let name = document.createElement("div");
	let count = document.createElement("div");
	let width = this.width;
	let height = this.height;
	
	circle.className = 'my_Circle';
	
	circle.style.cssText = `
		background: rgba(254, 130, 30, 1);
		width: 1.2rem;
		height: 1.2rem;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		border-radius: 50%;
		font-size: .24rem;
		color: #fff;
		text-align: center;
		position: absolute;
		animation: fade-in;
		animation-duration: .3s;  
		-webkit-animation:fade-in .3s;
	`;
	
	name.style.cssText = count.style.cssText = `
		width: 80%;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	`;
	
	name.innerHTML = this.content;
	
	// console.log(this.count)
	
	if( this.count < 1000 ){
		count.innerHTML = this.count + '套';
	} else {
		count.innerHTML = '999+套';
	}
	
	circle.appendChild(name);
	circle.appendChild(count);
	
	// click事件监听
	this.onClick = (e) => {
		Circle_zIndex++;
		this.dom.style.zIndex = Circle_zIndex;
		// DOMOverlay继承自EventEmitter，可以使用emit触发事件
		this.emit('click');
		
		e&&e.stopPropagation&&e.stopPropagation();
	};
	
	circle.addEventListener('click', this.onClick);
	
	return circle;
};

// 更新DOM元素，在地图移动/缩放后执行
Donut.prototype.updateDOM = function(e) {

	if (!this.map) {
		return;
	}
	
	// 经纬度坐标转容器像素坐标
	let pixel = this.map.projectToContainer(this.position);
	
	// 使饼图中心点对齐经纬度坐标点
	let left = pixel.getX() - this.dom.clientWidth / 2 + 'px';
	let top = pixel.getY() - this.dom.clientHeight / 2 + 'px';
	this.dom.style.transform = `translate(${left}, ${top})`;
	
	// if( e ){
	// 	const zoom = e.target.getZoom();
		
	// 	if( zoom >= 13.5 ){
	// 		this.dom.style.display = 'none'
	// 	} else {
	// 		this.dom.style.display = 'flex';
	// 	}
	// }
};

window.Donut = Donut;