/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：loading
 * @phpTemplate
 */

define(function(require, exports) {
    var $ = require('zepto'),
        showTips = require('./c_tips'), // 提示
        cookie = require('cookie'), // cookie
        $loadfixed = $('#cFixedLoading'),
        eClick = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1 ? 'tap' : 'click';
    require('./c_menu_right'); // 右侧弹窗

    window.lnurl = window.location.origin;

    $(window).on('load', function() {
        $loadfixed.hide();
    })

    /**
     * 阻止滑动
     */
    $(function() {
        $loadfixed.on('touchmove', function(e) {
            e.preventDefault && e.preventDefault();
        })
    })


    window.lnreturnurl = function(url, title, menu) {
        // console.log('a')
        if (window.android) {
            window.android.android_init(url, title, menu);
        }
        return false;
    }

    // 一键购买页的，常规购买
    $('#appOnKeyBuy').on(eClick, function() {
        if (window.android) {
            window.android.android_home();
            window.android.android_reload();
        }
    });

    // 杀死所有页面
    $('#lnmenuorder, #cx_btn_enter, #cQuickMenu .m2 a, #cTypsBuy').on(eClick, function() {
        if (window.android) {
            window.android.android_home();
            window.android.android_reload();
            return false;
        }
    })

    // 新品预订
    $('#cAddTypeLink').on('touchstart', function() {
        lnreturnurl(lnurl + '/app/order/goodsadd', '新品预订', '');
    });

    // 优惠信息
    $('#cYHinfo').on(eClick, function() {
        lnreturnurl(lnurl + '/app/notice/promotion', '优惠信息', '');
        return false;
    });

    // 余额
    $('#lnmenubalance').on(eClick, function() {
        lnreturnurl(lnurl + '/app/i/balanceamount', '余额明细', '');
        return false;
    });


    // 一键购买
    $('#lnmenuquickbuy').on(eClick, function() {
        if ($(this).attr('recommond') == 0) {
            showTips('暂无推荐商品: )');
            return false;
        }
        lnreturnurl(lnurl + '/app/onekeybuy/index', '一键购买', '');
        return false;
    });

    // 购物车
    $('#lnmenucar').on(eClick, function() {
        lnreturnurl(lnurl + '/app/i/cart', '购物车', '清空');
        return false;
    });

    // 订单
    $('#lnmenusucc').on(eClick, function() {
        lnreturnurl(lnurl + '/app/order/index', '未完成订单', '')
        return false;
    });

    // 修改密码
    $('#lnmenuupdate').on(eClick, function() {
        lnreturnurl(lnurl + '/app/i/password', '修改密码', '');
        return false;
    });
    // // 退出登录
    // $('#lnmenulogout').on(eClick, function() {
    //     try {
    //         window.android.android_home();
    //         window.android.android_reload();
    //     } catch (e) {

    //     }
    // });
    // 
    // 
    $('#lnmenuchoosecity').on(eClick, function() {
        if (window.location.href.indexOf('/home/index') == -1) {
            try {
                cookie.set('btn_choosecity', '1', {
                    path: '/'
                });
                window.android.android_home();
                window.android.android_reload();
            } catch (e) {

            }
        } else {
            window.location.href = '/app/home/choosecity';
        }
    })

    $('#lnmenulogin').on(eClick, function() {
        if (window.location.href.indexOf('/home/index') == -1) {
            try {
                cookie.set('btn_order', '1', {
                    path: '/'
                });
                window.android.android_home();
                window.android.android_reload();
            } catch (e) {

            }
        } else {
            window.location.href = '/app/passport/login';
        }
    })



    // m 下菜单
    $('#mOneKeyBuy').on(eClick, function() {
        var sHref = $(this).attr('href');
        if (/javascript:;/.test(sHref)) {
            showTips('暂无推荐商品: )');
            return false;
        }
    });

    // // andorid 调用
    // function appReturnPrev() {
    // }
})
