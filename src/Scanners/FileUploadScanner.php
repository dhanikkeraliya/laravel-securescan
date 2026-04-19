<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class FileUploadScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'File Upload Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            // Only care about files that handle uploads
            if (!str_contains($content, '$request->file(') && !str_contains($content, '$request->hasFile(')) {
                continue;
            }

            $hasValidation = str_contains($content, '->validate(')
                || str_contains($content, '->validated(')
                || str_contains($content, 'Validator::make(')
                || preg_match('/\[.*mimes:|image:|file:|max:.*\]/', $content); // inline rule hints

            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                if (!str_contains($line, '$request->file(')) {
                    continue;
                }

                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*')) {
                    continue;
                }

                if (!$hasValidation) {
                    $findings[] = new Finding(
                        'File Upload',
                        'HIGH',   // Upgraded: unvalidated uploads can lead to RCE
                        $file->getPathname(),
                        $lineNumber + 1,
                        'File upload handled with no detectable validation — this can lead to remote code execution',
                        "Validate with rules like: 'file' => 'required|file|mimes:jpg,png,pdf|max:2048'"
                    );
                } else {
                    // Validation exists but verify it covers the file field explicitly
                    $findings[] = new Finding(
                        'File Upload',
                        'LOW',
                        $file->getPathname(),
                        $lineNumber + 1,
                        'File upload detected — ensure validation explicitly restricts MIME type and file size',
                        "Use mimes:, mimetypes:, and max: rules to restrict uploads. Avoid storing in public/ without sanitization."
                    );
                }
            }
        }

        return $findings;
    }
}