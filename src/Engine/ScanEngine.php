<?php

namespace DhanikKeraliya\SecurityScanner\Engine;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;

class ScanEngine
{
    protected array $scanners = [];

    public function register(ScannerInterface $scanner): void
    {
        $this->scanners[] = $scanner;
    }

    public function getScanners(): array
    {
        return $this->scanners;
    }

    public function run(array $files, callable $onProgress = null): array
    {
        $findings = [];

        foreach ($this->scanners as $scanner) {

            // ✅ GLOBAL SCANNERS
            if ($scanner->isGlobal()) {
                $results = $scanner->scan($files);
                $findings = array_merge($findings, $results);

                if ($onProgress) {
                    $onProgress('GLOBAL', $scanner->name());
                }

                continue;
            }

            // ✅ FILE SCANNERS
            foreach ($files as $file) {

                $results = $scanner->scan([$file]);
                $findings = array_merge($findings, $results);

                if ($onProgress) {
                    $onProgress($file->getPathname(), $scanner->name());
                }
            }
        }

        return $findings;
    }
}