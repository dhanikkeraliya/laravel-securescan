<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class XssScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'XSS Scanner';
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {

            if (!str_contains($file->getFilename(), '.blade.php')) {
                continue;
            }

            $lines = @file($file->getPathname());

            if (!$lines) continue;

            foreach ($lines as $lineNumber => $line) {

                if (str_contains($line, '{!!')) {
                    $findings[] = new Finding(
                        type: 'XSS',
                        severity: 'HIGH',
                        file: $file->getPathname(),
                        line: $lineNumber + 1,
                        message: 'Unescaped output detected',
                        fix: 'Use {{ }} instead of {!! !!}'
                    );
                }
            }
        }

        return $findings;
    }

    public function isGlobal(): bool
    {
        return false;
    }
}