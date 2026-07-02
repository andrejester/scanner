@extends('layouts.app')
@section('content')
    <h4 class="mb-4 py-3">
        <span class="text-muted fw-light">System /</span> Backup Database
    </h4>

    <div class="row">
        <div class="col-md-6 col-12">
            <div class="card mb-6">
                <form action="{{ route('backupDb') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Backup Database</button>
                </form>

                {{-- <a href="{{ route('backup.database') }}" class="btn btn-outline-danger btn-sm mr-3">
                    Backup
                </a> --}}
                <h5 class="card-header">List data backup 30 hari terakhir</h5>
                <div class="card-body">
                    <div id="jstree-context-menu" class="overflow-auto"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 h-100">
            <div class="card bg-label-primary h-100 shadow-none">
                <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                    <div
                        class="w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-sm-start mb-0 text-center">
                        <div class="card-title">
                            <h5 class="text-primary mb-2">Informasi backup database</h5>
                            <p class="text-body w-sm-80 app-academy-xl-100">
                                Database akan secara otomatis melakukan backup setiap jam 00:00 WIB oleh server. Backup ini
                                akan menyimpan data dari 30 hari terakhir. Sistem akan melakukan backup secara berkala.
                            </p>
                        </div>
                    </div>
                    <div
                        class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-sm-0 mb-4">
                        <img class="img-fluid scaleX-n1-rtl"
                            src="{{ asset('assets/img/illustrations/boy-app-academy.png') }}" alt="boy illustration" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side -->
@endsection

@section('addon_js')
    <script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>

    <script>
        $(function() {
            var pathBackup = {!! json_encode($pathBackup) !!};

            // Example usage:
            console.log(pathBackup);

            var t = $("html").hasClass("light-style") ? "default" : "default-dark",
                e = $("#jstree-basic"),
                s = $("#jstree-custom-icons"),
                x = $("#jstree-context-menu"),
                n = $("#jstree-drag-drop"),
                c = $("#jstree-checkbox"),
                l = $("#jstree-ajax");
            x.length && x.jstree({
                core: {
                    themes: {
                        name: t
                    },
                    check_callback: !0,
                    data: pathBackup
                },
                plugins: ["types", "contextmenu"],
                types: {
                    default: {
                        icon: "bx bx-folder"
                    },
                    html: {
                        icon: "bx bxl-html5 text-danger"
                    },
                    css: {
                        icon: "bx bxl-css3 text-info"
                    },
                    img: {
                        icon: "bx bx-image text-success"
                    },
                    js: {
                        icon: "bx bxl-nodejs text-warning"
                    },
                    database: {
                        icon: 'bx bxl-postgresql text-info'
                    }
                }
            }).on('select_node.jstree', function(e, data) {
                if (data.node.a_attr.href !== "#") {
                    window.location.href = data.node.a_attr.href;
                }
            });
        });
    </script>
@endsection
