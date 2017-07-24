<?php
/**
 * Created by PhpStorm.
 * User: Elvis Lee
 * Date: 2016/11/11
 * Time: 12:10
 */

header("Content-type:text/html;charset=gbk");
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

$out_trade_no = filter_input(INPUT_POST, 'out_trade_no', FILTER_SANITIZE_STRING);
$refund_amount = filter_input(INPUT_POST, 'refund_amount');
$out_request_no = mb_substr(md5(time()), 0, 15);

$biz_Content = array(
    'out_trade_no'      =>  $out_trade_no,
    'refund_amount'     =>  $refund_amount,
    'out_request_no'    =>  $out_request_no
);
$bizContent = json_encode($biz_Content);
$signData = array(
    'app_id'        =>  APP_ID,
    'method'        =>  'alipay.trade.refund',
    'format'        =>  'JSON',
    'charset'       =>  'utf-8',
    'sign_type'     =>  'RSA',
    'sign'          =>  '',
    'timestamp'     =>  date('Y-m-d H:i:s', time()),
    'version'       =>  '1.0',
    'biz_content'   =>  $bizContent
);

$encpt = new Encryption();

/** 设置私钥 */
$encpt->setRsaPriKeyFile(PRIVATE_KEY);

$sign = $encpt->getSignature($signData);

$curl = new Curl();
$curl->setUrl('https://openapi.alipay.com/gateway.do');
$response = $curl->execute(true, 'GET', $sign);

$retObj = json_decode($response);
/** 会员卡逻辑处理 */
if ($retObj->alipay_trade_refund_response->code == "10000") {
    //
} else {
    //
}