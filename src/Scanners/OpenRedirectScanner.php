<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class OpenRedirectScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'Open Redirect Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        // Patterns that indicate a redirect driven by user-controlled input
        $redirectPatterns = [
            '/redirect\s*\(\s*\$request->/',         // redirect($request->input(...))
            '/redirect\s*\(\s*\$_GET/',              // redirect($_GET[...])
            '/redirect\s*\(\s*\$_POST/',             // redirect($_POST[...])
            '/redirect\s*\(\s*\$_REQUEST/',          // redirect($_REQUEST[...])
            '/->redirect\s*\(\s*\$request->/',       // ->redirect($request->...)
            '/Redirect::to\s*\(\s*\$request->/',     // Redirect::to($request->...)
        ];

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

                foreach ($redirectPatterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $findings[] = new Finding(
                            'Open Redirect',
                            'HIGH',
                            $file->getPathname(),
                            $lineNumber + 1,
                            'Redirect destination appears to be user-controlled',
                            'Validate redirect URLs against an allowlist or use named routes only'
                        );
                        break; // One finding per line
                    }
                }
            }
        }

        return $findings;
    }
}