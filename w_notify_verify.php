<?php
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

$encpt = WeEncryption::getInstance();
$obj = $encpt->getNotifyData();
if ($obj === false) {
	exit;
}
if ($obj) {
	$data = array(
		'appid'				=>	$obj->appid,
		'mch_id'			=>	$obj->mch_id,
		'nonce_str'			=>	$obj->nonce_str,
		'result_code'		=>	$obj->result_code,
		'openid'			=>	$obj->openid,
		'trade_type'		=>	$obj->trade_type,
		'bank_type'			=>	$obj->bank_type,
		'total_fee'			=>	$obj->total_fee,
		'cash_fee'			=>	$obj->cash_fee,
		'transaction_id'	=>	$obj->transaction_id,
		'out_trade_no'		=>	$obj->out_trade_no,
		'time_end'			=>	$obj->time_end
		);
	$sign = $encpt->getSign($data);
	if ($sign == $obj->sign) {
		$reply = "<xml>
					<return_code><![CDATA[SUCCESS]]></return_code>
					<return_msg><![CDATA[OK]]></return_msg>
				</xml>";
		echo $reply;
		exit;
	}
}