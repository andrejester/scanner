<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestFileScannerCommand extends Command
{
    protected $signature   = 'scanner:test {--path= : Folder custom untuk di-test}';
    protected $description = 'Test File Scanner (22 kategori) dengan file di storage/app/test_malicious';

    // Threshold skor (sesuai panduan)
    private const SCORE_AMAN        = 20;
    private const SCORE_PERLU_TINJAU = 50;
    private const SCORE_MENCURIGAKAN = 100;

    /** @var array<string, array{pattern: string, score: int}> */
    private array $checks = [
        // 1. Signature
        'eval\s*\('                  => ['label' => '[Sig] eval()',               'score' => 8],
        'assert\s*\('                => ['label' => '[Sig] assert()',             'score' => 8],
        'system\s*\('                => ['label' => '[Sig] system()',             'score' => 8],
        'exec\s*\('                  => ['label' => '[Sig] exec()',               'score' => 8],
        'shell_exec\s*\('            => ['label' => '[Sig] shell_exec()',         'score' => 8],
        'passthru\s*\('              => ['label' => '[Sig] passthru()',           'score' => 8],
        'base64_decode\s*\('         => ['label' => '[Sig] base64_decode()',      'score' => 8],
        'gzinflate\s*\('             => ['label' => '[Sig] gzinflate()',          'score' => 8],
        'fsockopen\s*\('             => ['label' => '[Sig] fsockopen()',          'score' => 8],
        'curl_exec\s*\('             => ['label' => '[Sig] curl_exec()',          'score' => 8],
        // 2. Dangerous Combinations
        'eval\s*\(\s*base64_decode'  => ['label' => '[Comb] eval+base64_decode', 'score' => 20],
        'eval\s*\(\s*gzinflate'      => ['label' => '[Comb] eval+gzinflate',     'score' => 20],
        'system\s*\(\s*\$_GET'       => ['label' => '[Comb] system+$_GET',       'score' => 20],
        // 3. Superglobal
        '\$_GET\s*\['                => ['label' => '[Super] $_GET',             'score' => 5],
        '\$_POST\s*\['               => ['label' => '[Super] $_POST',            'score' => 5],
        '\$_REQUEST\s*\['            => ['label' => '[Super] $_REQUEST',         'score' => 5],
        '\$_COOKIE\s*\['             => ['label' => '[Super] $_COOKIE',          'score' => 5],
        'php://input'                => ['label' => '[Super] php://input',       'score' => 5],
        // 4. Obfuscation
        'str_rot13\s*\('             => ['label' => '[Obf] str_rot13()',         'score' => 10],
        'strrev\s*\('                => ['label' => '[Obf] strrev()',            'score' => 10],
        'chr\s*\(\s*\d+'             => ['label' => '[Obf] chr() assembly',     'score' => 10],
        // 8. Suspicious Variable
        '\$\$\w+'                    => ['label' => '[Var] Variable variable',   'score' => 8],
        '\$GLOBALS\s*\['             => ['label' => '[Var] $GLOBALS',            'score' => 8],
        // 9. Dynamic Function Call
        'call_user_func\s*\('        => ['label' => '[Dyn] call_user_func',     'score' => 10],
        'create_function\s*\('       => ['label' => '[Dyn] create_function',    'score' => 10],
        'preg_replace.*\/e'          => ['label' => '[Dyn] preg_replace /e',    'score' => 10],
        // 10-11. Include
        'include\s*\(\s*\$'          => ['label' => '[Inc] dynamic include',    'score' => 15],
        'include\s*\(\s*["\']https?' => ['label' => '[Inc] remote include',     'score' => 15],
        // 12. Upload
        'move_uploaded_file\s*\('    => ['label' => '[Upload] move_uploaded',   'score' => 7],
        'file_put_contents\s*\('     => ['label' => '[Upload] file_put',        'score' => 7],
        // 16. Permission
        'chmod\s*\(\s*[^,]+,\s*0?777' => ['label' => '[Perm] chmod(777)',       'score' => 12],
        // 21. IOC
        'FilesMan'                   => ['label' => '[IOC] FilesMan Shell',     'score' => 35],
        'c99shell'                   => ['label' => '[IOC] C99 Shell',          'score' => 35],
        'b374k'                      => ['label' => '[IOC] B374K Shell',        'score' => 35],
        'China\s*Chopper'            => ['label' => '[IOC] China Chopper',      'score' => 35],
    ];

    public function handle(): int
    {
        $this->info('🛡  File Scanner — 22 Kategori Deteksi');
        $this->newLine();

        $path = $this->option('path') ?? storage_path('app/test_malicious');

        if (!File::exists($path)) {
            $this->error('❌ Folder tidak ditemukan: ' . $path);
            return 1;
        }

        $files = File::glob($path . '/*.php');

        if (empty($files)) {
            $this->error('❌ Tidak ada file .php di folder tersebut.');
            return 1;
        }

        $this->line('📁 Ditemukan <comment>' . count($files) . '</comment> file');
        $this->newLine();

        foreach ($files as $file) {
            $this->scanAndDisplay($file);
        }

        $this->info('✅ Test selesai.');
        return 0;
    }

    private function scanAndDisplay(string $filePath): void
    {
        $content  = File::get($filePath);
        $fileName = basename($filePath);
        $score    = 0;
        $found    = [];

        // Pattern checks
        foreach ($this->checks as $pattern => $meta) {
            if (@preg_match('/' . $pattern . '/is', $content)) {
                $found[] = $meta['label'];
                $score  += $meta['score'];
            }
        }

        // 5. Encoded String
        if (preg_match_all('/[A-Za-z0-9+\/]{500,}={0,2}/', $content, $m)) {
            $found[] = '[Enc] Long Base64 (' . count($m[0]) . 'x)';
            $score  += count($m[0]) * 15;
        }

        // 5b. eval(base64_decode('...payload...')) — signature backdoor klasik
        if (@preg_match('/eval\s*\(\s*base64_decode\s*\(\s*[\'"][A-Za-z0-9+\/\s]{50,}={0,2}[\'"]/is', $content)) {
            $found[] = '[Enc] eval(base64_decode) embedded payload';
            $score  += 50;
        }

        // 20b. Decode Base64 literal dan scan isinya
        if (preg_match_all('/base64_decode\s*\(\s*[\'"]([A-Za-z0-9+\/\s=]{40,})[\'"]\s*\)/is', $content, $b64m)) {
            foreach ($b64m[1] as $raw) {
                $dec = @base64_decode(preg_replace('/\s+/', '', $raw));
                if (!$dec || strlen($dec) < 10) continue;
                $iocDecoded = [
                    'api\.telegram\.org' => '[IOC-Decoded] Telegram C2',
                    'sendMessage'        => '[IOC-Decoded] Telegram sendMessage',
                    'REMOTE_ADDR'        => '[IOC-Decoded] IP Exfiltration',
                    'discord\.com/api'   => '[IOC-Decoded] Discord Webhook',
                    'pastebin\.com'      => '[IOC-Decoded] Pastebin C2',
                    'shell_exec|system\s*\(' => '[IOC-Decoded] Exec in Payload',
                ];
                foreach ($iocDecoded as $p => $l) {
                    if (@preg_match('/' . $p . '/is', $dec)) {
                        $found[] = $l;
                        $score  += 40;
                    }
                }
            }
        }

        // 6. Hex String
        if (@preg_match('/\\\\x[0-9a-fA-F]{2}|0x[0-9a-fA-F]{4,}/i', $content)) {
            $found[] = '[Hex] Hex payload';
            $score  += 12;
        }

        // 7. Very Long Line
        $longLines = array_filter(explode("\n", $content), fn($l) => strlen($l) > 1000);
        if (count($longLines)) {
            $found[] = '[LongLine] ' . count($longLines) . ' baris sangat panjang';
            $score  += count($longLines) * 10;
        }

        // 19. Entropy
        $entropy = $this->entropy($content);
        if ($entropy > 5.5) {
            $found[] = sprintf('[Entropy] %.2f', $entropy);
            $score  += (int)(($entropy - 5.5) * 20);
        }

        // Level
        if ($score > self::SCORE_MENCURIGAKAN) {
            $icon  = '🔴';
            $level = '<fg=red>SANGAT BERBAHAYA</> (>' . self::SCORE_MENCURIGAKAN . ')';
        } elseif ($score > self::SCORE_PERLU_TINJAU) {
            $icon  = '🟠';
            $level = '<fg=yellow>MENCURIGAKAN</> (51–100)';
        } elseif ($score > self::SCORE_AMAN) {
            $icon  = '🔵';
            $level = '<fg=blue>PERLU DITINJAU</> (21–50)';
        } elseif ($score > 0) {
            $icon  = '⚪';
            $level = '<fg=gray>AMAN BERSYARAT</> (1–20)';
        } else {
            $icon  = '🟢';
            $level = '<fg=green>BERSIH</>';
        }

        $this->line("$icon <comment>{$fileName}</comment> — Skor: <info>{$score}</info> — $level");

        foreach (array_slice($found, 0, 8) as $item) {
            $this->line("   ✗ {$item}");
        }
        if (count($found) > 8) {
            $this->line('   ... dan ' . (count($found) - 8) . ' deteksi lainnya');
        }
        $this->newLine();
    }

    private function entropy(string $content): float
    {
        if (!strlen($content)) return 0.0;
        $freq    = array_count_values(str_split($content));
        $len     = strlen($content);
        $entropy = 0.0;
        foreach ($freq as $c) {
            $p        = $c / $len;
            $entropy -= $p * log($p, 2);
        }
        return $entropy;
    }
}
