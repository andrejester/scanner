CKEDITOR.plugins.add("filebrowser", {
    requires: "dialog",
    init: function (editor) {
        var openBrowser = function (callback, value) {
            var url = editor.config.filebrowserBrowseUrl;

            if (url) {
                var width = editor.config.filebrowserWindowWidth || "80%";
                var height = editor.config.filebrowserWindowHeight || "70%";

                url = CKEDITOR.tools.addQueryString(
                    url,
                    "CKEditorFuncNum",
                    callback
                );

                editor.popup(url, width, height);
            }
        };

        editor.on("dialogDefinition", function (evt) {
            var dialogName = evt.data.name;
            var dialogDefinition = evt.data.definition;

            if (dialogName == "image") {
                var infoTab = dialogDefinition.getContents("info");
                var urlField = infoTab.get("txtUrl");

                urlField.filebrowser = {
                    action: "Browse",
                    target: "txtUrl",
                    url: editor.config.filebrowserBrowseUrl,
                };

                urlField.onBrowse = function () {
                    openBrowser(
                        CKEDITOR.tools.addFunction(function (url) {
                            urlField.setValue(url);
                        })
                    );
                };
            }
        });
    },
});
