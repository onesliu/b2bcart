<?php

class ControllerWeixinMenuCreate extends Controller {

/*	private $menu_def = '{
     "button":[
     {	
          "type":"click",
          "name":"我要买菜",
          "key":"V1001_BUY_NOW"
      },
      {
           "type":"click",
           "name":"会员信息",
           "key":"V1001_SELF_INFO"
      },
      {
           "name":"青悠悠",
           "sub_button":[{	
               "type":"view",
               "name":"关于我们",
               "url":"http://oc.ngrok.com/opencart/"
           }]
       }]}';*/

	
	public function index() {
		
		// 读取微信配置信息
		$token = $this->config->get('weixin_token');
		
		// 读取本地保存的access_token，没读到就去微信服务器取
		$access_token = $this->config->get('weixin_access_token');
		$token_expire = $this->config->get('weixin_token_expire');
		$token_starttime = $this->config->get('weixin_token_starttime');
		$menu_def = $this->prepare_menu_def($this->config->get('weixin_menu'));
		
		$ret = new stdClass();
		
		$this->load->model('setting/setting');
		$this->load->model('weixin/access_token');
		if (null == $access_token || null == $token_starttime || null == $token_expire ||
			(time() - $token_starttime >= $token_expire)) {

			$access_token = $this->model_weixin_access_token->get($this->config->get('weixin_appid'),
				$this->config->get('weixin_appsecret'));
			if ($access_token == false) {
				// 读取access_token失败
				$ret->errcode = -1;
				$ret->errmsg = 'got weixin access_token failed.';
				$this->response->setOutput(json_encode($ret));
				return;
			}
		}
		
		//创建菜单
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $menu_def);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
		$res = curl_exec($ch);
		$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->response->setOutput($res);
		/*
		$result = json_decode($res);
		if (!isset($result->errcode)) {
			$this->errcode = $result->errcode;
			$this->errmsg = $result->errmsg;
		}
		*/
	}
	
	private function prepare_menu_def($menu_def) {
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
		$url = str_replace('APPID', $this->config->get('weixin_appid'), $url);
		$url = str_replace('REDIRECT_URI', urlencode("http://".MY_DOMAIN."/pay/weixin.php?route=weixin/login"), $url);
		return str_replace('AUTO_LOGIN', $url, $menu_def);
	}
}
?>