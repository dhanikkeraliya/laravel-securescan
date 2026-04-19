<?php

namespace DhanikKeraliya\SecurityScanner\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DhanikKeraliya\SecurityScanner\Engine\ScanEngine;
use DhanikKeraliya\SecurityScanner\Support\FileCollector;
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
class ScanController extends Controller
{
    public function index()
    {
        return view('securescan::dashboard');
    }

    /**
     * SSE stream — runs the scan and pushes events live.
     * Route: GET /securescan/stream  (name: securescan.stream)
     */
    public function stream(): StreamedResponse
    {
        return response()->stream(function () {

            // ── Kill all output buffering ──────────────────────────────────────
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            while (ob_get_level()) ob_end_clean();

            // ── SSE emit helper ────────────────────────────────────────────────
            $emit = function (string $event, array $payload): void {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($payload) . "\n\n";
                flush();
            };

            // ── Build engine ───────────────────────────────────────────────────
            $engine = new ScanEngine();
            $files  = FileCollector::collect(base_path('app'));

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
            // ── Total steps ────────────────────────────────────────────────────
            $totalSteps = 0;
            foreach ($engine->getScanners() as $scanner) {
                $totalSteps += $scanner->isGlobal() ? 1 : count($files);
            }

            $emit('start', ['total' => $totalSteps]);

            $currentStep = 0;
            $allFindings = [];

            // ── Run scan ───────────────────────────────────────────────────────
            // The callback fires per file/scanner step.
            // If your ScanEngine passes findings as 3rd arg to the callback, they
            // are emitted immediately (truly live). Otherwise the fallback below
            // emits them all right after run() returns — still within the stream,
            // so the browser gets them before the `done` event closes the UI.
            $results = $engine->run(
                $files,
                function ($file = null, $scanner = null, $stepFindings = [])
                    use (&$currentStep, $totalSteps, $emit, &$allFindings)
                {
                    $currentStep++;
                    $progress = round(($currentStep / $totalSteps) * 100, 1);

                    $label = ($file === 'GLOBAL')
                        ? "[{$scanner}] Global checks"
                        : "[{$scanner}] " . basename((string) $file);

                    $emit('progress', [
                        'progress' => $progress,
                        'log'      => $label,
                        'step'     => $currentStep,
                        'total'    => $totalSteps,
                    ]);

                    // Emit findings live if engine passes them into the callback
                    foreach ((array) $stepFindings as $f) {
                        $finding = [
                            'severity' => $f->severity,
                            'type'     => $f->type,
                            'file'     => $f->file,
                            'line'     => $f->line ?? null,
                            'message'  => $f->message ?? '',
                            'fix'      => $f->fix ?? '',
                        ];
                        $allFindings[] = $finding;
                        $emit('finding', $finding);
                    }
                }
            );

            // ── Fallback: engine returns findings after full run ────────────────
            // Remove this block once your ScanEngine supports findings in callback.
            if (empty($allFindings)) {
                foreach ((array) $results as $f) {
                    $finding = [
                        'severity' => $f->severity,
                        'type'     => $f->type,
                        'file'     => $f->file,
                        'line'     => $f->line ?? null,
                        'message'  => $f->message ?? '',
                        'fix'      => $f->fix ?? '',
                    ];
                    $allFindings[] = $finding;
                    $emit('finding', $finding);   // emitted inside the stream, before `done`
                }
            }

            // ── Done ───────────────────────────────────────────────────────────
            $counts = [
                'high'   => count(array_filter($allFindings, fn($f) => strtoupper($f['severity']) === 'HIGH')),
                'medium' => count(array_filter($allFindings, fn($f) => strtoupper($f['severity']) === 'MEDIUM')),
                'low'    => count(array_filter($allFindings, fn($f) => strtoupper($f['severity']) === 'LOW')),
            ];

            $emit('done', [
                'progress' => 100,
                'counts'   => $counts,
                'total'    => count($allFindings),
            ]);

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}