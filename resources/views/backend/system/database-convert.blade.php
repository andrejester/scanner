@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">System /</span> Database Conversion
        </h4>

        <!-- Info Alert -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-info-circle"></i>
            <strong>Database Conversion Tool</strong> — Migrasi data dari database lama ke database baru per-tabel,
            dengan mapping field manual bila nama kolom berbeda.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        {{-- ===================== STEP 1: KONEKSI SUMBER ===================== --}}
        <div class="card mb-4" id="card-connection">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-plug me-1"></i> Step 1 — Koneksi Database Sumber (Lama)</h5>
                <span class="badge {{ $isConnected ? 'bg-success' : 'bg-secondary' }}" id="badge-status">
                    {{ $isConnected ? '✓ Terhubung' : 'Belum terhubung' }}
                </span>
            </div>
            <div class="card-body">
                @if ($errorMessage)
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle"></i> <strong>Koneksi gagal:</strong> {{ $errorMessage }}
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" class="form-control" id="src-host" value="{{ $sourceConfig['host'] }}"
                            placeholder="127.0.0.1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" id="src-port" value="{{ $sourceConfig['port'] }}"
                            placeholder="3306">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Database</label>
                        <input type="text" class="form-control" id="src-database" value="{{ $sourceConfig['database'] }}"
                            placeholder="nama_database_lama">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="src-username" value="{{ $sourceConfig['username'] }}"
                            placeholder="root">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="src-password" placeholder="(kosong jika tidak ada)">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="testAndSaveConnection()">
                            <i class="bx bx-link"></i> Test & Simpan Koneksi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== STEP 2: PILIH TABEL ===================== --}}
        <div class="card mb-4" id="card-table-select" {{ !$isConnected ? 'style=opacity:.5;pointer-events:none' : '' }}>
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-table me-1"></i> Step 2 — Pilih Tabel & Mapping Field</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tabel Sumber (Lama)</label>
                        <select class="form-select" id="source-table" onchange="onSourceTableChange()">
                            <option value="">-- Pilih Tabel Sumber --</option>
                            @foreach ($sourceTables as $tbl)
                                <option value="{{ $tbl }}">{{ $tbl }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-2" id="source-count"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <i class="bx bx-right-arrow-alt bx-lg text-primary"></i>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tabel Target (Baru)</label>
                        <select class="form-select" id="target-table" onchange="onTargetTableChange()">
                            <option value="">-- Pilih Tabel Target --</option>
                            @foreach ($targetTables as $tbl)
                                <option value="{{ $tbl }}">{{ $tbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tombol Auto-Match -->
                <div class="mt-3" id="btn-auto-match-wrap" style="display:none">
                    <button class="btn btn-outline-info btn-sm" onclick="autoMatchFields()">
                        <i class="bx bx-magic-wand"></i> Auto-Match Kolom (nama sama)
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ms-2" onclick="clearAllMapping()">
                        <i class="bx bx-x"></i> Clear Mapping
                    </button>
                    <button class="btn btn-outline-warning btn-sm ms-2" onclick="previewSourceData()">
                        <i class="bx bx-show"></i> Preview Data Sumber
                    </button>
                </div>

                <!-- Tabel Mapping Field -->
                <div class="mt-4" id="mapping-section" style="display:none">
                    <h6 class="fw-bold mb-3">Mapping Kolom</h6>
                    <p class="text-muted small mb-3">
                        Kolom dari tabel sumber ada di kiri. Pilih kolom tujuan di kanan.
                        Biarkan <em>-- Skip --</em> jika kolom tidak perlu dikonversi.
                    </p>
                    <div class="table-responsive">
                        <table class="table-bordered table-sm table" id="mapping-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" id="chk-all-map" onchange="toggleAllMapping(this)"
                                            title="Aktifkan semua">
                                    </th>
                                    <th>Kolom Sumber</th>
                                    <th>Tipe</th>
                                    <th width="260">→ Kolom Target</th>
                                </tr>
                            </thead>
                            <tbody id="mapping-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== STEP 3: OPSI KONVERSI ===================== --}}
        <div class="card mb-4" id="card-options" style="display:none">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-cog me-1"></i> Step 3 — Opsi Konversi</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mode Insert</label>
                        <select class="form-select" id="convert-mode">
                            <option value="insert">INSERT — Tambahkan saja (bisa duplikat)</option>
                            <option value="upsert">UPSERT — Insert atau Update (by id)</option>
                            <option value="replace">REPLACE — Replace jika primary key sama</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Batch Size</label>
                        <input type="number" class="form-control" id="batch-size" value="500" min="1"
                            max="5000">
                        <small class="text-muted">Record per batch</small>
                    </div>
                    <div class="col-md-5 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="truncate-first" value="1">
                            <label class="form-check-label text-danger fw-semibold" for="truncate-first">
                                <i class="bx bx-trash"></i> Hapus semua data target sebelum konversi
                            </label>
                            <div class="text-muted small">Hati-hati: data target akan dihapus permanen</div>
                        </div>
                    </div>
                </div>

                <hr>

                <button class="btn btn-success btn-lg px-5" onclick="startConversion()">
                    <i class="bx bx-transfer"></i> Mulai Konversi
                </button>
            </div>
        </div>

        {{-- ===================== STEP 4: HASIL ===================== --}}
        <div class="card mb-4" id="card-result" style="display:none">
            <div class="card-header">
                <h5 class="mb-0"><i class="bx bx-check-double me-1"></i> Step 4 — Hasil Konversi</h5>
            </div>
            <div class="card-body" id="result-body">
            </div>
        </div>

        {{-- ===================== PREVIEW MODAL ===================== --}}
        <div class="modal fade" id="previewModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Preview Data Sumber</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="preview-modal-body">
                        <div class="py-4 text-center"><span class="spinner-border"></span></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('addon_js')
    <script>
        // ─── State ────────────────────────────────────────────────────────────────
        let sourceColumns = [];
        let targetColumns = [];

        // ─── Koneksi ─────────────────────────────────────────────────────────────
        function testAndSaveConnection() {
            const payload = {
                _token: '{{ csrf_token() }}',
                host: $('#src-host').val(),
                port: $('#src-port').val(),
                database: $('#src-database').val(),
                username: $('#src-username').val(),
                password: $('#src-password').val(),
            };

            if (!payload.database) {
                Swal.fire('Peringatan', 'Nama database tidak boleh kosong.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Testing koneksi...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post('{{ route('system.database-convert.save-config') }}', payload)
                .done(function(res) {
                    Swal.close();
                    if (res.success) {
                        $('#badge-status').removeClass('bg-secondary').addClass('bg-success').text('✓ Terhubung');

                        // Isi dropdown tabel sumber
                        let opts = '<option value="">-- Pilih Tabel Sumber --</option>';
                        res.tables.forEach(t => {
                            opts += `<option value="${t}">${t}</option>`;
                        });
                        $('#source-table').html(opts);

                        // Aktifkan card step 2
                        $('#card-table-select').removeAttr('style');

                        Swal.fire('Berhasil', res.message, 'success');
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                })
                .fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Koneksi gagal.', 'error');
                });
        }

        // ─── Pilih tabel sumber ───────────────────────────────────────────────────
        function onSourceTableChange() {
            const table = $('#source-table').val();
            sourceColumns = [];
            resetMappingUI();

            if (!table) return;

            $.post('{{ route('system.database-convert.source-columns') }}', {
                    _token: '{{ csrf_token() }}',
                    table
                })
                .done(function(res) {
                    if (!res.success) {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }
                    sourceColumns = res.columns;

                    // Tampilkan jumlah record
                    $.post('{{ route('system.database-convert.preview-source') }}', {
                            _token: '{{ csrf_token() }}',
                            table
                        })
                        .done(function(pr) {
                            if (pr.success) {
                                $('#source-count').html(
                                    `<i class="bx bx-data"></i> <strong>${pr.count.toLocaleString()}</strong> record`
                                );
                            }
                        });

                    tryBuildMapping();
                })
                .fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal ambil kolom sumber.', 'error');
                });
        }

        // ─── Pilih tabel target ───────────────────────────────────────────────────
        function onTargetTableChange() {
            const table = $('#target-table').val();
            targetColumns = [];
            resetMappingUI();

            if (!table) return;

            $.post('{{ route('system.database-convert.target-columns') }}', {
                    _token: '{{ csrf_token() }}',
                    table
                })
                .done(function(res) {
                    if (!res.success) {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }
                    targetColumns = res.columns;
                    tryBuildMapping();
                })
                .fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal ambil kolom target.', 'error');
                });
        }

        // ─── Build tabel mapping ───────────────────────────────────────────────────
        function tryBuildMapping() {
            if (sourceColumns.length === 0 || targetColumns.length === 0) return;

            $('#btn-auto-match-wrap').show();
            $('#mapping-section').show();
            $('#card-options').show();

            const tbody = $('#mapping-body');
            tbody.empty();

            const targetOpts = buildTargetOptions();

            sourceColumns.forEach(function(col) {
                const autoMatch = targetColumns.find(t => t.field.toLowerCase() === col.field.toLowerCase());
                const selected = autoMatch ? autoMatch.field : '';
                const nullBadge = col.null === 'YES' ?
                    '<span class="badge bg-label-secondary ms-1">NULL</span>' :
                    '<span class="badge bg-label-danger ms-1">NOT NULL</span>';
                const keyBadge = col.key === 'PRI' ?
                    '<span class="badge bg-label-warning ms-1">PK</span>' :
                    '';

                tbody.append(`
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="map-chk" data-source="${col.field}" ${selected ? 'checked' : ''}>
                        </td>
                        <td>
                            <code>${col.field}</code>
                            ${nullBadge}${keyBadge}
                        </td>
                        <td><small class="text-muted">${col.type}</small></td>
                        <td>
                            <select class="form-select form-select-sm map-target" data-source="${col.field}">
                                ${buildTargetOptions(selected)}
                            </select>
                        </td>
                    </tr>
                `);
            });

            // Sinkron checkbox & select
            $(document).off('change', '.map-chk').on('change', '.map-chk', function() {
                const src = $(this).data('source');
                if (!$(this).is(':checked')) {
                    $(`.map-target[data-source="${src}"]`).val('');
                }
            });
            $(document).off('change', '.map-target').on('change', '.map-target', function() {
                const src = $(this).data('source');
                const chk = $(`.map-chk[data-source="${src}"]`);
                if ($(this).val()) {
                    chk.prop('checked', true);
                } else {
                    chk.prop('checked', false);
                }
            });
        }

        function buildTargetOptions(selected = '') {
            let opts = '<option value="">-- Skip --</option>';
            targetColumns.forEach(function(col) {
                const sel = col.field === selected ? 'selected' : '';
                opts += `<option value="${col.field}" ${sel}>${col.field} (${col.type})</option>`;
            });
            return opts;
        }

        // ─── Auto-match kolom ─────────────────────────────────────────────────────
        function autoMatchFields() {
            sourceColumns.forEach(function(col) {
                const match = targetColumns.find(t => t.field.toLowerCase() === col.field.toLowerCase());
                const sel = $(`.map-target[data-source="${col.field}"]`);
                const chk = $(`.map-chk[data-source="${col.field}"]`);
                if (match) {
                    sel.val(match.field);
                    chk.prop('checked', true);
                } else {
                    sel.val('');
                    chk.prop('checked', false);
                }
            });
        }

        function clearAllMapping() {
            $('.map-target').val('');
            $('.map-chk').prop('checked', false);
            $('#chk-all-map').prop('checked', false);
        }

        function toggleAllMapping(cb) {
            $('.map-chk').prop('checked', cb.checked);
            if (!cb.checked) {
                $('.map-target').val('');
            } else {
                // restore auto-match
                autoMatchFields();
            }
        }

        function resetMappingUI() {
            $('#mapping-section').hide();
            $('#btn-auto-match-wrap').hide();
            $('#card-options').hide();
            $('#card-result').hide();
            $('#mapping-body').empty();
        }

        // ─── Preview data sumber ──────────────────────────────────────────────────
        function previewSourceData() {
            const table = $('#source-table').val();
            if (!table) return;

            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            $('#preview-modal-body').html('<div class="text-center py-4"><span class="spinner-border"></span></div>');
            modal.show();

            $.post('{{ route('system.database-convert.preview-source') }}', {
                    _token: '{{ csrf_token() }}',
                    table
                })
                .done(function(res) {
                    if (!res.success) {
                        $('#preview-modal-body').html(`<div class="alert alert-danger">${res.message}</div>`);
                        return;
                    }

                    if (res.data.length === 0) {
                        $('#preview-modal-body').html('<div class="alert alert-info">Tidak ada data.</div>');
                        return;
                    }

                    const cols = Object.keys(res.data[0]);
                    let html =
                        `<p class="text-muted">Total record: <strong>${res.count.toLocaleString()}</strong> — Menampilkan 5 pertama</p>`;
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr>';
                    cols.forEach(c => {
                        html += `<th>${c}</th>`;
                    });
                    html += '</tr></thead><tbody>';

                    res.data.forEach(function(row) {
                        html += '<tr>';
                        cols.forEach(c => {
                            const val = row[c] !== null ? String(row[c]).substring(0, 80) :
                                '<em class="text-muted">NULL</em>';
                            html += `<td>${val}</td>`;
                        });
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#preview-modal-body').html(html);
                })
                .fail(function() {
                    $('#preview-modal-body').html('<div class="alert alert-danger">Gagal mengambil data.</div>');
                });
        }

        // ─── Mulai konversi ───────────────────────────────────────────────────────
        function startConversion() {
            const sourceTable = $('#source-table').val();
            const targetTable = $('#target-table').val();

            if (!sourceTable || !targetTable) {
                Swal.fire('Peringatan', 'Pilih tabel sumber dan tabel target.', 'warning');
                return;
            }

            // Kumpulkan mapping
            const fieldMapping = {};
            $('.map-chk:checked').each(function() {
                const src = $(this).data('source');
                const tgt = $(`.map-target[data-source="${src}"]`).val();
                if (tgt) fieldMapping[src] = tgt;
            });

            if (Object.keys(fieldMapping).length === 0) {
                Swal.fire('Peringatan', 'Tidak ada kolom yang dipetakan. Centang minimal satu kolom.', 'warning');
                return;
            }

            const mode = $('#convert-mode').val();
            const batchSize = $('#batch-size').val();
            const truncateFirst = $('#truncate-first').is(':checked') ? 1 : 0;

            // Summary konfirmasi
            let mappingList = '';
            for (const [src, tgt] of Object.entries(fieldMapping)) {
                mappingList += `<tr><td><code>${src}</code></td><td>→</td><td><code>${tgt}</code></td></tr>`;
            }

            Swal.fire({
                title: 'Konfirmasi Konversi',
                html: `
                    <div class="text-start">
                        <table class="table table-sm">
                            <tr><th>Sumber</th><td><code>${sourceTable}</code></td></tr>
                            <tr><th>Target</th><td><code>${targetTable}</code></td></tr>
                            <tr><th>Mode</th><td><strong>${mode.toUpperCase()}</strong></td></tr>
                            <tr><th>Batch</th><td>${batchSize} record/batch</td></tr>
                            ${truncateFirst ? '<tr><td colspan="2" class="text-danger fw-bold">⚠ Data target akan dihapus dulu!</td></tr>' : ''}
                        </table>
                        <details class="mt-2">
                            <summary class="text-muted small">Lihat mapping (${Object.keys(fieldMapping).length} kolom)</summary>
                            <table class="table table-sm mt-2">${mappingList}</table>
                        </details>
                    </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Ya, Konversi!',
                cancelButtonText: 'Batal',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Sedang konversi...',
                    html: 'Proses sedang berjalan, mohon tunggu...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Build form data — field_mapping sebagai array asosiatif
                const postData = {
                    _token: '{{ csrf_token() }}',
                    source_table: sourceTable,
                    target_table: targetTable,
                    mode: mode,
                    batch_size: batchSize,
                    truncate_first: truncateFirst,
                };

                // Kirim field_mapping sebagai field_mapping[src]=tgt
                for (const [src, tgt] of Object.entries(fieldMapping)) {
                    postData[`field_mapping[${src}]`] = tgt;
                }

                $.post('{{ route('system.database-convert.convert') }}', postData)
                    .done(function(res) {
                        Swal.close();
                        showResult(res, sourceTable, targetTable);
                    })
                    .fail(function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    });
            });
        }

        // ─── Tampilkan hasil ──────────────────────────────────────────────────────
        function showResult(res, sourceTable, targetTable) {
            const card = $('#card-result');
            const body = $('#result-body');

            card.show();

            if (!res.success) {
                body.html(`<div class="alert alert-danger"><i class="bx bx-error-circle"></i> ${res.message}</div>`);
                return;
            }

            const successRate = res.total > 0 ?
                Math.round(((res.inserted + res.updated) / res.total) * 100) :
                0;

            let html = `
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body py-3">
                                <h3 class="text-success mb-0">${res.inserted.toLocaleString()}</h3>
                                <small class="text-muted">Inserted</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body py-3">
                                <h3 class="text-info mb-0">${res.updated.toLocaleString()}</h3>
                                <small class="text-muted">Updated</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body py-3">
                                <h3 class="text-warning mb-0">${res.skipped.toLocaleString()}</h3>
                                <small class="text-muted">Skipped / Error</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-primary">
                            <div class="card-body py-3">
                                <h3 class="text-primary mb-0">${res.total.toLocaleString()}</h3>
                                <small class="text-muted">Total Record Sumber</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Success rate</small>
                        <small>${successRate}%</small>
                    </div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar bg-success" style="width:${successRate}%"></div>
                    </div>
                </div>

                <div class="alert ${res.errors && res.errors.length > 0 ? 'alert-warning' : 'alert-success'}">
                    <i class="bx bx-check-circle"></i>
                    ${res.message} — <strong>${sourceTable}</strong> → <strong>${targetTable}</strong>
                </div>
            `;

            if (res.errors && res.errors.length > 0) {
                html += `
                    <details>
                        <summary class="text-warning fw-semibold">
                            <i class="bx bx-error-alt"></i> ${res.errors.length} error (maks 20 ditampilkan)
                        </summary>
                        <ul class="mt-2 small text-danger">`;
                res.errors.forEach(e => {
                    html += `<li>${e}</li>`;
                });
                html += `</ul></details>`;
            }

            body.html(html);
            $('html, body').animate({
                scrollTop: card.offset().top - 80
            }, 500);
        }
    </script>
@endsection
