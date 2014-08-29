<?php

require_once ("tools.class.php");
require_once ("config.php");
require_once ("log.php");
require_once ("TenpayHttpClient.class.php");


$get_string = $_SERVER["QUERY_STRING"];

if($_GET["code"] == "")
{
	echo "error";
	exit(0);
}


//https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
//��ȡaccess_token
$tokenurl= "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$_GET["code"]."&grant_type=authorization_code";
$tokenclient = new TenpayHttpClient();
$tokenclient->setReqContent($tokenurl);
$tokenclient->setMethod("GET");
$tokenres="";
if( $tokenclient->call()){
	$tokenres =  $tokenclient->getResContent();
}
	
if( $tokenres != ""){
	$tk = json_decode($tokenres);
	if( $tk->access_token != "" )
	{
		log_result("addr|back|access_token:".$tk->access_token."|openid:".$tk->openid);
		$accesstoken=$tk->access_token;
	}else{
		echo "get access token empty";
		exit(0);
	}
}
else {
	echo "get access token error";
	exit(0);
}

//�����ַ�ؼ�ǩ��
$timestamp = time();
$noncestr = rand(100000,999999);
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$myaddr = new SignTool();
$myaddr->setParameter ("appid", $appid);
$myaddr->setParameter ("url", $url);
$myaddr->setParameter ("noncestr", $noncestr);
$myaddr->setParameter ("timestamp", $timestamp);
$myaddr->setParameter ("accesstoken", $accesstoken);

$addrsign=$myaddr->genSha1Sign();

$addrstring=$myaddr->getDebugInfo();
log_result("addr|back|addsign:".$addrstring);

?>


<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=gbk"/>
<meta id="viewport" name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1; user-scalable=no;" />
<title>΢��֧��</title>
<style type="text/css">
/* ���� [[*/
body,p,ul,li,h1,h2,form,input{margin:0;padding:0;}
h1,h2{font-size:100%;}
ul{list-style:none;}
body{-webkit-user-select:none;-webkit-text-size-adjust:none;font-family:Helvetica;background:#ECECEC;}
html,body{height:100%;}
a,button,input,img{-webkit-touch-callout:none;outline:none;}
a{text-decoration:none;}
/* ���� ]]*/

/* ���� [[*/
.hide{display:none!important;}
.cf:after{content:".";display:block;height:0;clear:both;visibility:hidden;}
/* ���� ]]*/


/* ��ť [[*/
a[class*="btn"]{display:block;height:42px;line-height:42px;color:#FFFFFF;text-align:center;border-radius:5px;}
.btn-blue{background:#3D87C3;border:1px solid #1C5E93;}
.btn-green{background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #43C750), color-stop(1, #31AB40));border:1px solid #2E993C;box-shadow:0 1px 0 0 #69D273 inset;}
/* ��ť [[*/

/* ��ֵҳ [[*/
.charge{font-family:Helvetica;padding-bottom:10px;-webkit-user-select:none;}
.charge h1{height:44px;line-height:44px;color:#FFFFFF;background:#3D87C3;text-align:center;font-size:20px;-webkit-box-sizing:border-box;box-sizing:border-box;}
.charge h2{font-size:14px;color:#777777;margin:5px 0;text-align:center;}
.charge .content{padding:10px 12px;}
.charge .select li{position:relative;display:block;float:left;width:100%;margin-right:2%;height:230px;line-height:230px;text-align:center;border:1px solid #BBBBBB;color:#666666;font-size:16px;margin-bottom:5px;border-radius:3px;background-color:#FFFFFF;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden;}
.charge .price{border-bottom:1px dashed #C9C9C9;padding:10px 10px 15px;margin-bottom:20px;color:#666666;font-size:12px;}
.charge .price strong{font-weight:normal;color:#EE6209;font-size:26px;font-family:Helvetica;}
.charge .showaddr{border:1px dashed #C9C9C9;padding:10px 10px 15px;margin-bottom:20px;color:#666666;font-size:12px;text-align:center;}
.charge .showaddr strong{font-weight:normal;color:#9900FF;font-size:26px;font-family:Helvetica;}
.charge .copy-right{margin:5px 0; font-size:12px;color:#848484;text-align:center;}
/* ��ֵҳ ]]*/
</style>
</head>
<script language="javascript">
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
WeixinJSBridge.call('hideToolbar');
});

function getaddr(){

WeixinJSBridge.invoke('editAddress',{
"appId" : "<?php echo $appid ?>",
"scope" : "jsapi_address",
"signType" : "sha1",
"addrSign" : "<?php echo $addrsign ?>",
"timeStamp" : "<?php echo $timestamp ?>",
"nonceStr" : "<?php echo $noncestr ?>",
},function(res){
//��res �������ķ���ֵ��Ϊ�գ����ʾ�û�ѡ��÷���ֵ��Ϊ�ջ���ַ�����������ؿգ����ʾ�û�ȡ������һ�α༭�ջ���ַ��
if(res.err_msg == 'edit_address:ok'){
	//alert("�ռ��ˣ�"+res.userName+"  ��ϵ�绰��"+res.telNumber+"  �ջ���ַ��"+res.proviceFirstStageName+res.addressCitySecondStageName+res.addressCountiesThirdStageName+res.addressDetailInfo+"  �ʱࣺ"+res.addressPostalCode);
	document.getElementById("showAddress").innerHTML="�ռ��ˣ�"+res.userName+"  ��ϵ�绰��"+res.telNumber+"  �ջ���ַ��"+res.proviceFirstStageName+res.addressCitySecondStageName+res.addressCountiesThirdStageName+res.addressDetailInfo+"  �ʱࣺ"+res.addressPostalCode;
}
else{
	alert("��ȡ��ַʧ�ܣ������µ��");
}

});

}

</script>

<body>
	<article class="charge">
		<h1>΢��֧��-JSAPI-demo</h1>
		<section class="content">
				<h2>��Ʒ��������Ʒ��</h2>		
		  <ul class="select cf">
					<li><img src="./weixin.jpg"></li>
				</ul>
				<p class="copy-right">�ף�����Ʒ���ṩ�˿�ͷ�������Ŷ</p>
				<div class="price">΢�żۣ�<strong>��0.01Ԫ</strong></div>
				<div class="showaddr" id="showAddress" ><a id="editAddress" href="javascript:getaddr();"><strong>�����ջ���ַ</strong></a></div>

				<p class="copy-right">΢��֧��demo ����Ѷ�Ƹ�ͨ�ṩ</p> 
		</section>
	</article>
</body>
</html>
