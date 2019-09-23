<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation()
 */
class ReCaptcha extends Constraint
{
    public $message = 'Your reCAPTCHA response is invalid.';

    public function validatedBy(): string
    {
        return \get_class($this) . 'Validator';
    }
}
