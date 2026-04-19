<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class UnvalidatedInputScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'Unvalidated Input Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            if (!str_contains($file->getPathname(), 'Controller')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            // If the entire file uses a FormRequest type-hint, consider it validated
            // e.g. function store(StoreUserRequest $request)
            $usesFormRequest = preg_match('/function\s+\w+\s*\(\s*\w*Request\s+\$request\s*\)/', $content);

            // If file-level validate() or validated() call is present, it's likely safe
            $hasValidation = str_contains($content, '->validate(')
                || str_contains($content, '->validated(')
                || str_contains($content, 'Validator::make(');

            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                if (!preg_match('/\$request->(input|get|post|all|query)\s*\(/', $line)) {
                    continue;
                }

                // Skip comment lines
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*')) {
                    continue;
                }

                if ($usesFormRequest || $hasValidation) {
                    // Validation exists at file level — lower severity but still flag for review
                    $findings[] = new Finding(
                        'Unvalidated Input',
                        'LOW',
                        $file->getPathname(),
                        $lineNumber + 1,
                        'Request input accessed — ensure this is covered by your FormRequest or validate() call',
                        'Confirm all inputs are validated via $request->validate(), validated(), or a FormRequest'
                    );
                } else {
                    $findings[] = new Finding(
                        'Unvalidated Input',
                        'HIGH',
                        $file->getPathname(),
                        $lineNumber + 1,
                        'Request input used without any detectable validation in this controller',
                        'Use $request->validate(), a FormRequest class, or Validator::make() before accessing input'
                    );
                }
            }
        }

        return $findings;
    }
}