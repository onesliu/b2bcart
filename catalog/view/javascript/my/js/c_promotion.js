/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：促销
 * @phpTemplate
 */

define(function(require, exports) {
    var $ = require('zepto');
    var iscroll = require('iscroll'); // 滚动
    var eClick = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1 ? 'tap' : 'click';
    var $sls = {
        btnEnter: $('#cx_btn_enter'), // 关闭促销按钮
        fixed: $('#cx_fixed') // 促销弹层
    };

    // 促销
    $sls.btnEnter.on('touchend', fnClosed);
    $sls.btnEnter.on('click', fnClosed);

    function fnClosed() {
        $sls.fixed.hide();
        // $sls.fixed.animate({opacity: 0}, 400, function(){
        // 	$(this).hide();
        // }); // 关闭促销
        $.ajax({
            url: '/home/promotion',
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                // console.log(data);
            }
        });
        return false;
    }

    setTimeout(function() {
    	if($sls.fixed.length) {
        	fnClosed();
    	}
    }, 5000);

    (function init() {
        if (document.getElementById('cx_scrollWrap')) {
            var myscroll = new iscroll('cx_scrollWrap', {
                hScroll: false,
                hScrollbar: false,
                vScrollbar: false,
                onScrollMove: function(e) {
                    e.preventDefault();
                }
            });
            $(window).on('resize', function() {
                var t = setTimeout(function() {
                    myscroll.refresh();
                    clearTimeout(t);
                    t = null;
                }, 0)
            });
        }
    })();
})
