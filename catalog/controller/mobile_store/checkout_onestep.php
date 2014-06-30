<?php  
class ControllerMobileStoreCheckoutOnestep extends Controller { 
	public function index() {
		if ((!$this->cart->hasProducts() && !empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
	  		$this->redirect($this->url->link('mobile_store/cart'));
    	}	

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('mobile_store/cart'));
			}				
		}

		$this->language->load('checkout/checkout');
		
		$this->document->setTitle($this->language->get('heading_title')); 
		
      	// check out values
	    $this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'));
		$this->data['text_checkout_account'] = $this->language->get('text_checkout_account');
		$this->data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$this->data['text_checkout_shipping_address'] = $this->language->get('text_checkout_shipping_address');
		$this->data['text_checkout_shipping_method'] = $this->language->get('text_checkout_shipping_method');
		$this->data['text_checkout_payment_method'] = $this->language->get('text_checkout_payment_method');		
		$this->data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');
		$this->data['text_modify'] = $this->language->get('text_modify');
		
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();	
		
		//payment address values
		$this->data['text_address_existing'] = $this->language->get('text_address_existing');
		$this->data['text_address_new'] = $this->language->get('text_address_new');
		$this->data['text_select'] = $this->language->get('text_select');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_zone'] = $this->language->get('entry_zone');
	
		if (isset($this->session->data['payment_address_id'])) {
			$this->data['address_id'] = $this->session->data['payment_address_id'];
		} else {
			$this->data['address_id'] = $this->customer->getAddressId();
		}

		$this->load->model('account/address');
		$this->load->model('account/district');

		$this->data['addresses'] = $this->model_account_address->getAddresses();

		$this->data['country_id'] = $this->config->get('config_country_id');
		
		$this->load->model('localisation/country');
		
		$this->data['countries'] = $this->model_localisation_country->getCountries();
		
		$this->data['telephone'] = $this->customer->getTelephone(); 

		// shipping values
		$this->data['text_shipping_time'] = $this->language->get('text_shipping_time');
		$this->data['text_shipping_district'] = $this->language->get('text_shipping_district');
		$this->data['shipping_districts'] = $this->model_account_district->getAddresses();
		
		// shipping time
		$shipping_interval = $this->config->get('shipping_interval');
		if ($shipping_interval == null)
			$shipping_interval = 1;
		
		$first_shipping_time = $this->config->get('first_shipping_time');
		if ($first_shipping_time == null)
			$first_shipping_time = 9;
			
		$last_shipping_time = $this->config->get('last_shipping_time');
		if ($last_shipping_time == null)
			$last_shipping_time = 19;
		
		$date_now = getdate();
		$start_time = $date_now['hours'] + 2;
		
		$today = date("Y-m-d", time());
		$tomorow = date("Y-m-d", time()+24*60*60);
		
		if ($start_time > $first_shipping_time) {
			$i = $start_time;
			//$this->data['shipping_time'] = array(0 => '立即配送');
		}
		else {
			$i = $first_shipping_time;
		}

		for(;$i <= $last_shipping_time; $i++) {
			$this->data['shipping_time']["$today $i:00:00"] = "$i:00";
		}
		
		for($i = $first_shipping_time; $i <= $last_shipping_time; $i++) {
			$this->data['shipping_time']["$tomorow $i:00:00"] = "明天 $i:00";
		}

		// cart product values
		$this->confirm();
		
		$this->data['weixin_payment'] = $this->url->link('payment/weixin');

		// view template
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mobile_store/checkout_onestep.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/mobile_store/checkout_onestep.tpl';
		} else {
			$this->template = 'default/template/mobile_store/checkout_onestep.tpl';
		}

		$this->children = array(
			'mobile_store/content_top',
			'mobile_store/content_bottom',
			'mobile_store/footer',
			'mobile_store/header'	
		);

		$this->response->setOutput($this->render());
  	}
  	
  	private function confirm() {
  		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();
		 
		$this->load->model('setting/extension');
		
		$sort_order = array(); 
		
		$results = $this->model_setting_extension->getExtensions('total');
		
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}
		
		array_multisort($sort_order, SORT_ASC, $results);
		
		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);
	
				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
		}
		
		$sort_order = array(); 
	  
		foreach ($total_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $total_data);

		$data = array();
		
		$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');
		
		if ($data['store_id']) {
			$data['store_url'] = $this->config->get('config_url');		
		} else {
			$data['store_url'] = HTTP_SERVER;	
		}
		
		if ($this->customer->isLogged()) {
			$data['customer_id'] = $this->customer->getId();
			$data['customer_group_id'] = $this->customer->getCustomerGroupId();
			$data['firstname'] = $this->customer->getFirstName();
			$data['lastname'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
			$data['fax'] = $this->customer->getFax();

			$this->load->model('account/address');
			$payment_address = $this->model_account_address->getAddress($this->data['address_id']);
		} elseif (isset($this->session->data['guest'])) {
			$data['customer_id'] = 0;
			$data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
			$data['firstname'] = $this->session->data['guest']['firstname'];
			$data['lastname'] = $this->session->data['guest']['lastname'];
			$data['email'] = $this->session->data['guest']['email'];
			$data['telephone'] = $this->session->data['guest']['telephone'];
			$data['fax'] = $this->session->data['guest']['fax'];

			$payment_address = $this->session->data['guest']['payment'];
		}
		
		$data['payment_firstname'] = $payment_address['firstname'];
		$data['payment_lastname'] = $payment_address['lastname'];	
		$data['payment_company'] = $payment_address['company'];	
		$data['payment_company_id'] = $payment_address['company_id'];	
		$data['payment_tax_id'] = $payment_address['tax_id'];	
		$data['payment_address_1'] = $payment_address['address_1'];
		$data['payment_address_2'] = $payment_address['address_2'];
		$data['payment_city'] = $payment_address['city'];
		$data['payment_postcode'] = $payment_address['postcode'];
		$data['payment_zone'] = $payment_address['zone'];
		$data['payment_zone_id'] = $payment_address['zone_id'];
		$data['payment_country'] = $payment_address['country'];
		$data['payment_country_id'] = $payment_address['country_id'];
		$data['payment_address_format'] = $payment_address['address_format'];
		$data['payment_telephone'] = $payment_address['telephone'];
	
		if (isset($this->session->data['payment_method']['title'])) {
			$data['payment_method'] = $this->session->data['payment_method']['title'];
		} else {
			$data['payment_method'] = '';
		}
		
		if (isset($this->session->data['payment_method']['code'])) {
			$data['payment_code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_code'] = '';
		}
					
		$data['shipping_firstname'] = $payment_address['firstname'];
		$data['shipping_lastname'] = $payment_address['lastname'];	
		$data['shipping_company'] = $payment_address['company'];	
		$data['shipping_address_1'] = $payment_address['address_1'];
		$data['shipping_address_2'] = $payment_address['address_2'];
		$data['shipping_city'] = $payment_address['city'];
		$data['shipping_postcode'] = $payment_address['postcode'];
		$data['shipping_zone'] = $payment_address['zone'];
		$data['shipping_zone_id'] = $payment_address['zone_id'];
		$data['shipping_country'] = $payment_address['country'];
		$data['shipping_country_id'] = $payment_address['country_id'];
		$data['shipping_address_format'] = $payment_address['address_format'];
		$data['shipping_district_id'] = $payment_address['district_id'];
		$data['shipping_telephone'] = $payment_address['telephone'];
	
		if (isset($this->session->data['shipping_method']['title'])) {
			$data['shipping_method'] = $this->session->data['shipping_method']['title'];
		} else {
			$data['shipping_method'] = '';
		}
		
		if (isset($this->session->data['shipping_method']['code'])) {
			$data['shipping_code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_code'] = '';
		}				
		
		$product_data = array();
	
		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['option_value'];	
				} else {
					$value = $this->encryption->decrypt($option['option_value']);
				}	
				
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],								   
					'name'                    => $option['name'],
					'value'                   => $value,
					'type'                    => $option['type']
				);					
			}
 
			$product_data[] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			); 
		}
		
		// Gift Voucher
		$voucher_data = array();
		
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$voucher_data[] = array(
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],						
					'amount'           => $voucher['amount']
				);
			}
		}  

		foreach($total_data as $tt) {
      		if ($tt['code'] == 'total') {
      			$this->data['totals'][] = $tt;
      		}
      	}
		$data['products'] = $product_data;
		$data['vouchers'] = $voucher_data;
		$data['totals'] = $total_data;
		$data['comment'] = (isset($this->session->data['comment']))?$this->session->data['comment']:"";
		$data['total'] = $total;
		
		if (isset($this->request->cookie['tracking'])) {
			$this->load->model('affiliate/affiliate');
			
			$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
			
			if ($affiliate_info) {
				$data['affiliate_id'] = $affiliate_info['affiliate_id']; 
				$data['commission'] = ($total / 100) * $affiliate_info['commission']; 
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
		} else {
			$data['affiliate_id'] = 0;
			$data['commission'] = 0;
		}
		
		$data['language_id'] = $this->config->get('config_language_id');
		$data['currency_id'] = $this->currency->getId();
		$data['currency_code'] = $this->currency->getCode();
		$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
		} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
		} else {
			$data['forwarded_ip'] = '';
		}
		
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
		} else {
			$data['user_agent'] = '';
		}
		
		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
		} else {
			$data['accept_language'] = '';
		}
					
		$this->load->model('checkout/order');
		
		//$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);
		$this->session->data['order_info'] = $data;
		$this->data['order_info'] = $data;
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_total'] = $this->language->get('column_total');

		$this->data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['option_value'];	
				} else {
					$filename = $this->encryption->decrypt($option['option_value']);
					
					$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
				}
									
				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}  
 
			$this->data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
				'total'      => $this->currency->format($this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'))),
				'href'       => $this->url->link('mobile_store/product', 'product_id=' . $product['product_id'])
			); 
		} 
		
		// Gift Voucher
		$this->data['vouchers'] = array();
		
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'])
				);
			}
		}
					
		//$this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);
  	}
}
?>