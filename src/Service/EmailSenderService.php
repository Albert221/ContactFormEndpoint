<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Exception\SendingEmailFailedException;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Twig;

class EmailSenderService
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Twig\Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $recipientEmail;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @param Swift_Mailer $mailer
     * @param Twig\Environment $twig
     * @param string $recipientEmail
     * @param string $senderEmail
     * @param string $senderName
     */
    public function __construct(
        Swift_Mailer $mailer,
        Twig\Environment $twig,
        string $recipientEmail,
        string $senderEmail,
        string $senderName
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->recipientEmail = $recipientEmail;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    /**
     * @param string $subject
     * @param string $sender
     * @param string $message
     *
     * @throws SendingEmailFailedException
     */
    public function send(string $subject, string $sender, string $message): void
    {
        try {
            $body = $this->twig->render('email.html.twig', [
                'sender' => $sender,
                'subject' => $subject,
                'message' => $message
            ]);
        } catch (Twig\Error\Error $e) {
            throw new RuntimeException('There\'s something wrong with the email view', 0, $e);
        }

        $message = (new Swift_Message())
            ->setFrom($this->senderEmail, 'Form on wolszon.me')
            ->setTo($this->recipientEmail)
            ->setReplyTo($sender)
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        if (1 !== $this->mailer->send($message)) {
            throw new SendingEmailFailedException();
        }
    }
}
