define(['mustache', 'widgets', 'cart', 'delayImg'], function(Mustache, widgets, cart, dimg) {
    var	isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
		sClick = isMobile ? 'tap' : 'click';
		
	var Products = {
		uri: $.baseUrl + 'index.php?route=rest/products&category=',
		dataAll: null,
		dataList: null,
		childArr: null,
		pList: $('#productList'),
		pTemp: $('#productTemp').html(),
		cur_li: null,
		no_image: "",
		limit_catid: 0,
		productAjax: function(cat_id, limit) {
			if (cat_id == null) return;
            var $this = this;
			if (limit > 0) $this.limit_catid = cat_id;

            $.ajax({
                url: $this.uri + cat_id,
                type: 'POST',
                dataType: 'json',
				data: {
					limit: limit
				},
                success: function(data) {
                    if (data.result == '0') {
						$this.no_image = data.no_image;
						if ($this.dataAll == null)
							$this.dataAll = {};
                        $this.dataAll[cat_id] = data;
                        $this.buildData(data.info);
						cart.sumView();
						
						if (cat_id == $this.limit_catid && limit == 0)
							$this.showProduct(cat_id, null);
                    } else {
                    }
                }
            })
        },
		buildData: function(data) {
			var $this = this;
			if (data.length == 0) {
                return false;
            }
			if ($this.childArr == null)
				$this.childArr = [];
            [].forEach.call(data, function(item, i) {
				var category_id = item.category_id;
				if ($this.childArr[category_id] == null)
					$this.childArr[category_id] = [];
				item.calcprice = function() {
					return '￥' + (this.sellprice-0).toFixed(2);
				};
				item.number = function() {
					var incart = cart.incart(item.product_id);
					if (incart > 0)
						return incart;
					return item.minimum;
				};
				item.buycls = function() {
					var incart = cart.incart(item.product_id);
					if (incart > 0)
						return 'buybutton';
					return '';
				};
				item.buylabel = function() {
					var incart = cart.incart(item.product_id);
					if (incart > 0)
						return '取消';
					return '购买';
				};
				item.no_image = $this.no_image;
				$this.childArr[category_id].push(item);
				var pid = item.product_id;
				if ($this.dataList == null)
					$this.dataList = {};
				$this.dataList[pid] = item;
            });
		},
		showProduct: function(pid, cid) {
			var $this = this;
			if ($this.dataAll == null) return;
			
			var dataArr = {info: null};
			if (pid != null) {
				if ($this.dataAll.hasOwnProperty(pid))
					dataArr.info = $this.dataAll[pid].info;
			}
			else if (cid != null) {
				dataArr.info = $this.childArr[cid];
			}
			
			if (dataArr.info != null) {
				$this.pList.html(Mustache.render($this.pTemp, dataArr));
				dimg.init($this.pList.find('li'));
				$this.pList.find('.amountBtn').bind(sClick, function(e) {
					e.stopPropagation();
					$this.hideAmount();
					$this.cur_li = $(this).parent().parent();
					var amount = $(this).prev('.item-amount');
					var aBtn = $(this);
					var bBtn = $(this).next('.buyBtn');
					amount.find('.text-amount').val(aBtn.find('button').text());
					if (widgets) {
						widgets.amount.init(amount.find('.minus'), amount.find('.text-amount'), amount.find('.plus'),
							function(val) {
								aBtn.find('button').text(val);
								$this.changeAmount(val);
							}
						);
					}
					if (amount.css('display') == 'none') {
						amount.show();
						aBtn.hide();
						bBtn.hide();
					}
				});
				$this.pList.listview( "refresh" );
				$this.pList.find('.buyBtn').bind(sClick, function(e) {
					e.stopPropagation();
					$this.cur_li = $(this).parent().parent();
					$this.toggleProduct();
				});
			}
		},
		hideAmount: function() {
			var $this = this;
			if ($this.pList == null) return;
			$this.pList.find('.item-amount').hide();
			$this.pList.find('.amountBtn').show();
			$this.pList.find('.buyBtn').show();
			if (widgets) widgets.amount.clear();
			$this.cur_li = null;
		},
		changeAmount: function(val) {
			var $this = this;
			if ($this.cur_li == null) return;
			var buyBtn = $this.cur_li.find('.buyBtn').find('button');
			var product_id = $this.cur_li.attr('product_id');
			if (!buyBtn.hasClass('buybutton')) {
				$this.toggleProduct();
			}
			if (cart) cart.addmodify($this.dataList[product_id], val);
		},
		toggleProduct: function() {
			var $this = this;
			if ($this.cur_li == null) return;
			var btn = $this.cur_li.find('.buyBtn').find('button');
			var product_id = $this.cur_li.attr('product_id');
			var num = $this.cur_li.find('.amountBtn').find('button').html();
			btn.toggleClass('buybutton');
			if (btn.hasClass('buybutton')) {
				widgets.dropAnimation($this.cur_li, $(window).width()/4, $(window).height() - 20);
				btn.text(btn.attr('del'));
				if (cart) cart.addmodify($this.dataList[product_id], num);
			}
			else {
				btn.text(btn.attr('buy'));
				if (cart) cart.del(product_id);
			}
		},
		init: function(cat_arr) {
			var $this = this;
			if (cat_arr == null || cat_arr.length == 0)
				return;
			Array.prototype.forEach.call(cat_arr, function(cat, i) {
				if (i == 0) {
					$this.productAjax(cat.cat_id, 10);
				}
				setTimeout(function(){
					$this.productAjax(cat.cat_id, 0);
				}, 500);
			});
			$(document).off(sClick, "#homepage");
			$(document).on(sClick, "#homepage", function() {
				$this.hideAmount();
			});
		}
	};
	
	return {
		init : function init(cat_arr) {
			Products.init(cat_arr);
			cart.init(Products);
		},
		showProducts: function(pid, cid) {
			Products.showProduct(pid, cid);
		}
　　};
})