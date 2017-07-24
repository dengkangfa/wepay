<?php
header("Content-type:text/html;charset=utf8");
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

// 获取传递数据
$subject = filter_input(INPUT_POST, 'subject');
$total_amount = filter_input(INPUT_POST, 'total_amount');

if (empty($subject) && empty($total_amount)) {
    exit;
}

// 将 subject 进行编码，防止中文出错
$subject = urldecode($subject);

$time = microtime(true);
$time = explode(".", $time);
$out_trade_no = date('YmdHi',time()).$time[1];

// 业务参数数组
$bizContent = array(
	"timeout_express"	=>	"30m",
	"product_code"		=>	"QUICK_MSECURITY_PAY",
	"total_amount"		=>	$total_amount,
	"subject"			=>	$subject,
	"out_trade_no"		=>	$out_trade_no
	);
$bizContent = json_encode($bizContent);
// 公共参数数组
$sParam = array(
		'app_id'			=>	APP_ID,
		'method'			=>	'alipay.trade.app.pay',
		'charset'			=>	'utf-8',
		'sign_type'			=>	'RSA',
		'sign'				=>	'',
		'timestamp'			=>	date("Y-m-d H:i:s",time()),
		'version'			=>	'1.0',
		'notify_url'		=>	ALI_NOTIFY_URL,
		'biz_content'		=>	$bizContent
	);

$encpt = new Encryption();		// 实例化支付宝支付类
/** 设置私钥 */
$encpt->setRsaPriKeyFile(PRIVATE_KEY);
/** 获取签名 */
$curl = new Curl();
$content = $encpt->requestAlipay($sParam, $curl);
echo $content;