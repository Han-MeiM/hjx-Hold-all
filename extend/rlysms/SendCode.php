<?php
namespace mobilecode;
class SendCode{
    public static function send($to,$datas,$tempId){
        $accountSid=config('code.accountSid');
        $accountToken=config('code.accountToken');
        $appId=config('code.appId');
        $serverIP=config('code.serverIP');
        $serverPort=config('code.serverPort');
        $softVersion=config('code.softVersion');
        $rest = new REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
            echo "result error!";
            exit;
        }
        if($result->statusCode!=0) {
//            return "error code :" . $result->statusCode . "<br>";
//            return "error msg :" . $result->statusMsg . "<br>";
            $data = array(
                'errorcode' => $result->statusCode,
                'errormsg'=>$result->statusMsg
            );
            return $data;
            //TODO 添加错误处理逻辑
        }else{
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
//            return "dateCreated:".$smsmessage->dateCreated."<br/>";
//            return "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
            $data = array(
                'dateCreated' => $smsmessage->dateCreated,
                'smsMessageSid'=>$smsmessage->smsMessageSid
            );
            return $data;
            //TODO 添加成功处理逻辑
        }
    }
}