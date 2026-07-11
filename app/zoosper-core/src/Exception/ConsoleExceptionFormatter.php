<?php

declare(strict_types=1);

namespace Zoosper\Core\Exception;

use Throwable;

/**
 * Formats Zoosper exceptions for CLI diagnostics with actionable suggestions.
 */
final readonly class ConsoleExceptionFormatter
{
    public function __construct(private SensitiveValueRedactor $redactor = new SensitiveValueRedactor())
    {
    }

    public function format(Throwable $exception): string
    {
        if (!$exception instanceof ZoosperException) {
            return $this->formatGeneric($exception);
        }

        $lines = [];
        $lines[] = 'Zoosper helpful error';
        $lines[] = '=====================';
        $lines[] = '';
        $lines[] = 'What went wrong:';
        $lines[] = '- ' . $exception->getMessage();

        if ($exception->context() !== '') {
            $lines[] = '';
            $lines[] = 'Context:';
            $lines[] = $exception->context();
        }

        if ($exception->suggestion() !== '') {
            $lines[] = '';
            $lines[] = 'Suggested solution:';
            $lines[] = $exception->suggestion();
        }

        if ($exception->docsUrl() !== null) {
            $lines[] = '';
            $lines[] = 'Docs:';
            $lines[] = $exception->docsUrl();
        }

        $details = $this->redactor->redactArray($exception->details());
        if ($details !== []) {
            $lines[] = '';
            $lines[] = 'Details:';
            foreach ($details as $key => $value) {
                $lines[] = '- ' . $key . ': ' . $this->stringify($value);
            }
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function formatGeneric(Throwable $exception): string
    {
        return implode(PHP_EOL, [
            'Zoosper error',
            '=============',
            '',
            'What went wrong:',
            '- ' . $exception->getMessage(),
            '',
            'Exception class:',
            $exception::class,
        ]) . PHP_EOL;
    }

    private function stringify(mixed $value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[unprintable]';
    }
}
