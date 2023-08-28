(function ($) {
  "use strict";

  $(document).ready(function () {
    // Initializing
    var reference = jQuery("input#reference").val();
    var finished = jQuery("input#finished").val();
    const button = jQuery("input#export_button");
    const export_box = jQuery("#export_log");
    const blogId = jQuery("input#blog_id").val();
    const now = new Date();
    const timestamp = "@" + Math.round(now.getTime() / 1000);
    var fname = null;
    const timedFunction = setInterval(fname, 1000);
    const lastLogFile = server_ref.export_path + server_ref.reference + ".log";

    // Load the last export log at pageload and set text value
    var lastLog = loadFile(lastLogFile);
    export_box.text(lastLog);
    export_box.scrollTop(export_box.prop('scrollHeight'));

    console.log(server_ref);

    // Delay keyup function
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

    // Load the filePath log and return
    function loadFile(filePath) {
      var result = null;
      var xmlhttp = new XMLHttpRequest();

      xmlhttp.addEventListener("error", logFailed);

      xmlhttp.open("GET", filePath, false);
      try {
        xmlhttp.send();
      } catch (error) {
        xmlhttp.onerror();
      }

      if (xmlhttp.status == 200) {
        result = xmlhttp.responseText;
      }
      return result;
    }

    // Handles failed log load attemps
    function logFailed() {
      return false;
    }

    // Get the latest log file and prints to export box
    function readLog() {
      const logFile = server_ref.export_path + server_ref.reference + '.log';
      logContent = loadFile(logFile);
      if (logContent != false) {
        export_box.text(logContent);
        export_box.scrollTop(export_box.prop('scrollHeight'));
      }
    }


    /**
     * Function that handles ajax calls to the backend.
     * Switch mode as you need, export to start export,
     * update to get the next log file and ping to
     * read the logs every second.
     *
     * @param {object} data The data object used to make the post call
     * @param {string} mode The function mode to operate (ping,export or update)
     *
     */
    function dispath(data = {}, mode = "ping") {

      /**
       * Mode Export
       *
       * In this mode, we will export the static
       * files to the client ftp
       *
       */
      if (mode == "export") {

        let dataExport = {
          action: "wpmu_init_export",
          blog_id: blogId,
          timestamp: timestamp
        };

        jQuery.post(ajaxurl, dataExport, function (response) {
          if (!response.success) {
            export_box.text("Erro ao processar o pedido de envio ao FTP...");
          }

          // Set the export box the response from server
          export_box.text(response.data.message);

          // Export scheduled, next wait for log
          let verifyData = {
            action: "check_export_status",
            schedule_id: response.data.id
          };

          dispath(verifyData, "verify");
        });

      } else if (mode == "verify") {

        /**
         * Mode Verify
         *
         * In this mode we will check if the scheduled
         * process is still running at 1sec interval
         * If yes, continue to read log, if the process
         * has finished, stop reading log, if it is pending
         * dispath this function again.
         *
         */

        jQuery.post(ajaxurl, data, function (response) {
          if (response.success === false) {
            console.log(response);
            alert("Erro ao obter o status do processo de envio ao servidor cliente.");
          } else {
            if (response.data.status == "finished") {
              export_box.append("\n Envio finalizado com sucesso.");
              export_box.scrollTop(export_box.prop('scrollHeight'));
              button.attr('disabled', false);
              button.val('Iniciar Envio');
            } else if (response.data.status == "running") {
              
              const logFile = server_ref.export_path + response.data.args.reference + '.log';
              var logContent = loadFile(logFile);
              if (logContent != false) {
                export_box.text(logContent);
                export_box.scrollTop(export_box.prop('scrollHeight'));
              }
              setTimeout(() => {
                dispath(data, "verify");
              }, 1000);
            } else if (response.data.status == "pending") {
              setTimeout(() => {
                dispath(data, "verify");
              }, 1000);
            }
          }
        });
      }
    }

    /* Function called when the page is loaded
    * and the export function is not done yet
    * so we need to block Export button and
    * read the ongoing export log
    */
    if (reference && finished == "false") {
      button.attr("disabled", true);
      button.val("Envio em andamento");

      let verifyData = {
        action: "check_export_status",
        blog_id: blogId,
        timestamp: server_ref.timestamp,
        reference: server_ref.reference
      };

      dispath(verifyData, "verify");
    }

    /**
     * Event listener called when the export button is pressed
     * so we need to start export process
     */
    $("input#export_button").on("click", function (e) {
      button.val("Enviando...");
      button.attr("disabled", true);
      export_box.text("...");

      dispath({}, "export");
    });

    /**
     * Event listener on client field and local export field
     * to check path and names
     */
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
