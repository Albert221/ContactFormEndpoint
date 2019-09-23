<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\IpRecord;
use App\Repository\IpRecordsRepository;
use App\Request\SendEmailRequest;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @var IpRecordsRepository
     */
    private $ipRecordsRepository;

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
    private $antifloodInterval;

    /**
     * @var int
     */
    private $antifloodQuantity;

    /**
     * @param ValidatorInterface $validator
     * @param Swift_Mailer $mailer
     * @param IpRecordsRepository $ipRecordsRepository
     * @param string $recipientEmail
     * @param string $senderEmail
     * @param string $antifloodInterval
     * @param int $antifloodQuantity
     */
    public function __construct(
        ValidatorInterface $validator,
        Swift_Mailer $mailer,
        IpRecordsRepository $ipRecordsRepository,
        string $recipientEmail,
        string $senderEmail,
        string $antifloodInterval,
        int $antifloodQuantity
    ) {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->ipRecordsRepository = $ipRecordsRepository;
        $this->recipientEmail = $recipientEmail;
        $this->senderEmail = $senderEmail;
        $this->antifloodInterval = $antifloodInterval;
        $this->antifloodQuantity = $antifloodQuantity;
    }

    /**
     * @param SendEmailRequest $sendEmailRequest
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     *
     * @ParamConverter("sendEmailRequest", converter="fos_rest.request_body")
     */
    public function send(SendEmailRequest $sendEmailRequest, Request $request): Response
    {
        $errors = $this->validator->validate($sendEmailRequest);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ($this->isFlooding($request->getClientIp())) {
            return $this->json([
                'error' => 'You are sending too much messages. Try again later.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!$this->sendMessage($sendEmailRequest)) {
            return $this->json([
                'error' => 'An unknown error has occured.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->saveIpRecord($request->getClientIp());

        return $this->json([]);
    }

    /**
     * @param string $clientIp
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function isFlooding(string $clientIp): bool
    {
        $records = $this->ipRecordsRepository->findForIpWithinInterval(
            $clientIp,
            new DateInterval($this->antifloodInterval)
        );

        return count($records) > $this->antifloodQuantity;
    }

    /**
     * @param SendEmailRequest $sendEmailRequest
     *
     * @return bool Whether sending an email was successful.
     */
    private function sendMessage(SendEmailRequest $sendEmailRequest): bool
    {
        $messageBody = $this->renderView('email.html.twig', [
            'sender' => $sendEmailRequest->sender,
            'subject' => $sendEmailRequest->subject,
            'message' => $sendEmailRequest->message
        ]);

        $message = (new Swift_Message())
            ->setFrom($this->senderEmail, 'Form on wolszon.me')
            ->setTo($this->recipientEmail)
            ->setReplyTo($sendEmailRequest->sender)
            ->setSubject($sendEmailRequest->subject)
            ->setBody($messageBody, 'text/html');

        return 1 === $this->mailer->send($message);
    }

    /**
     * @param string $clientIp
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    private function saveIpRecord(string $clientIp): void
    {
        $ipRecord = new IpRecord();
        $ipRecord->setIp($clientIp);
        $ipRecord->setCreatedAt(new DateTime());

        $this->ipRecordsRepository->persist($ipRecord);
    }
}
