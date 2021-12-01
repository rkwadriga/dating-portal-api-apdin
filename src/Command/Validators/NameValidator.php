<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

class NameValidator extends StringValidator
{
    protected bool $required = false;

    protected int $min = 2;

    protected int $max = 36;
}