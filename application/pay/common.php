<?php

/**
 * 微信扫码支付
 * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)
 */
function weixinpay($order)
{
    $order['trade_type']='NATIVE';
    $weixinpay=new \weixinpay\Weixinpay();
    $weixinpay->pay($order);
}