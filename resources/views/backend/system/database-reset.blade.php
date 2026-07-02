@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">System /</span> Database Reset - Truncate Tables
        </h4>

        <!-- Environment Warning -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle"></i>
            <strong>Development Mode!</strong> Fitur ini hanya tersedia di environment <code>local</code> atau
            <code>development</code>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1">Total Tables</span>
                                <h3 class="card-title mb-1">{{ count($tables) + count($protectedTables) }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial bg-label-primary rounded">
                                    <i class="bx bx-data bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1">Truncatable Tables</span>
                                <h3 class="card-title text-success mb-1">{{ count($tables) }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial bg-label-success rounded">
                                    <i class="bx bx-check-circle bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1">Protected Tables</span>
                                <h3 class="card-title text-danger mb-1">{{ count($protectedTables) }}</h3>
                            </div>
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial bg-label-danger rounded">
                                    <i class="bx bx-shield bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Quick Actions</h5>
                        <button type="button" class="btn btn-danger me-2" onclick="truncateAll()">
                            <i class="bx bx-trash"></i> Truncate All Tables
                        </button>
                        <button type="button" class="btn btn-warning me-2" onclick="selectAll()">
                            <i class="bx bx-check-square"></i> Select All
                        </button>
                        <button type="button" class="btn btn-secondary me-2" onclick="deselectAll()">
                            <i class="bx bx-square"></i> Deselect All
                        </button>
                        <button type="button" class="btn btn-primary" onclick="truncateSelected()">
                            <i class="bx bx-trash"></i> Truncate Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables List -->
        <div class="row">
            <!-- Truncatable Tables -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h6 class="font-weight-bold text-success m-0">
                            <i class="bx bx-data"></i> Truncatable Tables ({{ count($tables) }})
                        </h6>
                        <span class="badge bg-success">Can be truncated</span>
                    </div>
                    <div class="card-body">
                        @if (count($tables) > 0)
                            <div class="table-responsive">
                                <table class="table-hover table">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                            </th>
                                            <th>Table Name</th>
                                            <th width="100" class="text-center">Records</th>
                                            <th width="100" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tables as $table)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="table-checkbox"
                                                        value="{{ $table['name'] }}">
                                                </td>
                                                <td>
                                                    <code>{{ $table['name'] }}</code>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $table['count'] > 0 ? 'bg-info' : 'bg-secondary' }}">
                                                        {{ number_format($table['count']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="truncateSingle('{{ $table['name'] }}')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle"></i> Tidak ada tabel yang bisa di-truncate.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Protected Tables -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h6 class="font-weight-bold text-danger m-0">
                            <i class="bx bx-shield"></i> Protected Tables
                        </h6>
                        <span class="badge bg-danger">{{ count($protectedTables) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle"></i> Tabel-tabel ini <strong>TIDAK BISA</strong> di-truncate
                            karena berisi data sistem penting.
                        </div>
                        <ul class="list-group">
                            @foreach ($protectedTables as $table)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <code class="text-danger">{{ $table }}</code>
                                    <i class="bx bx-lock-alt text-danger"></i>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    <script>
        // Toggle all checkboxes
        function toggleAll(checkbox) {
            $('.table-checkbox').prop('checked', checkbox.checked);
        }

        // Select all checkboxes
        function selectAll() {
            $('.table-checkbox').prop('checked', true);
            $('#selectAllCheckbox').prop('checked', true);
        }

        // Deselect all checkboxes
        function deselectAll() {
            $('.table-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
        }

        // Truncate single table
        function truncateSingle(tableName) {
            Swal.fire({
                title: 'Konfirmasi Truncate',
                html: `Apakah Anda yakin ingin truncate tabel <code>${tableName}</code>?<br><br>
                       <strong class="text-danger">Semua data akan dihapus dan tidak bisa dikembalikan!</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Truncate!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performTruncate([tableName]);
                }
            });
        }

        // Truncate selected tables
        function truncateSelected() {
            const selectedTables = [];
            $('.table-checkbox:checked').each(function() {
                selectedTables.push($(this).val());
            });

            if (selectedTables.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada tabel yang dipilih',
                    text: 'Silakan pilih minimal satu tabel untuk di-truncate.'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Truncate',
                html: `Apakah Anda yakin ingin truncate <strong>${selectedTables.length}</strong> tabel?<br><br>
                       <strong class="text-danger">Semua data akan dihapus dan tidak bisa dikembalikan!</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Truncate!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performTruncate(selectedTables);
                }
            });
        }

        // Truncate all tables
        function truncateAll() {
            Swal.fire({
                title: 'Konfirmasi Truncate ALL',
                html: `<strong class="text-danger">PERINGATAN!</strong><br><br>
                       Apakah Anda yakin ingin truncate <strong>SEMUA</strong> tabel yang bisa di-truncate?<br><br>
                       <strong class="text-danger">Semua data inputan akan dihapus dan tidak bisa dikembalikan!</strong>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Truncate Semua!',
                cancelButtonText: 'Batal',
                input: 'text',
                inputPlaceholder: 'Ketik "TRUNCATE" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'TRUNCATE') {
                        return 'Ketik "TRUNCATE" untuk melanjutkan!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performTruncateAll();
                }
            });
        }

        // Perform truncate request
        function performTruncate(tables) {
            Swal.fire({
                title: 'Processing...',
                html: 'Sedang melakukan truncate tabel...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('system.database-reset.truncate') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tables: tables
                },
                success: function(response) {
                    let html = '<div class="text-start">';

                    if (response.deleted.length > 0) {
                        html += '<h6 class="text-success">✓ Berhasil dihapus:</h6>';
                        html += '<ul>';
                        response.deleted.forEach(table => {
                            html += `<li><code>${table}</code></li>`;
                        });
                        html += '</ul>';
                    }

                    if (response.failed.length > 0) {
                        html += '<h6 class="text-danger">✗ Gagal di-truncate:</h6>';
                        html += '<ul>';
                        response.failed.forEach(item => {
                            html += `<li><code>${item.table}</code>: ${item.reason}</li>`;
                        });
                        html += '</ul>';
                    }

                    html += '</div>';

                    Swal.fire({
                        icon: response.success ? 'success' : 'warning',
                        title: 'Proses Selesai',
                        html: html,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat truncate tabel.'
                    });
                }
            });
        }

        // Perform truncate all request
        function performTruncateAll() {
            Swal.fire({
                title: 'Processing...',
                html: 'Sedang melakukan truncate semua tabel...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('system.database-reset.truncate-all') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    let html = '<div class="text-start">';

                    if (response.deleted.length > 0) {
                        html += '<h6 class="text-success">✓ Berhasil di-truncate:</h6>';
                        html += '<ul>';
                        response.deleted.forEach(table => {
                            html += `<li><code>${table}</code></li>`;
                        });
                        html += '</ul>';
                    }

                    if (response.failed.length > 0) {
                        html += '<h6 class="text-danger">✗ Gagal di-truncate:</h6>';
                        html += '<ul>';
                        response.failed.forEach(item => {
                            html += `<li><code>${item.table}</code>: ${item.reason}</li>`;
                        });
                        html += '</ul>';
                    }

                    html += '</div>';

                    Swal.fire({
                        icon: response.success ? 'success' : 'warning',
                        title: 'Proses Selesai',
                        html: html,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat truncate tabel.'
                    });
                }
            });
        }
    </script>
@endsection
