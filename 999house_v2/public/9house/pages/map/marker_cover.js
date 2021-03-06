
// 自定义定位点标签 - 继承DOMOverlay
function Marker(options) {
	TMap.DOMOverlay.call(this, options);
}

Marker.prototype = new TMap.DOMOverlay();

// 初始化
Marker.prototype.onInit = function(options) {
	this.map = options.map;
	this.position = options.position;
	this.style = options.style;
};

// 销毁时需解绑事件监听
Marker.prototype.onDestroy = function() {
	if (this.onClick) {
		this.dom.removeEventListener('click',this.onClick);
	}
	this.removeAllListeners();
};

// 创建DOM元素，返回一个DOMElement，使用this.dom可以获取到这个元素
Marker.prototype.createDOM = function() {
	let tip = document.createElement("div");
	
	tip.className = 'user_site';
	
	tip.style.cssText = `
		position: absolute;
		animation: fade-in;
		animation-duration: .3s;  
		-webkit-animation: fade-in .3s;
		${ this.style['src'] ? ('background-image:' + 'url(' + this.style['src'] + ');') : ''  }
		background-size: 100% 100%;
		background-repeat: no-repeat;
		z-index: 9;
	`;
	
	for( let i in this.style){
		if( i != 'src' ){
			tip.style[i] = this.style[i];
		}
	}
	
	// click事件监听
	this.onClick = (e) => {
		e&&e.stopPropagation&&e.stopPropagation();
	};
	
	return tip;
};

// 更新DOM元素，在地图移动/缩放后执行
Marker.prototype.updateDOM = function(e) {
	if (!this.map) {
		return;
	}
	
	// 经纬度坐标转容器像素坐标
	let pixel = this.map.projectToContainer(this.position);
	
	// 使饼图中心点对齐经纬度坐标点
	let left = pixel.getX() - this.dom.clientWidth / 2 + 'px';
	let top = pixel.getY() - this.dom.clientHeight / 2 + 'px';
	this.dom.style.transform = `translate(${left}, ${top})`;
};

window.Marker = Marker;