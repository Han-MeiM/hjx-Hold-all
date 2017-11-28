<?php
namespace app\qrcode\controller;
use think\Controller;
use Endroid\QrCode\QrCode;
class QrC extends Controller
{
	public function index()
	{
		return $this->fetch();
	}

	public function creQr()
	{
		$qrCode=new QrCode();
		// 二维码中的内容，加http://这样扫码可以直接跳转url
        $url = 'https://www.hanjiaxin.com';
        $qrCode->setText($url)
            ->setSize(300) // 大小
            ->setLabelFontPath(VENDOR_PATH.'endroid\qrcode\assets\noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('韩佳鑫的个人blog') // 二维码下方注释
            ->setLabelFontSize(16);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
	}
}