<?php
namespace app\geetest\controller;

use think\Controller;
use geetest\geetestlib;

class Geetest extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 通过配置信息调取极验官方接口,获取极验验证码信息
     */
    public function starCaptcha()
    {
        $GtSdk = new geetestlib(config('geetest.CAPTCHA_ID'), config('geetest.PRIVATE_KEY'));

        $data = array(
            "user_id" => session('user_id'), # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => request()->ip() # 请在此处传输用户请求验证时所携带的IP
        );

        $status = $GtSdk->pre_process($data, 1);
        // 记录极验官网服务器状态
        session('gtserver', $status);
        session('user_id', $data['user_id']);
        return json($GtSdk->get_response());
    }

    /**
     * 二次验证
     * 获取前端提交的信息,查看验证码是否验证成功
     * @param $geetest_challenge
     * @param $geetest_validate
     * @param $geetest_seccode
     * @return string
     */
    public function verifyLogin($geetest_challenge, $geetest_validate, $geetest_seccode)
    {
        $GtSdk = new geetestlib(config('geetest.CAPTCHA_ID'), config('geetest.PRIVATE_KEY'));
        $data = array(
            "user_id" => session('user_id'), # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => request()->ip() # 请在此处传输用户请求验证时所携带的IP
        );

        // 获取session中存储的极验官网服务器状态
        if (session('gtserver') == 1) {   //服务器正常
            // 获取二次验证结果
            $result = $GtSdk->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $data);
            if ($result) {
                return 'success';
            } else {
                return 'fail';
            }
        }else{  //服务器宕机,走failback模式
            // 获取二次验证结果
            if ($GtSdk->fail_validate($geetest_challenge, $geetest_validate, $geetest_seccode)) {
                return 'success';
            } else {
                return 'fail';
            }
        }
    }
}
