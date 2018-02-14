<?php
namespace app\pay\controller;

use weixinpay\Weixinpay as WeixinpayClass;
use think\Controller;

class Weixinpay extends Controller
{

    /**
    * notify_url接收页面
    */
    public function notify()
    {
        // ↓↓↓下面的file_put_contents是用来简单查看异步发过来的数据 测试完可以删除；↓↓↓
        // 获取xml
        $xml=file_get_contents('php://input', 'r');
        //转成php数组 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data= json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        file_put_contents('./notify.text', $data);
        // ↑↑↑上面的file_put_contents是用来简单查看异步发过来的数据 测试完可以删除；↑↑↑
        // 导入微信支付sdk
        $wxpay=new Weixinpay();
        $result=$wxpay->notify();
        if ($result) {
            // 验证成功 修改数据库的订单状态等 $result['out_trade_no']为订单id

        }
    }

    /**
     * 公众号支付 必须以get形式传递 out_trade_no 参数
     */
    public function pay(WeixinpayClass $wxpay)
    {
        // 获取jssdk需要用到的数据
        $data = $wxpay->getParameters();
        // 将数据分配到前台页面
        return $this->fetch('', [
           'data'=>json_encode($data)
        ]);
    }

    /**
     * 微信 公众号jssdk支付 生成订单号后去调取支付
     */
    public function wexinpay_js()
    {
        // 此处根据实际业务情况生成订单 然后拿着订单去支付
        // 用时间戳虚拟一个订单号  （请根据实际业务更改）
        $out_trade_no = time();
        // 组合url
        $url = url('pay/weixinpay/pay',['out_trade_no'=>$out_trade_no]);
        // 前往支付
        $this->redirect($url);
    }

    /**
     * 微信二维码支付
     * body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)
     */
    public function qr_pay()
    {
        // 虚拟的订单号 请根据实际业务更改
        $time = time();
        $order = [
            'body'=>'test',
            'total_fee'=>1,
            'out_trade_no'=>strval($time),
            'product_id'=>1
        ];
        weixinpay($order);
    }

}
