<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/8/1
 * Time: 下午5:04
 */
/**
 * @param $address mixed 收件人  多个收件人/或需要设置收件人昵称时为数组 array($address1,$address1)/array(array('address'=>$address1,'nickname'=>$nickname1),array('address'=>$address2,'nickname'=>$nickname2))
 * @param $subject string 邮件主题
 * @param $body    string 邮件内容
 * @param  $file   string 附件
 * @return bool|string   发送成功返回true 反之返回报错信息
 * @throws Exception
 */
function send_mail_by_smtp($address, $subject, $body, $file = '')
{
    require('PHPMailer/PHPMailer-master/Exception.php');
    require('PHPMailer/PHPMailer-master/PHPMailer.php');
    require('PHPMailer/PHPMailer-master/SMTP.php');

    date_default_timezone_set("Asia/Shanghai");//设定时区东八区
    $mail = new PHPMailer();

    //Server settings
    $mail->SMTPDebug = 2;
    $mail->isSMTP();                                      // 使用SMTP方式发送
    $mail->Host = 'smtp.126.com';                         // SMTP邮箱域名
    $mail->SMTPAuth = true;                               // 启用SMTP验证功能
    $mail->Username = "*****@126.com";                    // 邮箱用户名(完整email地址)
    $mail->Password = "*****";                            // smtp授权码，非邮箱登录密码
    $mail->Port = 25;
    $mail->CharSet = "utf-8";                             //设置字符集编码 "GB2312"

    // 设置发件人信息，显示为  你看我那里像好人(xxxx@126.com)
    $mail->setFrom($mail->Username, '你看我那里像好人');

    //设置收件人 参数1为收件人邮箱 参数2为该收件人设置的昵称  添加多个收件人 多次调用即可
    //$mail->addAddress('********@163.com', '你看我那里像好人');

    if (is_array($address)) {
        foreach ($address as $item) {
            if (is_array($item)) {
                $mail->addAddress($item['address'], $item['nickname']);
            } else {
                $mail->addAddress($item);
            }
        }
    } else {
        $mail->addAddress($address, 'adsf');
    }


    //设置回复人 参数1为回复人邮箱 参数2为该回复人设置的昵称
    //$mail->addReplyTo('*****@126.com', 'Information');

    if ($file !== '') $mail->AddAttachment($file); // 添加附件

    $mail->isHTML(true);    //邮件正文是否为html编码 true或false
    $mail->Subject = $subject;     //邮件主题
    $mail->Body = $body;           //邮件正文 若isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取的html文件
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';  //附加信息，可以省略

    return $mail->Send() ? true : 'ErrorInfo:' . $mail->ErrorInfo;
}

$path = '.\wpic907.jpg';

$ret = send_mail_by_smtp('*******@163.com', 'PHPMailer邮件标题', 'PHPMailer邮件内容', $path);
