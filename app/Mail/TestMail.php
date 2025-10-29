<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public function build()
    {
        return $this->subject('اختبار MailHog')
                    ->view('emails.test');
    }
}
