<?php
namespace app\common\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class MailService
{
    /**
     * 发送邮箱
     * @param array $data
     * @param string $addr 地址
     * @param string $title 标题
     * @param string $content 内容
     * @return mixed
     */
    static function go($addr,$title,$content)
    {   
        $mail = new PHPMailer(true);
        $data = get_config('email');
        try {
            $mail->SMTPDebug = 0;                    
            $mail->CharSet = 'utf-8';          
            $mail->isSMTP();                                     
            $mail->Host = $data['smtp-host'];  
            $mail->SMTPAuth = true;                          
            $mail->Username =  $data['smtp-user'];             
            $mail->Password =  $data['smtp-pass'];                  
            $mail->SMTPSecure = 'ssl';                            
            $mail->Port =  $data['smtp-port'];                                
            $mail->setFrom($data['smtp-user'],self::title());
            $mail->addAddress($addr);    
            $mail->isHTML(true);                                 
            $mail->Subject = $title;
            $mail->Body    = $content;
            $mail->send();
            return ['code'=>'200','msg'=>'发送成功'];
        } catch (Exception $e) {
            return ['code'=>'201','msg'=>'发送失败'.$e->errorMessage()];
        }
    }



}