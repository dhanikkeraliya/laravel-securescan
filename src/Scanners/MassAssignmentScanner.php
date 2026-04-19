<?php

namespace DhanikKeraliya\SecurityScanner\Scanners;

use DhanikKeraliya\SecurityScanner\Contracts\ScannerInterface;
use DhanikKeraliya\SecurityScanner\DTO\Finding;

class MassAssignmentScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'Mass Assignment Scanner';
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function scan(array $files): array
    {
        $findings = [];

        foreach ($files as $file) {
            // Only check files in a Models directory
            if (!str_contains($file->getPathname(), 'Models')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            // Only report if this file actually extends Model (or uses the trait)
            $extendsModel = preg_match('/extends\s+(Model|Authenticatable|Pivot)\b/', $content)
                || str_contains($content, 'use HasFactory');

            if (!$extendsModel) {
                continue;
            }

            $hasFillable = str_contains($content, '$fillable');
            $hasGuarded  = str_contains($content, '$guarded');

            if ($hasFillable || $hasGuarded) {
                // Check for the dangerous $guarded = [] (fully unguarded model)
                if (preg_match('/\$guarded\s*=\s*\[\s*\]/', $content)) {
                    $findings[] = new Finding(
                        'Mass Assignment',
                        'HIGH',
                        $file->getPathname(),
                        0,
                        'Model uses $guarded = [] — all attributes are mass-assignable',
                        'Explicitly define $fillable with only the attributes users should be able to set'
                    );
                }
                // Otherwise the model is properly guarded — no finding needed
            } else {
                $findings[] = new Finding(
                    'Mass Assignment',
                    'MEDIUM',
                    $file->getPathname(),
                    0,
                    'Eloquent model is missing $fillable or $guarded definition',
                    'Define $fillable with allowed attributes or $guarded = [\'*\'] to block all by default'
                );
            }
        }

        return $findings;
    }
}