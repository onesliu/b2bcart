/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-12-1
 * @templatePage：lazy img
 * @phpTemplate
 */
define(function(require, exports, module) {
	// 滚动插件
	var $ = require('zepto');

	function lazyImg() {
		return new LazyImg();
	}

	function LazyImg() {
		this.init();
	}

	LazyImg.prototype.init = function() {
		var that = this;
		that.changeSrc();
		$(window).on('load resize scroll', function() {
			that.changeSrc();
		})
	}

	/**
	 * [lazyImg 图片加载]
	 * @return {[type]} [null]
	 */
	LazyImg.prototype.changeSrc = function() {
		var that = this;
		$.each($('[img-src]'), function() {
			var $this = $(this);
			if (that.eleInScreen($this)) {
				var src = $this.attr('img-src');
				if ($this.get(0).nodeName.toUpperCase() == 'IMG') {
					$this.attr('src', src);
				} else {
					$this.css({
						backgroundImage: 'url(' + src + ')'
					})
				}
				$(this).removeAttr('img-src');
			}
		})
	}

	/**
	 * [eleInScreen 元素是否在屏幕中]
	 * @param  {[type]} $ele [*zepto 对象]
	 * @return {[type]}      [boolean]
	 */
	LazyImg.prototype.eleInScreen = function($ele) {
		if ($ele.offset().top < $(window).scrollTop() + $(window).height()) {
			return true;
		} else {
			return false;
		}
	}
	lazyImg();
	module.exports = lazyImg;
})