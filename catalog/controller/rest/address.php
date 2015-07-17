<?php   
class ControllerRestAddress extends Controller {
	public function index() {
		
		if (!$this->customer->isLogged()) {
			$this->redirect($this->url->link("rest/user"));
			return;
		}
		
		$this->template = 'default/template/rest/address.tpl';
		$dir_img = $this->config->get('config_url') . 'image/';
		$this->data['logo'] = $dir_img . 'logo.png';

		$this->load->model('account/address');
		$this->load->model('account/district');

		$address = $this->model_account_address->getAddresses();

		$this->children = array(
			'rest/header'
		);

		$this->response->setOutput($this->render());
	}
}
?>