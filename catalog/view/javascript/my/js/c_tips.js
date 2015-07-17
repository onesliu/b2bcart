/**
 * @fileOverview  链农
 * @author xiaochen
 * @email xiaochen2@leju.com
 * @date 2014-11-29
 * @templatePage：弹窗提示
 * @phpTemplate
 */
define(function(require, exports, module) {
	var $ = require('zepto');
	/**
	 * [showTip 提示弹窗]
	 * @param  {[String]} txt [弹窗里面的文字]
	 * @return {[type]}     [description]
	 */

	function showTip(txt) {
		var message = $('<p style="background: rgba(0,0,0,0.5); position: fixed; z-index: 9999999; max-width: 80%; left: 50%; top: 50%; text-align: center;border-radius: 10px;padding: 10px; color: #fff" id="tips">' + txt + '</p>');
		// var mes = message.get(0);
		// mes.style.WebkitTransform = 'translate(-50%, -50%)';
		// mes.style.MozTransform = 'translate(-50%, -50%)';
		// mes.style.msTransform = 'translate(-50%, -50%)';
		// mes.style.OTransform = 'translate(-50%, -50%)';
		// mes.style.transform = 'translate(-50%, -50%)';
		$('body').append(message);

		message.css({
			'margin-left': -message.width()/2,
			'margin-top': -message.height()/2
		});

		window.setTimeout(function() {
			message.remove();
		}, 1000);
	}
	module.exports = showTip;
})