<?php

namespace app\cors\controller;

use think\Controller;

class Cors extends Controller
{
    public function isError()
    {
        echo 'no';
    }

    public function isSuccess()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: X-Custom-Header");
        header("Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE");
        header("Access-Control-Max-Age: 30");

        echo '恭喜跨域成功!';
    }

    public function isOther()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: X-Custom-Header");
        header("Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE");
        header("Access-Control-Max-Age: 30");

        echo "恭喜缓存预检成功";
    }
}