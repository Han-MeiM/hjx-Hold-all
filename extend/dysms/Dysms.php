<?php
namespace dysms;

error_reporting(E_ALL);
// 定义时区
ini_set('date.timezone','Asia/Shanghai');

class Dysms {
    /**
     * 发送验证码
     * @param $phoneNumArg 短信接收号码
     * @param $codeArg 发送的验证码
     */
    public function send($phoneNumArg,$codeArg)
    {
        date_default_timezone_set("GMT");
        $request_paras         =  array(
            'RegionId'         => 'cn-hangzhou', // API支持的RegionID，如短信API的值为：cn-hangzhou
            'AccessKeyId'      => config('alicode.AccessKeyId'), // 访问密钥，在阿里云的密钥管理页面创建
            "Format"           => 'JSON', // 返回值类型，没传默认为JSON，可选填值：XML
            "SignatureMethod"  => 'HMAC-SHA1', // 编码(固定值不用改)
            "SignatureVersion" => '1.0', // 版本(固定值不用改)
            'SignatureNonce'   => uniqid(mt_rand(0, 0xffff), true), // 用于请求的防重放攻击的唯一加密盐
            'Timestamp'        => date('Y-m-d\TH:i:s\Z'), // 格式为：yyyy-MM-dd’T’HH:mm:ss’Z’；时区为：GMT
            'Action'           => 'SendSms', // API的命名，固定值，如发送短信API的值为：SendSms
            'Version'          => "2017-05-25", // API的版本，固定值，如短信API的值为：2017-05-25
            'PhoneNumbers'     => $phoneNumArg, // 短信接收号码
            'SignName'         => '测试', // 短信签名
            'TemplateCode'     => config('alicode.TemplateCode'), // 短信模板ID
            'TemplateParam'    => json_encode([ // 短信模板变量替换JSON串
                "code"         => $codeArg
            ]),
        );
        // 按键值排序
        ksort($request_paras);

        $request_host = "http://dysmsapi.aliyuncs.com";
        $app_secret = config('alicode.AccessSecret');

        $requestUrl = "";
        foreach ($request_paras as $apiParamKey => $apiParamValue) {
            $requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        // 清除最后一个&
        $requestUrl = substr($requestUrl, 0, -1);
        $requestUrl = $this->sign('GET&/&' . $requestUrl);

        $sign = $this->sign(base64_encode(hash_hmac('sha1', $requestUrl , $app_secret . '&', true)));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_URL, $request_host . '?' . $requestUrl . '&Signature=' . $sign);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);
        return $ret;
    }

    public function sign($url)
    {
        $url = urlencode($url);
        str_replace("+","%20",$url);
        str_replace("*","%2A",$url);
        str_replace("%7E","~",$url);
        return $url;
    }
}
