<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class DebugConfigScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'Debug Config Scanner';
    }

    public function scan(array $files): array
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return [];
        }

        $content = file_get_contents($envPath);

        if (str_contains($content, 'APP_DEBUG=true')) {
            return [
                new Finding(
                    type: 'Configuration',
                    severity: 'MEDIUM',
                    file: '.env',
                    line: 0,
                    message: 'APP_DEBUG is enabled',
                    fix: 'Set APP_DEBUG=false in production'
                )
            ];
        }

        return [];
    }

    public function isGlobal(): bool
    {
        return true;
    }
}