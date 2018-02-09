<?php
namespace app\email\controller;

use phpmailer\Email as EmailClass;
use think\Controller;
use think\Request;

class Email extends Controller
{
    public function send()
    {
        if (request()->isPost()) {
            $data = input('post.');
            // 或者使用定界符拼接发送的内容
            $str= <<<EOT
姓名:{$data['name']} <br>
地址:{$data['address']} <br>
手机号:{$data['phone']}
EOT;
            // 发送邮箱;邮件标题;邮件内容;发件人
            $result = EmailClass::send($data['email'], '测试标题123', $str, '韩佳鑫');
            if ($result == 1) {
                return '发送邮件成功!';
            }
        } else {
            return $this->fetch();
        }
    }
}
