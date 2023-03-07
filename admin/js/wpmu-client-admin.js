(function( $ ) {
	'use strict';
    $(document).ready(function () {
        $('<tr class="form-field form-required"></tr>')
            .append($('<th scope="row">Cliente</th>'))
            .append(
                $("<td></td>")
                    .append(
                        $(
                            '<input class="regular-text" type="text" title="Cliente" name="blog[client]">'
                        )
                    )
                    .append($("<p>O Cliente deste site</p>"))
            )
            .insertAfter("#wpbody-content table tr:eq(2)");
    });
})( jQuery );
