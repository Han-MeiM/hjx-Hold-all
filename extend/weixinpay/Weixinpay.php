<?php
namespace weixinpay;
error_reporting(E_ALL);
ini_set('display_errors', '1');
// 定义时区
ini_set('date.timezone','Asia/Shanghai');
use think\Cache;
use think\Db;

class Weixinpay {
    /**
     * 统一下单
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function unifiedOrder($order){
        // 生成随机加密盐
        $nonce_str = $this->makeCode(4);
        // 获取配置项
        $config=array(
            'appid'=>config('wxpay.APPID'),
            'mch_id'=>config('wxpay.MCHID'),
            'nonce_str'=>$nonce_str,
            'spbill_create_ip'=>'192.168.0.1',
            'notify_url'=>config('wxpay.NOTIFY_URL')
            );
        // 合并配置数据和订单数据
        $data=array_merge($order,$config);
        // 生成签名
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            die($result['return_msg']);
        }
        $result['sign']=$sign;
        $result['nonce_str']=$nonce_str;
        return $result;
    }

    /**
     * 退款
     * @param transaction_id(微信订单号)、total_fee(订单总额)、refund_fee(退款金额)、out_refund_no(商户退款单号)
     * 必须包含支付所需要的参数 mch_id(商户号)、nonce_str(随机字符串)、out_refund_no(商户退款单号)、out_trade_no(商户订单号)、refund_fee(退款金额)、total_fee(订单金额	)、transaction_id(微信订单号)、sign(签名)
     */
    public function refund($transaction_id,$total_fee,$refund_fee,$out_refund_no){
        $nonce_str = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ( $i = 0; $i < 4; $i++ ) {
            $nonce_str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        $data = [
            'appid' => config('wxpay.APPID'),
            'mch_id' => config('wxpay.MCHID'),
            'nonce_str' => $nonce_str,
            'out_refund_no' => $out_refund_no,
            'refund_fee' => $refund_fee,
            'total_fee' => $total_fee,
            'transaction_id' => $transaction_id
        ];
        $sign=$this->makeSign($data);
        $data['sign'] = $sign;
        $xml = $this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT,30);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);//证书检查
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/cert/apiclient_key.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).'/cert/rootca.pem');
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            $data['sign']=$sign;
            $data['nonce_str']=$nonce_str;
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 查询退款
     */
    public function refundquery(){
        // 生成随机加密盐
        $nonce_str = $this->makeCode(4);
        // 获取配置项
        $data=array(
            'appid' => config('wxpay.APPID'),   // 公众账号ID
            'mch_id' => config('wxpay.MCHID'),  // 商户号
            'nonce_str' => $nonce_str,          // 随机字符串
            'transaction_id' => '',             // 微信订单号
            'out_trade_no' => '',               // 商户订单号，与微信订单号可二选一只填写一个
            'sign_type' => 'MD5'                // 加密方式
        );
        // 加密
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/refundquery';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            die($result['return_msg']);
        }
        $result['sign']=$sign;
        $result['nonce_str']=$nonce_str;
        return $result;
    }

    /**
     * 查询订单
     */
    public function orderquery(){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce_str = '';
        for ( $i = 0; $i < 4; $i++ ) {
            $nonce_str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        // 获取配置项
        $data=array(
            'appid'=>config('wxpay.APPID'),
            'mch_id'=>config('wxpay.MCHID'),
            'nonce_str'=>$nonce_str,
            'transaction_id'=>'4001722001201709101302301620'
        );
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            die($result['return_msg']);
        }
        $result['sign']=$sign;
        $result['nonce_str']=$nonce_str;
        return $result;
    }

    /**
     * 验证
     * @return array 返回数组格式的notify数据
     */
    public function notify(){
        // 获取xml
        $xml=file_get_contents('php://input', 'r'); 
        // 转成php数组
        $data=$this->toArray($xml);
        // 保存原sign
        $data_sign=$data['sign'];
        // sign不参与签名
        unset($data['sign']);
        $sign=$this->makeSign($data);
        // 判断签名是否正确  判断支付状态
        if ($sign===$data_sign && $data['return_code']=='SUCCESS' && $data['result_code']=='SUCCESS') {
            $result=$data;
        }else{
            $result=false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function toXml($data){
        if(!is_array($data) || count($data) <= 0){
            throw new WxPayException("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($data as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function makeSign($data){
        // 去空
        $data=array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a=http_build_query($data);
        $string_a=urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp=$string_a."&key=".config('wxpay.KEY');
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result=strtoupper($sign);
        return $result;
    }

    /**
     * 将xml转为array
     * @param  string $xml xml字符串
     * @return array       转换得到的数组
     */
    public function toArray($xml){   
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $result;
    }

    /**
     * 生成随机串
     * @param $count 随机串个数
     */
    public function makeCode($count)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce_str = '';
        for ( $i = 0; $i < $count; $i++ ) {
            $nonce_str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
    }

    /**
     * 获取jssdk需要用到的数据
     * @return array jssdk需要用到的数据
     */
    public function getParameters(){
        // 如果没有get参数没有code；则重定向去获取openid；
        if (!isset($_GET['code'])) {
            // 获取订单号
            $out_trade_no=input('request.out_trade_no',1,'intval');
            // 返回的url
            $redirect_uri=url('pay/weixinpay/pay','','',true);
            $redirect_uri=urlencode($redirect_uri);
            $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.config('wxpay.APPID').'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state='.$out_trade_no.'#wechat_redirect';
            Header("Location: $url");
            exit();
        }else{
            // 如果有code参数；则表示获取到openid
            $code=input('get.code');
            // 取出订单号
            $out_trade_no=input('request.state',0,'intval');
            // 组合获取prepay_id的url
            $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.config('wxpay.APPID').'&secret='.config('wxpay.APPSECRET').'&code='.$code.'&grant_type=authorization_code';
            // curl获取prepay_id
            $result=$this->curl_get_contents($url);
            $result=json_decode($result,true);
            $openid=$result['openid'];
            // 订单数据  请根据订单号out_trade_no 从数据库中查出实际的body、total_fee、out_trade_no、product_id
            $order=array(
                'body'=>'微信支付',// 商品描述（需要根据自己的业务修改）
                'total_fee'=>1,// 订单金额  以(分)为单位（需要根据自己的业务修改）
                'out_trade_no'=>$out_trade_no,// 订单号（需要根据自己的业务修改）
                'product_id'=>'1',// 商品id（需要根据自己的业务修改）
                'trade_type'=>'JSAPI',// JSAPI公众号支付
                'openid'=>$openid// 获取到的openid
            );
            // 统一下单 获取prepay_id
            $unified_order=$this->unifiedOrder($order);
            // 获取当前时间戳
            $time=time();
            // 组合jssdk需要用到的数据
            $data=array(
                'appId'=>config('wxpay.APPID'), //appid
                'timeStamp'=>strval($time), //时间戳
                'nonceStr'=>$unified_order['nonce_str'],// 随机字符串
                'package'=>'prepay_id='.$unified_order['prepay_id'],// 预支付交易会话标识
                'signType'=>'MD5'//加密方式
            );
            // 生成签名
            $data['paySign']=$this->makeSign($data);
            return $data;
        }
    }

    /**
     * 生成支付二维码
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function pay($order){
        $result=$this->unifiedOrder($order);
        $decodeurl=urldecode($result['code_url']);
        qrcode($decodeurl);
    }

    /**
     * curl 请求http
     */
    public function curl_get_contents($url){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                //设置访问的url地址
        // curl_setopt($ch,CURLOPT_HEADER,1);               //是否显示头部信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);               //设置超时
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   //用户访问代理 User-Agent
        curl_setopt($ch, CURLOPT_REFERER,$_SERVER['HTTP_HOST']);        //设置 referer
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);          //跟踪301
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
        $r=curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}

