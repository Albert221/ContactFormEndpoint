<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\SendEmailRequest;
use App\Service\AntifloodService;
use App\Service\EmailSenderService;
use App\Service\Exception\SendingEmailFailedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @var EmailSenderService
     */
    private $emailSenderService;

    /**
     * @var AntifloodService
     */
    private $antifloodService;

    /**
     * @param ValidatorInterface $validator
     * @param EmailSenderService $emailSenderService
     * @param AntifloodService $antifloodService
     */
    public function __construct(
        ValidatorInterface $validator,
        EmailSenderService $emailSenderService,
        AntifloodService $antifloodService
    ) {
        $this->validator = $validator;
        $this->emailSenderService = $emailSenderService;
        $this->antifloodService = $antifloodService;
    }

    /**
     * @param SendEmailRequest $requestBody
     * @param Request $request
     *
     * @return Response
     *
     * @ParamConverter("requestBody", converter="fos_rest.request_body")
     */
    public function send(SendEmailRequest $requestBody, Request $request): Response
    {
        $errors = $this->validator->validate($requestBody);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ($this->antifloodService->isIpFlooding($request->getClientIp())) {
            return $this->json([
                'error' => 'You are sending too much messages. Try again later.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        try {
            $this->emailSenderService->send(
                $requestBody->subject,
                $requestBody->sender,
                $requestBody->message
            );
        } catch (SendingEmailFailedException $e) {
            return $this->json([
                'error' => 'An unknown error has occurred.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->antifloodService->saveIpRecord($request->getClientIp());

        return $this->json(null);
    }
}
