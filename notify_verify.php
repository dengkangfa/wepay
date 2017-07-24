<?php
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

$responseData = $GLOBALS["HTTP_RAW_POST_DATA"];	//接受通知参数

$encpt = new Encryption();
$encpt->setRsaPubKeyFile(ALIPAY_PUBLIC_KEY);
/** 处理获取到的参数 */
$rParam = $encpt->disposeResponseData($responseData);
$stringToBeSign = $rParam['stringToBeSigned'];
$signature = $rParam['signature'];
/** 验证支付结果 */
$res = $encpt->verify($stringToBeSign, $signature);
if ($res) {
	echo 'SUCCESS';
}