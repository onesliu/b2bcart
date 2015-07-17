define(['swiper'], function () {
	return {
		set : (function () {
			setTimeout(function () {
				var mySwiper = new Swiper('.swiper-container', {
						pagination : '.swiper-pagination',
						paginationClickable : true,
						autoplay : 2000,
						loop : true
					});
				mySwiper.update();
			}, 500);
		})
	};
});
