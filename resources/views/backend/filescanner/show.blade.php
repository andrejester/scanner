@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">
                <i class="bx bx-file-find"></i> Detail Hasil Scan
            </h4>
            <a href="{{ route('filescanner.index') }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        @php
            $levelMap = [
                'critical' => ['danger', 'Sangat Berbahaya', 'bx-error'],
                'high' => ['warning', 'Mencurigakan', 'bx-error-circle'],
                'medium' => ['info', 'Perlu Ditinjau', 'bx-info-circle'],
                'low' => ['secondary', 'Aman Bersyarat', 'bx-minus-circle'],
                'safe' => ['success', 'Bersih', 'bx-check-circle'],
            ];
            [$levelColor, $levelLabel, $levelIcon] = $levelMap[$scan->threat_level] ?? [
                'secondary',
                'Unknown',
                'bx-question-mark',
            ];
            $patternCount = count($scan->suspicious_patterns ?? []);
        @endphp

        <!-- ── Skor & Threat Level ── -->
        <div class="alert alert-{{ $levelColor }} d-flex align-items-center mb-4 gap-3">
            <i class="bx {{ $levelIcon }} fs-2"></i>
            <div>
                <strong>{{ strtoupper($scan->threat_level) }} — {{ $levelLabel }}</strong><br>
                <span>{{ $scan->threat_type }}</span>
            </div>
            @if ($scan->is_quarantined)
                <span class="badge bg-danger ms-auto"><i class="bx bx-lock-alt"></i> Karantina</span>
            @endif
        </div>

        <div class="row">
            <!-- ── Informasi File ── -->
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="fw-bold text-primary m-0"><i class="bx bx-info-circle me-1"></i>Informasi File</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table-sm table-borderless mb-0 table">
                            <tr>
                                <th class="ps-3" width="150">Nama File</th>
                                <td>{{ $scan->file_name }}</td>
                            </tr>
                            <tr>
                                <th class="ps-3">Path</th>
                                <td><code class="small">{{ $scan->file_path }}</code></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Ukuran</th>
                                <td>{{ number_format($scan->file_size / 1024, 2) }} KB</td>
                            </tr>
                            <tr>
                                <th class="ps-3">Hash (SHA-256)</th>
                                <td><code class="small text-break">{{ $scan->file_hash }}</code></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Waktu Scan</th>
                                <td>{{ $scan->scanned_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-3">Di-scan oleh</th>
                                <td>{{ $scan->user->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-3">Status</th>
                                <td>
                                    @if ($scan->is_quarantined)
                                        <span class="badge bg-danger"><i class="bx bx-lock-alt"></i> Karantina</span>
                                    @else
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        @if (!$scan->is_quarantined)
                            <form action="{{ route('filescanner.quarantine', $scan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning"
                                    onclick="return confirm('Karantina file ini?')">
                                    <i class="bx bx-lock-alt"></i> Karantina
                                </button>
                            </form>
                        @else
                            <form action="{{ route('filescanner.restore', $scan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"
                                    onclick="return confirm('Pulihkan file ini?')">
                                    <i class="bx bx-lock-open-alt"></i> Pulihkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ── Ringkasan Deteksi ── -->
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="fw-bold text-{{ $levelColor }} m-0">
                            <i class="bx bx-shield-quarter me-1"></i>
                            Ringkasan — {{ $patternCount }} Deteksi
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($patternCount === 0)
                            <div class="alert alert-success mb-0">
                                <i class="bx bx-check-circle"></i> Tidak ada pola mencurigakan.
                            </div>
                        @else
                            {{-- Kategori Skor --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Distribusi Kategori Deteksi</span>
                                    <strong>{{ $patternCount }} total</strong>
                                </div>
                                @foreach ($pattern_groups as $group => $items)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-label-secondary text-start">{{ $group }}</span>
                                        <span class="badge bg-danger rounded-pill">{{ count($items) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-{{ $levelColor }} small mb-0">
                                <i class="bx bx-error-circle"></i>
                                File mengandung pola yang sering digunakan dalam malware / webshell.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Detail Deteksi per Kategori ── -->
        @if ($patternCount > 0)
            <div class="card mb-4 shadow-sm">
                <div class="card-header py-3">
                    <h6 class="fw-bold text-danger m-0">
                        <i class="bx bx-bug me-1"></i> Detail Pola Mencurigakan ({{ $patternCount }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle"></i>
                        <strong>Peringatan:</strong> Jangan menjalankan atau mengeksekusi kode dari file yang mencurigakan.
                    </div>

                    <div class="accordion" id="detectionAccordion">
                        @foreach ($pattern_groups as $group => $items)
                            @php
                                $groupColors = [
                                    'Sig' => 'danger',
                                    'Comb' => 'danger',
                                    'Super' => 'warning',
                                    'Obf' => 'warning',
                                    'Enc' => 'warning',
                                    'Hex' => 'warning',
                                    'LongLine' => 'info',
                                    'Var' => 'info',
                                    'Dyn' => 'warning',
                                    'Inc' => 'danger',
                                    'Upload' => 'warning',
                                    'ImgShell' => 'danger',
                                    'FakeImg' => 'danger',
                                    'SusFile' => 'warning',
                                    'Perm' => 'info',
                                    'Recent' => 'secondary',
                                    'Integrity' => 'danger',
                                    'Entropy' => 'warning',
                                    'YARA' => 'danger',
                                    'IOC' => 'danger',
                                ];
                                $gc = $groupColors[$group] ?? 'secondary';
                                $groupId = 'group_' . Str::slug($group);
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#{{ $groupId }}">
                                        <span class="badge bg-{{ $gc }} me-2">{{ $group }}</span>
                                        <span class="fw-semibold">{{ count($items) }} deteksi</span>
                                    </button>
                                </h2>
                                <div id="{{ $groupId }}" class="accordion-collapse collapse"
                                    data-bs-parent="#detectionAccordion">
                                    <div class="accordion-body p-0">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($items as $item)
                                                <li class="list-group-item">
                                                    <i class="bx bx-bug text-{{ $gc }}"></i>
                                                    {{ $item }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- ── Konten File ── -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold text-primary m-0">
                    <i class="bx bx-code-alt me-1"></i> Konten File
                </h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyContent()">
                    <i class="bx bx-copy"></i> Salin
                </button>
            </div>
            <div class="card-body p-0">
                <div class="alert alert-warning rounded-0 small mb-0 border-0 px-3 py-2">
                    <i class="bx bx-error-circle"></i>
                    <strong>Perhatian:</strong> Jangan jalankan kode dari file mencurigakan.
                </div>
                <pre id="fileContent" class="bg-dark text-light mb-0 p-3" style="max-height:500px;overflow-y:auto;font-size:0.8rem;"><code>{{ $file_content }}</code></pre>
            </div>
        </div>

    </div>
@endsection

@section('addon_js')
    <script>
        function copyContent() {
            const text = document.getElementById('fileContent').innerText;
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Disalin',
                    timer: 1000,
                    showConfirmButton: false
                });
            });
        }
    </script>
@endsection
