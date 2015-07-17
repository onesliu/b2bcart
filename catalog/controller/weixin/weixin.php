<?php

class ControllerWeixinWeixin extends Controller { 
	public function index() {
		
		if ($this->weixin_init() != true) {
			$this->log->write("微信接口初始化失败");
			return; //初始化失败
		}
		
		if ($this->valid_check() != true) {
			$this->log->write("首次验证成功或微信验证失败");
			return; //验证失败或首次验证成功
		}
		
		$wxtools = new WeixinTools();
		
		// 接收到消息或事件
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			//保存到数据库
			$this->load->model('weixin/message');
			$xmlstr = file_get_contents("php://input");
			$this->model_weixin_message->addAll($xmlstr);
			
			//关注事件与取消关注事件
			$this->load->model('weixin/get_userinfo');
			if ($this->WeixinMsgType == 'event' && $this->WeixinEvent == 'subscribe') {
				//发送关注欢迎消息
				$this->load->model("weixin/auto_reply");
				$reply = $this->model_weixin_auto_reply->getReply($this->WeixinFromUserName,
					$this->WeixinToUserName, 'subscribe');
				if ($reply != false) {
					$reply = $wxtools->prepareMenu($reply, $this->appid);
					$this->response->setOutput($reply);
					return;
				}
				else {
					$this->log->write("发送关注欢迎消息出错：".$this->WeixinFromUserName." ".$this->WeixinToUserName);
				}
			}
			else if ($this->WeixinMsgType == 'event' && $this->WeixinEvent == 'unsubscribe') {
				//注销用户
				//$this->model_weixin_get_userinfo->unSubscribeUser($this->WeixinFromUserName);
				$this->log->write("注销微信用户：".$this->WeixinFromUserName);
			}
			else if ($this->WeixinMsgType == 'event' && $this->WeixinEvent == 'CLICK') {
				//发送自动应答消息，当用户点击该图文消息时，自动登录到商城并跳转到首页
				//菜单消息事件
				if ($this->WeixinEventKey == 'V1001_SELF_INFO') {
					$this->load->model("weixin/auto_reply");
					$reply = $this->model_weixin_auto_reply->getReply($this->WeixinFromUserName,
						$this->WeixinToUserName, 'order');
					if ($reply != false) {
						$reply = $wxtools->prepareMenu($reply, $this->appid);
						$this->response->setOutput($reply);
						return;
					}
					else {
						$this->log->write("发送自动应答消息出错：".$this->WeixinFromUserName." ".$this->WeixinToUserName);
					}
				}
			}
			else if ($this->WeixinMsgType == 'event' && $this->WeixinEvent == 'VIEW') {
				//直接自动登录到商城并跳转到首页
			}
			else if ($this->WeixinMsgType == 'text') {
				//自动回复
				$this->load->model("weixin/auto_reply");
				$reply = $this->model_weixin_auto_reply->getReply($this->WeixinFromUserName,
					$this->WeixinToUserName, $this->WeixinContent);
				if ($reply != false) {
					$reply = $wxtools->prepareMenu($reply, $this->appid);
					$this->response->setOutput($reply);
					return;
				}
				else { //转至多客服系统
					$this->response->setOutput($this->model_weixin_auto_reply->makeXmlMuService(
						$this->WeixinFromUserName, $this->WeixinToUserName), 'onesliu@caigezi2');
					return;
				}
			}
		}
		$this->response->setOutput("");
	}
	
	public function weixin_init() {
		// 读取微信接入配置
		$this->appid = $this->config->get('weixin_appid');
		$this->appsecret = $this->config->get('weixin_appsecret');
		$this->token = $this->config->get('weixin_token');

		// 验证微信服务器
		if ($this->token == null) {
			$this->response->setOutput("");
			$this->log->write("微信token未配置");
			return false;
		}
		
		// 验证通过且不是首次验证
		// 读取本地保存的access_token，没读到就去微信服务器取
		$this->access_token = $this->config->get('weixin_access_token');
		$this->token_expire = $this->config->get('weixin_token_expire');
		$this->token_starttime = $this->config->get('weixin_token_starttime');
		// 读取微信支付配置
		$this->paysignkey = $this->config->get('weixin_paysignkey');
		$this->partnerid = $this->config->get('weixin_partnerid');
		$this->partnerkey = $this->config->get('weixin_partnerkey');
		
		// 获取新的access_token
		$this->load->model('setting/setting');
		$this->load->model('weixin/access_token');
		if (null == $this->access_token || null == $this->token_starttime || null == $this->token_expire ||
			(time() - $this->token_starttime >= $this->token_expire)) {

			$this->access_token = $this->model_weixin_access_token->get($this->config->get('weixin_appid'),
				$this->config->get('weixin_appsecret'));
			if ($this->access_token == false) {
				// 读取access_token失败
				$this->response->setOutput("");
				$this->log->write("从微信服务器取access_token失败");
				return false;
			}
		}
		
		return true;
	}
	
	public function auth() {
		if (isset($this->request->get['jump'])) {
			$redirect = $this->request->get['jump'];
			
			foreach($this->request->get as $key => $val) {
				if ($key != 'route' && $key != 'jump' && $key != 'code' && $key != 'state') {
					$redirect .= "&$key=$val";
				}
			}
		}
		else
			$redirect = 'rest/home';
		
		$redirect = $this->url->link($redirect);
		
		if (isset($this->request->get['code'])) {
			$this->load->model('weixin/access_token');
			$this->load->model('setting/setting');
			
			$this->session->data['oauth_code'] = $this->request->get['code'];
			$this->session->data['oauth_state'] = $this->request->get['state'];
			
			$wx = $this->model_setting_setting->getSetting('weixin');
			$ac = $this->model_weixin_access_token->getTempAccessToken($wx['weixin_appid'],
					$wx['weixin_appsecret'], $this->request->get['code']);
			if ($ac != false) {
				$openid = $ac->openid;
				$this->session->data['openid'] = $openid;
				$this->session->data['oauth_access_token'] = $ac->access_token;
			}
			else {
				$this->log->write("微信认证错误");
			}
		}
		
		$this->redirect($redirect);
	}
	
	private function valid_check() {
		$valid_result = $this->valid($this->token);
		if ($valid_result == false) {
			$this->response->setOutput("");
			$this->log->write("微信接入验证失败");
			return false;
		}
		else if (is_string($valid_result)){
			// 首次验证，返回echostr
			$this->response->setOutput($valid_result);
			return '';
		}
		return true;
	}
	
	private function valid($token)
    {
    	if ($this->request->server['REQUEST_METHOD'] == 'GET') {
    		if (isset($this->request->get["signature"])) {
		        $signature = $this->request->get["signature"];
		        $timestamp = $this->request->get["timestamp"];
		        $nonce = $this->request->get["nonce"];
		        $echostr = $this->request->get['echostr'];
	
	    		if($this->checkSignature($signature, $timestamp, $nonce, $token)){
		        	return $echostr;
		        }
    		}
    	}
    	else {
    		if (isset($this->request->get["signature"])) {
		        $signature = $this->request->get["signature"];
		        $timestamp = $this->request->get["timestamp"];
		        $nonce = $this->request->get["nonce"];	
	
		        if($this->checkSignature($signature, $timestamp, $nonce, $token)){
		        	return true;
		        }
    		}
    	}

        return false;
    }

	private function checkSignature($signature, $timestamp, $nonce, $token)
	{
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	public function error() {
		$this->session->data['error_msg'] = "微信服务器出错";
		$this->session->data['url_continue'] = $this->url->link('mobile_store/cart', '', 'wxpay');
		$this->session->data['text_continue'] = '返回购物车';
		$this->redirect($this->url->link('weixin/error'));
	}
	
	//发送支付成功消息
	public function sendPayNotify($url, $order_id, $time, $amount, $bank, $openid=null) {
		if ($openid == null) {
			if (isset($this->session->data['openid']))
				$openid = $this->session->data['openid'];
			else {
				$this->log->write("不能发送付款成功消息，因为无openid");
				return;
			}
		}
		
		$data = array();
		$data['first'] = "您的订单已付款成功";
		$data['keyword1'] = $order_id;
		$data['keyword2'] = $time;
		$data['keyword3'] = $amount;
		$data['keyword4'] = $bank;
		$data['remark'] = "如有任何疑问请拨打客服电话18180423915";;
		
		$wxtool = new WeixinTools();
		$msg = $wxtool->makeModelMsg($openid, 'n-edcHT4FxaOFtm10sYUD8SRL52NCDQJ3pGWMGWUPKg', $url, $data);
		$res = $wxtool->sendModelMsg($msg, $this->access_token);
		if ($res == false) {
			$this->log->write("发送付款成功消息出错");
		}
	}

	public function sendOrderNotify($order_info) {
		if (isset($this->session->data['openid']))
			$openid = $this->session->data['openid'];
		else {
			$this->log->write("不能发送下单通知，因为无openid");
			return;
		}
		
		$messages = '';
		switch ($order_info['order_type']) {
			case 0:
			case 1:
				$messages = sprintf('亲，我们已收到您的订单\n您已购买：%s\n预估金额：%s\n我们称重后您会收到支付通知，如有疑问请在此发送消息，或拨打热线电话18180423915。',
					$order_info['comment'], $this->currency->format($order_info['total']));
				break;
			case 2:	
				$messages = sprintf('亲，我们已收到您的预定\n您已购买：%s\n金额：%s\n我们发货前您会收到支付通知，如有疑问请在此发送消息，或拨打热线电话18180423915。',
					$order_info['comment'], $this->currency->format($order_info['total']));
				break;
		}
		
		$wxtools = new WeixinTools();
		$msg = $wxtools->makeKfMsg($openid, "text", $messages, "onesliu@caigezi2");
		$response = $wxtools->sendKfMsg($msg, $this->access_token);
		if ($response == false) {
			$this->log->write("发送下单通知出错");
		}
	}

}

?>