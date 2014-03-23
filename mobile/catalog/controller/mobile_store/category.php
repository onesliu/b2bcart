<?php 
class ControllerMobileStoreCategory extends Controller {  
	public function index() { 
		$this->language->load('mobile_store/category');
		
		$this->load->model('catalog/category');
		
		$this->load->model('catalog/product');
		$this->load->model('mobile_store/product');
		
		$this->load->model('tool/image'); 
		
		// -- FILTER ATTRIBUTES MODULE --
		$this->load->model('tool/image');
		
		if (isset($this->request->get['filter_price'])){
			$filter_price = $this->request->get['filter_price'];
			list($filter_price_from, $filter_price_to) = preg_split('/\|/', $filter_price);
		} else {
			$filter_price = '';
			$filter_price_from = '';
			$filter_price_to = '';
		}
		
		if (isset($this->request->get['filter_manufacturer'])){
			$filter_manufacturer = implode("," , preg_split('/\-/', $this->request->get['filter_manufacturer']));
		} else {
			$filter_manufacturer = array();
		}
		
		if (isset($this->request->get['filter_attributes'])){
			$filter_attributes = implode("," , preg_split('/\-/', html_entity_decode($this->request->get['filter_attributes'])));
		} else {
			$filter_attributes = array();
		}
		// -- STOP FILTER ATTRIBUTES MODULE --
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 6;
		}
					
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);	
			
		if (isset($this->request->get['fspath'])) {
			$path = '';
		
			$parts = explode('_', (string)$this->request->get['fspath']);
		
			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}
									
				$category_info = $this->model_catalog_category->getCategory($path_id);
				
				if ($category_info) {
	       			$this->data['breadcrumbs'][] = array(
   	    				'text'      => $category_info['name'],
						'href'      => $this->url->link('mobile_store/category', 'fspath=' . $path),
        				'separator' => $this->language->get('text_separator')
        			);
				}
			}		
		
			$category_id = array_pop($parts);
		} else {
			$category_id = 0;
		}
		
		$category_info = $this->model_catalog_category->getCategory($category_id);
	
		if ($category_info) {
	  		$this->document->setTitle($category_info['name']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);
			
			$this->data['heading_title'] = $category_info['name'];
			
			$this->data['text_refine'] = $this->language->get('text_refine');
			$this->data['text_select'] = $this->language->get('text_select');
			$this->data['text_empty'] = $this->language->get('text_empty');			
			$this->data['text_quantity'] = $this->language->get('text_quantity');
			$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$this->data['text_model'] = $this->language->get('text_model');
			$this->data['text_price'] = $this->language->get('text_price');
			$this->data['text_tax'] = $this->language->get('text_tax');
			$this->data['text_points'] = $this->language->get('text_points');
			$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$this->data['text_display'] = $this->language->get('text_display');
			$this->data['text_list'] = $this->language->get('text_list');
			$this->data['text_grid'] = $this->language->get('text_grid');
			$this->data['text_sort'] = $this->language->get('text_sort');
			$this->data['text_limit'] = $this->language->get('text_limit');
					
			$this->data['button_cart'] = $this->language->get('button_cart');
			$this->data['button_wishlist'] = $this->language->get('button_wishlist');
			$this->data['button_compare'] = $this->language->get('button_compare');
			$this->data['button_continue'] = $this->language->get('button_continue');
					
			if ($category_info['image']) {
				$this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			} else {
				$this->data['thumb'] = '';
			}
									
			$this->data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$this->data['compare'] = $this->url->link('mobile_store/compare');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}	
			
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
								
			$this->data['categories'] = array();
			
			$results = $this->model_catalog_category->getCategories($category_id);
			
			foreach ($results as $result) {
				$data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true	
				);
							
				$product_total = $this->model_catalog_product->getTotalProducts($data);
				
				$this->data['categories'][] = array(
					'name'  => $result['name'] . ' (' . $product_total . ')',
					'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '_' . $result['category_id'] . $url)
				);
			}
			
			$this->data['products'] = array();
			
			$data = array(
				'filter_category_id' => $category_id, 
				'filter_sub_category'=> true, 
				// -- STOP FILTER ATTRIBUTES MODULE --
				'filter_price'       => $filter_price,
				'filter_price_from'  => $filter_price_from,
				'filter_price_to'    => $filter_price_to,
				'filter_manufacturer'=> $filter_manufacturer,
				'filter_attributes'  => $filter_attributes,
				// -- STOP FILTER ATTRIBUTES MODULE -
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
					
			$product_total = $this->model_mobile_store_product->getTotalProducts($data); 
			
			$results = $this->model_mobile_store_product->getProducts($data);
			
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('mobile_store_image_width'), $this->config->get('mobile_store_image_height'));
				} else {
					$image = $this->model_tool_image->resize('no_image.jpg', $this->config->get('mobile_store_image_width'), $this->config->get('mobile_store_image_height'));
				}
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
				
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}	
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
				} else {
					$tax = false;
				}				
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
								
				$this->data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => mb_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'        => $this->url->link('mobile_store/product', 'fspath=' . $this->request->get['fspath'] . '&product_id=' . $result['product_id'])
				);
			}
			
			$url = '';
	
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
							
			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=p.price&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=p.price&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=rating&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=rating&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . '&sort=p.model&order=DESC' . $url)
			);
			
			$url = '';
	
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			$this->data['limits'] = array();
						
			$this->data['limits'][] = array(
				'text'  => 6,
				'value' => 6,
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . $url . '&limit=6')
			);
			
			$this->data['limits'][] = array(
				'text'  => 9,
				'value' => 9,
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . $url . '&limit=9')
			);

			$this->data['limits'][] = array(
				'text'  => 12,
				'value' => 12,
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . $url . '&limit=12')
			);
			
			$this->data['limits'][] = array(
				'text'  => 15,
				'value' => 15,
				'href'  => $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . $url . '&limit=15')
			);
						
			$url = '';
	
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
					
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('mobile_store/category', 'fspath=' . $this->request->get['fspath'] . $url . '&page={page}');
		
			$this->data['pagination'] = $pagination->render();
		
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
		
			$this->data['continue'] = $this->url->link('mobile_store/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mobile_store/category.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/mobile_store/category.tpl';
			} else {
				$this->template = 'default/template/mobile_store/category.tpl';
			}
			
			$this->children = array(
				'mobile_store/column_left',
				'mobile_store/content_top',
				'mobile_store/content_bottom',
				'mobile_store/footer',
				'mobile_store/header'
			);
				
			$this->response->setOutput($this->render());										
    	} else {
			$url = '';
			
			if (isset($this->request->get['fspath'])) {
				$url .= '&fspath=' . $this->request->get['fspath'];
			}
									
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
				
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('mobile_store/category', $url),
				'separator' => $this->language->get('text_separator')
			);
				
			$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('mobile_store/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mobile_store/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/mobile_store/not_found.tpl';
			} else {
				$this->template = 'default/template/mobile_store/not_found.tpl';
			}
			
			$this->children = array(
				'mobile_store/column_left',
				'mobile_store/content_top',
				'mobile_store/content_bottom',
				'mobile_store/footer',
				'mobile_store/header'
			);
					
			$this->response->setOutput($this->render());
		}
  	}
}
?>