<?php
/**
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/3/25
 * Time: 16:10
 */

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
class Email
{
    //email title
    public $subject ;

    //email view
    public $view;

    public function __construct($subject = '密码', $view ='email.register')
    {
        $this->subject = $subject;
        $this->view = $view;
    }

    public static function register($email,$data,$subject = '密码')
    {
        $mail = new Email($subject);
        Mail::queue('email.register',$data, function($message) use($email,$mail)
        {
            $message->to($email)->subject($mail->subject);
        });
    }
}