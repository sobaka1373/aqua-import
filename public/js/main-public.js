(function( $) {
    'use strict';
    $(document).ready(function() {
        if ($('div.data')) {
            $('div.data').each(function (){

                if ($(this)[0].childElementCount === 1) {
                    $(this)[0].classList.add("dv-100");
                }
                if ($(this)[0].childElementCount === 2) {
                    $(this)[0].classList.add("dv-50");
                }
                if ($(this)[0].childElementCount === 3) {
                    $(this)[0].classList.add("dv-30");
                }
                if ($(this)[0].childElementCount === 4) {
                    $(this)[0].classList.add("dv-25");
                }
                if ($(this)[0].childElementCount > 4) {
                    $(this)[0].classList.add("dv-auto");
                }
            })
        }
    });
})(jQuery);