<?php

namespace DhanikKeraliya\SecurityScanner\DTO;

class Finding
{
    public function __construct(
        public string $type,
        public string $severity,
        public string $file,
        public int $line,
        public string $message,
        public string $fix
    ) {}
}