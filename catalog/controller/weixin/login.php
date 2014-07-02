<?php 
class ControllerWeixinLogin extends Controller {
	private $error = array();
	
	public function index() {
		// Login override for admin users
		if (!empty($this->request->get['token'])) {
			$this->customer->logout();
			
			$this->load->model('account/customer');
			
			$customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);
			
		 	if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
				$this->redirect($this->url->link('mobile_store/home', '', 'SSL')); 
			}
		}
		
		if (isset($this->request->get['code'])) {
			$this->load->model('weixin/access_token');
			$this->load->model('setting/setting');
			
			$wx = $this->model_setting_setting->getSetting('weixin');
			if ($this->model_weixin_access_token->getTempAccessToken($wx['weixin_appid'],
					$wx['weixin_appsecret'], $this->request->get['code']) == true) {
				$openid = $this->model_weixin_access_token->auth2_access_token->openid;
			}
			else {
				$this->response->setOutput("微信认证错误，请重试");
				return;
			}
		}
		
		if ($this->customer->isLogged()) {  
      		$this->redirect($this->url->link('mobile_store/home', '', 'SSL'));
    	}
    	
    	if (isset($this->request->post['email']) && isset($this->request->post['password'])) {
  			$email = $this->request->post['email'];
  			$passwd = $this->request->post['password'];
  		}
  		else if (isset($this->request->get['email']) && isset($this->request->get['password'])) {
  			$email = $this->request->get['email'];
  			$passwd = $this->request->get['password'];
  		}
  		else if (isset($openid)) {
  			$email = $openid;
  			$passwd = WEIXIN_USERPWD;
  		}
  		else {
  			$this->response->setOutput("没有登录用户名和密码，请重试");
  			return;
  		}
    	
  		if ($this->customer->login($email, $passwd)) {
			unset($this->session->data['guest']);
			
			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], HTTP_SERVER) !== false || strpos($this->request->post['redirect'], HTTPS_SERVER) !== false)) {
				$this->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->redirect($this->url->link('mobile_store/home', '', 'SSL')); 
			}
    	}
    	else {
    		$this->response->setOutput("自动登录失败");
    	}
  	}
  
}
?>