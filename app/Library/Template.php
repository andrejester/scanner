<?php

namespace App\Library;

class Template
{
    static function get($jenis = '')
    {
        $data['pilihCss'] = [];
        $data['pilihJs'] = [];
        // $data['username'] = Auth::user()->name;
        /*
            CSS YANG SELALU DIGUNAKAN ADMIN LTE
        */

        $css['Favicon'] = ["src" => "assets/img/favicon/favicon.ico", "rel" => "preconnect"]; //template-customizer-core-css
        $css['Fonts'] = ["src" => "https://fonts.googleapis.com", "rel" => "preconnect"];
        // $css['Font1'] = ["src" => "https://fonts.gstatic.com", "crossorigin" => "crossorigin"];
        $css['Font2'] = ["src" => "https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"];

        $css['boxicons'] = ["src" => "assets/vendor/fonts/boxicons.css"];
        $css['fontawesome'] = ["src" => "assets/vendor/fonts/fontawesome.css"];
        $css['flag-icons'] = ["src" => "assets/vendor/fonts/flag-icons.css"];
        $css['core'] = ["src" => "assets/vendor/css/rtl/core.css", "class" => 'template-customizer-core-css'];
        $css['theme-default'] =  ["src" => "assets/vendor/css/rtl/theme-default.css", "class" => "template-customizer-theme-css"];
        $css['demo'] = ["src" => "assets/css/demo.css"];

        $css['perfect-scrollbar'] = ["src" => "assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"];
        $css['typeahead'] = ["src" => "assets/vendor/libs/typeahead-js/typeahead.css"];
        $css['apex-charts'] = ["src" => "assets/vendor/libs/apex-charts/apex-charts.css"];
        $css['index.min'] = ["src" => "assets/vendor/libs/@form-validation/umd/styles/index.min.css"];
        $css['sweetalert2'] = ["src" => "assets/vendor/libs/sweetalert2/sweetalert2.css"];
        $css['select2'] = ["src" => "assets/vendor/libs/select2/select2.css"];
        $css['flatpicker'] = ["src" => "assets/vendor/libs/flatpickr/flatpickr.css"];
        $css['bootstrap-select'] = ["src" => "assets/vendor/libs/bootstrap-select/bootstrap-select.css"];

        foreach ($css as $key => $value) {
            array_push($data['pilihCss'], $key);
        }
        // $css['datatables'] = ["src" => "https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css"];
        // $css['datatables'] = ["src" => "https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-1.13.5/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/sl-1.7.0/datatables.min.css"];
        $css['dropzone'] = ["src" => "assets/css/dropzone.css"];
        $css['datatables.bootstrap5'] = ["src" => "assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css"];
        $css['responsive.bootstrap5'] = ["src" => "assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"];
        $css['buttons.bootstrap5'] = ["src" => "assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css"];
        $css['chart'] = ["src" => "assets/vendor/libs/apex-charts/apex-charts.css"];
        $css['stepper'] = ["src" => "assets/vendor/libs/bs-stepper/bs-stepper.css"];
        $css['form-validation'] = ["src" => "assets/vendor/libs/@form-validation/umd/styles/index.min.css"];
        $css['apex-charts'] = ["src" => "assets/vendor/libs/apex-charts/apex-charts.css"];
        $css['card-analytics'] = ["src" => "assets/vendor/css/pages/card-analytics.css"];
        $css['appEmail'] = ["src" => "assets/vendor/css/pages/app-email.css"];


        /*
            SELECT CSS
        */
        foreach ($css as $key => $value) {
            $rel = isset($value['rel']) ? $value['rel'] : 'stylesheet';
            $class = isset($value['class']) ? "class ='{$value['class']}'" : "";
            $crossOrigin = isset($value['crossorigin']) ? $value['crossorigin'] : "";
            $link = "<link rel ='$rel' href='" . asset($value['src']) . "' $class $crossOrigin>";
            $css[$key] = $link;
        }



        /*
            JAVASCRIPT YANG SELALU DIGUNAKAN ADMIN LTE
        */
        $js['jquery'] = "assets/vendor/libs/jquery/jquery.js";
        $js['popper'] = "assets/vendor/libs/popper/popper.js";
        $js['bootstrap'] = "assets/vendor/js/bootstrap.js";
        $js['perfect-scrollbar'] = "assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js";

        $js['hammer'] = "assets/vendor/libs/hammer/hammer.js";
        $js['i18n'] = "assets/vendor/libs/i18n/i18n.js";
        $js['typeahead'] = "assets/vendor/libs/typeahead-js/typeahead.js";
        $js['perfect-scrollbar'] = "assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js";

        $js['popular'] = "assets/vendor/libs/@form-validation/umd/bundle/popular.min.js";
        $js['index.min'] = "assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js";
        $js['index.min.auto'] = "assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js";

        $js['menu'] = "assets/vendor/js/menu.js";
        $js['main'] = "assets/js/main.js";
        $js['sweetalert2'] = "assets/vendor/libs/sweetalert2/sweetalert2.js";
        $js['select2'] = "assets/vendor/libs/select2/select2.js";
        $js['money'] = "assets/js/jquery-mask-money.js";
        $js['cleave'] = "assets/vendor/libs/cleavejs/cleave.js";
        $js['custom'] = "assets/js/custom.js";
        $js['picker'] = "assets/vendor/libs/flatpickr/flatpickr.js";
        $js['forms-selects'] = "assets/js/forms-selects.js";
        // $js['tables-datatables-basic'] = "assets/js/tables-datatables-basic.js";

        foreach ($js as $key => $value) {
            array_push($data['pilihJs'], $key);
        }
        $js['autosize'] = "assets/vendor/libs/autosize/autosize.js"; //textarea
        $js['dropzone'] = "assets/js/dropzone.js";
        $js['datatable'] = "assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js";
        $js['datatablesB'] = "https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-1.13.5/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/sl-1.7.0/datatables.min.js";
        $js['chart'] = "assets/vendor/libs/apex-charts/apexcharts.js";

        $js['stepper'] = "assets/vendor/libs/bs-stepper/bs-stepper.js";
        $js['form-validation'] = "assets/vendor/libs/@form-validation/umd/bundle/popular.min.js";
        $js['form-validation1'] = "assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js";
        $js['form-validation2'] = "assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js";

        $js['appEmail'] = "assets/js/app-email.js";
        // <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
        // <script src="{{ asset('') }}"></script>
        // <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-foc

        if ($jenis == 'datatable') {
            array_push($data['pilihCss'],  "datatables.bootstrap5", "responsive.bootstrap5", "buttons.bootstrap5");
            array_push($data['pilihJs'], "datatable"); //, "datatablesB");
        }
        $data['js'] = $js;
        $data['css'] = $css;
        return $data;
    }
}
