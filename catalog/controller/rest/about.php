<?php   
class ControllerRestAbout extends Controller {
	public function index() {
		$dir_img = $this->config->get('config_url') . 'image/';
		$this->data['logo'] = $dir_img . 'logo.png';
		
		$this->template = 'default/template/rest/about.tpl';
		
		$this->children = array(
				'rest/header'
			);
			
    	$this->response->setOutput($this->render());
	} 	
}
?>