<?php
namespace app\email\controller;
use think\Controller;
class Email extends Controller
{
    public function send()
    {
        $result = \phpmailer\Email::send('test@test.com','测试标题123','测试内容1223','韩佳鑫');
        if ($result == 1){
            return '发送哦邮件成功';
        }
    }
}