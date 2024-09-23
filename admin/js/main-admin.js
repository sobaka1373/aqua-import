(function( $) {
    'use strict';
    $(document).ready(function() {
        $('.button-parse-plugin').click(function() {
            $('.spinner').addClass('is-active');
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    'action' : 'start_convert_data_to_posts',
                },
                success:function() {
                    $('.spinner').removeClass('is-active');
                },
                error: function(errorThrown){
                    $('.spinner').removeClass('is-active');
                    console.log(errorThrown);
                }
            });
        });

        $('.button-parse-post-plugin').click(function() {
            $('.message-parse-end').addClass('hidden');
            $('.message-parse').addClass('hidden');
            $('.spinner').addClass('is-active');
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    'action' : 'start_parse_posts',
                },
                success:function(data) {
                    $('.spinner').removeClass('is-active');
                    if (parseInt(data) === 1) {
                        $('.message-parse-end').removeClass('hidden');
                    }
                    if (parseInt(data) === 2) {
                        $('.message-parse').removeClass('hidden');
                    }
                },
                error: function(errorThrown){
                    $('.spinner').removeClass('is-active');
                    console.log(errorThrown);
                }
            });
        });

        $('.button-second-parse-post-plugin').click(function() {
            $('.message-parse-end').addClass('hidden');
            $('.message-parse').addClass('hidden');
            $('.spinner').addClass('is-active');
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    'action' : 'start_second_parse_posts',
                },
                success:function(data) {
                    $('.spinner').removeClass('is-active');
                    if (parseInt(data) === 1) {
                        $('.message-parse-end').removeClass('hidden');
                    }
                    if (parseInt(data) === 2) {
                        $('.message-parse').removeClass('hidden');
                    }
                },
                error: function(errorThrown){
                    $('.spinner').removeClass('is-active');
                    console.log(errorThrown);
                }
            });
        });
    });
})(jQuery);