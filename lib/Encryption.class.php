<?php 

class Encryption {

	private $rsaPriKeyFile = null;
	private $rsaPubKeyFile = null;
	private $verifyStr = null;

	/**
	 * 设置私钥文件路径
	 * @param  String $path 私钥文件路径
	 */
	public function setRsaPriKeyFile($path) {
		$this->rsaPriKeyFile = $path;
	}

	/**
	 * 设置公钥文件路径
	 * @param  String $path 公钥文件路径
	 */
	public function setRsaPubKeyFile($path) {
		$this->rsaPubKeyFile = $path;
	}

	/**
	 * 请求支付宝网关
	 * @return Mixed       返回请求到的内容；
	 */
	public function requestAlipay($sParam, Curl $curl) {
		$str = $this->getSignature($sParam);			// 获取签名
		return $str;
	}

	/**
	 * 获取待传输的数据
	 * @return String 待传输的数据
	 */
	public function getSignature($sParam) {
		$tempStr = $this->Assembling($sParam);		/** 拼接待签名字符串 */
		$sign = $this->Signature($tempStr);			/** 获取签名 */
		$encode_str = $this->Assembling($sParam, false, true);
		$str = $encode_str.'&sign='.rawurlencode($sign);
		return $str;
	}

	/**
	 * 待签名字符串拼接函数
	 * @param boolean $isEncode 是否进行url编码
	 */
	private function Assembling($params, $filterSignType = false, $isEncode = false) {
		/** 当filterSignType参数为真时，剔除sign_type参数 */
		if ($filterSignType) {
			unset($params['sign_type']);
		}
		ksort($params);			/** 将参数数组按照键的自然顺序排序 */
		$stringToBeSigned = "";		// 将要被签名的字符串
		foreach ($params as $k => $v) {
			if (false === empty($v) && 'sign' != $k) {
				// 转换成目标字符集
				$v = mb_convert_encoding($v, 'utf-8');
				$filterArr[] = ($isEncode) ? $k.'='.rawurlencode($v) : $k.'='.$v;
			}
		}
		$stringToBeSigned = implode("&", $filterArr);		//使用 & 连接参数
		unset ($k, $v);
		return $stringToBeSigned;
	}

	/**
	 * 获取参数签名方法
	 * @param  String $str 待签名的参数字符串
	 * @return mixed 
	 *         String：签名过的参数字符串
	 *         boolean：私钥文件不存在；
	 */
	private function Signature($tempStr) {
		//读取私钥文件
		$priKey = file_get_contents($this->rsaPriKeyFile);
		$res = openssl_get_privatekey($priKey);
		($res) or die('您使用的私钥格式错误，请检查RSA私钥配置'); 
		openssl_sign($tempStr, $sign, $res);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	/**
	 * 处理支付宝异步通知参数
	 * @param  Array $rParam 异步通知的参数
	 * @return Array 返回包含待验签参数字符串和待验证签名；
	 */
	public function disposeResponseData($rParam) {
		/** 获取待签名字符串 */
		$stringToBeSigned = $this->Assembling($rParam, true, true);
		$stringToBeSigned = rawurlencode($stringToBeSigned);
		/** 获取sign节点内容 */
		$signature = base64_decode($rParam['sign']);
		$ret = array(
			'stringToBeSigned'	=>	$stringToBeSigned,
			'signature'			=>	$signature
			);
		return $ret;
	}

	/**
	 * 验证签名方法
	 * @param  string $stringToBeSigned 待验签的参数
	 * @param  string $signature        待验证的签名
	 * @return boolean                   验证结果
	 */
	public function verify($stringToBeSigned, $signature) {
		/** 使用RSA的验签方法，通过签名字符串、签名参数（经过base64解码）及支付宝公钥验证签名。 */
		if (file_exists($this->rsaPubKeyFile)) {
			//读取公钥文件
			$pubKey = file_get_contents($this->rsaPubKeyFile);
			//转换为openssl格式密钥
			$res = openssl_get_publickey($pubKey);
			($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确'); 
			//调用openssl内置方法验签，返回bool值
			$result = (bool)openssl_verify($stringToBeSigned, base64_decode($signature), $res, OPENSSL_ALGO_SHA1);
			openssl_free_key($res);	//释放资源
			return $result;
		}
	}

    /**
     * 退单查询接口
     * @param $out_trade_no     退款单号
     * @return resource     查询结果
     */
    public function refundQuery($out_trade_no, $out_request_no)
    {
        $biz_Content = array(
            'out_trade_no'      =>  $out_trade_no,
            'out_request_no'    =>  $out_request_no
        );
        $bizContent = json_encode($biz_Content);
        $signData = array(
            'app_id'        =>  APP_ID,
            'method'        =>  'alipay.trade.fastpay.refund.query',
            'format'        =>  'JSON',
            'charset'       =>  'utf-8',
            'sign_type'     =>  'RSA',
            'sign'          =>  '',
            'timestamp'     =>  date('Y-m-d H:i:s',time()),
            'version'       =>  '1.0',
            'biz_content'   =>  $bizContent
        );

        /** 设置私钥 */
        $this->setRsaPriKeyFile(PRIVATE_KEY);

        $sign = $this->getSignature($signData);
        $curl = new Curl();
        $curl->setUrl('https://openapi.alipay.com/gateway.do');
        $response = $curl->execute(true, 'GET', $sign);
        return $response;
	}
}