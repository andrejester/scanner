CKEDITOR.plugins.filebrowser = {
    browse: function (editor, url, callback) {
        url = CKEDITOR.tools.addQueryString(url, "CKEditorFuncNum", callback);

        var width = editor.config.filebrowserWindowWidth || "80%";
        var height = editor.config.filebrowserWindowHeight || "70%";

        editor.popup(url, width, height);
    },
};
