<?php  
class ControllerRestProducts extends Controller {
	public function index() {
		
		$product = new stdClass();
		$product->result = -1;

		if (isset($this->request->get['category'])) {
			$cat_id = $this->request->get['category'];
		} else {
			$this->response->setOutput(json_encode($product));
			return;
		}
		
		$limit = 0;
		if (isset($this->request->post['limit'])) {
			$limit = $this->request->post['limit'];
		}
		
		$this->load->model('mobile_store/product');
		$this->load->model('tool/image');
		
		$results = $this->model_mobile_store_product->getRestProducts($cat_id, $limit);
		if ($results == false || !is_array($results)) {
			$this->response->setOutput(json_encode($product));
			return;
		}
		
		$no_image = $this->model_tool_image->resize('no_image.jpg', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));

		$product->no_image = $no_image;
		$product->info = array();
		foreach ($results as $result) {
		
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = $no_image;
			}

			$c = new stdClass();
			$c->product_id = $result['product_id'];
			$c->parent_id = $result['parent_id'];
			$c->category_id = $result['category_id'];
			$c->name = $result['name'];
			$c->model = $result['model'];
			$c->minimum = $result['minimum'];
			$c->price = $result['price'];
			$c->sellprice = $result['sellprice'];
			$c->unit = $result['unit'];
			$c->sellunit = $result['sellunit'];
			$c->product_type = $result['product_type'];
			$c->image = $image;
			$product->info[] = $c;
		}
		
		$product->result = 0;
		$this->response->setOutput(json_encode($product));
	}
}
?>