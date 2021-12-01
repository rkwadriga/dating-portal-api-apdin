<?php
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

use Exception;

interface ValidatorInterface
{
    public function validate(mixed &$value, string $exceptionClass = null): bool;

    public function getErrors(): array;

    public function isRequired(): bool;
}