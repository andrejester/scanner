@extends('layouts.app')

@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Home / Alumni /</span> Upload Kolektif
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upload Data Alumni dari CSV</h5>
                    <a href="{{ route('masteralumni.download-template') }}" class="btn btn-sm btn-success">
                        <i class="bx bx-download"></i> Download Template CSV
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-2">Petunjuk Upload:</h6>
                        <ol class="mb-0 ps-3">
                            <li>Download template CSV terlebih dahulu</li>
                            <li>Isi data alumni sesuai format template</li>
                            <li>Upload file CSV yang sudah diisi</li>
                            <li>Preview data di tabel untuk memastikan data sudah benar</li>
                            <li>Klik tombol "Proses Import" untuk menyimpan ke database</li>
                        </ol>
                        <hr>
                        <p class="mb-0"><strong>Catatan:</strong></p>
                        <ul class="mb-0 ps-3">
                            <li><strong>jenjang:</strong> isi dengan "s2" atau "s3"</li>
                            <li><strong>is_active:</strong> isi dengan "1" (aktif) atau "0" (tidak aktif)</li>
                            <li><strong>is_pinned:</strong> isi dengan "1" (ditampilkan) atau "0" (tidak ditampilkan)</li>
                        </ul>
                    </div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Pilih File CSV</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv"
                                required>
                            <div class="form-text">Format file: CSV (maksimal 10MB)</div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="bx bx-upload"></i> Upload & Preview
                        </button>
                        <a href="{{ route('masteralumni.index') }}" class="btn btn-label-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="row" id="previewSection" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Preview Data</h5>
                    <div>
                        <span class="badge bg-success me-2" id="totalValid">0 Valid</span>
                        <span class="badge bg-danger" id="totalError">0 Error</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="errorSection" class="alert alert-danger" style="display: none;">
                        <h6 class="alert-heading">Data dengan Error:</h6>
                        <div id="errorList"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table-bordered table-hover table" id="previewTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Row</th>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Jenjang</th>
                                    <th>Program Studi</th>
                                    <th>Tahun Lulus</th>
                                    <th>Pekerjaan</th>
                                    <th>Instansi</th>
                                    <th>Status</th>
                                    <th>Pinned</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="btnProcess">
                            <i class="bx bx-check-circle"></i> Proses Import
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnCancel">
                            <i class="bx bx-x"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    <script>
        $(document).ready(function() {
            // Upload and Preview
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();

                const fileInput = $('#csv_file')[0];
                if (!fileInput.files.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Silakan pilih file CSV terlebih dahulu'
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('csv_file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $('#btnUpload').prop('disabled', true).html(
                    '<i class="bx bx-loader bx-spin"></i> Memproses...');

                $.ajax({
                    url: '{{ route('masteralumni.preview-csv') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            displayPreview(response.data, response.errors);
                            $('#previewSection').slideDown();
                            $('#totalValid').text(response.total + ' Valid');
                            $('#totalError').text(response.error_count + ' Error');

                            if (response.error_count > 0) {
                                displayErrors(response.errors);
                            }

                            $('html, body').animate({
                                scrollTop: $('#previewSection').offset().top - 100
                            }, 500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan saat memproses file';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    },
                    complete: function() {
                        $('#btnUpload').prop('disabled', false).html(
                            '<i class="bx bx-upload"></i> Upload & Preview');
                    }
                });
            });

            // Display preview data in table
            function displayPreview(data, errors) {
                const tbody = $('#previewTableBody');
                tbody.empty();

                data.forEach(function(item) {
                    const row = item.data;
                    const tr = $('<tr>');

                    tr.append($('<td>').text(item.row));
                    tr.append($('<td>').text(row.nama || '-'));
                    tr.append($('<td>').text(row.nim || '-'));
                    tr.append($('<td>').text(row.jenjang ? row.jenjang.toUpperCase() : '-'));
                    tr.append($('<td>').text(row.program_studi || '-'));
                    tr.append($('<td>').text(row.tahun_lulus || '-'));
                    tr.append($('<td>').text(row.pekerjaan || '-'));
                    tr.append($('<td>').text(row.instansi || '-'));

                    const statusBadge = row.is_active == '1' ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>';
                    tr.append($('<td>').html(statusBadge));

                    const pinnedBadge = row.is_pinned == '1' ?
                        '<span class="badge bg-warning"><i class="bx bxs-star"></i></span>' :
                        '<span class="badge bg-secondary">-</span>';
                    tr.append($('<td>').html(pinnedBadge));

                    tbody.append(tr);
                });
            }

            // Display errors
            function displayErrors(errors) {
                const errorList = $('#errorList');
                errorList.empty();

                const ul = $('<ul>');
                errors.forEach(function(error) {
                    const li = $('<li>');
                    li.html('<strong>Baris ' + error.row + ':</strong> ' + error.errors.join(', '));
                    ul.append(li);
                });

                errorList.append(ul);
                $('#errorSection').slideDown();
            }

            // Process Import
            $('#btnProcess').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi Import',
                    text: 'Apakah Anda yakin ingin memproses import data ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processImport();
                    }
                });
            });

            function processImport() {
                $('#btnProcess').prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Memproses...');
                $('#btnCancel').prop('disabled', true);

                $.ajax({
                    url: '{{ route('masteralumni.process-csv') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route('masteralumni.index') }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                            $('#btnProcess').prop('disabled', false).html(
                                '<i class="bx bx-check-circle"></i> Proses Import');
                            $('#btnCancel').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan saat memproses import';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                        $('#btnProcess').prop('disabled', false).html(
                            '<i class="bx bx-check-circle"></i> Proses Import');
                        $('#btnCancel').prop('disabled', false);
                    }
                });
            }

            // Cancel
            $('#btnCancel').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin membatalkan? Data preview akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batal',
                    cancelButtonText: 'Tidak',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });
        });
    </script>
@endsection
