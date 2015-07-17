<?php echo $header; ?>
<body>
<div data-role="page" data-theme="a" id="homepage" data-title="菜鸽子供应链">
	<div data-role="panel" id="user_panel" data-position="right" data-display="overlay" data-theme="b">
		<ul data-role="listview" data-icon="false" data-theme="b" data-divider-theme="a" data-count-theme="b">
			<li><a href="javascript:;" data-rel="close" class="ui-btn ui-icon-delete ui-btn-icon-left">关闭</a></li>
			<li><a href="<?php echo $userlogin; ?>" rel="external" data-prefetch="ture" 
				class="ui-btn ui-icon-user ui-btn-icon-left">登录</a></li>
			<li><a href="<?php echo $addredit; ?>" rel="external"
				class="ui-btn ui-icon-tag ui-btn-icon-left">收货地址编辑</a></li>
			<li><a href="javascript:;">关于我们</a></li>
		</ul>
	</div>
	<div id="categoryDiv" data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<div>
			<div class="titlebar">
				<div id="menuBox" data-role="navbar" data-theme="b">
					<ul>
						<li><a href="javascript:;" style="border-left-width: 0;">分类购买</a></li>
						<li><a href="javascript:;">经常购买</a></li>
						<li><a href="javascript:;">每日特惠</a></li>
					</ul>
				</div>
				<div style="display:none;" id="product_search">
					<input onchange="searchproduct();" type="search" name="product_search" value="" placeholder="查找商品..." />
				</div>
				<a id="searchBtn" href="javascript:;" class="ui-btn ui-icon-search ui-btn-icon-notext ui-corner-all ui-btn-left"></a>
				<a href="#user_panel" class="ui-btn ui-icon-user ui-btn-icon-notext ui-corner-all ui-btn-right"></a>
			</div>
		</div>
	</div>
	<div id="categoryListDiv" class="categoryList" >
		<ul id="categoryList" class="clearfix">
			<li cat_id='1' class='cur'><a href="javascript:;">全部</a></li>
		</ul>
	</div>
	<div id="cMenuDiv" class="cMenu">
		<ul id="cMenu" class="clearfix">
			<li cat_id='1' class='cur'><a href="javascript:;">全部</a></li>
		</ul>
	</div>
	<div data-role="content">
		<div class="productlist">
			<ul data-role="listview" id="productList">
			</ul>
		</div>
	</div>
	<div id="sumFooter" class="footerbar" data-role="footer" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<div>
			<div class="cart ui-icon-cart ui-btn-icon-notext ui-corner-all ui-btn-left"></div>
			<a href="#cartpage" data-transition="slide" class="abtn ui-btn ui-mini ui-corner-all ui-btn-right">去购物车</a>
			<h3 class="sum">总共 <span class="price" id="sumCount">0</span>种 <span class="price" id="sumPrice">0.00</span>元</h3>
		</div>
	</div>
	<script type="text/template" id="categoryTemp">
		{{#info}}<li cat_id={{cat_id}}><a href="javascript:;">{{name}}</a></li>{{/info}}
	</script>
	<script type="text/template" id="productTemp">
		{{#info}}
		<li product_id={{product_id}}>
			<img src="{{no_image}}" _src="{{image}}" />
			<div class="info">
				<div class="name">{{name}}</div>
				<div class="desc">{{model}}</div>
				<div class="pricediv"><span class="price">{{calcprice}}</span> /{{sellunit}}</div>
			</div>
			<div class="btnWrap">
				<div class="item-amount" style="display:none">
					<a href="javascript:;" class="minus">-</a>
					<input type="text" readonly="readonly" value="1" class="text-amount">
					<a href="javascript:;" class="plus">+</a>
				</div>
				<div class="amountBtn">
					<button class="ui-btn ui-btn-inline ui-corner-all">{{number}}</button>
					<span>{{sellunit}}</span>
				</div>
				<div class="buyBtn">
					<button class="ui-btn ui-btn-inline ui-corner-all {{buycls}}" data-theme="d" buy="购买" del="取消">{{buylabel}}</button>
				</div>
			</div>
		</li>
		{{/info}}
	</script>
</div>

<div data-role="page" data-theme="a" id="cartpage" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<a href="javascript:;" data-rel="back" class="ui-btn ui-icon-arrow-l ui-btn-icon-notext ui-corner-all"></a>
		<h1>购物车</h1>
	</div>
	<div data-role="content">
		<div class="productlist">
			<ul data-role="listview" id="cartlist">
			</ul>
		</div>
	</div>
	<div id="cartFooter" class="footerbar" data-role="footer" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<div>
			<a href="javascript:;" data-rel="back" class="cart ui-icon-arrow-l ui-btn-icon-notext ui-corner-all ui-btn-left"></a>
			<a href="#orderpage" data-transition="slide" class="abtn ui-btn ui-mini ui-corner-all ui-btn-right">确认订单</a>
			<h3 class="sum">合计 <span class="price" id="sumCart">0.00</span>元</h3>
		</div>
	</div>
	<script type="text/template" id="cartTemp">
		{{#info}}
		<li product_id={{product_id}}>
			<img src="{{image}}" />
			<div class="info">
				<div class="name">{{name}}</div>
				<div class="pricediv"><span class="price">{{calcprice}}</span> /{{sellunit}}</div>
				<div class="pricediv">合计<span class="price">{{totalprice}}</span> 元</div>
			</div>
			<div class="btnWrap">
				<div class="item-amount" style="display:none">
					<a href="javascript:;" class="minus">-</a>
					<input type="text" readonly="readonly" value="1" class="text-amount">
					<a href="javascript:;" class="plus">+</a>
				</div>
				<div class="amountBtn">
					<button class="ui-btn ui-btn-inline ui-corner-all">{{number}}</button>
					<span>{{sellunit}}</span>
				</div>
				<div class="buyBtn">
					<button class="ui-btn ui-btn-inline ui-corner-all" data-theme="d">删除</button>
				</div>
			</div>
		</li>
		{{/info}}
	</script>
</div>

<div data-role="page" data-theme="a" id="orderpage" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<a href="javascript:;" data-rel="back" class="ui-btn ui-icon-arrow-l ui-btn-icon-notext ui-corner-all"></a>
		<h1>确认订单</h1>
	</div>
	<div data-role="content">
		<div class="ui-body">
			<p>晚上0点前下单，早上8:00-10:30收货</p>
		</div>
		
	    <div id="shipping_address" class="ui-body line-box">
		    <p class="labelh">收货地址</p>
			<p id="addr" <?php if (!isset($address)) echo "style=\"display:none\""; ?> >
			    <div><span id="user_name"><?php if (isset($address)) {echo $address['firstname']; echo $address['lastname'];} ?></span>
			    	<span id="user_telephone"><?php if (isset($address)) {echo $address['telephone'];} ?></span></div>
			    <div id="user_addr"><?php if (isset($address)) {echo $address['address_1'];} ?></div>
			    <input type="hidden" name="user_name" id="user_name2" value="<?php if (isset($address)) {echo $address['firstname']; echo $address['lastname'];} ?>"></input>
			    <input type="hidden" name="user_telephone" id="user_telephone2" value="<?php if (isset($address)) {echo $address['telephone'];} ?>"></input>
			    <input type="hidden" name="user_addr" id="user_addr2" value="<?php if (isset($address)) {echo $address['address_1'];} ?>"></input>
			    <input type="hidden" name="user_city" id="user_city" value="<?php if (isset($address)) {echo $address['city'];} ?>"></input>
			    <input type="hidden" name="user_postcode" id="user_postcode" value="<?php if (isset($address)) {echo $address['postcode'];} ?>"></input>
	      	</p>
			<p id="addr_none" <?php if (isset($address)) echo "style=\"display:none\""; ?> >
				<span class="checkout-heading" style="color:#9999ff;">点击编辑收货地址</span>
			</p>
	    </div>
	    
		<div class="ui-body line-box">
			<div class="line" style="width:25%;"><span>购买品种</span></div>
			<div class="line" style="width:75%;"><span id="ordernumber" style="color:black;">0</span> 种</div>
	    	<div class="line" style="width:25%;"><span>订单合计</span></div>
	    	<div class="line" style="width:75%;"><span id="ordertotal" style="color:red;">￥0.00</span> 元</div>
	    </div>

		<div class="ui-body line-box">
			<form>
				<label for="comment">订单备注</label>
				<textarea name="comment" id="comment" placeholder="特殊要求"></textarea>
			</form>
	    </div>

		<div class="ui-body line-box" style="text-align:center;">
			<a href="javascript:;" id="wxpay" enable="true" class="btn-red ui-btn ui-corner-all">微信支付</a>
			<a href="javascript:;" id="cashpay" enable="true" class="ui-btn ui-corner-all">货到付款</a>
		</div>
	</div>
</div>
</body>
