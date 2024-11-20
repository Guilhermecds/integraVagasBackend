<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacaoCandidatura extends Mailable
{
    use Queueable, SerializesModels;

    public $detalhes;

    public function __construct($detalhes)
    {
        $this->detalhes = $detalhes;
    }

    public function build()
    {
        return $this->subject($this->detalhes['titulo'])
                    ->view('emails.notificacao_candidatura')
                    ->with('detalhes', $this->detalhes);
    }
}