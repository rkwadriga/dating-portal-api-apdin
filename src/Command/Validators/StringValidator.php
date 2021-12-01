<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

class StringValidator extends AbstractValidator
{
    protected string $type = 'string';
}