<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class RateLimitScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'Rate Limit Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            if (!str_contains($file->getPathname(), 'routes')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            if (!str_contains($content, 'Route::')) {
                continue;
            }

            // Accept any of the common rate-limiting patterns:
            // throttle middleware, RateLimiter facade, or custom limiters
            $hasRateLimit = str_contains($content, 'throttle')
                || str_contains($content, 'RateLimiter')
                || str_contains($content, 'rate_limit')
                || preg_match('/middleware\s*\(\s*[\'"]throttle/', $content);

            if (!$hasRateLimit) {
                $findings[] = new Finding(
                    'Rate Limiting Missing',
                    'MEDIUM',
                    $file->getPathname(),
                    0,
                    'Route file contains routes with no detectable rate limiting',
                    "Apply throttle middleware: Route::middleware('throttle:60,1')->group(...) or define a RateLimiter in AppServiceProvider"
                );
            }
        }

        return $findings;
    }
}