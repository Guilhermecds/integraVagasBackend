<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Cria uma nova instância de mensagem.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Constrói a mensagem.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reset_password')
                    ->subject('Redefinição de Senha')
                    ->with([
                        'token' => $this->token,
                    ]);
    }
}
