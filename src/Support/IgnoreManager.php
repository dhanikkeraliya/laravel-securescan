<?php

namespace DhanikKeraliya\SecurityScanner\Support;

class IgnoreManager
{
    protected array $rules = [];

    public function __construct()
    {
        $path = base_path('.securescan-ignore');

        if (file_exists($path)) {
            $this->rules = array_filter(array_map('trim', file($path)));
        }
    }

    public function shouldIgnore($finding): bool
    {
        foreach ($this->rules as $rule) {

            if (str_contains($finding->file, $rule)) {
                return true;
            }

            if (str_contains($finding->type, $rule)) {
                return true;
            }
        }

        return false;
    }
}