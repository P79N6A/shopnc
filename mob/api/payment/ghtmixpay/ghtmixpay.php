<?php 
/**
 * 混合支付接口
 *
 */
defined('Inshopec') or exit('Access Invalid!');

require_once(__DIR__."/lib/rsa.php");
require_once(__DIR__."/lib/aes.php");

class ghtmixpay {
    /* 版本号 */
    private $version = "2.0.0";

    /* 产品id */
    private $appid= "yifen";

    /* 用户所属机构号 */
    private $merchant_no = "549440179410002";  

    /* 终端号 */
    private $terminal_no = "21570381";         

    /* 交易服务号 */
    private $tranCode = "111111";

    /* 报文类型*/
    private $msgType= "01";

    /* 货币代码，人民币：CNY    */     
    private $currency_type = 'CNY';

    /* 清算货币代码，人民币：CNY    */     
    private $sett_currency_type = 'CNY';

    /* 交易完成后页面即时通知跳转的URL */  
    private $return_url = "";//MOBILE_SITE_URL.'/return_url.php';
  
    /* 接收后台通知的URL */ 
    private $notify_url = "";//MOBILE_SITE_URL.'/notify_url.php';

    /* 直连银行参数  */
    private $bank_code = '';

    /* 积分余额查询接口 */
    private $balanceUrl  = "https://epay.gaohuitong.com/person_interface/appUserCustomer/queryIntegral";

    /* C端预下单接口 */
    private $preorderUrl = "https://epay.gaohuitong.com/person_interface/appUserCustomer/preYwOrder";

    /* 混合支付接口 */
    private $mixpayUrl = "https://epay.gaohuitong.com/person_interface/appUserCustomer/admixPay";

    /* H5收银台接口 */
    private $gateUrl   = "https://epay.gaohuitong.com/preEntry.do";

    /* sha256 key */
    public $sha256_key = "b313cf46091c05db39439cc0e133ba6d";   

    /* aes cipher */
    private $aes_cipher = null;

    /* rsa cipher */
    private $rsa_cipher = null;

    public function __construct(){
        $this->aes_cipher = new AESCrypt();
        $this->rsa_cipher = new RSACrypt();
        $this->return_url = MOBILE_SITE_URL.'/return_url.php';
        $this->notify_url = MOBILE_SITE_URL.'/notify_url.php';
    }

    /**
     * 获取return信息
     */
    public function getReturnInfo($payment_config) {
        $verify = $this->verifyResp('return', $payment_config);

        if($verify) {
            if ($_GET['pay_result'] == 1){
                return array(
                    //商户订单号
                    'out_trade_no' => $_GET['order_no'],
                    //高汇通交易号
                    'trade_no' => $_GET['pay_no'],
                );
             }
        }

        return false;
    }

    /**
     * 获取notify信息
     */
    public function getNotifyInfo($payment_config) {
        $verify = $this->verifyResp('notify', $payment_config);

        if($verify) {
            if ($_POST['pay_result'] == 1){
                return array(
                    //商户订单号
                    'out_trade_no' => $_POST['order_no'],
                    //高汇通交易号
                    'trade_no' => $_POST['pay_no'],
                );
            }
        }

        return false;
    }

    
    /*
     * 高汇通积分余额查询
    */
    public function balance($param){
        date_default_timezone_set('UTC');
        $req_date = date("YmdHis");
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<merchant>';
        $xml .= '<head>';
        $xml .= '<version>'.$this->version.'</version>';
        $xml .= '<agencyId>'.$this->merchant_no.'</agencyId>';
        $xml .= '<msgType>'.$this->msgType.'</msgType>';
        $xml .= '<tranCode>'.$this->tranCode.'</tranCode>';
        $xml .= '<reqMsgId>'.$req_date.'</reqMsgId>';
        $xml .= '<reqDate>'.$req_date.'</reqDate>';
        $xml .= '</head>';
        $xml .= '<body>';
        $xml .= '<userNo>'.$param['user_no'].'</userNo>';
        $xml .= '<targetCurrencyCode>'.$this->currency_type.'</targetCurrencyCode>';
        $xml .= '</body>';
        $xml .= '</merchant>';
        $post = $this->encrypt_request($xml);
        $resp = curl_post($this->balanceUrl, $post);
        $result = $this->decrypt_response($resp);
        if($result['head'][respType] == 'S'){
            $IgAccountInfoList = $result['body']['IgAccountInfoList'];
            $integral_list = array();
            $i= 0;
            if (array_keys($IgAccountInfoList) !== range(0, count($IgAccountInfoList) - 1)){
                // 只有一种积分时为关联数组 
                if($IgAccountInfoList['balance'] > 0 && floor($IgAccountInfoList['balance']*$IgAccountInfoList['sysBuyingRate']) > 0){
                    $integral_list[$i++] = $IgAccountInfoList; 
                }
                return $integral_list;
            }else{
                // 多种积分时为数字数组
                foreach($IgAccountInfoList as $k=>$integral){
                    if($integral['balance'] > 0 && floor($integral['balance']*$integral['sysBuyingRate']) > 0){
                        $integral_list[$i++] = $integral; 
                    }
                }
                return $integral_list;
            }
        }
        else{
            return array();
        }
    }

    /*
     * 高汇通C端预下单
    */
    public function preorder($param){
        date_default_timezone_set('UTC');
        $req_date = date("YmdHis");
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<merchant>';
        $xml .= '<head>';
        $xml .= '<version>'.$this->version.'</version>';
        $xml .= '<agencyId>'.$this->merchant_no.'</agencyId>';
        $xml .= '<msgType>'.$this->msgType.'</msgType>';
        $xml .= '<tranCode>'.$this->tranCode.'</tranCode>';
        $xml .= '<reqMsgId>'.$req_date.'</reqMsgId>';
        $xml .= '<reqDate>'.$req_date.'</reqDate>';
        $xml .= '</head>';

        $total_amount = floatval($param['order_amount']) + floatval($param['jf_amount']);
        $qr_content = array();
        $qr_content['agencyId'] = $this->merchant_no;
        $qr_content['childMerchantNo'] = $param['store_merchantno'];
        $qr_content['childMerchantName'] = $param['store_name'];
        $qr_content['terminalNo'] = $this->terminal_no;
        $xml .= '<body>';
        $xml .= '<qrCodeContent>'.json_encode($qr_content).'</qrCodeContent>';
        $xml .= '<appOrderNo>'.$param['order_sn'].'</appOrderNo>';
        $xml .= '<amount>'.$total_amount.'</amount>';   //积分+现金金额
        $xml .= '<userNo>'.$param['user_no'].'</userNo>';
        $xml .= '</body>';
        $xml .= '</merchant>';
        $post = $this->encrypt_request($xml);
        $resp = curl_post($this->preorderUrl, $post);
        $result = $this->decrypt_response($resp);
        if($result['head'][respType] == 'S'){
            return $result['body']['prePayTonkenId'];
        }
        else {
            return "";
        }
    }

    /*
     * 高汇通混合支付
    */
    public function mixpay($param){
        date_default_timezone_set('UTC');
        $req_date = date("YmdHis");
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<merchant>';
        $xml .= '<head>';
        $xml .= '<version>'.$this->version.'</version>';
        $xml .= '<agencyId>'.$this->merchant_no.'</agencyId>';
        $xml .= '<msgType>'.$this->msgType.'</msgType>';
        $xml .= '<tranCode>'.$this->tranCode.'</tranCode>';
        $xml .= '<reqMsgId>'.$req_date.'</reqMsgId>';
        $xml .= '<reqDate>'.$req_date.'</reqDate>';
        $xml .= '</head>';
        $xml .= '<body>';
        $xml .= '<userNo>'.$param['user_no'].'</userNo>';
        $xml .= '<prePayTonkenId>'.$param['prePayTonkenId'].'</prePayTonkenId>';
        $xml .= '<amount>'.$param['order_amount'].'</amount>';
        $xml .= '<integralJson>'.json_encode($param['jf_orders']).'</integralJson>';
        $xml .= '<currencyType>'.$this->currency_type.'</currencyType>';
        $xml .= '<settCurrencyType>'.$this->sett_currency_type.'</settCurrencyType>';
        $xml .= '<productName>'.$param['product_name'].'</productName>';
        $xml .= '<returnUrl>'.$this->return_url.'</returnUrl>';
        $xml .= '<notifyUrl>'.$this->return_url.'</notifyUrl>';
        $xml .= '</body>';
        $xml .= '</merchant>';
        $post = $this->encrypt_request($xml);
        $resp = curl_post($this->mixpayUrl, $post);
        $result = $this->decrypt_response($resp);
        //支付失败
        if($result['head'][respType] != 'S'){
            return "";
        }
        //全额抵扣,无需调用H5收银台 
        if(empty($result['body'])){
            return "redirect";
        }

        //H5收银台token
        return $result['body']['wgTokenId'];
    }

    /*
     * 高汇通收银台
    */
    public function submit($param){
        //step1: C端预下单得到prePayTonkenId
        $prePayTonkenId = $this->preorder($param);

        //step2: 混合支付(积分+现金)得到wgTokenId
        $param['prePayTonkenId'] = $prePayTonkenId;
        $wgTokenId= $this->mixpay($param);
        if($wgTokenId == 'redirect'){
            redirect($this->return_url.'?redirect=1&order_no='.$param['order_sn'].'&pay_no='.$prePayTonkenId);        
            return;
        }

        //step3: H5收银台
        $params = array();
        $params["version"] = $this->version;
        $params["busi_code"] = 'PRE_PAY';
        $params["merchant_no"] = $this->merchant_no;
        $params["child_merchant_no"] = $param['store_merchantno'];
        $params["terminal_no"] = $this->terminal_no;
        $params["token_id"] = $wgTokenId;
        $sign = $this->buildRequestSign($params);
        $params['sign'] = $sign; 
        $html_text = $this->sendRequest($this->gateUrl, $params, 'get', '正在跳转支付页面...');
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-
transitional.dtd">
                <html>
                      <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                            <title>高汇通即时到账交易接口</title>
                      </head>
                      <body>'
                            .$html_text.'
                      </body>
                </html>';
    }

    /*
     * 加密请求 
    */
    private function encrypt_request($xml){
        $encryptData = $this->aes_cipher->encrypt($xml);
        $signData = $this->rsa_cipher->sign($xml);
        $encryptKey = $this->rsa_cipher->encrypt($this->aes_cipher->get_aes_key());

        // 表单数据
        $post = array();
        $post['encryptData'] = base64_encode($encryptData);
        $post['signData'] = $signData;
        $post['encryptKey'] = $encryptKey;
        $post['agencyId'] = $this->merchant_no;
        $post['tranCode'] = $this->tranCode;
        $post['appid'] = $this->appid;
        return $post;
    }

    /*
     * 解密响应 
    */
    private function decrypt_response($resp){
        // 切分字段
        if(!is_array($resp)){
            $rs = explode('&',$resp);
            $rs = str_replace( array('encryptData=', 'encryptKey=', 'signData=', 'tranCode='), array('encryptData:','encryptKey:','signData:', 'tranCode:'), $rs);
            $encode_content = array();
            foreach($rs as $k => $v){
                $rst = explode(':',$v);
                $encode_content[$rst[0]] = $rst[1];
            }
        }else{
            $encode_content = $resp;
        }
        
        //解密验证
        $aes_key = $this->rsa_cipher->decrypt($encode_content['encryptKey']);
        $this->aes_cipher->set_key($aes_key);
        $decode_content = $this->aes_cipher->decrypt(base64_decode($encode_content["encryptData"]));
        $xml_end = array('</ipay>', '</merchant>');
        $xml = '';
        foreach ($xml_end as $value){
            if(strstr($decode_content,$value)){
                $xml_arr = explode( $value, $decode_content);
                $xml = $xml_arr[0].$value;
                break;
            }
        }

        // 转换字典
        $xml_obj = @simplexml_load_string( $xml );
        $xmljson = json_encode($xml_obj);
        $xmlarray=json_decode($xmljson,true);
        return $xmlarray;
    }

    /**
     * 支付参数签名
    */
    private function buildRequestSign($params) {
        $signOrigStr = "";
        ksort($params);
        foreach($params as $k => $v) {
            if("" != $v && "sign" != $k) {
                $signOrigStr .= $k . "=" . $v . "&";
            }
        }
        $signOrigStr .= "key=" . $this->sha256_key;
        $sign = strtolower(hash("sha256",$signOrigStr));
        return $sign;
    }

    /**
    * 发送支付请求,form表单形式
    * @param $request_params 表单参数 
    * @param $method 提交方式。两个值可选：post、get
    * @param $button_name 确认按钮显示文字
    * @return 提交HTML文本
    */
    private function sendRequest($gate_url, $request_params, $method, $button_name) {
        $sHtml = "<form id='ghtpaysubmit' name='ghtpaysubmit' action='".$gate_url."' method='".$method."'>";
        foreach($request_params as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
        $sHtml = $sHtml."<script>document.forms['ghtpaysubmit'].submit();</script>";
        return $sHtml;
    }

     /**
     * 验证返回信息
     */
    private function verifyResp($type, $payment_config) {
        switch ($type) {
            case 'notify':
                if(empty($_POST)) {
                    return false;
                }
                return $this->verifySign($_POST);
            case 'return':
                if(empty($_GET)) {
                    return false;
                }
                return $this->verifySign($_GET);
            default:
                return false;
        }
    }

    /**
    *回调参数验证签名
    *true: 验证通过
    *false: 验证失败
    */
    private function verifySign($params) {
        $signPars = "";
        ksort($params);
        foreach($params as $k => $v) {
            if("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $this->sha256_key;
        $sign = strtolower(hash("sha256",$signPars));
        $tenpaySign = strtolower($params["sign"]);
        return $sign == $tenpaySign;
    }
}
?>
