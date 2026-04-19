<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class AuthorizationScanner implements ScannerInterface
{
    /**
     * Methods that are typically public by design and don't require auth checks.
     */
    protected array $publicMethods = [
        '__construct', 'middleware', 'index', 'show',
    ];

    public function name(): string
    {
        return 'Authorization Scanner';
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

            // Check if there's any authorization mechanism in the file
            $hasAuth = str_contains($content, 'authorize(')
                || str_contains($content, '->can(')
                || str_contains($content, 'Gate::')
                || str_contains($content, '@can')
                || str_contains($content, 'Policy')
                || preg_match('/middleware\s*\(\s*[\'"]auth/', $content)
                || str_contains($content, 'AuthorizesRequests');

            if ($hasAuth) {
                continue;
            }

            // Only flag if there are non-trivial public methods that write data
            $hasMutatingMethod = preg_match(
                '/function\s+(store|update|destroy|create|delete|edit)\s*\(/',
                $content
            );

            if (!$hasMutatingMethod) {
                continue;
            }

            $findings[] = new Finding(
                'Authorization Missing',
                'HIGH',
                $file->getPathname(),
                0,
                'Controller has data-mutating methods (store/update/destroy) with no detectable authorization checks',
                'Use $this->authorize(), Gate::, policies, or auth middleware to protect write operations'
            );
        }

        return $findings;
    }
}