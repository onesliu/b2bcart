<?php  
class ControllerRestCart extends Controller {
	public function index() {
		
		$cart = new stdClass();
		$cart->result = -1;
		$cart->total = 0;
		$cart->products = array();
		
		$this->load->model('tool/image');

		$products = $this->cart->getProducts();
		foreach ($products as $product) {
			$item = new stdClass();
			$item->pid = $product['key'];
			$item->number = $product['quantity'];
			$item->name = $product['name'];
			$item->sellunit = $product['sellunit'];
			$item->sellprice = $product['sellprice'];
			
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			}
			
			$item->image = $image;
			
			$cart->products[] = $item;
			$cart->total++;
		}
		//$this->log->write(print_r($this->session->data['cart'],true));
		$cart->result = 0;
		$this->response->setOutput(json_encode($cart));
	}
	
	public function add() {

		$json = new stdClass();
		$json->result = -1; //加入购物车失败
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
			if (isset($this->request->post['number'])) {
				$quantity = $this->request->post['number'];
			} else {
				$quantity = 1;
			}
			
			$products[$product_id] = $quantity;
		}
		
		if (isset($products)) {
			foreach($products as $product_id => $quantity) {
				if ($quantity <= 0) $quantity = 1;
				$this->cart->add($product_id, $quantity);
				//$this->log->write("cart add: $product_id, $quantity");
			}
			$json->result = 0; //加入购物车成功
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function update() {
		$json = new stdClass();
		$json->result = -1; //购物车更新失败
		
		if (isset($this->request->post['product_id']) && isset($this->request->post['number'])) {
			$product_id = $this->request->post['product_id'];
			$number = $this->request->post['product_id'];
			$this->cart->update($product_id, $number);
			//$this->log->write("cart update: $product_id, $number");
			$json->result = 0;
		}
		$this->response->setOutput(json_encode($json));
	}
	
	public function remove() {
		$json = new stdClass();
		$json->result = -1;
		
		if (isset($this->request->post['product_id'])) {
			$this->cart->remove($this->request->post['product_id']);
			//$this->log->write("cart remove: ".$this->request->post['product_id']);
			$json->result = 0;
		}
		$this->response->setOutput(json_encode($json));
	}
}
?>