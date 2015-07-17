<?php  
class ControllerUserInvitecode extends Controller {  
  	public function index() {
		
    	$return = new stdClass();
		$return->result = -1;
		$return->msg = "获取邀请码出错";
		
		if (isset($this->request->get['usertype'])) {
			$usertype = $this->request->get['usertype'];
		
			$user = $this->user->getUserInfo();
			$userid = $user['user_id'];
			$code = $this->invitecode->getCode($userid, $usertype);
			if ($code == false) {
				$return->msg = "获取邀请码出错";
			} else if ($code == 'FULL') {
				$return->msg = "邀请码已用完";
			} else {
				$return->result = 0;
				$return->code = $code;
			}
		}

    	$this->response->setOutput(json_encode($return));
  	}
	
}
?>