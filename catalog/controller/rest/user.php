<?php 
class ControllerRestUser extends Controller {
	private $error = array();
	
	public function index() {
		
		if ($this->customer->isLogged()) {
			$this->data['user'] = $this->customer->getEmail();
		}
		
		$this->template = 'default/template/rest/userlogin.tpl';
		$dir_img = $this->config->get('config_url') . 'image/';
		$this->data['logo'] = $dir_img . 'logo.png';
		$this->data['home'] = $this->url->link('rest/home');

		$this->children = array(
			'rest/header'
		);

		$this->response->setOutput($this->render());
  	}
	
	public function login() {
		
		$ret = new stdClass();
		$ret->result = 0;
		
		if (!$this->customer->isLogged()) {
			if (isset($this->request->post['user']) && isset($this->request->post['password'])) {
				$user = $this->request->post['user'];
				$password = $this->request->post['password'];
			}
			else {
				$ret->result = -1;
				$this->response->setOutput(json_encode($ret));
				return;
			}
			
			if ($this->customer->login($user, $password)) {
				unset($this->session->data['guest']);
			}
			else {
				$ret->result = -1;
			}
    	}
		$this->response->setOutput(json_encode($ret));
	}
	
	public function register() {
		$ret = new stdClass();
		$ret->result = -1;
		
		if (!isset($this->request->post['telephone']) ||
			!isset($this->request->post['password']) ||
			!isset($this->request->post['storename']) ||
			!isset($this->request->post['username']) ||
			!isset($this->request->post['address']) ||
			!isset($this->request->post['invitecode'])) {
			$ret->error = "注册信息不全";
			$this->log->write($ret->error);
  			$this->response->setOutput(json_encode($ret));
			return;
  		}
		
		$data = array();
		$data['telephone'] = $this->request->post['telephone'];
		$data['password'] = $this->request->post['password'];
		$data['storename'] = $this->request->post['storename'];
		$data['username'] = $this->request->post['username'];
		$data['address'] = $this->request->post['address'];
		$data['invitecode'] = $this->request->post['invitecode'];
		
		$usertype = $this->invitecode->codeType($data['invitecode']);
		if ($usertype === false) {
			$ret->error = "邀请码不正确";
			$this->log->write($ret->error);
			$this->response->setOutput(json_encode($ret));
			return;
		} else if ($usertype < 0 || $usertype > 1) {
			$ret->error = "不是客户邀请码";
			$this->log->write($ret->error);
			$this->response->setOutput(json_encode($ret));
			return;
		}
		
		$data["usertype"] = $usertype;
		
		$this->load->model('account/customer');
		$this->load->model('account/user');
		
		if ($usertype == 0) {
			$userid = $this->model_account_customer->add_customer($data);
			if (!is_int($userid)) {
				$ret->error = $userid;
				$this->log->write($ret->error);
				$this->response->setOutput(json_encode($ret));
				return;
			}
		} else if ($usertype == 1) {
			$userid = $this->model_account_customer->add_customer($data);
			if (!is_int($userid)) {
				$ret->error = $userid;
				$this->log->write($ret->error);
				$this->response->setOutput(json_encode($ret));
				return;
			}
			
			$userid = $this->model_account_user->add_user($data);
			if (!is_int($userid)) {
				$ret->error = $userid;
				$this->log->write($ret->error);
				$this->response->setOutput(json_encode($ret));
				return;
			}
		}
		else {
			$ret->error = "不是客户邀请码";
			$this->log->write($ret->error);
			$this->response->setOutput(json_encode($ret));
			return;
		}
		
		$this->invitecode->useCode($data['invitecode'], $userid);
		//$this->customer->login($data['telephone'], $data['password']);
		//unset($this->session->data['guest']);
	
		$ret->result = 0;
		$this->response->setOutput(json_encode($ret));
	}
}
?>