<?php
/**
 * ǩ��������
 * ============================================================================
 * api˵����
 * init(),��ʼ��������Ĭ�ϸ�һЩ������ֵ
 *getUrlString()  ��ȡurlģʽ�Ĳ����ַ���(encode������)
 *genSha1Sign() ����SHA1ǩ��
  * getKey()/setKey(),��ȡ/����key
 * getParameter()/setParameter(),��ȡ/���ò���ֵ
 * getAllParameters(),��ȡ���в���
 * getDebugInfo(),��ȡdebug��Ϣ
 * 
 * ============================================================================
 *
 */
class SignTool {
	/** ��Կ */
	var $key;
	/** ����Ĳ��� */
	var $parameters;
	/** debug��Ϣ */
	var $debugInfo;
	function __construct() {
		$this->initTool();
	}
	//function SignTool() {
	function initTool() {
		$this->key = "";
		$this->parameters = array();
		$this->debugInfo = "";
	}
	/**
	*��ʼ��������
	*/
	function init() {
		//nothing to do
	}
	/**
	*��ȡ��Կ
	*/
	function getKey() {
		return $this->key;
	}  
	/**
	*������Կ
	*/
	function setKey($key) {
		$this->key = $key;
	}
	/**
	*��ȡ����ֵ
	*/
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	/**
	*���ò���ֵ
	*/
	function setParameter($parameter, $parameterValue) {
		$this->parameters[$parameter] = $parameterValue;
	}
	/**
	*��ȡ��������Ĳ���
	*@return array
	*/
	function getAllParameters() {
		return $this->parameters;
	}
	/**
	*��ȡurlģʽ�Ĳ����ַ���(encode������)
	*/
	function getUrlString() {
		$this->createMD5Sign();
		$reqPar = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			$reqPar .= $k . "=" . urlencode($v) . "&";
		}
		//ȥ�����һ��&
		$reqPar = substr($reqPar, 0, strlen($reqPar)-1);
		return $reqPar;
	}
	/**
	*��ȡdebug��Ϣ
	*/
	function getDebugInfo() {
		return $this->debugInfo;
	}
	/**
	*����md5ժҪ,������:����������a-z����,������ֵ�Ĳ������μ�ǩ����
	*/
	function createMD5Sign() {
		$signPars = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("" != $v && "sign" != $k) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		$sign = strtoupper(md5($signPars));
		$this->setParameter("sign", $sign);
		//debug��Ϣ
		$this->_setDebugInfo("source:".$signPars . "|sign:" . $sign);	
	}	
	//����ǩ��SHA1
	function genSha1Sign(){
		$signPars = '';
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("" != $v && "sign" != $k) {
				if($signPars == '')
					$signPars .= $k . "=" . $v;
				else
					$signPars .=  "&". $k . "=" . $v;
			}
		}
		$sign = SHA1($signPars);
		$this->setParameter("sign", $sign);
		//debug��Ϣ
		$this->debugInfo = "source:".$signPars."|appsign:" . $sign;
		return $sign;
	}
	//��ȡ�������б��ظ�app�ˣ��Զ���Э��
	function getXmlBody(){
		foreach ($this-> parameters as $k => $v){
			if ($k != "appkey" && $k != "key") {
				$reqPars .= "<" . $k . ">" . $v . "</". $k .">" .PHP_EOL;
			}
		}
		return $reqPars;
	}
	
	//native֧����getpackage���ز���
	function genGetPackage(){
		$signPars = '';
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("AppSignature" != $k) {
				if($signPars == '')
					$signPars .= strtolower($k) . "=" . $v;
				else
					$signPars .=  "&". strtolower($k) . "=" . $v;
			}
		}
		$sign = SHA1($signPars);
		$this->setParameter("AppSignature", $sign);
		//debug��Ϣ
		$this->debugInfo = "source:".$signPars."|sha1sign:" . $sign;
		
		foreach ($this-> parameters as $k => $v){
			if ($k != "AppKey") {
				if($k == "RetCode" || $k == "TimeStamp")
					$reqPars .= "<" . $k . ">" . $v . "</". $k .">";
				else
					$reqPars .= "<" . $k . "><![CDATA[" . $v . "]]></". $k .">";
			}
		}
		return "<xml>".$reqPars."<SignMethod><![CDATA[sha1]]></SignMethod></xml>";
	}
	
	//άȨ�ӿڲ���ǩ�����
	function checkFeedBackSign(){
		
		if($this->parameters["AppId"] == "" || $this->parameters["TimeStamp"] == "" || $this->parameters["OpenId"] == "")
			return false;
		
		$signPars = "appid=".$this->parameters["AppId"]."&appkey=".$this->parameters["AppKey"]."&openid=".$this->parameters["OpenId"]."&timestamp=".$this->parameters["TimeStamp"];
		
		$sign = SHA1($signPars);
		
		$this->debugInfo = "source:".$signPars."|sha1sign:" . $sign;
		
		return $sign == $this->parameters["AppSignature"];
	}
	
	/**
	*����debug��Ϣ
	*/
	function _setDebugInfo($debugInfo) {
		$this->debugInfo = $debugInfo;
	}

}
?>