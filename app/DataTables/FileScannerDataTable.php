<?php

namespace App\DataTables;

use App\Models\Backend\FileScanner;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FileScannerDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function (FileScanner $row) {
                $btn = '';
                if (auth()->user()->can('filescanner_read')) {
                    $btn .= '<a href="' . route('filescanner.show', $row->id) . '" '
                        . 'class="btn btn-sm btn-info" title="Detail">'
                        . '<i class="bx bx-show"></i></a> ';
                }
                if (auth()->user()->can('filescanner_delete')) {
                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" '
                        . 'class="btn btn-sm btn-danger btn-delete" title="Hapus">'
                        . '<i class="bx bx-trash"></i></a>';
                }
                return $btn;
            })
            ->addColumn('threat_badge', function (FileScanner $row) {
                $map = [
                    'critical' => ['bg-danger',   'bx-error',         'CRITICAL',   'Sangat Berbahaya'],
                    'high'     => ['bg-warning',   'bx-error-circle',  'HIGH',       'Mencurigakan'],
                    'medium'   => ['bg-info',      'bx-info-circle',   'MEDIUM',     'Perlu Ditinjau'],
                    'low'      => ['bg-secondary', 'bx-minus-circle',  'LOW',        'Aman Bersyarat'],
                    'safe'     => ['bg-success',   'bx-check-circle',  'SAFE',       'Bersih'],
                ];
                [$bg, $icon, $label, $title] = $map[$row->threat_level] ?? ['bg-secondary', 'bx-question-mark', 'UNKNOWN', ''];

                // Extract score from threat_type
                $score = '';
                if (preg_match('/Skor:\s*(\d+)/', $row->threat_type ?? '', $m)) {
                    $score = ' <small>(' . $m[1] . ')</small>';
                }

                return '<span class="badge ' . $bg . '" title="' . $title . '">'
                    . '<i class="bx ' . $icon . '"></i> ' . $label . $score . '</span>';
            })
            ->addColumn('quarantine_status', function (FileScanner $row) {
                return $row->is_quarantined
                    ? '<span class="badge bg-danger"><i class="bx bx-lock-alt"></i> Karantina</span>'
                    : '<span class="badge bg-success"><i class="bx bx-check"></i> Aktif</span>';
            })
            ->addColumn('file_info', function (FileScanner $row) {
                $nameOnly  = pathinfo($row->file_name, PATHINFO_FILENAME);
                $ext       = strtolower(pathinfo($row->file_name, PATHINFO_EXTENSION));
                $displayPath = preg_replace('#^app/public/files/2/?#', '', $row->file_path);

                // Deteksi nama file mencurigakan untuk visual flag
                $fileWarnings = [];

                // Nama pendek + php
                if (strlen($nameOnly) <= 2 && in_array($ext, ['php', 'phtml', 'phar'])) {
                    $fileWarnings[] = 'Nama sangat pendek';
                }
                // Nama tampak hash/random hex
                if (strlen($nameOnly) >= 8 && preg_match('/^[a-f0-9]{8,}$/i', $nameOnly)) {
                    $fileWarnings[] = 'Nama acak/hash';
                }
                // Nama berupa angka
                if (preg_match('/^\d{4,}$/', $nameOnly)) {
                    $fileWarnings[] = 'Nama berupa angka';
                }
                // Karakter tidak lazim
                if (preg_match('/[^a-zA-Z0-9\-_.()]/', $nameOnly)) {
                    $fileWarnings[] = 'Karakter tidak lazim';
                }
                // Ekstensi ganda berbahaya
                if (preg_match('/\.(php\d?|phtml|phar)\.|\..*\.(php\d?|phtml|phar)$/i', $row->file_name)) {
                    $fileWarnings[] = 'Ekstensi ganda';
                }
                // Keyword berbahaya di nama
                $susKeywords = ['shell', 'cmd', 'backdoor', 'webshell', 'bypass', 'exploit', 'hack', 'trojan', 'c99', 'r57', 'b374k', 'wso'];
                foreach ($susKeywords as $kw) {
                    if (str_contains(strtolower($nameOnly), $kw)) {
                        $fileWarnings[] = 'Keyword berbahaya';
                        break;
                    }
                }

                // Flag dari deteksi hasil scan: PHP tersembunyi & file manager
                $patterns = $row->suspicious_patterns ?? [];
                foreach ($patterns as $p) {
                    if (str_contains($p, 'TANPA ekstensi berisi kode PHP')) {
                        $fileWarnings[] = 'PHP tanpa ekstensi';
                        break;
                    }
                    if (str_contains($p, 'berisi kode PHP')) {
                        $fileWarnings[] = 'PHP tersembunyi';
                        break;
                    }
                }
                foreach ($patterns as $p) {
                    if (str_contains($p, 'File Manager') || str_contains($p, 'Shell fungsional')) {
                        $fileWarnings[] = 'File Manager/Shell';
                        break;
                    }
                }
                // Tandai file tanpa ekstensi apapun
                if ($ext === '') {
                    $fileWarnings[] = 'Tanpa ekstensi';
                }

                // Render
                $nameHtml = e($row->file_name);
                if (!empty($fileWarnings)) {
                    // Pilih warna badge per tipe warning
                    $badgeColors = [
                        'PHP tanpa ekstensi' => 'bg-danger',
                        'PHP tersembunyi'    => 'bg-danger',
                        'File Manager/Shell' => 'bg-danger',
                        'Tanpa ekstensi'     => 'bg-warning text-dark',
                        'Ekstensi ganda'     => 'bg-danger',
                        'Keyword berbahaya'  => 'bg-danger',
                        'Nama sangat pendek' => 'bg-warning text-dark',
                        'Nama acak/hash'     => 'bg-warning text-dark',
                        'Nama berupa angka'  => 'bg-secondary',
                        'Karakter tidak lazim' => 'bg-warning text-dark',
                    ];
                    $warnTags = implode(' ', array_map(
                        fn($w) => '<span class="badge ' . ($badgeColors[$w] ?? 'bg-secondary') . ' ms-1 small">'
                            . '<i class="bx bx-error-circle me-1"></i>' . e($w) . '</span>',
                        array_unique($fileWarnings)
                    ));
                    $nameHtml = '<span class="fw-bold text-danger"><i class="bx bx-error-circle me-1"></i>'
                        . e($row->file_name) . '</span><br>' . $warnTags;
                } else {
                    $nameHtml = '<span class="fw-semibold">' . e($row->file_name) . '</span>';
                }

                return $nameHtml . '<br>'
                    . '<small class="text-muted"><i class="bx bx-folder-open"></i> files/2/' . e($displayPath) . '</small>';
            })
            ->addColumn('detections_count', function (FileScanner $row) {
                $patterns = $row->suspicious_patterns ?? [];
                $count    = count($patterns);
                if ($count === 0) {
                    return '<span class="text-success">0</span>';
                }
                $color = $count > 10 ? 'text-danger' : ($count > 4 ? 'text-warning' : 'text-info');
                return '<span class="' . $color . ' fw-bold">' . $count . '</span>';
            })
            ->editColumn('file_size', function (FileScanner $row) {
                return $this->formatBytes($row->file_size);
            })
            ->editColumn('scanned_at', function (FileScanner $row) {
                return $row->scanned_at ? $row->scanned_at->format('d/m/Y H:i') : '-';
            })
            ->rawColumns(['action', 'threat_badge', 'quarantine_status', 'file_info', 'detections_count'])
            ->setRowId('id')
            ->setRowClass(function (FileScanner $row) {
                return match ($row->threat_level) {
                    'critical' => 'table-danger',
                    'high'     => 'table-warning',
                    default    => '',
                };
            });
    }

    public function query(FileScanner $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('user')
            // Hanya tampilkan medium ke atas — low dan safe disembunyikan
            ->whereNotIn('threat_level', ['low', 'safe'])
            ->selectRaw('*, CASE
                WHEN threat_level = "critical" THEN 1
                WHEN threat_level = "high"     THEN 2
                WHEN threat_level = "medium"   THEN 3
                ELSE 4
            END as threat_order')
            ->orderBy('threat_order', 'asc')
            ->orderBy('scanned_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('filescanner-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->parameters(['responsive' => true, 'pageLength' => 25])
            ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::computed('file_info')
                ->title('File')
                ->width(280)
                ->orderable(false),
            Column::make('file_size')
                ->title('Ukuran')
                ->width(80),
            Column::computed('threat_badge')
                ->title('Level')
                ->width(130)
                ->orderable(true)
                ->name('threat_level'),
            Column::computed('detections_count')
                ->title('Deteksi')
                ->width(70)
                ->orderable(false),
            Column::make('threat_type')
                ->title('Keterangan')
                ->width(200)
                ->orderable(false),
            Column::computed('quarantine_status')
                ->title('Status')
                ->width(110)
                ->orderable(true)
                ->name('is_quarantined'),
            Column::make('scanned_at')
                ->title('Waktu Scan')
                ->width(130),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(90)
                ->addClass('text-center')
                ->orderable(false),
        ];
    }

    protected function filename(): string
    {
        return 'FileScanner_' . date('YmdHis');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
