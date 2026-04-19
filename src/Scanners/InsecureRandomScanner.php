<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class InsecureRandomScanner implements ScannerInterface
{
    /**
     * Insecure functions and their safer alternatives.
     */
    protected array $insecureFunctions = [
        'rand('      => 'random_int()',
        'mt_rand('   => 'random_int()',
        'array_rand(' => 'Use shuffle() on a securely generated range or random_int()',
        'uniqid('    => 'bin2hex(random_bytes(16)) for cryptographic uniqueness',
        'lcg_value(' => 'random_int() or random_bytes()',
    ];

    public function name(): string
    {
        return 'Insecure Random Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            $lines = @file($file->getPathname());
            if (!$lines) {
                continue;
            }

            foreach ($lines as $lineNumber => $line) {
                // Skip comment lines
                $trimmed = ltrim($line);
                if (
                    str_starts_with($trimmed, '//') ||
                    str_starts_with($trimmed, '#') ||
                    str_starts_with($trimmed, '*')
                ) {
                    continue;
                }

                foreach ($this->insecureFunctions as $func => $alternative) {
                    // Use word-boundary aware check to avoid matching e.g. "grand(" or "str_rand("
                    if (preg_match('/(?<![a-zA-Z0-9_])' . preg_quote($func, '/') . '/', $line)) {
                        $findings[] = new Finding(
                            'Weak Random',
                            'MEDIUM',
                            $file->getPathname(),
                            $lineNumber + 1,
                            "Insecure random function '{$func}' used — not suitable for security-sensitive contexts",
                            "Use {$alternative} for cryptographic safety"
                        );
                        break; // One finding per line is enough
                    }
                }
            }
        }

        return $findings;
    }
}