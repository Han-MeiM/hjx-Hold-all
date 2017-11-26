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
}