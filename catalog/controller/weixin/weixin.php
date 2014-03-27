<?php

class ControllerWeixinWeixin extends Controller { 
	public function index() {
		
		// ��ȡ΢��������Ϣ
		$token = $this->config->get('weixin_token');
		
		// ��֤΢�ŷ�����
		$valid_result = $this->valid();
		if ($token == null || $valid_result == false) {
			// ���ش���
		}
		else if (is_string($valid_result)) {
			// �״���֤������echostr
			
		}
		
		// ��֤ͨ���Ҳ����״���֤
		// ��ȡ���ر����access_token��û������ȥ΢�ŷ�����ȡ
		$access_token = $this->config->get('weixin_access_token');
		if (null == $access_token) {
			require('access_token.php');
			$AccessToken = new AccessToken();
			if ($AccessToken->get($this->config->get('weixin_appid'),
				$this->config->get('weixin_appsecret')) == false) {
				// ��ȡaccess_tokenʧ��
			}
			else {
				$access_token = $AccessToken->access_token;
			}
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
		}
	}
	
	private function valid($token)
    {
    	if (isset($this->request->get['signature'])) {
	        $signature = $this->request->get["signature"];
	        $timestamp = $this->request->get["timestamp"];
	        $nonce = $this->request->get["nonce"];	
    		$echoStr = $this->request->get["echostr"];

    		if($this->checkSignature($signature, $timestamp, $nonce, $token)){
	        	return $echoStr;
	        }
    	}
    	else if (isset($this->request->post['signature'])) {
	        $signature = $this->request->get["signature"];
	        $timestamp = $this->request->get["timestamp"];
	        $nonce = $this->request->get["nonce"];	

	        if($this->checkSignature($signature, $timestamp, $nonce, $token)){
	        	return true;
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
}

?>