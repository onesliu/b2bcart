<?php echo $header; ?>
<body>
<div data-role="page" id="pageone" data-title="<?php echo $heading_title; ?>">
	<?php echo $navi; ?>
	<div data-role="content">
	<?php echo $content_top; ?>

  	<ul data-role="listview" id="plist">
  	<?php foreach ($products as $product) { ?>
  		<li data-icon="plus">
  			<a href="<?php echo $product['href']; ?>">
  			<img src="<?php echo $product['thumb']; ?>">
  			<h2><?php echo $product['name']; ?></h2>
  			<p><?php echo $product['description']; ?></p>

  			<?php if ($product['price']) { ?>
				<p class="ui-li-aside">
				<span><?php echo $product['price']; ?></span><br/>
				<?php if (!$product['special']) { ?>
					<span><?php echo $product['special']; ?></span>
				<?php } ?>
				</p>
			<?php } ?>
			
    		<a href="#1" onclick="addToCart(<?php echo $product['product_id'];?>);"><?php echo $button_cart; ?></a>
  		</li>
  	<?php } ?>
  	</ul>
	<br/>
	<?php if ($pagination->page < $pagination->num_pages) { ?>
	<p><a id="bmore" href="#" data-role="button">更多...</a></p>
	<?php } ?>

	<?php echo $content_bottom; ?>
	
	<script type="text/javascript"><!--
	function addToCart(product_id, quantity) {
		quantity = typeof(quantity) != 'undefined' ? quantity : 1;

		$.ajax({
			url: 'index.php?route=mobile_store/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + quantity,
			dataType: 'json',
			success: function(json) {
				$('.success, .warning, .attention, .information, .error').remove();
				
				if (json['redirect']) {
					location = json['redirect'];
				}
				
				if (json['success']) {
					$('#buy_alert').html(json['success']);
					
					//$('#cart_total').html(json['total']);
					$('#alert_footer').slideDown('fast');
					setTimeout(function() {
						$("#alert_footer").hide()
					}, 1000);
				}	
			}
		});
	}
	
	var url_more="<?php echo $pagination->url; ?>";
	var um_page=<?php echo $pagination->page; ?>;
	var um_pages=<?php echo $pagination->num_pages; ?>;
	$(document).on("pageinit","#pageone",function(){
		$("#bmore").on("click", function(){
			um_page++;
			url = "<?php echo $pagination->url; ?>" + "&page=" + um_page;
			$.get(url, function(data,status) {
				$("#plist").append(data).find("li:last").hide();
				$("#plist").listview('refresh');
				$("#plist").find("li:last").slideDown(300);
				if (um_page >= um_pages) {
					$("#bmore").hide();
				}
			});
		});
	});
	//--></script> 
	</div>
	
	<div data-role="footer" id="alert_footer" data-position="fixed" style="display:none;">
		<h1 id="buy_alert"></h1>
	</div>
</div>

</body>
