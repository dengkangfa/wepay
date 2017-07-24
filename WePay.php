<?php
header("Content-type:text/html;charset=utf8");
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

$body = filter_input(INPUT_POST, 'body');
$out_trade_no = filter_input(INPUT_POST, 'out_trade_no');
$total_fee = filter_input(INPUT_POST, 'total_fee');
$spbill_create_ip = filter_input(INPUT_POST, 'spbill_create_ip');
$body = urldecode($body);

$data = array(
	'body'				=>	$body,
	'out_trade_no'		=>	$out_trade_no,
	'total_fee'			=>	$total_fee,
	'spbill_create_ip'	=>	$spbill_create_ip,
	);												/** 模拟数据 */

$encpt = WeEncryption::getInstance();		//实例化签名类
$url = WE_NOTIFY_URL;
$encpt->setNotifyUrl($url);			//设置异步通知地址

$curl = new Curl();				//实例化传输类；
$xml_data = $encpt->sendRequest($curl, $data);		//发送请求

$postObj = $encpt->xmlToObject($xml_data);			//解析返回数据
if ($postObj === false) {
	//
	exit;
}
if ($postObj->return_code == 'FAIL') {
	echo $postObj->return_msg;
} else {
	$resignData = array(
		'appid'			=>	$postObj->appid,
		'partnerid'		=>	$postObj->mch_id,
		'prepayid'		=>	$postObj->prepay_id,
		'noncestr'		=>	$postObj->nonce_str,
		'timestamp'		=>	time(),
		'package'	=>	'Sign=WXPay'
		);
	$sign = $encpt->getClientPay($resignData);
	$resignData['sign'] = $sign;
	echo json_encode($resignData);
}
