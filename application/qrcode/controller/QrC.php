<?php
namespace app\qrcode\controller;
use think\Controller;
class QrC extends Controller
{
	public function index()
	{
		return $this->fetch();
	}

	public function creQr()
	{
        echo qrcode('https://www.hanjiaxin.com','韩佳鑫的个人blog');
        exit;
	}
}