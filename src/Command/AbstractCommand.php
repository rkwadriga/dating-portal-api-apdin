<?php declare(strict_types=1);
/**
 * Created 2021-11-25
 * Author Dmitry Kushneriov
 */

namespace App\Command;

use App\Command\Validators\ValidatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends Command
{
    protected function getOrAskArgument(
        InputInterface $input,
        OutputInterface $output,
        string $argumentName,
        string $question = null,
        ValidatorInterface $validator = null,
        mixed $defaultValue = null,
        bool $isRequired = true,
        string $errorOutputType = 'error',
        string $emptyValueErrorMessage = null
    ): ?string {
        $value = $input->getArgument($argumentName);
        if ($value === null) {
            if ($question === null) {
                $question = "Enter {$argumentName}: ";
            }
            $value = $this->getHelper('question')->ask($input, $output, new Question($question, $defaultValue));
        }
        if ($value === null) {
            if (!$isRequired || $validator?->isRequired() === false) {
                return $defaultValue;
            }
            if ($emptyValueErrorMessage === null) {
                $emptyValueErrorMessage = "Argument {$argumentName} is required";
            }
            $this->outputFormatted($output, $emptyValueErrorMessage, $errorOutputType);
            return $defaultValue;
        }

        if ($validator === null) {
            return $value;
        }

        if (!$validator->validate($value)) {
            $this->outputFormatted($output, "Invalid {$argumentName}:\n", $errorOutputType);
            foreach ($validator->getErrors() as $error) {
                $this->outputFormatted($output, "    {$error};\n", $errorOutputType);
            }
            return $defaultValue;
        }

        return $value;
    }

    protected function outputFormatted(OutputInterface $output, mixed $value, string $outputType = 'info'): self
    {
        $output->write("<{$outputType}>{$value}</{$outputType}>");
        return $this;
    }
}