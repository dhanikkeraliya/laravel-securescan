<?php

namespace DhanikKeraliya\SecurityScanner\Console;

use Illuminate\Console\Command;
use DhanikKeraliya\SecurityScanner\Engine\ScanEngine;
use DhanikKeraliya\SecurityScanner\Support\{FileCollector, IgnoreManager};
use DhanikKeraliya\SecurityScanner\Scanners\{
    SqlInjectionScanner,
    XssScanner,
    DebugConfigScanner,
    HardcodedSecretsScanner,
    FileUploadScanner,
    CsrfScanner,
    EnvExposureScanner,
    DangerousFunctionsScanner,
    MassAssignmentScanner,
    OpenRedirectScanner,
    InsecureRandomScanner,
    UnvalidatedInputScanner,
    AuthorizationScanner,
    SensitiveDataLoggingScanner,
    HardcodedUrlScanner,
    RateLimitScanner
};

class SecurityScanCommand extends Command
{
    protected $signature = 'security:scan {--path=app} {--ignore}';
    protected $description = 'Run Laravel security scan';

    public function handle()
{
    $this->info('🔍 Starting security scan...');

    $path = base_path($this->option('path'));
    $files = FileCollector::collect($path);

    $this->info("📂 Scanning " . count($files) . " files");

    $engine = new ScanEngine();

    // 🔧 Load config
    $config = config('securescan.scanners', []);

    // Register scanners (config-based)
    if ($config['env'] ?? true) {
        $engine->register(new EnvExposureScanner());
    }

    if ($config['debug'] ?? true) {
        $engine->register(new DebugConfigScanner());
    }

    if ($config['sql_injection'] ?? true) {
        $engine->register(new SqlInjectionScanner());
    }

    if ($config['xss'] ?? true) {
        $engine->register(new XssScanner());
    }

    if ($config['secrets'] ?? true) {
        $engine->register(new HardcodedSecretsScanner());
    }

    if ($config['file_upload'] ?? true) {
        $engine->register(new FileUploadScanner());
    }

    if ($config['csrf'] ?? true) {
        $engine->register(new CsrfScanner());
    }

    if ($config['dangerous_functions'] ?? true) {
        $engine->register(new DangerousFunctionsScanner());
    }

    if ($config['mass_assignment'] ?? true) {
        $engine->register(new MassAssignmentScanner());
    }

    if ($config['open_redirect'] ?? true) {
        $engine->register(new OpenRedirectScanner());
    }

    if ($config['random'] ?? true) {
        $engine->register(new InsecureRandomScanner());
    }

    if ($config['input'] ?? true) {
        $engine->register(new UnvalidatedInputScanner());
    }

    if ($config['authorization'] ?? true) {
        $engine->register(new AuthorizationScanner());
    }

    if ($config['logging'] ?? true) {
        $engine->register(new SensitiveDataLoggingScanner());
    }

    if ($config['url'] ?? true) {
        $engine->register(new HardcodedUrlScanner());
    }

    if ($config['rate_limit'] ?? true) {
        $engine->register(new RateLimitScanner());
    }

    // ✅ Calculate total steps
    $totalSteps = 0;
    
    foreach ($engine->getScanners() as $scanner) {
        $totalSteps += $scanner->isGlobal() ? 1 : count($files);
    }

    // ✅ Progress bar
    $progressBar = $this->output->createProgressBar($totalSteps);

    $progressBar->setFormat(
        " %current%/%max% [%bar%] %percent:3s%% | <fg=cyan>%message%</>"
    );

    $progressBar->setMessage('Starting...');
    $progressBar->start();

    // ✅ Run scan
    $findings = $engine->run($files, function ($file = null, $scanner = null) use ($progressBar) {

        if ($file === 'GLOBAL') {
            $progressBar->setMessage("[$scanner] Global checks...");
        } elseif ($file) {

            $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);

            if (strlen($relative) > 60) {
                $relative = '...' . substr($relative, -57);
            }

            $progressBar->setMessage("[$scanner] $relative");
        }

        $progressBar->advance();
    });

    $progressBar->finish();
    $this->newLine(2);

    // ✅ Apply ignore system (AFTER scan)
    $ignoreManager = new IgnoreManager();

    if ($this->option('ignore')) {
        $findings = array_filter($findings, function ($finding) use ($ignoreManager) {
            return !$ignoreManager->shouldIgnore($finding);
        });
    }

    // ✅ Ignore by severity (config)
    $ignoreSeverity = config('securescan.ignore_severity', []);

    $findings = array_filter($findings, function ($finding) use ($ignoreSeverity) {
        return !in_array($finding->severity, $ignoreSeverity);
    });

    // ✅ Display findings
    foreach ($findings as $finding) {

        $color = match ($finding->severity) {
            'HIGH' => 'bg=red;fg=white',
            'MEDIUM' => 'bg=yellow;fg=black',
            'LOW' => 'bg=green;fg=black',
            default => 'bg=default;fg=white'
        };

        $this->line("<{$color}> {$finding->severity} </> {$finding->type}");
        $this->line(" <fg=cyan>File:</> {$finding->file}:{$finding->line}");
        $this->line(" <fg=yellow>Issue:</> {$finding->message}");
        $this->line(" <fg=green>Fix:</> {$finding->fix}");
        $this->line(str_repeat('-', 60));
    }

    // ✅ Summary
    $summary = ['HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];

    foreach ($findings as $finding) {
        $summary[$finding->severity]++;
    }

    $this->info("\n📊 Summary:");
    $this->line("<bg=red;fg=white> HIGH: {$summary['HIGH']} </>");
    $this->line("<bg=yellow;fg=black> MEDIUM: {$summary['MEDIUM']} </>");
    $this->line("<bg=green;fg=black> LOW: {$summary['LOW']} </>");
}
}