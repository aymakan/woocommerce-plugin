jQuery(function ($) {
    var aymakan_shipping_method = {

        init: function () {
            $('#woocommerce-order-data')
                .on('click', 'button.aymakan_show_modal', this.aymakan_show_modal);
            $('body')
                .on('click', 'button.aymakan_shipping_create', this.aymakan_shipping_create);
        },

        aymakan_show_modal: function (e) {
            e.preventDefault();
            $(this).WCBackboneModal({
                template: 'wc-aymakan-modal-shipping'
            });
            return false;
        },

        aymakan_shipping_create: function (e) {
            e.preventDefault();
            var section = $('.aymakan-shipping-form');
            section.addClass('loader');
            var data = {
                action: 'aymakan_shipping_create',
                data: $('#create_shipping_form').serialize()
            };

            $.ajax({
                type: 'POST',
                url: aymakan_shipping.ajax_url,
                data: data,
                success: function (response) {

                    if (response.success === true) {
                        section.find('.notification').prepend('<span class="dashicons-yes-alt aymakan-shipment-success">Aymakan Shipment Created</span>');
                        var shipping = response.data.shipping,
                            note = wp.template( 'wc-aymakan-order-note' );
                        $(note( shipping )).prependTo('.order_notes');
                        setTimeout(function () {
                            $('.aymakan-shipment-success').fadeOut(1000, function () {
                                $(this).remove();
                                $('.modal-close').trigger('click');
                            });
                        }, 1000);
                    }

                    if (response.errors) {
                        section.find('.has-error').removeClass('has-error');
                        $.each(response.errors, function (key, value) {
                            section.find('.notification').prepend('<span class="dashicons-warning noti-' + key + '">' + value[0] + '</span>');
                            $('.noti-' + key).delay(3000).fadeOut(1000, function () {
                                $(this).remove();
                            });
                            $('#' + key).addClass('has-error');
                        });
                        section.removeClass('loader');
                    }

                },
                dataType: 'json'
            });
        },

    };

    aymakan_shipping_method.init();
});
