<?php
namespace rlysms;

class SendCode
{
    /**
     * @param $to 接收手机号
     * @param $datas 发送内容
     * @param $tempId 模版ID
     * @return array 返回信息
     */
    public static function send($to,$datas,$tempId)
    {
        $rest = new REST(config('code.serverIP'),config('code.serverPort'),config('code.softVersion'));
        $rest->setAccount(config('code.accountSid'),config('code.accountToken'));
        $rest->setAppId(config('code.appId'));
        // 发送模板短信
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
            echo "result error!";
            exit;
        }
        if($result->statusCode != 0) {
            $data = array(
                'errorcode' => $result->statusCode,
                'errormsg' => $result->statusMsg
            );
            return $data;
            //TODO 添加错误处理逻辑
        }else{
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            $data = array(
                'dateCreated' => $smsmessage->dateCreated,
                'smsMessageSid' => $smsmessage->smsMessageSid
            );
            return $data;
            //TODO 添加成功处理逻辑
        }
    }
}
