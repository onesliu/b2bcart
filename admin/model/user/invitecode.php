<?php
class ModelUserInvitecode extends Model {
	public function addCode($code, $usertype, ) {
		$this->db->query("INSERT INTO " . DB_PREFIX . 
		"district SET city = '" . $this->db->escape($data['city']) . 
		"', name = '" . $this->db->escape($data['name']) . 
		"', address = '" . $this->db->escape($data['address']) . 
		"', map = '" . $this->db->escape($data['map']) . 
		"', desp = '" . $this->db->escape($data['desp']) . "'");
		
		$address_id = $this->db->getLastId();
		
		return $address_id;
	}
	
	public function editAddress($address_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . 
		"district SET city = '" . $this->db->escape($data['city']) . 
		"', name = '" . $this->db->escape($data['name']) . 
		"', address = '" . $this->db->escape($data['address']) . 
		"', map = '" . $this->db->escape($data['map']) . 
		"', desp = '" . $this->db->escape($data['desp']) .
		"' where id=" . $address_id);
	}
	
	public function deleteAddress($address_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "district WHERE id =" . (int)$address_id);
	}	
	
	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "district WHERE id = '" . (int)$address_id . "'");
		
		if ($address_query->num_rows) {
			$address_data = array(
				'id'		=> $address_query->row['id'],
				'city'      => $address_query->row['city'],
				'name'       => $address_query->row['name'],
				'address'        => $address_query->row['address'],
				'map'     => $address_query->row['map'],
				'desp'         => $address_query->row['desp']
			);
			
			return $address_data;
		} else {
			return false;	
		}
	}
	
	public function getAddresses() {
		$address_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "district");
	
		foreach ($query->rows as $result) {
			$address_data[$result['id']] = array(
				'id'	=> $result['id'],
				'city'      => $result['city'],
				'name'       => $result['name'],
				'address'        => $result['address'],
				'map'     => $result['map'],
				'desp'         => $result['desp']
			);
		}		
		
		return $address_data;
	}	
	
	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "district");
	
		return $query->row['total'];
	}
}
?>