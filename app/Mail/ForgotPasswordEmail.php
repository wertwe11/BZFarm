<?php

namespace BZpoultryfarm\Mail;

use Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $address = 'admin@bzpoultry.com';
        $subject = 'Request for Password Reset';
        $name = 'BZ Farms Admin';

        return $this->view('admin.sendtoken')
                    ->from($address, $name)
                    ->replyTo('jorap600@gmail.com', $name)
                    ->subject($subject);
    }
}
