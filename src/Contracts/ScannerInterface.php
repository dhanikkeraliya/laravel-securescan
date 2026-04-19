<?php

namespace DhanikKeraliya\SecurityScanner\Contracts;

use DhanikKeraliya\SecurityScanner\DTO\Finding;

interface ScannerInterface
{
    /**
     * @param array $files
     * @return Finding[]
     */
    public function scan(array $files): array;

    public function name(): string;

    public function isGlobal(): bool;
}