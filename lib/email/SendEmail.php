<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/8/1
 * Time: 下午5:10
 */
namespace App\Lib\Email;

require('PHPMailer/PHPMailer-master/Exception.php');
require('PHPMailer/PHPMailer-master/PHPMailer.php');
require('PHPMailer/PHPMailer-master/SMTP.php');
date_default_timezone_set("Asia/Shanghai");//设定时区东八区

/**
 * Class SendEmail
 * @package App\Lib\Email
 */
class SendEmail {

    /**
     * @var
     */
    private static $instance = null;
    /**
     * @var
     */
    private $email;

    /**
     * SendEmail constructor.
     */
    private function __construct()
    {
        return true;
    }

    /**
     * @return SendEmail|bool|null
     * @throws \phpmailerException
     */
    public static function getInstance(){
        if (self::$instance == null){
            self::$instance = new SendEmail();

            if( !self::$instance->emailConfig() ){
                return false;
            }
        }
        return self::$instance;
    }

    /**
     * 邮件配置[后面添加到配置中]
     * @return bool
     * @throws \phpmailerException
     */
    private function emailConfig() {
        $this->email = new \PHPMailer();

        if (!$this->email) {
            return false;
        }
        $this->email->SMTPDebug = 2;
        $this->email->isSMTP();
        $this->email->Host = 'smtp.163.com';
        $this->email->SMTPAuth = true;                               // 启用SMTP验证功能
        $this->email->Username = "zhg5482482@163.com";               // 邮箱用户名(完整email地址)
        $this->email->Password = "************";                     // smtp授权码，非邮箱登录密码
        $this->email->Port = 25;
        $this->email->CharSet = "utf-8";                             //设置字符集编码 "GB2312"

        // 设置发件人信息，显示为  你看我那里像好人(xxxx@126.com)
        $this->email->setFrom($this->email->Username , 'zhg5482');
        return true;
    }
    /**
     * 发送邮件
     * @param $address 收件人  多个收件人/或需要设置收件人昵称时为数组 array($address1,$address1)/array(array('address'=>$address1,'nickname'=>$nickname1),array('address'=>$address2,'nickname'=>$nickname2))
     * @param $subject
     * @param $body
     * @param string $file
     * @return bool|string
     */
    public function mailer($address, $subject, $body, $file = '') {
        if (is_array($address)) {
            foreach ($address as $item) {
                if (is_array($item)) {
                    $this->email->addAddress($item['address'], $item['nickname']);
                } else {
                    $this->email->addAddress($item);
                }
            }
        } else {
            $this->email->addAddress($address, 'nobody');
        }


        //设置回复人 参数1为回复人邮箱 参数2为该回复人设置的昵称
        //$mail->addReplyTo('*****@126.com', 'Information');

        if ($file !== '') self::$email->AddAttachment($file); // 添加附件

        $this->email->isHTML(true);    //邮件正文是否为html编码 true或false
        $this->email->Subject = $subject;     //邮件主题
        $this->email->Body = $body;           //邮件正文 若isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取的html文件
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';  //附加信息，可以省略

        return $this->email->Send() ? true : 'ErrorInfo:' . $this->email->ErrorInfo;
    }

}
