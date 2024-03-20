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
    var   fname = null;
    const lastLogFile = server_ref.export_root + server_ref.reference + ".log";
    const select_box = jQuery('.target_selector');
    const addRedirectButton = jQuery('#add_redirect');

    if (select_box.length > 0) {
      select_box.hide();
    }

    // Load the last export log at pageload and set text value
    if(reference != undefined && reference != ''){
      var lastLog = loadFile(lastLogFile);
      export_box.text(lastLog);
      export_box.scrollTop(export_box.prop('scrollHeight'));    
    }

    jQuery('.notice.is-dismissible .notice-dismiss').on('click', function () {
      var notice = $(this).closest('.notice'); 
      var data = {
        action: 'wp_ajax_wpmu_client_dismiss_message',
        displayedAt: $(this).closest('.notice').data('displayed-at')
      };
  
      jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: data,
        success: function (res) {         
          notice.remove();
        }
      });
    });

    // Verificar expiração a cada segundo
    setInterval(function() {
      jQuery('.notice.is-dismissible').each(function() {
        var message = jQuery(this);
        var displayedAt = message.data('displayed-at');
        var duration = message.data('duration');
        var currentTime = Math.floor(Date.now() / 1000);
  
        if (currentTime - displayedAt >= duration) {
          message.remove();
        }
      });
    }, 1000);
    
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
      xmlhttp.setRequestHeader("Authorization", "Basic " + btoa("drb.mkt:drb.mkt"));
      try {
        xmlhttp.send(null);
      } catch (error) {
<<<<<<< HEAD
        result =  "Log nao encontrado";
=======
          result = "Erro ao tentar obter o Log anterior."
>>>>>>> da093396d998dec4b156a9066ecba2462eaf04c8
      }

      if (xmlhttp.status == 200) {
        result = xmlhttp.responseText;
      } else if (xmlhttp.status == 404) {
<<<<<<< HEAD
	result = "Log não encontrado";
=======
        result = "Log não encontrado. Possivelmente excluído por uma nova geração do site estático."
>>>>>>> da093396d998dec4b156a9066ecba2462eaf04c8
      }

      return result;
    }

    // Handles failed log load attemps
    function logFailed() {
      return false;
    }

    // Get the latest log file and prints to export box
    function readLog() {
      const logFile = server_ref.export_root + server_ref.reference + '.log';
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
              const logFile = server_ref.export_root + response.data.args.reference + '.log';
              var logContent = loadFile(logFile);
              if (logContent != false) {
                export_box.text(logContent);
                export_box.scrollTop(export_box.prop('scrollHeight'));
              }
              setTimeout(() => {
                dispath(data, "verify");
              }, 1000);
            } else if (response.data.status == "pending") {
              let content = export_box.text();
              export_box.text(content + "\n" + response.data.expiration + "s");
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

    jQuery("#target_url").keyup(
      delay(function (e) {
        e.preventDefault();
        let partialTarget = jQuery("#target_url").val();
        let data = {
          action: "get_internal_permalink",
          partial_input: partialTarget
        };

        jQuery.post(ajaxurl, data, function (response) {
          if (response.success == false) {
            select_box.hide();
            select_box.empty();
            return;
          }

          if (Object.keys(response.data).length > 0) {

            select_box.empty();

            const permalinksObject = response.data;

            for (const id in permalinksObject) {
              const permalink = permalinksObject[id];
              select_box.append(`<li data-value="${id}">${permalink}</li>`);
            }
            select_box.fadeIn(200);

            jQuery('.target_selector li').on('click', function() {
              const dataValue = $(this).text();
              jQuery('#target_url').val(dataValue);
              select_box.fadeOut(200);
            });

          }
        });
      }, 500)
    )

    jQuery(".table-buttons #delete").on('click', function(e){
      e.preventDefault();

      let key = $(this).data('key');
      let value = $(this).data('value');

      let deleteDate = {
        action: 'delete_redirect',
        key: key,
        value: value
      };

      jQuery.post(ajaxurl, deleteDate, function(response){
        if(response.success == false) {
          console.log(response);
        }
        location.reload();
      });
    })

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

    addRedirectButton.on('click', function (event) {
      event.preventDefault();

      let sourceUrl = $('#source_url').val();
      let targetUrl = $('#target_url').val();
      let forceHttps = $('#force_https').is(':checked');

      let data = {
        action: "add_redirect",
        source_url: sourceUrl,
        target_url: targetUrl,
        force_https: forceHttps
      };

      $.post(ajaxurl, data, function(response){
        if(response.success == false) {
          alert(response.data);
          console.log(response);
        };
        location.reload();
      });
    });

    $('.delete_redirect').on('click', function(elem){
      let sourceUrl = $(this).data('key');
      let targetUrl = $(this).data('value');
      
      let data = {
        action: "delete_redirect",
        source_url: sourceUrl,
        target_url: targetUrl,
      };

      $.post(ajaxurl, data, function(response){
        if(response.success == false){
          console.log(response);
          alert(response.data);
        }
        location.reload();
      });

    });

    $('.edit_redirect').on('click', function(elem){
      let sourceUrl = $(this).data('key');
      let targetUrl = $(this).data('value');
      
      $('#source_url').val(sourceUrl);
      $('#target_url').val(targetUrl);
      
    });

    $(document).on('click', function (event) {
      const target = $(event.target);
      const elementToHide = select_box; // Substitua 'seu-elemento' pelo seletor adequado

      if (!target.is(elementToHide) && !elementToHide.has(event.target).length) {
        elementToHide.hide();
      }
    });

  });
})(jQuery);
