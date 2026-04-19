<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class CsrfScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'CSRF Scanner';
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {

            if (!str_contains($file->getFilename(), '.blade.php')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (
                str_contains($content, '<form') &&
                !str_contains($content, '@csrf')
            ) {
                $findings[] = new Finding(
                    'CSRF',
                    'HIGH',
                    $file->getPathname(),
                    0,
                    'Form without CSRF protection',
                    'Add @csrf directive'
                );
            }
        }

        return $findings;
    }

    public function isGlobal(): bool
    {
        return false;
    }
}