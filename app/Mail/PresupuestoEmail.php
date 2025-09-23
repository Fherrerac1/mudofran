<?php

namespace App\Mail;

use App\Helpers\ConfigHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PresupuestoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $usuario;
    public $pdf;



    /**
     * Create a new message instance.
     *
     * @param $message
     * @param $pdf
     */
    public function __construct($message, $pdf)
    {
        $this->message = $message;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $business_name = ConfigHelper::get('business_name');

        return $this->markdown('emails.PresupuestoEmail')
            ->subject($business_name)->attachData($this->pdf, 'Presupuesto.pdf', [
                    'mime' => 'application/pdf',
                ]);
        ; // Using the app name in the subject
    }
}
