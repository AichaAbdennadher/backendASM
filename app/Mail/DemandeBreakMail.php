<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemandeBreakMail extends Mailable
{
    use Queueable, SerializesModels;

    public $demande;

    public function __construct($demande)
    {
        $this->demande = $demande;
    }

    public function build()
    {
        return $this->subject('New pause request')
                    ->markdown('emails.demande-break'); // <-- Utiliser markdown()
    }
}
