<?php

namespace BZpoultryfarm\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderReceipt extends Mailable
{
    use Queueable, SerializesModels;
    
    public $data, $details;

    public function __construct($data, $details)
    {
        $this->data = $data;
        $this->details = $details;
    }

    public function build()
    {
        $address = 'admin@bzpoultry.com';
        $subject = 'BZ Farms - Receipt for Order #' . $this->data->trans_id;
        $name = 'BZ Farms Admin';

        return $this->view('admin.receipt')
                    ->from($address, $name)
                    ->replyTo('jorap600@gmail.com', $name)
                    ->subject($subject);
    }
}
