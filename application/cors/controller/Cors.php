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
        echo '恭喜跨域成功!';
    }
}