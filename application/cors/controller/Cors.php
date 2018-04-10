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
        header("Access-Control-Max-Age: 1728000");
        header("Access-Control-Request-Method: GET,POST,PUT,PATCH,DELETE");

        echo '恭喜跨域成功!';
    }
}