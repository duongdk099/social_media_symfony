<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;
    private string $adminEmail;

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }    

    public function sendAdminNotification(string $userEmail, string $username): void
    {
        $email = (new Email())
            ->from('noreply@subworld.com')
            ->to($this->adminEmail)
            ->subject('Nouvel utilisateur inscrit 🚀')
            ->text("Un nouvel utilisateur s'est inscrit : \n\n- Nom d'utilisateur : $username\n- Email : $userEmail");

        $this->mailer->send($email);
    }

    public function sendVerificationEmail(string $to, string $verificationUrl): void
    {
        $email = (new Email())
            ->from('noreply@subworld.com')
            ->to($to)
            ->subject('Verify your email address')
            ->html("
                <p>Hello,</p>
                <p>Please verify your email address by clicking the link below:</p>
                <p><a href='$verificationUrl'>Verify Email</a></p>
                <p>This link will expire in 24 hours.🚀</p>
            ");

        $this->mailer->send($email);
    }

}
