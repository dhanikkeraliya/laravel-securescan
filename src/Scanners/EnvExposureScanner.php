<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class EnvExposureScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'ENV Exposure Scanner';
    }

    public function isGlobal(): bool
    {
        return true;
    }

    public function scan(array $files): array
    {
        $findings = [];

        $publicEnv = public_path('.env');

        if (file_exists($publicEnv)) {
            $findings[] = new Finding(
                'ENV Exposure',
                'HIGH',
                'public/.env',
                0,
                '.env file is publicly accessible',
                'Remove .env from public directory immediately'
            );
        }

        return $findings;
    }
}