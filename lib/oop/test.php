<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/7/1
 * Time: 下午5:38
 */

namespace App\Lib\OOP;

/*---------------接口描述一类事可以做什么、抽象类描述一类事是什么---------------*/

/**
 * Interface sendMail
 * @package test8
 */
interface SendMail {
    function sendEmail();
}

/**
 * Class EmailQq
 * @package test8
 */
class EmailQq implements SendMail {
    function sendEmail()
    {
        // TODO: Implement email() method.
        echo "EmailQq";
    }
}

/**
 * Class Email163
 * @package test8
 */
class Email163 implements SendMail {
    function sendEmail()
    {
        // TODO: Implement email() method.
        echo "Email163";
    }
}


/**
 * Class UserManager
 * @package test8
 */
class UserManager {

    private $sendEmail;

    function __construct(SendMail $sendEmail)
    {
        $this->sendEmail = $sendEmail;
    }

    function doSendEmail() {
        $this->sendEmail->sendEmail();
    }
}

$userModel = new UserManager(new Email163());
$userModel->doSendEmail();

/**
 * Class Email
 * @package test8
 */
abstract class Email implements SendMail {

    protected $from_User;
    protected $to_User;
    protected $type;

    public function __construct()
    {
        // TODO: construct method.
    }

    public abstract function sendEmail();
}

/**
 * Class EmailQqAbstract
 * @package test8
 */
class EmailQqAbstract extends Email {

    public function sendEmail()
    {
        // TODO: Implement email() method.
        echo "EmailQqAbstract";
    }
}

/**
 * Class Email163Abstract
 * @package test8
 */
class Email163Abstract extends Email {

    public function sendEmail()
    {
        // TODO: Implement email() method.
        echo "Email163Abstract";
    }
}

/**
 * Class UserManager
 * @package test8
 */
class UserManagerAbstract {

    private $email;

    function __construct(Email $email)
    {
        $this->email = $email;
    }

    function doSendEmail() {
        $this->email->sendEmail();
    }
}

$userModel = new UserManager(new Email163Abstract());
$userModel->doSendEmail();


