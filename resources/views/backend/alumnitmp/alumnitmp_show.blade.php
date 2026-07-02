@extends('layouts.app')

@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">Pendaftaran Alumni /</span> Detail
    </h4>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Pendaftaran Alumni</h5>
                    <a href="{{ route('alumnitmp.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Foto -->
                        <div class="col-md-3 mb-4 text-center">
                            @if ($alumni->foto)
                                <img src="{{ asset('storage/files/2/' . $alumni->foto) }}" alt="Foto {{ $alumni->nama }}"
                                    class="img-fluid rounded shadow" style="max-width: 250px;">
                            @else
                                <div class="bg-light rounded p-5">
                                    <i class="bx bx-user display-1 text-muted"></i>
                                    <p class="text-muted mt-2">Tidak ada foto</p>
                                </div>
                            @endif
                        </div>

                        <!-- Data Alumni -->
                        <div class="col-md-9">
                            <table class="table-bordered table">
                                <tr>
                                    <th width="200">Status</th>
                                    <td>
                                        @if ($alumni->status == 0)
                                            <span class="badge bg-warning">Belum Diproses</span>
                                        @elseif($alumni->status == 1)
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <td>{{ $alumni->nama }}</td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td>{{ $alumni->nim }}</td>
                                </tr>
                                <tr>
                                    <th>Jenjang</th>
                                    <td>{{ strtoupper($alumni->jenjang) }}</td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td>{{ $alumni->program_studi }}</td>
                                </tr>
                                <tr>
                                    <th>Tahun Lulus</th>
                                    <td>{{ $alumni->tahun_lulus }}</td>
                                </tr>
                                <tr>
                                    <th>Pekerjaan</th>
                                    <td>{{ $alumni->pekerjaan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Instansi</th>
                                    <td>{{ $alumni->instansi ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Testimoni</th>
                                    <td>{{ $alumni->testimoni ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Daftar</th>
                                    <td>{{ $alumni->created_at->format('d F Y, H:i') }} WIB</td>
                                </tr>
                            </table>

                            <!-- Action Buttons -->
                            @if ($alumni->status == 0)
                                <div class="mt-4">
                                    @can('masteralumni_update')
                                        <button type="button" class="btn btn-success"
                                            onclick="updateStatus({{ $alumni->id }}, 1)">
                                            <i class="bx bx-check"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-danger"
                                            onclick="updateStatus({{ $alumni->id }}, 2)">
                                            <i class="bx bx-x"></i> Tolak
                                        </button>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addon_js')
    <script>
        function updateStatus(id, status) {
            const statusText = status == 1 ? 'menyetujui' : 'menolak';

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
                            }).then(() => {
                                window.location.href = '{{ route('alumnitmp.index') }}';
                            });
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
    </script>
@endsection
