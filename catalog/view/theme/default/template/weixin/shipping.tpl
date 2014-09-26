    <div id="shipping_address">
      <div class="checkout-content" style="display:block;" onclick="editaddr();" >
		<div id="addr" <?php if (!isset($address)) echo "style=\"display:none\""; ?> >
			<div class="checkout-heading">收货地址</div>
		    <div><span id="user_name"><?php if (isset($address)) {echo $address['firstname']; echo $address['lastname'];} ?></span>
		    	<span id="user_telephone"><?php if (isset($address)) {echo $address['telephone'];} ?></span></div>
		    <div id="user_addr"><?php if (isset($address)) {echo $address['address_1'];} ?></div>
		    <input type="hidden" name="user_name" id="user_name2" value="<?php if (isset($address)) {echo $address['firstname']; echo $address['lastname'];} ?>"></input>
		    <input type="hidden" name="user_telephone" id="user_telephone2" value="<?php if (isset($address)) {echo $address['telephone'];} ?>"></input>
		    <input type="hidden" name="user_addr" id="user_addr2" value="<?php if (isset($address)) {echo $address['address_1'];} ?>"></input>
		    <input type="hidden" name="user_city" id="user_city" value="<?php if (isset($address)) {echo $address['city'];} ?>"></input>
		    <input type="hidden" name="user_postcode" id="user_postcode" value="<?php if (isset($address)) {echo $address['postcode'];} ?>"></input>
      	</div>
		<div id="addr_none" <?php if (isset($address)) echo "style=\"display:none\""; ?> >
			<span class="checkout-heading">选择收货地址</span>
		</div>
      </div>
    </div>

	<div id="district">
		<div class="checkout-content" style="display:block;">
		    <span class="checkout-heading"><?php echo $text_shipping_district; ?></span>
		    <select id="district-select" name="district-select">
	      		<option value="0">请选择就近的配送点</option>
	      		<?php foreach($shipping_districts as $district) {
	      			if ($district['id'] != $order_info['shipping_district_id']) {
	      		?>
	      		<option value="<?php echo $district['id']; ?>"><?php echo $district['name'];?></option>
	      		<?php } else { ?>
	      		<option selected="selected" value="<?php echo $district['id']; ?>"><?php echo $district['name'];?></option>
	      		<?php }} ?>
      		</select>
		</div>
	</div>
	
    <div id="shipping-time">
      <div class="checkout-content" style="display:block;">
      	<span class="checkout-heading"><?php echo $text_shipping_time; ?></span>
      	<select name="time-select">
      		<?php foreach($shipping_time as $sk => $st) {?>
      		<option value="<?php echo $sk; ?>"><?php echo $st; ?></option>
      		<?php } ?>
      	</select>
      </div>
    </div>
    
<script type="text/javascript"><!--
function check_form() {
	if ($('#addr').css("display") == "none") {
		alert('请选择一个收货地址吧！');
		return false;
	}
	
	if ($('#district-select option:selected').val() == 0) {
		alert('系统无法自动从您的地址中判断出就近的配送点，请人工选择。如果太远无法配送，客服会联络您哦～');
		return false;
	}

	return true;
}

function editaddr() {
	if (typeof WeixinJSBridge == "undefined") {
		alert("请通过微信加载该页面");
	}
	else {
		WeixinJSBridge.invoke('editAddress',{
			"appId" : "<?php echo $addrParam['appId']; ?>",
			"scope" : "jsapi_address",
			"signType" : "sha1",
			"addrSign" : "<?php echo $addrParam['addrSign']; ?>",
			"timeStamp" : "<?php echo $addrParam['timeStamp']; ?>",
			"nonceStr" : "<?php echo $addrParam['nonceStr']; ?>",
			},function(res){
			//若res 中所带的返回值不为空，则表示用户选择该返回值作为收货地
			//址。否则若返回空，则表示用户取消了这一次编辑收货地址。
				if (res != null) {
					if (res.err_msg == "edit_address:ok") {
						$('#user_name').text(res.userName);
						$('#user_telephone').text(res.telNumber);
						$('#user_addr').text(res.proviceFirstStageName + 
											res.addressCitySecondStageName +
											res.addressCountiesThirdStageName + " " +
											res.addressDetailInfo);
						$('#user_name2').val(res.userName);
						$('#user_telephone2').val(res.telNumber);
						$('#user_addr2').val($('#user_addr').text());
						$('#user_city').val(res.addressCitySecondStageName);
						$('#user_postcode').val(res.addressPostalCode);
						
						$('#addr_none').css("display", "none");
						$('#addr').css("display", "block");
					}
				}
			});
	}
}
//--></script>
    