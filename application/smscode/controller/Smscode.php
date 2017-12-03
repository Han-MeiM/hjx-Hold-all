<?php
namespace app\smscode\controller;
use think\Controller;
use think\Db;
class Weixinpay extends Controller
{
    /**
     * 阿里大于发送短信
     */
    public function ali_smscode(){
        $phone = 18000000000;
        $code = mt_rand(1000,9999);
        // 实例化类
        $wxpay = new \alicode\Dysms();
        $result = $wxpay->send($phone,$code);
        if ($result['Code'] == 'OK')
        {
            echo '发送成功';
        }
    }

    /**
     * 容联云发送短信
     */
    public function rly_smscode(){
        $phone = 18000000000;
        $code = mt_rand(1000,9999);
        // 实例化类
        $sendcode = new \mobilecode\SendCode;
        $tempid = 1;
        // $phone = 接收的手机号，$code = 发送的验证码，$tempid = 模版id
        $result = $sendcode->send($phone,$code,$tempid);
        if ($result['Code'] == 'OK')
        {
            echo '发送成功';
        }
    }
}