<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\SendEmailRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $recipientEmail;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @param ValidatorInterface $validator
     * @param Swift_Mailer $mailer
     * @param string $recipientEmail
     * @param string $senderEmail
     */
    public function __construct(
        ValidatorInterface $validator,
        Swift_Mailer $mailer,
        string $recipientEmail,
        string $senderEmail
    ) {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->recipientEmail = $recipientEmail;
        $this->senderEmail = $senderEmail;
    }

    /**
     * @param SendEmailRequest $request
     *
     * @return Response
     *
     * @ParamConverter("request", converter="fos_rest.request_body")
     */
    public function send(SendEmailRequest $request): Response
    {
        $errors = $this->validator->validate($request);

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $messageBody = $this->renderView('email.html.twig', [
            'sender' => $request->sender,
            'subject' => $request->subject,
            'message' => $request->message
        ]);

        $message = (new Swift_Message())
            ->setFrom($this->senderEmail, 'Form on wolszon.me')
            ->setTo($this->recipientEmail)
            ->setReplyTo($request->sender)
            ->setSubject($request->subject)
            ->setBody($messageBody, 'text/html');

        if (0 === $this->mailer->send($message)) {
            return $this->json(['error' => 'An error has occured.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([]);
    }
}
