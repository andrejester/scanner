<div class="card-datatable text-nowrap">
    @extends('layouts.app')
    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Alumni /</span> Pendaftaran Alumni
        </h4>

        @include('utils.modal')

        <div class="card">
            <h5 class="card-header">Daftar Pendaftaran Alumni</h5>
            <div class="card-body">
                <!-- Filter Status -->
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">Filter Status:</label>
                    <select id="statusFilter" class="form-select" style="width: 200px;">
                        <option value="">Semua Status</option>
                        <option value="Belum Diproses">Belum Diproses</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
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
            function updateStatus(id, status) {
                const statusText = status == 1 ? 'menyetujui' : 'menolak';
                const statusAction = status == 1 ? 'disetujui dan dipindahkan ke database alumni' : 'ditolak';

                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Apakah Anda yakin ingin ${statusText} pendaftaran alumni ini?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: status == 1 ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, ' + statusText + '!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/alumnitmp/${id}/status`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // Reload DataTable
                                $('#alumnitmp-table').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan saat memproses data.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            }

            // Filter by status
            $(document).ready(function() {
                $('#statusFilter').on('change', function() {
                    const table = $('#alumnitmp-table').DataTable();
                    table.column(7).search(this.value).draw(); // Column 7 is status
                });
            });
        </script>
    @endsection
</div>
