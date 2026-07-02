@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Home /</span> Banner Iklan
    </h4>

    @include('utils.modal')

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 flex-shrink-0">
                            <span class="avatar-initial bg-label-primary rounded">
                                <i class="bx bx-image-add"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Banner</small>
                            <h5 class="mb-0">{{ $totalBanners ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 flex-shrink-0">
                            <span class="avatar-initial bg-label-success rounded">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Banner Aktif</small>
                            <h5 class="mb-0">{{ $activeBanners ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 flex-shrink-0">
                            <span class="avatar-initial bg-label-info rounded">
                                <i class="bx bx-left-arrow-alt"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Banner Kiri</small>
                            <h5 class="mb-0">{{ $leftBanners ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 flex-shrink-0">
                            <span class="avatar-initial bg-label-warning rounded">
                                <i class="bx bx-right-arrow-alt"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Banner Kanan</small>
                            <h5 class="mb-0">{{ $rightBanners ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 flex-shrink-0">
                            <span class="avatar-initial bg-label-danger rounded">
                                <i class="bx bx-image"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Atas Logo</small>
                            <h5 class="mb-0">{{ $aboveLogoBanners ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('banner_write')
        <a class="btn btn-primary btn-sm mb-1" href="{{ route('banner-ads.create') }}">
            <i class="bx bx-plus"></i> Tambah Banner Iklan
        </a>
    @endcan

    <div class="card">
        <h5 class="card-header">Daftar Banner Iklan (Di Atas Footer)</h5>
        <div class="card-body">
            <p class="text-muted mb-3">
                <i class="bx bx-info-circle"></i> Banner iklan akan ditampilkan di atas footer dengan dua kolom (kiri
                dan kanan). Ukuran rekomendasi: 600 x 300 px
            </p>

            <!-- Filter Section -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Filter Posisi</label>
                    <select class="form-select" id="filter-position">
                        <option value="">Semua Posisi</option>
                        <option value="top">Atas</option>
                        <option value="above_logo">Atas Logo</option>
                        <option value="left">Kiri</option>
                        <option value="right">Kanan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select class="form-select" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary" id="btn-filter">
                            <i class="bx bx-filter"></i> Terapkan Filter
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-reset">
                            <i class="bx bx-reset"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <div class="table-responsive">
                {!! $dataTable->table() !!}
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function() {
            var table = $('#banner-ads-table').DataTable();

            // Apply filter
            $('#btn-filter').on('click', function() {
                var position = $('#filter-position').val();
                var status = $('#filter-status').val();

                // Filter by position (column 3)
                if (position) {
                    table.column(3).search(position);
                } else {
                    table.column(3).search('');
                }

                // Filter by status (column 6)
                if (status !== '') {
                    var statusText = status === '1' ? 'Aktif' : 'Tidak Aktif';
                    table.column(6).search(statusText);
                } else {
                    table.column(6).search('');
                }

                table.draw();
            });

            // Reset filter
            $('#btn-reset').on('click', function() {
                $('#filter-position').val('');
                $('#filter-status').val('');
                table.columns().search('').draw();
            });

            // Search on enter key
            $('#banner-ads-table_filter input').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    table.search(this.value).draw();
                }
            });
        });
    </script>
@endsection
