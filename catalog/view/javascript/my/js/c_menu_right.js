/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：右侧弹出菜单
 * @phpTemplate
 */

define(function(require, exports, module) {
    var $ = require('zepto');
    var MyMenu = {
        myscroll: null,
        isMobile: window.navigator.userAgent.indexOf('mobile') != -1,
        btnMenu: '#cMine, #cMenuR', // id string
        wrapper: $('#cFixedMenuBoxIndex'), // 最外层 object id
        conMenu: $('#cFixedMenuIndex'), // 中间层 id string
        offClass: 'cMenuOff',
        onClass: 'cMenuOn',
        isOpen: false,
        open: function() {
            var $this = this;
            $this.wrapper.show()
            $this.fnTimeout(function() {
                $this.conMenu.removeClass(this.offClass).addClass(this.onClass);
            }, 60);
            $this.myscroll.refresh()
            $this.isOpen = true;
        },
        close: function() {
            var $this = this;
            $this.conMenu.removeClass(this.onClass).addClass(this.offClass);
            $this.fnTimeout(function() {
                $this.wrapper.hide();
            }, 300);
            $this.myscroll.refresh()
            $this.isOpen = false;
        },
        fnTimeout: function(fn, time) {
            time = time || 400;
            var $this = this,
                t = setTimeout(function() {
                    fn && fn.call($this);
                    t = null;
                }, time);
        },
        hasMenu: function() {
            // !!0 : !!n(n>1)
            return !!this.wrapper.length;
        },
        toggle: function() {
            !this.isOpen ? this.open() : this.close();
        },
        init: function() {
            if (!this.hasMenu()) {
                return false;
            }
            var $this = this,
                iscroll = require('iscroll'),
                eClick = $this.isMobile ? 'tap' : 'click',
                $wrapper = $this.wrapper,
                conMenu = $this.conMenu.attr('id'),
                refreshscroll = function() {
                    if ($this.myscroll) {
                        $this.myscroll.destroy();
                    }
                    $this.myscroll = new iscroll(conMenu, {
                        hScroll: false,
                        hScrollbar: false,
                        vScrollbar: false,
                        onScrollMove: function(e) {
                            e.preventDefault();
                        }
                    });
                };

            refreshscroll();
            $(window).on('resize scroll', function() {
                refreshscroll()
                $this.myscroll.refresh()
            });

            // my menu on
            $(document).on(eClick, this.btnMenu, function() {
                $this.open()
            });
            // my menu on
            $wrapper.on('swipeLeft', function() {
                $this.close()
            });
            $wrapper.on(eClick, function() {
                $this.close()
            });
            // prevent touchmove
            $wrapper.on('touchmove', function(e) {
                e.preventDefault()
            });
            // 阻止事件冒泡
            $wrapper.find('.cFixedMenu').on(eClick, function(e) {
                e.stopPropagation()
            });

            function openAndClose() {
                $this.toggle();
            }

            window.android_onclick = openAndClose;
        }
    };

    MyMenu.init();
    module.exports = MyMenu;
})
