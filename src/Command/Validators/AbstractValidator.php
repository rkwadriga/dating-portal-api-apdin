<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command\Validators;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use Exception;
use RuntimeException;

abstract class AbstractValidator implements ValidatorInterface
{
    protected array $errors = [];
    protected string $type;
    protected ?int $filterType = null;
    protected int $min = 0;
    protected int $max = 0;
    protected bool $required = false;
    protected mixed $defaultValue = null;
    protected bool $throwExceptionOnError = false;
    protected ?string $regExp = null;
    protected ?string $errorMessage = null;
    protected ?int $errorCode = null;
    protected ?string $exceptionClass = null;
    protected ?string $invalidTypeMessage = null;
    protected ?string $requiredParamMissedMessage = null;
    protected ?string $invalidLengthMessage = null;

    public function __construct(array $params = []) {
        foreach ($params as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
    }

    public function validate(mixed &$value, string $exceptionClass = null): bool
    {
        // Validate empty value
        if (empty($value)) {
            if ($this->defaultValue !== null) {
                $value = $this->defaultValue;
                return true;
            }
            if (!$this->required && is_scalar($value)) {
                return true;
            }

            $this->errors[] = $this->requiredParamMissedMessage ?? 'This param is required';
            return false;
        }

        // Validate value type
        if (!$this->validateType($value) && !empty(empty($value))) {
            $type = gettype($value);
            $this->errors[] = $this->invalidTypeMessage ?? "This param must be an {$this->type}, {$type} given";
        }

        // Validate value length
        $this->validateValueLength($value);

        // Validate value format
        $this->validateByRegexp($value);

        // Specific validation for each type
        $this->validateValue($value);

        if (!$this->throwExceptionOnError) {
            return empty($this->errors);
        }

        throw $this->createException($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    protected function validateValue(mixed $value): void
    {
        if ($this->filterType === null || filter_var($value, $this->filterType)) {
            return;
        }

        $this->errors[] = $this->errorMessage ?? 'Invalid param value';
    }

    protected function validateType(mixed $value): bool
    {
        $type = gettype($value);
        if ($type === 'object') {
            return get_class($value) === $this->type;
        } else {
            return $type === $this->type;
        }
    }

    protected function validateValueLength(mixed $value): void
    {
        if ($this->min <= 0 || $this->max <= 0) {
            return;
        }

        $length = (int) match (gettype($value)) {
            'int', 'float' => $value,
            'string' => strlen($value),
            'array' => count($value),
        };

        if ($length < $this->min || ($this->max > 0 && $length > $this->max)) {
            $message = $this->invalidLengthMessage ?? 'This param length should be ';
            if ($message !== null) {
                if ($this->min > 0 && $this->max > 0) {
                    $message .= "between {$this->min} and {$this->max}";
                } elseif ($this->min > 0) {
                    $message .= "more than {$this->min}";
                } else {
                    $message .= "less than {$this->max}";
                }
            }

            $this->errors[] = $message;
        }
    }

    protected function validateByRegexp(mixed $value): void
    {
        if ($this->regExp === null) {
            return;
        }
        if (!is_string($value)) {
            $type = gettype($value);
            throw new RuntimeException("Impossible validate {$type} by regexp");
        }
        if (!preg_match($this->regExp, $value)) {
            $this->errors[] = $this->errorMessage ?? 'Invalid param value format';
        }
    }

    protected function createException(string|array $errorMessage = null, int $errorCode = null, string $exceptionClass = null): Exception
    {
        if ($exceptionClass === null) {
            $exceptionClass = $this->exceptionClass;
        }
        if ($exceptionClass === null) {
            $exceptionClass = ValidationException::class;
        }
        if (is_array($errorMessage)) {
            $errorMessage = implode('; ', $errorMessage);
        }

        return new $exceptionClass($errorMessage, $errorCode ?? $this->errorCode);
    }
}