<!DOCTYPE html>
<html>

<head>
    <title>File Browser</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        img {
            width: 120px;
            margin: 10px;
            cursor: pointer;
            border: 1px solid #ccc;
        }
    </style>

    <script>
        function selectImage(url) {
            window.opener.CKEDITOR.tools.callFunction({{ request('CKEditorFuncNum') }}, url);
            window.close();
        }
    </script>
</head>

<body>

    <h3>Pilih Gambar</h3>

    @foreach ($files as $file)
        <img src="{{ $file }}" onclick="selectImage('{{ $file }}')" />
    @endforeach

</body>

</html>
