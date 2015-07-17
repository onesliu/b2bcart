<?php
include_once(DIR_APPLICATION."controller/weixin/weixin.php");

class ControllerWeixinShare extends ControllerWeixinWeixin {
	public function index() {

		if ($this->weixin_init() != true) {
			$this->error();
			return; //首次验证或初始化失败
		}
		
		if (!isset($this->request->data['actionid'])) {
			$this->redirect($this->url->link('mobile_store/home'));
		}
		$actionid = $this->request->data['actionid'];
		
		if (!isset($this->request->data['shareid'])) {
			$openid = $this->request->data['shareid'];
			$url = $this->request->data['url'];
			$this->redirect($this->url->link('mobile_store/home'));
		}
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->fastgetOrder($this->session->data['order_info']['order_id']);
    	if ($order_info['order_status_id'] < 3) {
    		
    		//订单还是未支付状态，发起支付查询
			$wxPayHelper = new PayHelper();
			$wxPayHelper->add_param("appid", (string)$this->appid);
			$wxPayHelper->add_param("mch_id", (string)$this->partnerid);
			$wxPayHelper->add_param("nonce_str", (string)time());
			$wxPayHelper->add_param("out_trade_no", (string)$order_info['order_id']);
			
			$wxtools = new WeixinTools();
			$request = $wxPayHelper->make_request($this->partnerkey);
			$url = "https://api.mch.weixin.qq.com/pay/orderquery";
			$response = $wxtools->postToWx($url, $request);
			
			if ($response == false) {
				$this->log->write("weixin order query response error.");
				$this->error();
				return;
			}
			
			$resHelper = new PayHelper();
			$res = $resHelper->parse_response($response);
			if (isset($res->return_code) == false || isset($res->return_msg) == false ||
				isset($res->result_code) == false || (string)$res->return_code != 'SUCCESS' ||
				(string)$res->result_code != 'SUCCESS') {
				$this->log->write("order query response error: \n". $response);
				$this->error();
				return;
			}
			
			if ($resHelper->sign_verify($this->partnerkey) != true) {
				$this->log->write("order query response sign verify error: \n". $response);
				$this->error();
				return;
			}

			//支付结果显示
			$trade_state = array('SUCCESS' => '支付成功',
							'REFUND' => '转入退款',
							'NOTPAY' => '未支付',
							'CLOSED' => '已关闭',
							'REVOKED' => '已撤销',
							'USERPAYING' => '用户支付中',
							'NOPAY' => '未支付(输入密码或确认支付超时)',
							'PAYERROR' => '支付失败(其他原因，如银行返回失败)');
			$this->data['error_msg'] = $trade_state[(string)$res->trade_state];
			
			if ((string)$res->trade_state == 'SUCCESS') {
				if ($order_info['order_status_id'] == 2) {
					$this->model_checkout_order->orderChangeStatus($order_info);
					
					if (isset($this->session->data['coupon']) && ($order_info['coupon_total'] > 0)) {
						$coupon = $this->session->data['coupon'];
						$this->load->model('checkout/coupon');
						$ret = $this->model_checkout_coupon->commitCoupon($this->customer->getId(), $coupon['coupon_id'],
							$order_info['order_id'], $order_info['total']);
					}
				}
				
				//发送支付成功消息
				$url = $this->url->link2('mobile_store/order/info', 'order_id='.$order_info['order_id']);
				$order_id = $order_info['order_id'];
				$time = $res->time_end;
				$amount = $this->currency->format($res->total_fee/100);
				if (isset($res->coupon_fee) && $res->coupon_fee > 0) {
					$amount = $this->currency->format(($res->total_fee - $res->coupon_fee)/100) .
						"(代金劵支付：".$this->currency->format($res->coupon_fee/100). ")";
				}
				$bank = $res->bank_type;
				$this->sendPayNotify($url, $order_id, $time, $amount, $bank);
			}
			else {
				//发送支付失败消息
				//$this->sendOrderNotify()
			}

			$this->save_result($order_info, $response);
    	}
    	else {
			$this->data['error_msg'] = '订单已经支付';
    	}
    	
		$this->data['continue'] = $this->url->link('mobile_store/order');
		$this->data['text_continue'] = '马上查看订单';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/weixin/error.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/weixin/error.tpl';
		} else {
            $this->template = 'default/template/weixin/error.tpl';
        }
		
		$this->children = array(
			'mobile_store/titlebar',
			'mobile_store/header'	
		);

		$this->response->setOutput($this->render());
	}
	
}
?>