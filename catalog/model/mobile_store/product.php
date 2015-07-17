<?php
class ModelMobileStoreProduct extends Model {
	public function updateViewed($product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function getRestProducts($category_id, $limit = 0) {
		$sql = "select p.product_id,pd.name,p.model,c.category_id,c.parent_id,p.minimum,p.price,p.mpn as sellprice,
				p.sku as unit, p.upc as sellunit, p.product_type, p.image from oc_product p 
				join oc_product_to_category pc on p.product_id = pc.product_id 
				join oc_category c on pc.category_id = c.category_id 
				join oc_product_description pd on p.product_id = pd.product_id
				where p.status > 0 and c.parent_id = $category_id";
		if ($limit > 0) $sql .= " limit $limit";
		$query = $this->db->query($sql);
		if ($query->num_rows) {
			return $query->rows;
		} else {
			return false;
		}
	}
	
	public function getProduct($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
				
		$sql = "SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, 
		(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND 
		pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND 
		((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) 
		ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
		(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND 
		ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
		AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, 
		(SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . 
		(int)$customer_group_id . "') AS reward, 
		(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE 
		ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, 
		(SELECT wcd.title FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id 
		AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, 
		(SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id 
		AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, 
		(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND 
		r1.status = '1' GROUP BY r1.product_id) AS rating, 
		(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1'
		 GROUP BY r2.product_id) AS reviews, 
		p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON 
		(p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
		LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . 
		(int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . 
		"' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		$query = $this->db->query($sql);
		if ($query->num_rows) {
			if ($query->row['product_type']==0)
				$type = '固定重量商品';
			else if ($query->row['product_type']==1)
				$type = '先称重后付款商品';
			else
				$type = '特产精品';
			$query->row['price'] = ($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
			$query->row['rating'] = (int)$query->row['rating'];
			$query->row['type']	= $type;
			$query->row['subscribe'] = ($query->row['model'].'，每'.((int)$query->row['weight']) . $query->row['weight_class'].'单价');
			
			return $query->row;
		} else {
			return false;
		}
	}
	
	public function getProductsByCategoryName($name, $data = array()) {
		$query = $this->db->query("select category_id from oc_category_description where name='$name'");
		if ($query->num_rows) {
			$cid = $query->row['category_id'];
			
			$data['filter_category_id'] = $cid;
			$data['filter_sub_category'] = true;
			return $this->getProducts($data);
		}
		return array();
	}
	
	public function getTotalProductsByCategoryName($name, $data = array()) {
		$query = $this->db->query("select category_id from oc_category_description where name='$name'");
		if ($query->num_rows) {
			$cid = $query->row['category_id'];
			
			$data['filter_category_id'] = $cid;
			$data['filter_sub_category'] = true;
			return $this->getTotalProducts($data);
		}
		return 0;
	}

	public function getProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$cache = md5(http_build_query($data));
		
		$product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);
		
		if (!$product_data) {
			$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"; 
						
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			
			// -- FILTER ATTRIBUTES MODULE ---
			if (!empty($data['filter_attributes'])){
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pa ON ( p.product_id = pa.product_id) ";
			}
			// -- STOP FILTER ATTRIBUTES MODULE ---
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"; 
			
			if ($this->config->get('mobile_store_selected_products') != 0 && $this->config->get('mobile_store_selected_products') != ""){
				$sql .= " AND p.product_id IN (" . $this->config->get('mobile_store_selected_products') .") "; 
			}
			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";
											
				if (!empty($data['filter_name'])) {
					$implode = array();
					
					if (isset($data['filter_search_type']) && !empty($data['filter_search_type'])){
						$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
						
					} else {  // vine din cautarea normala
						$words = explode(' ', $data['filter_name']);
						
						foreach ($words as $word) {
							if (!empty($data['filter_description'])) {
								$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
							} else {
								$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
							}				
						}
					} 
					
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				

				if (!empty($data['filter_tag'])) {
					$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
				}
				
			
				$sql .= ")";
			}
			
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					
					$this->load->model('mobile_store/extra');
					
					$categories = $this->model_mobile_store_extra->getCategoriesByParentId($data['filter_category_id']);
										
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}
								
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
			
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			
			// -- FILTER ATTRIBUTES MODULE --
			if (!empty($data['filter_price'])) {
				if (!empty($data['filter_price_from'])){
				   $sql .= " AND p.price >= '" . $data['filter_price_from'] . "' "; 
				}
				
				if (!empty($data['filter_price_to'])){
				   $sql .= " AND p.price <= '" . $data['filter_price_to'] . "' "; 
				}
			}
			
			if (!empty($data['filter_manufacturer'])){
				$sql .= " AND p.manufacturer_id IN ( " . $data['filter_manufacturer'] . ") ";
			}
			
			if (!empty($data['filter_attributes'])){

				$implode_attributes = array();
				
				$array_filter_attributes = explode(",", $data['filter_attributes']);
				
				foreach($array_filter_attributes as $attribute_id_attribute_value ){
					$implode_attributes[] = " LOCATE('" . $attribute_id_attribute_value . "',  ( SELECT GROUP_CONCAT( CONCAT( pa.attribute_id,  '|', pa.text ) SEPARATOR  ',' ) AS attributes_group FROM " . DB_PREFIX . "product_attribute pa WHERE pa.product_id = p.product_id GROUP BY pa.product_id)) > 0 ";
				}
				
				if ($implode_attributes) {
					$sql .= " AND " . implode(" AND ", $implode_attributes) . "";
				}
				
			}
			
			$sql .= " and p.ean <> 1";
			
			// -- STOP FILTER ATTRIBUTES MODULE --
			
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
				'pd.name',
				'p.model',
				'p.quantity',
				'p.price',
				'rating',
				'p.sort_order',
				'p.date_added',
				'p2c.category_id',
			);
			
			if (!isset($data['sort'])) {
				$data['sort'] = 'pd.name';
			}
			else {
				$data['sort'] .= ',pd.name';
			}
			
			if (isset($data['sort'])) {
				if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
				} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
			} else {
				$sql .= " ORDER BY p.sort_order";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}				
	
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
			
			$product_data = array();
			
			$query = $this->db->query($sql);
		
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
		}
		
		return $product_data;
	}		
		
	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"; 
						
			if (!empty($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			}
			
			// -- FILTER ATTRIBUTES MODULE ---
			if (!empty($data['filter_attributes'])){
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pa ON ( p.product_id = pa.product_id) ";
			}
			// -- STOP FILTER ATTRIBUTES MODULE ---
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"; 
			
			if ($this->config->get('mobile_store_selected_products') != 0 && $this->config->get('mobile_store_selected_products') != ""){
				$sql .= " AND p.product_id IN (" . $this->config->get('mobile_store_selected_products') .") "; 
			}
			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";
											
				if (!empty($data['filter_name'])) {
					$implode = array();
					
					if (isset($data['filter_search_type']) && !empty($data['filter_search_type'])){
						$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
						
					} else {  // vine din cautarea normala
						$words = explode(' ', $data['filter_name']);
						
						foreach ($words as $word) {
							if (!empty($data['filter_description'])) {
								$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
							} else {
								$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
							}				
						}
					} 
					
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				

				if (!empty($data['filter_tag'])) {
					$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
				}
				
			
				$sql .= ")";
			}
			
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					
					$this->load->model('mobile_store/extra');
					
					$categories = $this->model_mobile_store_extra->getCategoriesByParentId($data['filter_category_id']);
										
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}
								
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
					
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			
			// -- FILTER ATTRIBUTES MODULE --
			if (!empty($data['filter_price'])) {
				if (!empty($data['filter_price_from'])){
				   $sql .= " AND p.price >= '" . $data['filter_price_from'] . "' "; 
				}
				
				if (!empty($data['filter_price_to'])){
				   $sql .= " AND p.price <= '" . $data['filter_price_to'] . "' "; 
				}
			}
			
			if (!empty($data['filter_manufacturer'])){
				$sql .= " AND p.manufacturer_id IN ( " . $data['filter_manufacturer'] . ") ";
			}
			
			if (!empty($data['filter_attributes'])){

				$implode_attributes = array();
				
				$array_filter_attributes = explode(",", $data['filter_attributes']);
				
				foreach($array_filter_attributes as $attribute_id_attribute_value ){
					$implode_attributes[] = " LOCATE('" . $attribute_id_attribute_value . "',  ( SELECT GROUP_CONCAT( CONCAT( pa.attribute_id,  '|', pa.text ) SEPARATOR  ',' ) AS attributes_group FROM " . DB_PREFIX . "product_attribute pa WHERE pa.product_id = p.product_id GROUP BY pa.product_id)) > 0 ";
				}
				
				if ($implode_attributes) {
					$sql .= " AND " . implode(" AND ", $implode_attributes) . "";
				}
				
			}
			
			// -- STOP FILTER ATTRIBUTES MODULE --
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function getBuchaProduct() {
		$q = $this->db->query("select * from oc_product p join oc_product_description pd on p.product_id=pd.product_id where ean=1");
		if ($q->num_rows > 0)
			return $q->row;
		return false;
	}
	
	public function getProductSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$fs_selected_products = $this->config->get('mobile_store_selected_products');	
		
		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps 
				LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
				WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' 
				AND ps.customer_group_id = '" . (int)$customer_group_id . "' 
				AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ";
		
		if ( $fs_selected_products !=0 && $fs_selected_products != "" ){		
			$sql .= "AND p.product_id IN (" . $fs_selected_products . ") ";
		}
		
		$sql .= "GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
		
		return $product_data;
	}
	
	public function getLatestProducts($limit) {
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit);

		if (!$product_data) { 
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);
		 	 
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}
	
	public function getPopularProducts($limit) {
		$product_data = array();
		
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed, p.date_added DESC LIMIT " . (int)$limit);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
					 	 		
		return $product_data;
	}

	public function getBestSellerProducts($limit) {
		$product_data = $this->cache->get('fs_product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit);

		$fs_selected_products = $this->config->get('mobile_store_selected_products');
		
		if (!$product_data || ($fs_selected_products !=0 && $fs_selected_products != "") ) { 
			$product_data = array();
			
			$sql = "SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op 
					LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) 
					LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) 
					LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
					WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() 
					AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ";
			
			if ( $fs_selected_products !=0 && $fs_selected_products != "" ){		
				$sql .= "AND p.product_id IN (" . $fs_selected_products . ") ";
			}
					
			$sql .=	" GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit;
			
			$query = $this->db->query($sql);
			
			foreach ($query->rows as $result) { 		
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('fs_product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}
	
	public function getSpecialPriceEndData($product_id) {
		if ($this->customer->isLogged()){
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = 0;
		}
		
		$sql = "SELECT date_end FROM " . DB_PREFIX . "product_special 
				WHERE product_id ='" . $product_id . "' AND date_start <= NOW() AND date_end >= NOW() ORDER BY priority ";
		if ($customer_group_id){
			$sql .= " AND customer_group_id ='" . $customer_group_id . "'";
		}
		
		$sql .= "LIMIT 0,1";
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows){
			return $query->row['date_end'];
		} else {
			return 0;
		}		
			
	}	
}
?>