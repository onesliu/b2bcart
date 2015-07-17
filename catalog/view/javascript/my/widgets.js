define(['mustache'], function(Mustache) {
    var	isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
		sClick = isMobile ? 'tap' : 'click';

	var Amount = {
		minus: null,
		text: null,
		plus: null,
		func: null,
		init: function(minus, text, plus, func) {
			var $this = this;
			$this.minus = minus;
			$this.text = text;
			$this.plus = plus;
			$this.func = func;
			
			$this.minus.bind(sClick, function(e){
				e.stopPropagation();
				$this = Amount;
				val = $this.text.val();
				if (val <= 1) return;
				$this.text.val(--val);
				if ($this.func) $this.func(val);
			});
			$this.text.bind(sClick, function(e){
				e.stopPropagation();
			});
			$this.plus.bind(sClick, function(e){
				e.stopPropagation();
				$this = Amount;
				val = $this.text.val();
				$this.text.val(++val);
				if ($this.func) $this.func(val);
			});
		},
		register: function(func) {
			this.func = func;
		},
		clear: function() {
			if (this.minus) {
				this.minus.unbind(sClick);
				this.text.unbind(sClick);
				this.plus.unbind(sClick);
				this.minus = null;
				this.text = null;
				this.plus = null;
			}
			this.func = null;
		}
	};
	
	function dropAnimation($dropobj, $aim_x, $aim_y) {
		var $ani = $dropobj.clone();
		$dropobj.parent().append($ani);
		$ani.css({
			'position': 'absolute',
			'top': $dropobj.position().top,
			'left': $dropobj.position().left
		})
		$ani.animate({
			background: '#fff',
			zIndex: 9999999,
			left: $aim_x,
			top: $dropobj.position().top + $aim_y,
			width: 0,
			height: 0,
			opacity: 0
		}, 800, function() {
			$(this).remove();
			$ani = null;
		})
	}
	
	return {
		amount: Amount,
		dropAnimation: dropAnimation
	}
})