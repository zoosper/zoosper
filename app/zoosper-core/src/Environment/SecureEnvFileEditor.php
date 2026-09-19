<?php

declare(strict_types=1);

namespace Zoosper\Core\Environment;

use RuntimeException;

final class SecureEnvFileEditor
{
    /**
     * @param array<string, string> $generated
     * @param callable(string): bool $shouldReplace
     * @return array<string, string>
     */
    public function update(string $path, array $generated, callable $shouldReplace): array
    {
        $contents = is_file($path) ? file_get_contents($path) : '';
        if ($contents === false) {
            throw new RuntimeException('Unable to read environment file: ' . $path);
        }

        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $hadFinalNewline = $contents !== '' && (str_ends_with($contents, "\n") || str_ends_with($contents, "\r"));
        $lines = $contents === '' ? [] : preg_split('/\r\n|\n|\r/', rtrim($contents, "\r\n"));
        if (!is_array($lines)) {
            throw new RuntimeException('Unable to parse environment file: ' . $path);
        }

        /** @var array<string, list<int>> $indexes */
        $indexes = [];
        /** @var array<int, EnvAssignment> $assignments */
        $assignments = [];
        foreach ($lines as $index => $line) {
            $assignment = EnvLineParser::parseAssignment($line);
            if ($assignment === null || !array_key_exists($assignment->key, $generated)) {
                continue;
            }
            $indexes[$assignment->key][] = $index;
            $assignments[$index] = $assignment;
        }

        foreach ($indexes as $key => $keyIndexes) {
            if (count($keyIndexes) > 1) {
                throw new RuntimeException('Refusing to modify ' . $path . ': generated secret key ' . $key . ' is assigned more than once. Remove the duplicate assignment and retry.');
            }
        }

        $status = [];
        foreach ($generated as $key => $newValue) {
            $index = $indexes[$key][0] ?? null;
            if ($index === null) {
                $lines[] = $key . '=' . $newValue;
                $status[$key] = 'Appended new line';
                continue;
            }

            $assignment = $assignments[$index];
            if (!$shouldReplace($assignment->value)) {
                $status[$key] = 'Preserved existing value';
                continue;
            }

            $lines[$index] = $assignment->withValue($newValue);
            $status[$key] = 'Updated existing line';
        }

        $updated = implode($newline, $lines) . ($hadFinalNewline || $contents === '' ? $newline : '');
        $directory = dirname($path);
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new RuntimeException('Environment file directory is not writable: ' . $directory);
        }

        $temp = tempnam($directory, '.zoosper-env-');
        if ($temp === false) {
            throw new RuntimeException('Unable to create a temporary environment file in: ' . $directory);
        }

        try {
            $bytes = file_put_contents($temp, $updated, LOCK_EX);
            if ($bytes === false || $bytes !== strlen($updated)) {
                throw new RuntimeException('Unable to write the complete temporary environment file for: ' . $path);
            }
            if (!chmod($temp, 0600)) {
                throw new RuntimeException('Unable to apply mode 0600 to the temporary environment file for: ' . $path);
            }
            clearstatcache(true, $temp);
            $tempPermissions = fileperms($temp);
            if ($tempPermissions === false || ($tempPermissions & 0777) !== 0600) {
                throw new RuntimeException('Temporary environment file did not retain required mode 0600 for: ' . $path);
            }
            if (!rename($temp, $path)) {
                throw new RuntimeException('Unable to atomically replace environment file: ' . $path);
            }
            clearstatcache(true, $path);
            $publishedPermissions = is_file($path) ? fileperms($path) : false;
            if ($publishedPermissions === false || ($publishedPermissions & 0777) !== 0600) {
                throw new RuntimeException('Environment file replacement could not be verified with mode 0600: ' . $path);
            }
        } finally {
            if (is_file($temp)) {
                @unlink($temp);
            }
        }

        return $status;
    }

    /** @return array<string, string> */
    public function values(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read environment file: ' . $path);
        }
        $values = [];
        foreach (preg_split('/\r\n|\n|\r/', $contents) ?: [] as $line) {
            $assignment = EnvLineParser::parseAssignment($line);
            if ($assignment !== null) {
                $values[$assignment->key] = $assignment->value;
            }
        }
        return $values;
    }
}
