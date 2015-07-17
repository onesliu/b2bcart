define([], function () {

	return {
		refreshTimer: null,
		listView: null,
		/*
		*滚动结束 屏幕静止一秒后检测哪些图片出现在viewport中
		*和PC端不同 由于无线速度限制 和手机运算能力的差异 1秒钟的延迟对手机端的用户来说可以忍受
		*/
		init: function(listView) {
			var $this = this;
			if (!listView || listView.length <= 0) return;
			$this.listView = listView;
			$this.getInViewportList($this.listView);
			
			$(window).off('scrollstop');
			$(window).on('scrollstop', function () {
				if ($this.refreshTimer) {
					clearTimeout($this.refreshTimer);
					$this.refreshTimer = null;
				}
				$this.refreshTimer = setTimeout(function() {
					$this.getInViewportList($this.listView);
				}, 600);
			});
		},
		belowthefold : function (element) {
			var fold = $(window).height() + $(window).scrollTop();
			return fold <= $(element).offset().top;
		},
		abovethetop : function (element) {
			var top = $(window).scrollTop();
			return top >= $(element).offset().top + $(element).height();
		},
		/*
		*判断元素是否出现在viewport中 依赖于上两个扩展方法
		*/
		inViewport : function (element) {
			return !this.belowthefold(element) && !this.abovethetop(element)
		},
		getInViewportList : function (list) {
			var $this = this,
				ret = []; //list = $('#bookList li'),
			list.each(function (i) {
				var li = list.eq(i);
				if ($this.inViewport(li)) {
					$this.loadImg(li);
				}
			});
		},
		loadImg : function (li) {
			if (li.find('img[_src]').length) {
				var img = li.find('img[_src]'),
					src = img.attr('_src');
				img.attr('src', src).load(function () {
					img.removeAttr('_src');
				});
			}
		}
	}
});