<?php

namespace App\Mail;

use App\Helpers\ConfigHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResponseEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $usuario;
    public $link;



    /**
     * Create a new message instance.
     *
     * @param $message
     * @param $link
     */
    public function __construct($message, $link)
    {
        $this->message = $message;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $business_name = ConfigHelper::get('business_name');

        return $this->markdown('emails.ResponseEmail')
            ->subject($business_name); // Using the app name in the subject
    }
}
