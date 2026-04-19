<?php

namespace DhanikKeraliya\SecurityScanner\Support;

class FileCollector
{
    public static function collect(string $path): array
    {
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        $files = [];

        foreach ($rii as $file) {
            if ($file->isDir()) continue;

            if ($file->getExtension() === 'php') {
                $files[] = $file;
            }
        }

        return $files;
    }
}