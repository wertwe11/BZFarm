<?php

namespace BZpoultryfarm\Mail;

use Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $lname;
    public $fname;
    public $pass;

    //passes data object
    public function __construct($data)
    {
        $this->lname = $data['lname'];
        $this->fname = $data['fname'];
        $this->pass = $data['pass'];
    }
    
    public function build()
    {
        $address = 'admin@bzpoultry.com';
        $subject = 'Hello from BZ Poultry Farms!';
        $name = 'BZ Farms Admin';

        return $this->view('admin.sendpw')
                    ->from($address, $name)
                    ->replyTo('jorap600@gmail.com', $name)
                    ->subject($subject);
    }
}
