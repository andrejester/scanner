<div class="card-datatable text-nowrap">
    @extends('layouts.app')

    @section('addon_js')
        <script>
            function writeANote(nID, nTop, nLeft, cTgl, cMessage) {
                var cID = "anote-" + nID;
                var oBody = document.getElementById("mainFrame");

                // Cek apakah #mainFrame ada
                if (!oBody) {
                    console.error("Element with ID 'mainFrame' not found.");
                    return;
                }

                var oANote = oBody.querySelector("#" + cID);
                if (oANote === null) {
                    // Jika belum ada, buat elemen baru dengan ID tersebut
                    oANote = document.createElement("div");
                    oANote.id = cID;
                    oBody.appendChild(oANote);
                }

                // Set gaya untuk elemen catatan
                oANote.style.display = "block";
                oANote.style.position = "absolute";
                oANote.style.top = nTop + "px";
                oANote.style.left = nLeft + "px";
                oANote.style.width = "250px";
                oANote.style.height = "auto"; // Menggunakan auto untuk menyesuaikan konten
                oANote.style.background = "#fff68f";
                oANote.style.borderRadius = "8px";
                oANote.style.boxShadow = "2px 2px 10px rgba(0, 0, 0, 0.2)";
                oANote.style.padding = "10px";
                oANote.style.fontFamily = "Arial, sans-serif";
                oANote.style.zIndex = "1000";

                // Membuat HTML untuk catatan dengan desain baru
                var html = '';

                // Header tanggal
                html +=
                    '<div style="background:#ffab00; padding:6px; text-align:center; font-weight:bold; color:#fff; border-radius:8px 8px 0 0;">' +
                    cTgl + '</div>';

                // Isi catatan
                html += '<div style="padding:10px; font-size:14px; color:#333;">' + cMessage + '</div>';

                // Footer (edit & delete)
                html += '<div style="text-align:right; font-size:12px; margin-top:10px;">';

                // Edit Link
                html += '<a href="' + '{{ route('notes.edit', ['id' => 'replaceID']) }}'.replace('replaceID', nID) +
                    '" style="text-decoration:none; color:#007bff; font-weight:bold;">[edit]</a> ';

                // Delete Link
                var deleteUrl = '{{ route('notes.destroy', ':id') }}'.replace(':id', nID);
                html += '<a href="javascript:void(0);" onclick="deleteNote(\'' + deleteUrl +
                    '\');" style="text-decoration:none; color:#dc3545; font-weight:bold;">[delete]</a></div>';

                html += '<div style="position:absolute;top:-10px;left:10px;width:24px;height:29px;background:url(' +
                    @json(asset('assets/img/icons/clip-anote.gif')) + ') no-repeat;"></div>';

                // Menambahkan HTML ke dalam elemen
                oANote.innerHTML = html;

                // Menambahkan event listener untuk drag-and-drop
                addDragFunctionality(oANote);

            }

            // Fungsi untuk menangani drag-and-drop
            function addDragFunctionality(oANote) {
                var offsetX, offsetY, isDragging = false;

                // Ketika mouse mulai ditekan
                oANote.onmousedown = function(e) {
                    e.preventDefault(); // Mencegah seleksi teks saat dragging
                    isDragging = true;

                    // Menghitung jarak antara mouse dan posisi div
                    offsetX = e.clientX - oANote.offsetLeft;
                    offsetY = e.clientY - oANote.offsetTop;

                    // Ketika mouse bergerak
                    document.onmousemove = function(e) {
                        if (isDragging) {
                            oANote.style.left = (e.clientX - offsetX) + "px";
                            oANote.style.top = (e.clientY - offsetY) + "px";
                        }
                    };

                    // Ketika mouse dilepaskan
                    document.onmouseup = function() {
                        isDragging = false;
                        document.onmousemove = null;
                        document.onmouseup = null;

                        // Menyimpan posisi terbaru ke server atau di memori (jika diperlukan)
                        saveNotePosition(oANote.id.split('-')[1]); // Menyimpan posisi dengan ID note
                    };
                };
            }

            // Fungsi untuk menyimpan posisi catatan
            function saveNotePosition(nID) {
                var oANote = document.getElementById("anote-" + nID);
                var nTop = parseInt(oANote.style.top);
                var nLeft = parseInt(oANote.style.left);

                // Melakukan AJAX untuk menyimpan posisi catatan dengan CSRF token yang benar
                $.ajax({
                    url: "/notes/" + nID + "/position",
                    type: "POST",
                    data: {
                        top: nTop,
                        left: nLeft
                    },
                    success: function(response) {
                        // console.log("Position saved");
                    },
                    error: function(xhr, status, error) {
                        // console.error("Error saving position: " + error);
                    }
                });
            }

            function deleteNote(url, nID) {
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Data yang sudah dihapus tidak bisa dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#6366f1", // Warna tombol "Ya, Hapus"
                    cancelButtonColor: "#ff6b6b", // Warna tombol "Cancel"
                    confirmButtonText: "Ya, Tetap Hapus!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Dihapus!",
                                    text: "Catatan berhasil dihapus.",
                                    icon: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr, status, error) {
                                Swal.fire("Error!", "Terjadi kesalahan saat menghapus.", "error");
                            }
                        });
                    }
                });
            }


            // writeANote(1, 100, 150, '11-07-2024', 'Cek Point Harian:', 'Muhtarom00');
            @foreach ($notes as $note)
                document.addEventListener("DOMContentLoaded", function() {
                    writeANote(
                        {{ $note->id }},
                        {{ $note->top }},
                        {{ $note->left }},
                        '{{ $note->date }}',
                        `{!! nl2br(e($note->message)) !!}`
                    );
                });
            @endforeach
        </script>
    @endsection

    @section('content')
        <h4 class="mb-4 py-3">
            <span class="text-muted fw-light">Home /</span> Note
        </h4>

        @include('utils.modal')

        @can('notes_write')
            <a class="btn btn-primary btn-sm mb-1" href="{{ route('notes.create') }}"><i class="bx bx-plus"></i>Tambah</a>
        @endcan

        <div class="card">
            <!-- Tempatkan elemen div dengan id "mainFrame" di sini -->
            <div id="mainFrame"></div>
        </div>
    @endsection

    @push('custom_js')
        <script>
            $(document).ready(function() {
                // Menyertakan token CSRF dalam setiap permintaan AJAX
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });
        </script>
    @endpush
