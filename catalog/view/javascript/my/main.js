require.config({
	baseUrl : "catalog/view/javascript/",
	urlArgs : "bust=2",
	paths : {
		"swiper" : 'swiper/swiper.min',
		"mustache" : 'mustache/mustache.min',
		"iscroll" : 'iscroll/iscroll.min',
		"swipeImg" : 'my/swipeImg',
		"category" : 'my/category',
		"products" : 'my/products',
		"widgets" : 'my/widgets',
		"cart" : 'my/cart',
		"delayImg" : 'my/delayImg',
		"user" : 'my/user'
	},
	shim : {
		"swiper" : {
			deps : ['css!../javascript/swiper/swiper.min.css']
		}
	}
});

$.baseUrl = $('base').attr('href');

var category = null;

if (typeof homepage !== "undefined") {
	require(['category'], function (c) {
		c.init();
		category = c;
	});
	
	$(document).on("pageshow", "#homepage", function () {
		if (category) {
			var c = category.getCategory();
			c.showMenu1();
		}
	});
	
	$(document).on("pageinit", "#cartpage", function () {
		cart = require('cart');
		cart.showCartList();
	});
	
	$(document).on("pageshow", "#cartpage", function () {
		cart = require('cart');
		cart.showCartList();
	});
}

if ((typeof loginpage !== "undefined") || (typeof registerpage !== "undefined")) {
	require(['user'], function(u) {
		u.init();
	});
}
