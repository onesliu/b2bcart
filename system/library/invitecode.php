<?php
class Invitecode {
	private $config;
	private $db;
	private $data = array();
	
  	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->log = $registry->get('log');
	}
	
	public function codeType($code) {
		
		$this->db->begin();
		$ret = $this->db->query("select usertype from oc_invitecode where code='$code'");
		if ($ret == false || $ret->num_rows == 0) {
			$this->log->write("invitecode get ERROR.");
			$this->db->rollback();
			return false;
		}
		$this->db->commit();
		
		return $ret->row['usertype'];
	}
	
	public function useCode($code, $userid) {
		$this->db->begin();
		$ret = $this->db->query("update oc_invitecode set userid=$userid,hasused=1 where code='$code'");
		if ($ret == false) {
			$this->log->write("update invitecode ERROR.");
			$this->db->rollback();
			return false;
		}
		$this->db->commit();
	}
	
	public function getCode($userid, $usertype) {
		
		if ($usertype < 0 && $usertype > 3 && $userid <= 0) return false;
		
		$code = null;

		$this->db->begin();
		$ret = $this->db->query("select code from oc_invitecode where userpid=0 and hasused=0 and usertype=$usertype limit 1 for update");
		if ($ret == false) {
			$this->log->write("invitecode get ERROR.");
			$this->db->rollback();
			return false;
		}
		if ($ret->num_rows == 0) {
			$this->log->write("invitecode FULL.");
			$this->db->rollback();
			return 'FULL';
		}
		$code = $ret->row['code'];
		
		$ret = $this->db->query("update oc_invitecode set userpid=$userid, gettime=now() where code='$code'");
		if ($ret == false) {
			$this->log->write("invitecode get ERROR.");
			$this->db->rollback();
			return false;
		}
		
		$this->db->commit();
		
		return $code;
	}
}
?>