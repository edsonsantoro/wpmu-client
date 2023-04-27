(function ($) {
    "use strict";
    $(document).ready(function () {
        $("input#export_button").on("click", function (e) {
            e.preventDefault();

            var output = $('#export_log').text('...');

            const blogId = $('input#blog_id').val();

            var data = {
                'action': 'wpmu_init_export',
                'whatever': 1234,
                'blog_id': blogId
            };

            jQuery.post(ajaxurl, data, function(response) {
                output.text(response);
            });
            
        });
    });
})(jQuery);
