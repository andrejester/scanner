@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> File Scanner
            <small class="text-muted fs-6">— Deteksi Malware &amp; Webshell (22 Kategori)</small>
        </h4>

        <div class="d-flex mb-4 gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scanModal">
                <i class="bx bx-search-alt"></i> Mulai Scan
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="reloadTable()">
                <i class="bx bx-refresh"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="clearAllData()">
                <i class="bx bx-trash"></i> Clear Data
            </button>
        </div>

        <!-- ── Statistik Ancaman ── -->
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-primary rounded"><i
                                    class="bx bx-file-find bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">Total Scan</small>
                            <h4 class="mb-0">{{ $total_scans }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-danger rounded"><i class="bx bx-error bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">Critical <span class="text-muted">&gt;100</span></small>
                            <h4 class="text-danger mb-0">{{ $critical_threats }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-warning rounded"><i
                                    class="bx bx-error-circle bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">High <span class="text-muted">51–100</span></small>
                            <h4 class="text-warning mb-0">{{ $high_threats }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-info rounded"><i
                                    class="bx bx-info-circle bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">Medium <span class="text-muted">21–50</span></small>
                            <h4 class="text-info mb-0">{{ $medium_threats }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-secondary rounded"><i
                                    class="bx bx-minus-circle bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">Low <span class="text-muted">1–20</span></small>
                            <h4 class="text-secondary mb-0">{{ $low_threats }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar flex-shrink-0">
                            <span class="avatar-initial bg-label-danger rounded"><i class="bx bx-lock-alt bx-sm"></i></span>
                        </span>
                        <div>
                            <small class="text-muted d-block">Karantina</small>
                            <h4 class="text-danger mb-0">{{ $quarantined }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Panduan Risiko ── -->
        <div class="alert alert-light mb-4 border p-3">
            <strong><i class="bx bx-shield-quarter text-primary"></i> Sistem Skor Risiko:</strong>
            <span class="ms-2">
                <span class="badge bg-success">0–20: Aman</span>
                <span class="badge bg-secondary ms-1">21–50: Perlu Ditinjau</span>
                <span class="badge bg-warning ms-1">51–100: Mencurigakan</span>
                <span class="badge bg-danger ms-1">&gt;100: Sangat Berbahaya</span>
            </span>
            <span class="text-muted small ms-3">22 kategori deteksi aktif</span>
        </div>

        <!-- ── DataTable ── -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="fw-bold text-primary m-0">
                        <i class="bx bx-table me-1"></i>Hasil Scan File
                    </h6>
                    <small class="text-muted">
                        Hanya menampilkan <span class="badge bg-danger">Critical</span>
                        <span class="badge bg-warning text-dark">High</span>
                        <span class="badge bg-info">Medium</span>
                        — level Low &amp; Safe disembunyikan
                        @if ($low_threats + $safe_files > 0)
                            <span class="text-muted ms-1">({{ $low_threats + $safe_files }} file tersembunyi)</span>
                        @endif
                    </small>
                </div>
            </div>
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table table-bordered table-hover table-sm']) }}
            </div>
        </div>
    </div>

    <!-- ══════════════════ SCAN MODAL ══════════════════ -->
    <div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('filescanner.scan') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="scanModalLabel">
                            <i class="bx bx-search-alt"></i> Konfigurasi Scan — 22 Kategori Deteksi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Info lokasi tetap --}}
                        <div class="alert alert-info mb-3 py-2">
                            <i class="bx bx-folder-open"></i>
                            <strong>Target Scan:</strong>
                            <code>storage/app/public/files/2/</code>
                            <span class="text-muted small ms-1">— semua file di folder ini akan di-scan</span>
                        </div>

                        <div class="row g-3">
                            <!-- Subfolder (opsional) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Subfolder <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted small">files/2/</span>
                                    <input type="text" class="form-control" name="scan_path" id="scan_path"
                                        list="folderList" placeholder="Ketik prefix atau pilih dari daftar..."
                                        value="." autocomplete="off">
                                </div>
                                <datalist id="folderList"></datalist>
                                <div id="folderHint" class="small text-muted d-none mt-1">
                                    <i class="bx bx-folder-open text-success"></i>
                                    <span id="folderHintText"></span>
                                </div>
                                <div id="folderNotFound" class="small text-danger d-none mt-1">
                                    <i class="bx bx-x-circle"></i> Folder tidak ditemukan
                                </div>
                                <small class="text-muted">
                                    Gunakan prefix, misal <code>assist-bpr.net</code> untuk folder
                                    <code>assist-bpr.net_20260702_103416</code>.
                                    Kosongkan / titik untuk scan semua.
                                </small>
                            </div>

                            <!-- Depth -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kedalaman Subfolder</label>
                                <select class="form-select" name="scan_depth">
                                    <option value="2">Level 2</option>
                                    <option value="5">Level 5</option>
                                    <option value="10" selected>Level 10 — Semua (Rekomendasi)</option>
                                    <option value="20">Level 20 — Sangat Dalam</option>
                                </select>
                            </div>

                            <!-- Filter ekstensi -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Filter Ekstensi <span
                                        class="text-muted fw-normal">(opsional — biarkan tidak dicentang untuk scan semua
                                        tipe file)</span></label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach (['php', 'phtml', 'phar', 'js', 'html', 'htm', 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'] as $ext)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="scan_extensions[]"
                                                value="{{ $ext }}" id="ext_{{ $ext }}">
                                            <label class="form-check-label"
                                                for="ext_{{ $ext }}">.{{ $ext }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Tidak ada yang dicentang = scan <strong>semua</strong> tipe
                                    file</small>
                            </div>
                        </div>

                        <!-- Kategori aktif -->
                        <hr>
                        <p class="fw-semibold mb-2"><i class="bx bx-shield-quarter text-primary"></i> Kategori Deteksi
                            Aktif (22)</p>
                        <div class="row g-1 small">
                            @foreach (['1. Signature Scanner', '2. Dangerous Combination', '3. Superglobal Input', '4. Obfuscation', '5. Encoded String', '6. Hex String', '7. Very Long Line', '8. Suspicious Variable', '9. Dynamic Function Call', '10. Dynamic Include', '11. Remote Include', '12. Hidden Upload', '13. Image Shell', '14. Fake Image', '15. Suspicious Filename', '16. Permission Scanner', '17. Recently Modified', '18. Integrity Check', '19. Entropy Scanner', '20. YARA Rules', '21. IOC Scanner', '22. Malware Scoring'] as $cat)
                                <div class="col-md-4">
                                    <span class="badge bg-label-primary w-100 text-start">{{ $cat }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-warning mb-0 mt-3">
                            <i class="bx bx-error"></i> Semakin banyak file, semakin lama proses scan. Pastikan folder
                            <code>files/2</code> tidak kosong.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search-alt"></i> Mulai Scan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    {!! $dataTable->scripts() !!}
    <script>
        function reloadTable() {
            $('#filescanner-table').DataTable().ajax.reload();
        }

        // ── Folder list & prefix resolver ──────────────────────────────────
        let folderData = [];

        function loadFolders() {
            $.getJSON('{{ route('filescanner.folders') }}', function(data) {
                folderData = data;
                const dl = document.getElementById('folderList');
                dl.innerHTML = '';
                // Titik = scan semua
                const optAll = document.createElement('option');
                optAll.value = '.';
                optAll.label = 'Scan semua folder';
                dl.appendChild(optAll);
                // Satu option per folder: value = prefix, label = nama lengkap
                data.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.prefix;
                    opt.label = f.name;
                    dl.appendChild(opt);
                    // Juga tambahkan nama lengkap sebagai option
                    if (f.prefix !== f.name) {
                        const optFull = document.createElement('option');
                        optFull.value = f.name;
                        optFull.label = f.name + ' (nama lengkap)';
                        dl.appendChild(optFull);
                    }
                });
            });
        }

        // Resolve prefix saat user mengetik → tampilkan hint nama folder lengkap
        $('#scan_path').on('input', function() {
            const val = $(this).val().trim();
            const hint = $('#folderHint');
            const notFound = $('#folderNotFound');
            hint.addClass('d-none');
            notFound.addClass('d-none');

            if (!val || val === '.') return;

            // Cari match prefix (case-insensitive)
            const match = folderData.find(f =>
                f.name.toLowerCase().startsWith(val.toLowerCase()) ||
                f.prefix.toLowerCase() === val.toLowerCase()
            );
            if (match) {
                $('#folderHintText').text('→ ' + match.name);
                hint.removeClass('d-none');
            } else {
                // Cek apakah persis nama folder
                const exact = folderData.find(f => f.name === val);
                if (!exact) notFound.removeClass('d-none');
            }
        });

        // Load folder list saat modal dibuka
        document.getElementById('scanModal').addEventListener('show.bs.modal', function() {
            loadFolders();
        });

        function clearAllData() {
            Swal.fire({
                title: 'Clear semua data scan?',
                html: 'Semua hasil scan akan dihapus.<br><small class="text-muted">Data karantina tidak ikut terhapus.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, clear',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route('filescanner.clearAll') }}',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        $('#filescanner-table').DataTable().ajax.reload();
                        // Reload halaman setelah sebentar agar statistik ikut ter-update
                        setTimeout(() => location.reload(), 2100);
                    },
                    error: () => Swal.fire('Error', 'Gagal menghapus data.', 'error'),
                });
            });
        }

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Hapus log ini?',
                text: 'Data tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, hapus'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/backend/filescanner/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: () => {
                            Swal.fire('Terhapus', 'Log berhasil dihapus.', 'success');
                            $('#filescanner-table').DataTable().ajax.reload();
                        },
                        error: () => Swal.fire('Error', 'Gagal menghapus.', 'error')
                    });
                }
            });
        });
    </script>
@endsection
