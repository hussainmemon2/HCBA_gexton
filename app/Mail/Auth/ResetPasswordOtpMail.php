<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;

    public function __construct($otp, $userName)
    {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password – OTP Verification'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.reset-password-otp',
            with: [
                'otp' => $this->otp,
                'name' => $this->userName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
