<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class DangerousFunctionsScanner implements ScannerInterface
{
    /**
     * Map of dangerous function patterns to fix suggestions.
     * Keys are regex patterns; values are fix messages.
     */
    protected array $dangerous = [
        '/\bexec\s*\(/'                         => 'Avoid exec(); use Symfony Process component instead',
        '/\bshell_exec\s*\(/'                   => 'Avoid shell_exec(); use Symfony Process component instead',
        '/\bsystem\s*\(/'                        => 'Avoid system(); use Symfony Process component instead',
        '/\bpassthru\s*\(/'                      => 'Avoid passthru(); use Symfony Process component instead',
        '/\beval\s*\(/'                          => 'Never use eval(); refactor to avoid dynamic code execution',
        '/\bassert\s*\(\s*[\'"\$]/'             => 'Avoid assert() with string/variable args — it evaluates code',
        '/\bcall_user_func\s*\(\s*\$/'          => 'Avoid call_user_func() with variable function names and user input',
        '/\bpreg_replace\s*\([\'"].*\/e[\'"]/'  => 'The /e modifier in preg_replace() executes PHP — use preg_replace_callback() instead',
        '/\bpopen\s*\(/'                         => 'Avoid popen(); use Symfony Process component instead',
        '/\bproc_open\s*\(/'                     => 'Avoid proc_open() with unsanitized input',
    ];

    public function name(): string
    {
        return 'Dangerous Functions Scanner';
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

                foreach ($this->dangerous as $pattern => $fix) {
                    if (preg_match($pattern, $line, $matches)) {
                        $findings[] = new Finding(
                            'Dangerous Function',
                            'HIGH',
                            $file->getPathname(),
                            $lineNumber + 1,
                            "Usage of dangerous function detected: '{$matches[0]}'",
                            $fix
                        );
                        break; // One finding per line
                    }
                }
            }
        }

        return $findings;
    }
}