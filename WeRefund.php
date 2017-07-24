<?php
/**
 * Created by PhpStorm.
 * User: Elvis Lee
 * Date: 2016/11/11
 * Time: 8:58
 */
header("Content-type:text/html;charset=utf8");
require dirname(__FILE__).'/conf/common.php';
require LIB_PATH."Autoload".CLASS_EXT;

// 注册自动加载类
spl_autoload_register("Autoload::autoload");

$out_trade_no = filter_input(INPUT_POST, "out_trade_no" , FILTER_SANITIZE_STRING);
$refund_fee = filter_input(INPUT_POST, "refund_fee");
$total_fee = filter_input(INPUT_POST, 'total_fee');
$out_refund_no = date("YmdHis", time());
$total_fee = $total_fee * 100;
$refund_fee = $refund_fee * 100;
$op_user_id = MCHID;


$encpt = WeEncryption::getInstance();
$nonce_str = $encpt->getNonceStr();

/** @var  $signData 待签名参数*/
$signData = [
    'appid'         =>  APPID,
    'mch_id'        =>  MCHID,
    'nonce_str'     =>  $nonce_str,
    'out_trade_no'  =>  $out_trade_no,
    'out_refund_no' =>  $out_refund_no,
    'total_fee'     =>  $total_fee,
    'refund_fee'    =>  $refund_fee,
    'op_user_id'    =>  $op_user_id
];
$sign = $encpt->getSign($signData);

$xmlData = "<xml><appid>%s</appid><mch_id>%s</mch_id><nonce_str>%s</nonce_str><op_user_id>%s</op_user_id><out_refund_no>%s</out_refund_no><out_trade_no>%s</out_trade_no><refund_fee>%d</refund_fee><total_fee>%d</total_fee><transaction_id></transaction_id><sign>%s</sign></xml>";

$xmlData = sprintf($xmlData, APPID, MCHID, $nonce_str, $op_user_id, $out_refund_no, $out_trade_no, $refund_fee, $total_fee, $sign);

$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

$curl = new Curl();
$curl->setUrl($url);
$response = $curl->execute(true, 'POST', $xmlData, true);
$response = $encpt->xmlToObject($response);

/** 解析返回数据 */
if ($response->return_code == "SUCCESS") {
    if ($response->result_code == "SUCCESS") {
        //
    } else {
        //
    }
} else {
    //
}