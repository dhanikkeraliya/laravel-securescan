<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class HardcodedUrlScanner implements ScannerInterface
{
    /**
     * URL patterns that are safe to ignore (docs, placeholders, examples).
     */
    protected array $allowedPatterns = [
        'example.com',
        'localhost',
        '127.0.0.1',
        'schema.org',
        'www.w3.org',
        'github.com',           // Often in composer.json references / docblocks
        'opensource.org',       // Licenses
        'php.net',
        'laravel.com',
    ];

    public function name(): string
    {
        return 'Hardcoded URL Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                // Skip comment lines
                $trimmed = ltrim($line);
                if (
                    str_starts_with($trimmed, '//') ||
                    str_starts_with($trimmed, '#') ||
                    str_starts_with($trimmed, '*') ||
                    str_starts_with($trimmed, '/*')
                ) {
                    continue;
                }

                if (!preg_match('/https?:\/\/[^\s"\']+/', $line, $matches)) {
                    continue;
                }

                $url = $matches[0];

                // Skip known safe/placeholder URLs
                $isAllowed = false;
                foreach ($this->allowedPatterns as $pattern) {
                    if (str_contains($url, $pattern)) {
                        $isAllowed = true;
                        break;
                    }
                }

                if ($isAllowed) {
                    continue;
                }

                // Skip if it's accessed via config() or env() — already properly managed
                if (preg_match('/(config|env)\s*\(/', $line)) {
                    continue;
                }

                $findings[] = new Finding(
                    'Hardcoded URL',
                    'LOW',
                    $file->getPathname(),
                    $lineNumber + 1,
                    "Hardcoded URL detected: {$url}",
                    'Move URLs to config files or .env for environment flexibility'
                );
            }
        }

        return $findings;
    }
}