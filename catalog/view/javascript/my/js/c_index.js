/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：首页
 * @phpTemplate
 */

define(function(require, exports) {
    var $ = require('zepto');
    var $cookie = require('cookie'),
        showTips = require('./c_tips'); // 提示

    require('./c_index_category'); // 分类
    require('./c_index_addData'); // 商品分类及分页
    require('./c_loading'); // loading
    require('./c_promotion'); // 促销
    var eClick = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1 ? 'tap' : 'click';

    /**
     * [点击app下一步，在当前页显示]
     * @return {[type]} [description]
     */

    $('#cNextStep').on(eClick, function() {
        lnreturnurl(lnurl + '/app/i/cart', '购物车', '清空');
        return false;
    });

    // 一键购买
    $('#cOneKeyBuy').on(eClick, function() {
        if($(this).attr('recommond') == 0) { 
            showTips('暂无推荐商品: )');
            return false;
        }
        lnreturnurl(lnurl + '/app/onekeybuy/index', '一键购买', '');
        return false;
    });

    // 新品预订
    // $('#cAddGoods').on(eClick, function() {
    //     lnreturnurl(lnurl + '/app/order/goodsadd', '新品预订', '');
    //     return false;
    // });

    // 余额不足提示
    ;
    (function() {
        var $balance = $('#cBalance'),
            t = null,
            time = 6000;
        if ($balance.length) {
            t = setTimeout(function() {
                $balance.hide();
                t = null;
            }, time)
        }
    })();

    (function() {
        // 一键购买
        var QuickBuy = {
            eclick: eClick,
            menu: $('#cQuickMenu'),
            sTip: '暂无推荐商品:)',
            firstBuy: function() {
                // this -> a : 点击事件
                if(/javascript/.test($(this).attr('href'))) {
                    showTips(QuickBuy.sTip);
                }
            },
            init: function() {
                var $this = this,
                    eclick = $this.eclick;
                $this.menu.on(eclick, '.m1 a', function() {
                    $this.firstBuy.call(this);
                });
            }
        };
        QuickBuy.init();
    })();
})