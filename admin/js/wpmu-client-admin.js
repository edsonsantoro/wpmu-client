(function ($) {
    "use strict";

    $(document).ready(function () {
        const reference = jQuery("input#reference").val();
        const finished = jQuery("input#finished").val();
        const button = jQuery("input#export_button");
        const export_box = jQuery("#export_log");
        const blogId = jQuery("input#blog_id").val();
        const now = new Date();
        const timestamp = "@" + Math.round(now.getTime() / 1000);

        function delay(callback, ms) {
            var timer = 0;
            return function () {
                var context = this,
                    args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, ms || 0);
            };
        }

        function dispath(data, mode = "ping") {
            if (mode == "export") {
                jQuery.post(ajaxurl, data, function (response) {
                    if (!response.result) {
                        export_box.text("Erro");
                    }

                    export_box.text(response.data.result);

                    let readData = {
                        action: "read_export_log",
                        blog_id: blogId,
                        exportRef: response.data.reference,
                    };

                    dispath(readData, "ping");
                });
            } else {
                setInterval(function () {
                    jQuery.post(ajaxurl, data, function (response) {
                        if (!response.result) {
                            clearInterval();
                        }
                        export_box.text(response.data);
                    });
                }, 1000);
            }
        }

        if (reference && finished == 'false') {
            button.attr("disabled", true);
            button.val("Exportação na fila ou em andamento");

            let readData = {
                action: "read_export_log",
                blog_id: blogId,
                exportRef: reference,
            };

            dispath(readData, "ping");
        }   

        $("input#export_button").on("click", function (e) {
            button.val("Exportando...");
            button.attr("disabled", true);
            export_box.text("...");

            let dataExport = {
                action: "wpmu_init_export",
                blog_id: blogId,
                timestamp: timestamp,
            };

            dispath(dataExport, "export");
        });

        $("#local_path, #client").keyup(
            delay(function (e) {
                e.preventDefault();

                // Reset log box
                var ret = $("#return").text("");

                // Set client
                var is_client = false;
                if ($("#client").length) {
                    is_client = true;
                }

                var path = "";
                if (!is_client) {
                    path = $("#local_path").val();
                }

                if (is_client) {
                    path = $("#client").val();
                }

                var data = {
                    action: "check_typed_directory",
                    path: path,
                    is_client: is_client,
                };

                jQuery.post(ajaxurl, data, function (response) {
                    ret.html(response);
                });
            }, 500)
        );
    });
})(jQuery);
