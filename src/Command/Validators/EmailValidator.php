<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

class EmailValidator extends StringValidator
{
    protected ?int $filterType = FILTER_VALIDATE_EMAIL;

    protected bool $required = true;
}