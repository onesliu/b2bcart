define(['iscroll', 'mustache', 'products'], function(iscroll, Mustache, products) {
    var	isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
		sClick = isMobile ? 'tap' : 'click';

    /****************************************************
    分类滚动
    ****************************************************/
	CatScroll = function (wrapper) {
		this.wrapper = wrapper;
		this.sview = wrapper.find('ul');
	};
	
	CatScroll.prototype = {
		cateTemp: $('#categoryTemp').html(),
		wrapper: null,
		currentId: 0,
		scroll: null,
		data: null,
		itemFunc: null,
		setScroll: function() {
			var $this = this;
			
			if ($this.scroll) {
				$this.scroll.stop();
				$this.scroll.destroy();
			}
			
			var wapper = $this.wrapper.attr('id');

			$this.scroll = new iScroll(wapper, {
				vScroll: false,
				hScrollbar: false,
				vScrollbar: false,
				onScrollMove: function(e) {
					e.preventDefault();
				}
			});
			
			$this.scroll.refresh();
		},
		setView: function() {
			var $this = this;

			$this.sview.css({
                width: 10000
            });
			
			$this.sview.html(Mustache.render($this.cateTemp, $this.data));

			$li = $this.sview.find('li'),
			$ul = $li.parent();
			$num = 0;
			[].forEach.call($li, function(ele, i) {
				var $a = ele.getElementsByTagName('a')[0],
					$a_w = $a.offsetWidth,
					$li_w = ele.offsetWidth;
				$num += Math.max($a_w, $li_w) + 1;
			});

			$ul.css({
				width: $num * 1.5
			});
		},
		selectItem: function() {
			var $self = this,
				$sview = $self.sview;
				$sview.find('li').off(sClick);
				$sview.find('li').on(sClick, function(e) {
					//e.stopPropagation();
					var $this = $(this);

					$sview.find('li.cur').removeClass('cur');
					$this.addClass('cur');
					$self.currentId = $this.attr('cat_id');
					$self.itemFunc($self.currentId);

					if ($self.scroll.x < 0 || $self.scroll.x > $self.scroll.maxScrollX) {
						var $prev = $this.prev();
						$prev.length > 0 && $self.scroll.scrollToElement($prev.get(0), 600)
					}
				})
		},
		selectFirst: function() {
			this.sview.find('li').eq(0).trigger(sClick);
		},
		init: function(data, func) {
			var $this = this;
			$this.data = data;
			$this.itemFunc = func;
			$this.setView();
			$this.setScroll();
			$this.selectItem();
			$this.selectFirst();
		}
	};

    var Category = {
        uri: $.baseUrl + 'index.php?route=rest/category',
        dataArr: null,
        dataAll: null,
        menu1: $('#categoryListDiv'),
        menu2: $('#cMenuDiv'),
		scroll1 : null,
		scroll2 : null,
        menuTmp: $('#categoryTemp').html(),
        categoryAjax: function() {
            var $this = this;
            $.ajax({
                url: $this.uri,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.result == '0') {
                        $this.dataAll = data;
                        $this.buildData(data.info);
						products.init(data.info);
						$this.showMenu1();
						//$this.setScroll();
                    } else {
                        //showTips(data.reason)
                    }
                }
            })
        },
        buildData: function(data) {
            var $this = this;
            if (data.length == 0) {
                return false;
            }
            $this.dataArr = {};
            [].forEach.call(data, function(item, i) {
                var cat_id = item.cat_id;
				item.info = item.child;
                $this.dataArr[cat_id] = item;
            })
        },
        showMenu1: function() {
			var $this = this;
			if (!$this.scroll1) {
				$this.scroll1 = new CatScroll($this.menu1);
			}
			$this.scroll1.init($this.dataAll, function(cat_id) {
				$this.showMenu2(cat_id);
				//$this.scroll1.selectFirst();
			});
		},
        showMenu2: function(catId) {
			var $this = this;
			if (!$this.scroll2) {
				$this.scroll2 = new CatScroll($this.menu2);
			}
			$this.scroll2.init($this.dataArr[catId], function(cat_id) {
				if (cat_id == 0) {
					setTimeout(function(){
						products.showProducts(catId, null);
					}, 300);
				}
				else {
					products.showProducts(null, cat_id);
				}
				//$this.scroll2.selectFirst();
			});
        },
		setScroll: function() {
			var $this = this;
			$(document).on('scrollstart', function(){
				if ($this.menu1 && $(document).scrollTop() <= 100) {
					//Titlebar.hideHeader();
				}
			});
			$(document).on('scrollstop', function(){
				if ($this.menu1 && $(document).scrollTop() <= 100) {
					//Titlebar.showHeader();
				}
			});
		},
        init: function() {
            var $this = this;
            $this.categoryAjax();
        }
    };

	var Titlebar = {
		header: $('#categoryDiv'),
		search : $('#product_search'),
		menu : $('#menuBox'),
		searchBtn : $('#searchBtn'),
		showMenu : function() {
			this.menu.css('display', 'block');
			this.search.css('display', 'none');
		},
		showSearch : function() {
			this.search.css('display', 'block');
			this.menu.css('display', 'none');
		},
		hideHeader: function() {
			this.header.toolbar("hide");
		},
		showHeader: function() {
			this.header.toolbar("show");
		},
		init : function() {
			var $this = this;
			$this.showMenu();
			this.searchBtn.bind(sClick, function() {
				var val = $this.menu.css('display');
				if (val == 'block') {
					$this.showSearch();
				}
				else {
					$this.showMenu();
				}
			});
		}
	};
	
	return {
		init : function init() {
			Titlebar.init();
			Category.init();
		},
		getCategory : function() {
			return Category;
		},
		getTitlebar : function() {
			return Titlebar;
		}
　　};
})
