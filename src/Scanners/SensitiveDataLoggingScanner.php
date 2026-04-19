<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class SensitiveDataLoggingScanner implements ScannerInterface
{
    protected array $sensitiveKeywords = [
        'password', 'passwd', 'token', 'secret',
        'api_key', 'apikey', 'credit_card', 'card_number',
        'cvv', 'ssn', 'social_security', 'private_key',
    ];

    public function name(): string
    {
        return 'Sensitive Data Logging Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        // Covers Laravel's Log facade and helper, plus raw error_log
        $logPattern = '/\b(Log::|logger\(|error_log\(|info\(|debug\()/';

        foreach ($files as $file) {
            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*')) {
                    continue;
                }

                if (!preg_match($logPattern, $line)) {
                    continue;
                }

                foreach ($this->sensitiveKeywords as $keyword) {
                    if (stripos($line, $keyword) !== false) {
                        $findings[] = new Finding(
                            'Sensitive Logging',
                            'HIGH',
                            $file->getPathname(),
                            $lineNumber + 1,
                            "Potentially sensitive field '{$keyword}' appears in a log statement",
                            'Never log passwords, tokens, or secrets. Mask or omit them before logging.'
                        );
                        break; // One finding per line
                    }
                }
            }
        }

        return $findings;
    }
}