<?php

declare(strict_types=1);

namespace App\Request;

use App\Validator\ReCaptcha;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class SendEmailRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $sender;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $subject;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=10, minMessage="Message is too short.")
     */
    public $message;

    /**
     * @var string
     *
     * @SerializedName("g-recaptcha-response")
     *
     * @Assert\NotBlank()
     * @ReCaptcha()
     */
    public $recaptcha;
}
