window.main = {};
//图片懒加载函数封装
function Limg() {
	var viewHeight = document.documentElement.clientHeight // 可视区域的高度
	var t = document.documentElement.scrollTop || document.body.scrollTop;
	var limg = document.querySelectorAll("[data-xurl]")
	// Array.prototype.forEach.call()是一种快速的方法访问forEach，并将空数组的this换成想要遍历的list
	Array.prototype.forEach.call(limg, function(item, index) {
		var rect
		if(item.getAttribute("data-xurl") === "")
			return
		//getBoundingClientRect用于获取某个元素相对于视窗的位置集合。集合中有top, right, bottom, left等属性。
		rect = item.getBoundingClientRect()
		// 图片一进入可视区，动态加载
		if(rect.bottom >= 0 && rect.top < viewHeight) {
			(function() {
				var img = new Image();
				var qq=item.getAttribute("data-type");

				if(item.nodeName==='IMG'){
				img.src = item.getAttribute("data-xurl");
				img.onload = function(){
				item.src = img.src;
item.classList.add('ojbk');msnry.layout();
				}
img.onerror= ()=> {
item.src = globals.themes+'images/break.svg';
item.classList.add('ojbk','object-contain');
console.info('图片加载失败，图片地址：'+img.src);
}
				}
				item.removeAttribute('data-xurl')
			})()
		}
	})
}


main.all=function(){
Limg();
if(document.querySelector('.open')){
document.querySelector('.open').onclick=function(){
    document.querySelector('#about').classList.remove('hidden');
    document.querySelector('body').classList.add('overflow-hidden');
}
	let close = document.querySelectorAll('.close');
	Array.prototype.forEach.call(close, function(item, index) {
	    item.onclick=function(){
	        document.querySelector('#about').classList.add('hidden');
	        document.querySelector('body').classList.remove('overflow-hidden');
	    }
	});
}	
window.onscroll = function(){Limg();}
};
main.all();

cn={
  CLOSE: "关闭",
  NEXT: "下一张",
  PREV: "上一张",
  MODAL: "您可以使用 ESC 键关闭此模式",
  ERROR: "出现错误，请稍后再次尝试",
  IMAGE_ERROR: "未找到图片",
  ELEMENT_NOT_FOUND: "找不到 HTML 内容",
  AJAX_NOT_FOUND: "AJAX 加载错误：未找到",
  AJAX_FORBIDDEN: "AJAX 加载错误：禁止",
  IFRAME_ERROR: "加载页面时出错",
  TOGGLE_ZOOM: "缩放",
  TOGGLE_THUMBS: "切换缩略图",
  TOGGLE_SLIDESHOW: "播放/暂停幻灯片",
  TOGGLE_FULLSCREEN: "切换全屏模式",
  DOWNLOAD: "下载",
};
Fancybox.defaults.l10n = cn;
Fancybox.defaults.infinite=globals.fancyboxinfinite;
Fancybox.defaults.preload=globals.fancyboxinpreload;
Fancybox.bind("[data-fancybox]", {
  on: {
    destroy: (fancybox, slide) => {},
    ready: (fancybox, slide) => {},
  },
  Toolbar: {
    display: [
      { id: "prev", position: "center" },
      { id: "counter", position: "center" },
      { id: "next", position: "center" },
      "zoom",
      "slideshow",
      "fullscreen",
      "download",
      "thumbs",
      "close",
    ],
  },
});


if(document.querySelector('.ajax-container')){
let ias = new InfiniteAjaxScroll('.ajax-container', {
  item: '.ajax-post',
  next: '.ajax-next',
  pagination: '.ajax-pagination',
  negativeMargin: 300,
  spinner: {
    // element to show as spinner
    element: '.ajax-spinner',

    // delay in milliseconds
    // this is the minimal time the loader should be displayed. If loading takes longer, the spinner
    // will be shown for the duration of the loading. If the loading takes less then this duration,
    // say 300ms, then the spinner is still shown for 600ms.
    delay: 300,

    // this function is called when the button has to be shown
    show: function(element) {
      element.classList.remove('hidden'); // default behaviour
    },

    // this function is called when the button has to be hidden
    hide: function(element) {
      element.classList.add('hidden'); // default behaviour
    }
  }
});

/**
 * Triggers Masonry layout every time an image loads. Also
 * adds the `demo--loaded` class to make the item visible.
 *
 * Returns a promise so you can act when all images are done loading
 * and the layout update has finished.
 *
 * @param elem
 * @returns {Promise<any>}
 */
function imagesLoadedAndLayout(elem) {
  return new Promise((resolve) => {
    imagesLoaded(elem)
        .on('progress', (imgLoad, e) => {
          e.img.parentNode.parentNode.parentNode.classList.add('in-loaded');
          msnry.appended(e.img.parentNode.parentNode.parentNode);
          msnry.layout();
        })
        .on('done', () => {
          msnry.once('layoutComplete', () => {
            resolve();
          });
        });
  });
}

// wrap the append function so that IAS will wait for all
// images to load before considering the items to be appended
ias.on('append', (event) => {
  let appendFn = event.appendFn;

  event.appendFn = (items, parent, last) => {
    return new Promise((resolve) => {
      appendFn(items, parent, last);

      imagesLoadedAndLayout(items).then(resolve);
    });
  };
});

ias.on('last', () => {
document.querySelector('.ajax-nomore').classList.remove('hidden');
});

}else{
ias.on('nexted', () => {
Limg();
});

}