<?php

declare(strict_types=1);

namespace Zoosper\Core\Environment;

final class EnvLineParser
{
    public static function parseValue(string $rawValue): string
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return '';
        }

        $first = $rawValue[0];
        $length = strlen($rawValue);
        if (($first === '"' || $first === "'") && $length >= 2 && $rawValue[$length - 1] === $first) {
            return substr($rawValue, 1, -1);
        }

        if (preg_match('/^(.*?)\s+#.*$/', $rawValue, $matches) === 1) {
            return trim($matches[1]);
        }

        return $rawValue;
    }

    public static function parseAssignment(string $line): ?EnvAssignment
    {
        if (preg_match('/^(?<leading>\s*)(?<export>export\s+)?(?<key>[A-Za-z_][A-Za-z0-9_]*)(?<delimiter>\s*=\s*)(?<raw>.*)$/', $line, $matches) !== 1) {
            return null;
        }

        $raw = $matches['raw'];
        [$token, $suffix] = self::splitValueAndSuffix($raw);

        return new EnvAssignment(
            $matches['key'],
            self::parseValue($token),
            $matches['leading'] . ($matches['export'] ?? '') . $matches['key'] . $matches['delimiter'],
            $suffix,
        );
    }

    /** @return array{string, string} */
    private static function splitValueAndSuffix(string $raw): array
    {
        $trimmedLeft = ltrim($raw);
        $leading = substr($raw, 0, strlen($raw) - strlen($trimmedLeft));
        if ($trimmedLeft === '') {
            return [$leading, ''];
        }

        $quote = $trimmedLeft[0];
        if ($quote === '"' || $quote === "'") {
            $end = strpos($trimmedLeft, $quote, 1);
            if ($end !== false) {
                $token = substr($trimmedLeft, 0, $end + 1);
                return [$leading . $token, substr($trimmedLeft, $end + 1)];
            }
        }

        if (preg_match('/^(.*?)(\s+#.*)$/', $trimmedLeft, $matches) === 1) {
            return [$leading . rtrim($matches[1]), substr($trimmedLeft, strlen(rtrim($matches[1])))];
        }

        return [$raw, ''];
    }
}
