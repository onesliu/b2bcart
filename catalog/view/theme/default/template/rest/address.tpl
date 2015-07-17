<?php echo $header; ?>
<body>
<div data-role="page" data-theme="a" id="addresspage" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<a href="javascript:;" style="top:.8em;" data-rel="back" class="ui-btn ui-icon-arrow-l ui-btn-icon-notext ui-corner-all"></a>
		<h1><img src="<?php echo $logo; ?>" style="width:100%;height:2.2em;" /></h1>
	</div>
	<div data-role="content">
		<ul data-role="listview" id="addrList">
		</ul>
	</div>
</div>
<div data-role="page" data-theme="a" id="addressdialog" data-title="菜鸽子供应链">
	<div data-role="header" data-position="fixed" data-theme="a" data-tap-toggle="false">
		<h1><img src="<?php echo $logo; ?>" style="height:1;" /></h1>
	</div>
</div>
</body>