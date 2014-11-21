<?php

/*微信订单编号格式：
 * 平台：0-9 公众号支付(1)、小额刷卡(2)
 * 支付类型：0-9 JSAPI(0)、NATIVE(1)、APP支付(2)
 * 业务类型：0-9 菜鸽子(0)、快消品、等等
 * 时间：20040801150101 当前时间，年月日时分秒
 * 序号：00 同一时间发生的重复订单序号不同
*/
function new_wx_orderid($platform = 1, $paytype = 0, $btype = 0, $serial = 0, $time = '') {
	$stime = $time;
	if (strlen($time) != 14) $stime = strftime('%Y%m%d%H%M%S');
	return sprintf("%d%d%d%s%02d", $platform, $paytype, $btype, $stime, $serial);
}

function inc_order_serial($orderid) {
	list($platform, $paytype, $btype, $serial, $time) = sscanf($orderid, "%d%d%d%02d%s");
	$serial++;
	if ($serial == 0) return false;
	return new_wx_orderid($platform, $paytype, $btype, $serial, $time);
}

function postToWx($url, $content) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
	$ret['content'] = curl_exec($ch);
	$ret['rescode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	return $ret;
}

function getFromWx($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
	$ret['content'] = curl_exec($ch);
	$ret['rescode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $ret;
}

class SimpleXMLExtend extends SimpleXMLElement
{
  public function addCData($nodename,$cdata_text)
  {
    $node = $this->addChild($nodename); //Added a nodename to create inside the function
    $node = dom_import_simplexml($node);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
  }
}

class PayHelper {
	
	var $params = array();
	
	public function add_param($key, $val) {
		if (is_string($val))
			$this->params[trim($key)] = trim($val);
		else
			$this->params[trim($key)] = $val;
	}
	
	public function get($key) {
		return $this->params[$key];
	}
	
	private function psort() {
		ksort($this->params);
	}
	
	private function make_param_str() {
		$this->psort();
		$pstr = "";
		foreach($this->params as $key => $val) {
			if ($key != 'sign' && $val != null && $val != "") {
				$pstr .= "$key=$val&";
			}
		}
		return trim($pstr, "&");
	}
	
	public function sign_make($key) {
		$pstr = $this->make_param_str();
		$pstr .= "&key=".$key;
		return strtoupper(md5($pstr));
	}
	
	public function make_addr_sign() {
		$pstr = $this->make_param_str();
		return sha1($pstr);
	}
	
	public function make_request($key) {
		$xml = new SimpleXMLExtend("<xml></xml>");
		foreach($this->params as $k => $val) {
			if ($val != null || $val != "") {
				if (is_string($val))
					$xml->addCData($k, $val);
				else
					$xml->addChild($k, $val);
			}
		}
		$xml->addCData('sign', $this->sign_make($key));
		return $xml->asXML();
	}
	
	public function make_param_xml() {
		$xml = new SimpleXMLExtend("<xml></xml>");
		foreach($this->params as $k => $val) {
			if ($val != null || $val != "") {
				if (is_string($val))
					$xml->addCData($k, $val);
				else
					$xml->addChild($k, $val);
			}
		}
		return $xml->asXML();
	}
	
	public function parse_response($xmlstr) {
		//解析xml并写入params数组
		$xml = new SimpleXMLExtend($xmlstr);
		unset($this->params);
		$this->params = array();
		$xmlarr = get_object_vars($xml);
		foreach ($xmlarr as $k => $val) {
			$this->add_param($k,(string)$val);
		}
		return $xml;
	}
	
	public function sign_verify($key) {
		//验证签名
		if (!isset($this->params['sign'])) return false;
		if ($this->params['sign'] != $this->sign_make($key)) return false;
		return true;
	}
}
?>