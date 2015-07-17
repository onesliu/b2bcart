define(['mustache', 'widgets'], function(Mustache, widgets) {
	var	isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
		sClick = isMobile ? 'tap' : 'click';
		
	return {
		//数据MODEL与Controller
		uri: $.baseUrl + 'index.php?route=rest/cart',
		products: {},
		productObj: null,
		total: 0,
		init: function(p) {
			this.productObj = p;
			this.getAjax();
		},
		clear: function() {
			delete this.products;
			this.products = {};
			this.total = 0;
			this.sumView();
		},
		incart: function(pid) {
			if (this.products.hasOwnProperty(pid))
				return this.products[pid].number;
			return 0;
		},
		addmodify: function(product, num) {
			if (!this.products.hasOwnProperty(product.product_id)) {
				this.addAjax(product, num);
			} else {
				this.updateAjax(product.product_id, num);
			}
		},
		del: function(pid) {
			if (this.products.hasOwnProperty(pid)) {
				this.removeAjax(pid);
			}
		},
		getAjax: function() {
			var $this = this;
			$.ajax({
                url: $this.uri,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.result == '0') {
						$this.clear();
						$this.total = data.total;
						
						[].forEach.call(data.products, function(item, i) {
							$this.products[item.pid] = {
								image: item.image,
								number: item.number,
								name: item.name,
								sellunit: item.sellunit,
								sellprice: item.sellprice
							};
						});
                    }
					$this.sumView();
                }
            });
		},
		addAjax: function(product, num) {
			var $this = this;
			$.ajax({
                url: $this.uri + "/add",
                type: 'POST',
                dataType: 'json',
				data: {
					product_id: product.product_id,
					number: num
				},
                success: function(data) {
                    if (data.result == '0') {
						$this.total += 1;
						$this.products[product.product_id] = {
							image: product.image,
							number: num,
							name: product.name,
							sellunit: product.sellunit,
							sellprice: product.sellprice
						};
					} else {
						alert("add to cart error.");
                    }
					$this.sumView();
                }
            });
		},
		updateAjax: function(pid, num) {
			var $this = this;
			$.ajax({
                url: $this.uri + "/update",
                type: 'POST',
                dataType: 'json',
				data: {
					product_id: pid,
					number: num
				},
                success: function(data) {
                    if (data.result == '0') {
						$this.products[pid].number = num;
					} else {
						alert("update cart error.");
                    }
					$this.sumView();
                }
            });
		},
		removeAjax: function(pid) {
			var $this = this;
			$.ajax({
                url: $this.uri + "/remove",
                type: 'POST',
                dataType: 'json',
				data: {
					product_id: pid,
				},
                success: function(data) {
                    if (data.result == '0') {
						delete $this.products[pid];
						$this.total -= 1;
					} else {
						alert("remove from cart error.");
                    }
					$this.showCartList();
					$this.sumView();
                }
            });
		},
		
		//界面VIEW
		sumCount: $('#sumCount'),
		sumPrice: $('#sumPrice'),
		sumCart: $("#sumCart"),
		sumView: function() {
			var $this = this,
				sum = 0;
			if (!$this.sumCount || !$this.sumPrice) return;
			for(var id in $this.products){
				if($this.products.hasOwnProperty(id)){
					var p = $this.products[id];
					sum += p.number * p.sellprice;
				}
			}
			
			this.sumCount.html(this.total);
			this.sumPrice.html(sum.toFixed(2));
			this.sumCart.html(sum.toFixed(2));
		},
		
		cartTemp: $('#cartTemp').html(),
		cartList: $('#cartlist'),
		cur_li: null,
		showCartList: function() {
			var $this = this;
			var info = {info: []};
			for(var id in $this.products){
				if($this.products.hasOwnProperty(id)){
					$this.products[id].product_id = id;
					$this.products[id].calcprice = function() {
						return '￥' + (this.sellprice-0).toFixed(2);
					};
					$this.products[id].totalprice = function() {
						return '￥' + (this.sellprice * this.number).toFixed(2);
					};
					info.info.push($this.products[id]);
				}
			}
			
			$this.cartList.html(Mustache.render($this.cartTemp, info));
			$this.cartList.find('.amountBtn').bind(sClick, function(e) {
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
							$this.cartList.listview( "refresh" );
						}
					);
				}
				if (amount.css('display') == 'none') {
					amount.show();
					aBtn.hide();
					bBtn.hide();
				}
			});
			$this.cartList.find('.buyBtn').bind(sClick, function(e) {
				e.stopPropagation();
				$this.cur_li = $(this).parent().parent();
				$this.del($this.cur_li.attr('product_id'));
			});
			
			$(document).off(sClick, "#cartpage");
			$(document).on(sClick, "#cartpage", function() {
				$this.hideAmount();
				$this.showCartList();
			});

			$this.cartList.listview( "refresh" );
			$this.sumView();
		},
		hideAmount: function() {
			var $this = this;
			if ($this.cartList == null) return;
			$this.cartList.find('.item-amount').hide();
			$this.cartList.find('.amountBtn').show();
			$this.cartList.find('.buyBtn').show();
			if (widgets) widgets.amount.clear();
			$this.cur_li = null;
		},
		changeAmount: function(val) {
			var $this = this;
			if ($this.cur_li == null) return;
			var product_id = $this.cur_li.attr('product_id');
			$this.addmodify($this.products[product_id], val);
		}
	}
})
