(function ($) {
    "use strict";

    function delay(callback, ms) {
        var timer = 0;
        return function() {
          var context = this, args = arguments;
          clearTimeout(timer);
          timer = setTimeout(function () {
            callback.apply(context, args);
          }, ms || 0);
        };
      }

    $(document).ready(function () {
        $("input#export_button").on("click", function (e) {
            e.preventDefault();

            const button = $("input#export_button");

            button.val("Exportando...");

            var output = $('#export_log').text('...');

            const blogId = $('input#blog_id').val();

            var data = {
                'action': 'wpmu_init_export',
                'blog_id': blogId
            };

            jQuery.post(ajaxurl, data, function(response) {
                output.text(response);
                button.val('Iniciar Exportação')
            });
            
        });

        $('#local_path, #client').keyup(delay(function(e){
            e.preventDefault();

            // Reset log box
            var ret = $('#return').text('');

            // Set client
            var is_client = false;
            if($('#client').length) {
                is_client = true;
            }

            var path = '';
            if(!is_client) {
                path = $('#local_path').val();
            } 

            if(is_client) {
                path = $('#client').val();
            }

            var data = {
                'action': 'check_typed_directory',
                'path': path,
                'is_client': is_client
            };

            jQuery.post(ajaxurl, data, function(response) {
                ret.html(response);
            });
            
        }, 500));
    });
})(jQuery);
