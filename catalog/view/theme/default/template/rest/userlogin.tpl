<?php echo $header; ?>
<body>
<div data-role="page" data-theme="a" id="loginpage" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<a href="javascript:;" style="top:.8em;" data-rel="back" class="ui-btn ui-icon-arrow-l ui-btn-icon-notext ui-corner-all"></a>
		<h1><img src="<?php echo $logo; ?>" style="width:100%;height:2.2em;" /></h1>
	</div>
	<div data-role="content">
		<div class="ui-body">
			<form>
				<input name="user" type="text" data-clear-btn="true" placeholder="手机号码" <?php
					if (isset($user)) echo "value=$user";
				?> ></input>
				<input name="pwd" type="password" data-clear-btn="true" placeholder="密码"></input>
			</form>
			<p>&nbsp;</p>
			<a href="javascript:;" id="btnlogin" enable="true" class="btn-red ui-btn ui-corner-all">登 录</a>
			<a href="#registerpage" enable="true" data-prefetch="ture" class="ui-btn ui-corner-all">注 册</a>
		</div>
	</div>
	<div data-role="popup" id="loginPopup" class="ui-content" data-dismissible="false">
	  <a href="javascript:;" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-icon-delete ui-btn-icon-notext ui-btn-right ui-btn-a">Close</a>
	  <p></p>
	</div>
</div>
<div data-role="page" data-theme="a" id="registerpage" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<a href="javascript:;" style="top:.8em;" data-rel="back" class="ui-btn ui-icon-arrow-l ui-btn-icon-notext ui-corner-all"></a>
		<h1><img src="<?php echo $logo; ?>" style="width:100%;height:2.2em;" /></h1>
	</div>
	<div data-role="content">
		<div class="ui-body">
			<form>
				<input name="telephone" type="text" data-clear-btn="true" placeholder="手机号码"></input>
				<input name="password" type="password" data-clear-btn="true" placeholder="密码"></input>
				<input name="password2" type="password" data-clear-btn="true" placeholder="再次输入密码"></input>
				<input name="storename" type="text" data-clear-btn="true" placeholder="店名"></input>
				<input name="username" type="text" data-clear-btn="true" placeholder="联系人"></input>
				<input name="address" type="text" data-clear-btn="true" placeholder="地址"></input>
				<input name="invitation" type="text" data-clear-btn="true" placeholder="邀请码"></input>
			</form>
			<p>&nbsp;</p>
			<a href="javascript:;" id="btnregister" enable="true" class="btn-red ui-btn ui-corner-all">注 册</a>
		</div>
	</div>
	<div data-role="popup" id="registerPopup" class="ui-content" data-dismissible="false">
	  <a href="javascript:;" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-icon-delete ui-btn-icon-notext ui-btn-right ui-btn-a">Close</a>
	  <p></p>
	</div>
</div>
</body>
