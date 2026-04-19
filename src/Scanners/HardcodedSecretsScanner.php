<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class HardcodedSecretsScanner implements ScannerInterface
{
    /**
     * Extended list of sensitive key names to detect.
     */
    protected array $sensitiveKeys = [
        'api_key', 'apikey', 'api_secret', 'secret', 'secret_key',
        'token', 'access_token', 'auth_token', 'password', 'passwd',
        'private_key', 'client_secret', 'db_password', 'database_password',
        'smtp_password', 'encryption_key', 'jwt_secret',
    ];

    public function name(): string
    {
        return 'Secrets Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        // Build a combined pattern from all sensitive key names
        $keyPattern = implode('|', array_map('preg_quote', $this->sensitiveKeys));
        $pattern = '/\b(' . $keyPattern . ')\s*[=:>]+\s*[\'"].{4,}[\'"]/i';

        foreach ($files as $file) {
            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                // Skip comments
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '*')) {
                    continue;
                }

                // Skip env() calls — those are fine
                if (preg_match('/env\s*\(/', $line)) {
                    continue;
                }

                if (preg_match($pattern, $line)) {
                    $findings[] = new Finding(
                        'Hardcoded Secret',
                        'HIGH',
                        $file->getPathname(),
                        $lineNumber + 1,
                        'Hardcoded sensitive value detected',
                        'Move secrets to .env and access via env() or config()'
                    );
                }
            }
        }

        return $findings;
    }
}