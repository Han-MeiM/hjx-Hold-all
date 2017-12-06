<?php
/**
 * 发送邮件类库
 */
namespace phpmailer;
class Email{
    /**
     * @param $to 收件人邮箱
     * @param $title 邮件标题
     * @param $content 邮件内容
     * @param $name 发件人姓名;
     * @return bool
     */
    public static function send($to,$title,$content,$name='韩佳鑫'){
        date_default_timezone_set('PRC');   //set time
        if(empty($to)){
            return false;
        }
        try{
            //Create a new PHPMailer instance
            $mail = new PHPMailer;

            //Tell PHPMailer to use SMTP
            $mail->isSMTP();

            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            // 调试模式
            // 如果开发过程中遇到发送失败的情况，请取消该行注释查看错误信息。
            //$mail->SMTPDebug = 2;

            //Ask for HTML-friendly debug output
            $mail->Debugoutput = 'html';

            //Set the hostname of the mail server
            $mail->Host = config('email.host');

            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = config('email.port');

            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;

            //Username to use for SMTP authentication
            $mail->Username = config('email.username');

            //Password to use for SMTP authentication
            $mail->Password = config('email.password');

            //Set who the message is to be sent from
            $mail->setFrom(config('email.username'), $name);

            //Set an alternative reply-to address
            //$mail->addReplyTo('replyto@example.com', 'First Last');
            //Set who the message is to be sent to
            $mail->addAddress($to);

            //Set the subject line
            $mail->Subject = $title;

            //Read an HTML message body from an external file, convert referenced images to embedded,
             //convert HTML into a basic plain-text alternative body
            $mail->msgHTML($content);

            //Replace the plain text body with one created manually
            //$mail->AltBody = 'This is a plain-text message body';
            //Attach an image file
            //$mail->addAttachment('images/phpmailer_mini.png');

            //send the message, check for errors
            if (!$mail->send()) {
                return false;
                // echo "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return true;
                // echo "Message sent success!";
            }
        }catch (phpmailerException $e){
            return false;
        }
    }
}