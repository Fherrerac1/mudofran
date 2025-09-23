<?php

namespace App\Mail;

use App\Helpers\ConfigHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimeOffEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $timeOff;
    public $user;


    /**
     * Create a new timeOff instance.
     *
     * @param $timeOff
     * @param $user
     */
    public function __construct($timeOff, $user)
    {
        $this->timeOff = $timeOff;
        $this->user = $user;
    }

    /**
     * Build the timeOff.
     *
     * @return $this
     */
    public function build()
    {
        $business_name = ConfigHelper::get('business_name');

        return $this->markdown('emails.TimeOffEmail')
            ->subject($business_name); // Using the app name in the subject
    }
}
