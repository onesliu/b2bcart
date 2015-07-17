<?php
class ModelAccountUser extends Model {
	public function addUser($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['username']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', district_id = '" . (int)$data['district_id'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
	}
	
	public function add_user($data) {
		$sql = "select count(*) as cnt from oc_user where username='%s'";
		$sql = sprintf($sql, $this->db->escape($data["telephone"]));
		$ret = $this->db->query($sql);
		if ($ret == false || $ret->row['cnt'] > 0) {
			return "重复的手机号码";
		}
		
		$usergroup = 0;
		if ($data["usertype"] < 1 || $data["usertype"] > 4)
			return "邀请码用户类型不正确";
		
		switch ($data["usertype"]) {
			case 1:
				$uname = "配送人员";
				break;
			case 2:
				$uname = "销售人员";
				break;
			case 3:
				$uname = "中心人员";
				break;
			case 4:
				$uname = "供应商";
				break;
		}
		$sql = "select user_group_id,name from oc_user_group where name='$uname'";
		$ret = $this->db->query($sql);
		if ($ret->num_rows > 0) {
			$usergroup = $ret->row['user_group_id'];
		}
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['telephone']) .
		"', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . 
		"', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . 
		"', firstname = '" . $this->db->escape($data['username']) . 
		"', usertype = '" . (int)$data['usertype'] . 
		"', user_group_id = '" . (int)$usergroup . 
		"', status = 1, date_added = NOW()");
		
		$user_id = $this->db->getLastId();
		return (int)$user_id;
	}
	
	public function editUser($user_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['username']) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', user_group_id = '" . (int)$data['user_group_id'] . "', district_id = '" . (int)$data['district_id'] . "', status = '" . (int)$data['status'] . "' WHERE user_id = '" . (int)$user_id . "'");
		
		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
		}
	}

	public function editPassword($user_id, $password) {
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', code = '' WHERE user_id = '" . (int)$user_id . "'");
	}

	public function editCode($email, $code) {
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET code = '" . $this->db->escape($code) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}
			
	public function deleteUser($user_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	}
	
	public function getUser($user_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	
		return $query->row;
	}
	
	public function getUserByUsername($username) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($username) . "'");
	
		return $query->row;
	}
		
	public function getUserByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");
	
		return $query->row;
	}
		
	public function getUsers($data = array()) {
		$sql = "select u.*,ug.name as user_group,d.name as district from `" . DB_PREFIX . "user` u join `" . DB_PREFIX . "user_group` ug on u.user_group_id=ug.user_group_id left outer join `" . DB_PREFIX . "district` d on u.district_id=d.id";
			
		$sort_data = array(
			'username',
			'status',
			'date_added',
			'user_group',
			'district'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY username";	
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
			
		$query = $this->db->query($sql);
	
		return $query->rows;
	}

	public function getTotalUsers() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user`");
		
		return $query->row['total'];
	}

	public function getTotalUsersByGroupId($user_group_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE user_group_id = '" . (int)$user_group_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalUsersByEmail($email) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row['total'];
	}	
}
?>