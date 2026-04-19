<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class SqlInjectionScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'SQL Injection Scanner';
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            $lines = @file($file->getPathname());

            if (!$lines) continue;

            foreach ($lines as $lineNumber => $line) {

                if (
                    str_contains($line, 'whereRaw(') ||
                    str_contains($line, 'DB::raw(')
                ) {
                    $findings[] = new Finding(
                        type: 'SQL Injection',
                        severity: 'HIGH',
                        file: $file->getPathname(),
                        line: $lineNumber + 1,
                        message: 'Potential SQL injection via raw query',
                        fix: 'Use parameter binding instead of raw queries'
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