<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ResetPasswordService
{
    public function __construct(private readonly MailerInterface $mailerInterface){}

    public function send(
        string $to,
        string $subject,
        string $templateTwig,
        array $context
    ): void {
        $email  = (new TemplatedEmail())
            ->from(new Address('noreply@monsitededev.fr', 'The Recrut'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("resetPassword/$templateTwig")
            ->context($context);

        try {
            $this->mailerInterface->send($email);
        } catch (TransportExceptionInterface $transportException) {
            throw $transportException;
        }
    }
}
