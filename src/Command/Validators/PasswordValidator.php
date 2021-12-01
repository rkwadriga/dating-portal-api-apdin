<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

class PasswordValidator extends StringValidator
{
    protected bool $required = true;

    protected int $min = 4;

    protected int $max = 36;
}