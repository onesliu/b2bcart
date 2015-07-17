/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：首页分类
 * @phpTemplate
 */

define(function(require, exports) {
    var $ = require('zepto'),
        iscroll = require('iscroll'),
        mustache = require('mustache'),
        showTips = require('./c_tips'), // 提示
        cookie = require('cookie'), // cookie
        isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
        sClick = isMobile ? 'tap' : 'click';


    var Category = {
        speed: 200, // speed
        aniType: 'ease',
        myscroll: null, // 一级分类scroll
        uri: '/api/category/list',
        uid: $('#uid').val(), // 用户id
        typesIndex: null, // 一级分类索引
        typeTit: $('#categoryTit'), // 分类id
        types: $('#categoryList'), // 一级级分类
        menu: $('#cMenu'), // 二级分类id
        menuTmp: $('#cMenuTmp').html(), // 二级分类template
        triggerTimer: null,
        typesList: null,
        isOpen: false,
        showtypes: function() { // show types
            this.types.animate({
                translate: [0, '100%']
            }, this.speed, this.aniType);
            this.isOpen = true;
            this.typeTit.find('span').addClass('cur')
            this.myscroll.refresh();
            $('#cQuickMenu').find('.mark').show();
        },
        hidetypes: function() { // hide types
            this.types.animate({
                translate: [0, 0]
            }, this.speed, this.aniType, function() {
                setTimeout(function() {
                    $('#cQuickMenu').find('.mark').hide();
                }, 200)
            });
            this.typeTit.find('span').removeClass('cur')
            this.isOpen = false;
            this.myscroll.refresh();
        },
        // 分类请求
        categoryAjax: function() {
            var $this = this;
            $.ajax({
                url: $this.uri,
                type: 'POST',
                dataType: 'json',
                data: {
                    uid: $this.uid
                },
                success: function(data) {
                    if (data.result == 'succ') {
                        $this.typesList = data.info;
                        $this.setTypesIndex(data);
                    } else {
                        showTips(data.reason)
                    }
                }
            })
        },
        // 设置分类索引
        setTypesIndex: function(data) {
            var $this = this;
            if (data.info.length == 0) {
                return false;
            }
            $this.typesIndex = {};
            [].forEach.call(data.info, function(item, i) {
                var cat_id = item.cat_id;
                item.child.unshift({
                    cat_id: cat_id,
                    cat_name: '全部'
                });
                $this.typesIndex[cat_id] = item;
            })
        },
        // 获取二级分类
        getSubTypes: function(typesId) {
            var $this = this;

            $this.menu.css({
                width: 10000
            });

            try {
                this.checkSubTypesSize($this.typesIndex[typesId]);
                $this.menu.html(mustache.to_html($this.menuTmp, $this.typesIndex[typesId]))
                $this.exeSubTypes();
            } catch (e) {

            }
        },
        // 执行二级分类
        exeSubTypes: function() {
            SubTypes.init();
            this.menu.find('li').eq(0).trigger(sClick);
        },
        // show or hide
        typesToggle: function() {
            if (!!this.isOpen) {
                this.hidetypes();
            } else {
                this.showtypes();
            }
        },
        setTit: function(s) { // 分类
            if (!s) {
                return false;
            }
            this.typeTit.find('span').html(s);
        },
        // 重新执行iscroll
        refreshscroll: function() {
            var $this = this,
                wrapper = $this.types.attr('id');
            if ($this.myscroll) {
                $this.myscroll.destroy();
            }
            $this.myscroll = new iscroll(wrapper, {
                hScroll: false,
                hScrollbar: false,
                vScrollbar: false,
                onScrollMove: function(e) {
                    e.preventDefault();
                }
            });
        },
        // 自定义分类 并无二级分类： 如常购
        is_recommond: function(cat_name, cat_id) {
            var json = {},
                item_0 = {
                    cat_name: cat_name,
                    cat_id: cat_id
                };
            json.child = [];
            json.child.push(item_0);
            this.checkSubTypesSize(json);
            try {
                this.menu.html(mustache.to_html(this.menuTmp, json))
                this.exeSubTypes();
            } catch (e) {

            }
        },
        // 检查二级分类数量
        checkSubTypesSize: function(json) {
            if (!json || !json.child) {
                return false;
            }
            if (json.child.length < 3) {
                this.hideSubTypes();
            } else {
                this.showSubTypes();
            }
        },
        // 显示二级分类
        showSubTypes: function() {
            this.menu.parents('#cMenuDiv').show()
        },
        // 隐藏二级分类
        hideSubTypes: function() {
            this.menu.parents('#cMenuDiv').hide()
        },
        init: function() { // 初始化
            if (this.typeTit.length == 0) { // 是否有分类id
                return false;
            }
            var $this = this;

            $this.categoryAjax();
            // 一级分类iscroll
            $this.refreshscroll();
            $(window).on('resize', function() {
                $this.refreshscroll();
            })

            // show or hide types
            $this.typeTit.find('span').on(sClick, function() {
                $this.typesToggle();
                return false;
            });

            // 点击一级分类
            $this.types.find('a').on(sClick, function(e) {
                var _this = $(this),
                    cat_id = $(this).attr('cat_id'),
                    $span = $(this).find('span');

                if (_this.attr('id') == 'cAddGoods') {
                    $this.hidetypes();
                    // app 跳转
                    lnreturnurl(lnurl + '/app/order/goodsadd', '新品预订', '');
                    return false;
                }

                if (!$this.typesIndex) {
                    if ($this.triggerTimer) {
                        clearTimeout($this.triggerTimer);
                    }
                    $this.triggerTimer = setTimeout(function() {
                        _this.trigger(sClick);
                    }, 1000)
                } else {
                    if ($this.triggerTimer) {
                        clearTimeout($this.triggerTimer);
                    }
                }
                // 是否有cat_id
                // if (!cat_id || $span.length == 0) {
                //     showTips('暂无分类：)');
                //     return false;
                // }

                cat_id && $this.setTit($span.text());
                
                if (isNaN(cat_id)) { // 自定义分类：常购
                    $this.is_recommond($span.text(), cat_id);
                } else {
                    $this.getSubTypes(cat_id);
                }
                $this.hidetypes();
            })

            // 阻止事件冒泡
            $('#cQuickMenu').find('.mark').on('touchstart', function(e) {
                e.stopPropagation && e.stopPropagation();
                return false;
            })

            // $this.types.on(sClick, function(e) {
            //     e.stopPropagation && e.stopPropagation();
            //     return false;
            // })

            // 向下滑动一级菜单标题
            $this.typeTit.on('swipeDown', function() {
                $this.showtypes();
                return false;
            })

            // 向下滑动一级菜单标题
            $this.typeTit.on('swipeDown', function() {
                $this.hidetypes();
                return false;
            })

            $(document).on(sClick, function() {
                if ($this.isOpen) {
                    $this.hidetypes();
                }
            })
        }
    };

    /****************************************************
    二级分类
    ****************************************************/
    var SubTypes = {
        subtypes: $('#cMenuDiv'), // 二级分类
        subScroll: null,
        refereshScroll: function() {
            var $this = this,
                wapper = $this.subtypes.attr('id');

            if ($this.subScroll) {
                $this.subScroll.destroy();
            }

            $this.subScroll = new iscroll(wapper, {
                vScroll: false,
                hScrollbar: false,
                vScrollbar: false,
                onScrollMove: function(e) {
                    e.preventDefault();
                }
            });
        },
        setSubTypesItem: function() {
            var $this = this,
                $li = $this.subtypes.find('li'),
                $ul = $li.parent(),
                t = setTimeout(function() {
                    var $num = 0;
                    [].forEach.call($li, function(ele, i) {
                        var $a = ele.getElementsByTagName('a')[0],
                            $a_w = $a.offsetWidth,
                            $li_w = ele.offsetWidth;
                        $num += Math.max($a_w, $li_w) + 1;
                    });

                    $ul.css({
                        width: $num * 1.5
                    });

                    $ul.animate({
                        width: $num
                    }, function() {
                        $this.subScroll && $this.subScroll.refresh();
                    });
                }, 400);
        },
        selectSubTypeItem: function() { // 选择二级分类
            var $self = this,
                $subtypes = $self.subtypes,
                $cur = $subtypes.find('cur');
            $subtypes.find('li').on(sClick, function() {
                var $this = $(this);
                if ($self.subScroll.x < 0 || $self.subScroll.x > $self.subScroll.maxScrollX) {
                    var $prev = $this.prev();
                    $prev.length > 0 && $self.subScroll.scrollToElement($prev.get(0), 600)
                }
                $cur = $this;
            })
        },
        init: function() {
            var $this = this,
                scrollEvent = function() {
                    $this.refereshScroll();
                    $this.setSubTypesItem();
                };
            // 滚动
            scrollEvent();
            $(window).on('scroll resize', function() {
                scrollEvent();
            })
            $this.selectSubTypeItem();
            $this.subScroll.refresh();
        }
    };


    /****************************************************
    menu 随屏滚动
    ****************************************************/
    var FixedMenu = {
        menu: $('#cMenuDiv'),
        target: $('#cFixedHeadWrap'),
        temp: null,
        fix: false,
        setMenu: function(fix) {
            var target = this.target,
                temp = this.temp,
                menu = this.menu;
            if (fix) {
                this.setMenuTop();
                temp.show().css({
                    height: menu.height() / 14 + 'em'
                });
            } else {
                menu.css({
                    position: 'relative',
                    top: 0,
                });
                temp.hide();
            }
        },
        setMenuTop: function() {
            var $this = this,
                t = setTimeout(function() {
                    $this.menu.css({
                        position: 'fixed',
                        top: $this.target.height(),
                        backgroundColor: '#fff',
                        zIndex: 999,
                        left: 0,
                        right: 0
                    });
                    t = null;
                }, 0)
        },
        fixMenu: function() {
            var st = $(window).scrollTop(),
                mt = this.menu.offset().top,
                tt = this.temp.offset().top,
                iTarget = this.target.height();
            if (mt - st <= iTarget) {
                this.fix = true;
            }
            if (tt - st > iTarget || st == 0) {
                this.fix = false;
            }
            this.setMenu(this.fix);
        },
        insetTemp: function() {
            this.temp = $('<div>');
            this.menu.before(this.temp);
            this.temp.hide();
        },
        init: function() {
            var $this = this,
                $menu = $this.menu,
                $target = $this.target;
            $this.insetTemp();
            $(window).on('scroll resize', function() {
                $this.fixMenu();
            })
        }
    };

    // init
    (function init() {
        FixedMenu.init(); // 屏蔽滚动
        SubTypes.init(); // 二级分类
        Category.init(); // 一级分类

        (function() {
            var $cOftenBuy = $('#cOftenBuy'), // 常购
                $cTypeBuy = $('#cTypeBuy'), // 分类购买
                $cQuickMenu = $('#cQuickMenu'); // 快速分类



            $cOftenBuy.on(sClick, function() {
                if ($(this).parent().hasClass('cur')) {
                    return false;
                }
                hideQuickMenuCur($(this));
                Category.typeTit.find('span').hide();
                Category.types.find('a[cat_id=is_recommond]').eq(0).trigger(sClick);
            })


            // // 如果在一键购买页点击常购
            // console.log(!!cookie.get('m2'))
            // if(!!cookie.get('m2')){
            //     $cOftenBuy.trigger(sClick);
            //     cookie.remove('m2', {
            //         path: '/'
            //     })
            // }


            $cTypeBuy.on(sClick, function() {
                if ($(this).parent().hasClass('cur')) {
                    return false;
                }
                hideQuickMenuCur($(this));
                Category.types.find('a[cat_id]').eq(0).trigger(sClick);
                Category.typeTit.find('span').show();
            })

            function hideQuickMenuCur($obj) {
                $cQuickMenu.find('li').removeClass('cur');
                $obj.parents('li').addClass('cur');
            }
        })();
    })()
})
