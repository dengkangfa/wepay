<?php
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

// 接收参数
$out_trade_no = filter_input(INPUT_POST, "out_trade_no");

if (empty($userno) || empty($VipNo) || empty($out_trade_no)) {
    exit;
}

$curl = new Curl();				//实例化传输工具类；
$encpt = WeEncryption::getInstance();   // 实例化微信支付工具类
$content = $encpt->queryOrder($curl, $out_trade_no);     // 调用统一下单API

if(empty($content)){
	//
	exit;
}
$postObj = $encpt->xmlToObject($content);			//解析返回数据
/** 
 * 判断请求是否成功
 */
if ('FAIL' == $postObj->return_code) {
	//
	exit;
}

/**
 * 判断支付结果是否成功
 */
if ('FAIL' == $postObj->result_code) {
	//
	exit;
}