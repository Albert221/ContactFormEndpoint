<?php

declare(strict_types=1);

namespace App\Validator;

use ReCaptcha\ReCaptcha as GoogleReCaptcha;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ReCaptchaValidator extends ConstraintValidator
{
    /**
     * @var GoogleReCaptcha
     */
    private $reCaptcha;

    /**
     * @param GoogleReCaptcha $recaptcha
     */
    public function __construct(GoogleReCaptcha $recaptcha)
    {
        $this->reCaptcha = $recaptcha;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ReCaptcha) {
            throw new UnexpectedTypeException($constraint, ReCaptcha::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $response = $this->reCaptcha->verify($value);

        if ($response->isSuccess()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation();
    }
}
