"use strict";

jQuery(function ($) {
    const aymakanShipping = {

        init: function () {
            $('#woocommerce-order-data')
                .on('click', 'button.aymakanShowModal', this.aymakanShowModal);
            $('body')
                .on('click', 'button.aymakan-shipping-create-btn', this.aymakanShippingCreate)
                .on('click', 'button.aymakan-toggle-header', this.aymakanToggle)
                .on('click', 'input#doaction', this.aymakanBulkShippingCreate)
                .on('click', '.aymakan-notice-dismiss', this.aymakanNoticeDismiss);
        },

        aymakanShowModal: function (e) {
            e.preventDefault();
            $(this).WCBackboneModal({
                template: 'wc-aymakan-modal-shipping'
            });
            return false;
        },

        aymakanToggle: function (e) {
            e.preventDefault();
            let el = $(this);
            el.closest('article').toggleClass('expanded-content')
            el.next().toggle();
        },

        aymakanShippingCreate: function (e) {
            e.preventDefault();
            let el = $(this);
            let section = el.closest('.aymakan-toggle-content');
            section.addClass('aymakan-loading');
            let data = {
                action: 'aymakan_shipping_create',
                data: section.find('form').serialize()
            };

            $.ajax({
                type: 'POST',
                url: aymakan_shipping.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    let notify = section.find('.notification');
                    if (response.success) {
                        notify.prepend('<span class="dashicons-yes-alt aymakan-shipment-success">Aymakan Shipment Created</span>');
                        setTimeout(function () {
                            $('.aymakan-shipment-success').fadeOut(1000, function () {
                                $(this).remove();
                            });
                        }, 1000);
                    }

                    if (response.errors) {
                        $.each(response.errors, function (key, value) {
                            section.find('input#' + key).addClass('has-error');
                            notify.prepend('<span class="dashicons-warning noti-' + key + '">' + value[0] + '</span>').fadeIn('fast')
                                .find('.noti-' + key)
                                .delay(7000)
                                .fadeOut(1000, function () {
                                    $(this).remove();
                                });
                        });
                    }

                    if (response.error) {
                        notify.prepend('<span class="dashicons-warning">' + response.message + '</span>').fadeIn('fast');
                        setTimeout(function () {
                            notify.fadeOut('slow', function () {
                                notify.html('');
                            });
                        }, 10000);
                    }

                    section.removeClass('aymakan-loading');

                },
                error: function (error) {
                    console.log(error);
                    section.removeClass('aymakan-loading');
                },

            });
        },

        aymakanBulkShippingCreate: function (e) {
            let el = $(this);

            if (el.prev().val() !== 'aymakan_bulk_shipment') {
                return '';
            }

            e.preventDefault()

            el.addClass('aymakan-disable-btn');
            let form = el.closest('form');

            form.addClass('aymakan-loading');

            let data = {
                action: 'aymakan_bulk_shipping_create',
                data: form.serialize()
            };

            $.ajax({
                type: 'POST',
                url: aymakan_shipping.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    $.each(response, function (i) {
                        let item = response[i]
                        let row = $('tr#post-' + item.id)

                        if (item.success === true) {
                            let shipping = item.shipping;
                            aymakanShipping.aymakanAdminNotice('Aymakan Shipments Created Successfully.', 'success', item.id, 'success')

                            row.find('.column-aymakan').html('<a href="' + shipping.pdf_label + '" class="order-status aymakan-btn aymakan-awb-btn" target="_blank">Print Airway Bill</a>');

                            row.find('.column-aymakan-tracking').html('<a href="' + item.tracking_link + '" class="order-status aymakan-btn aymakan-shipping-track-btn" target="_blank">' + shipping.tracking_number + '</a>');

                        }

                        row.find('.check-column input').prop('checked', false)

                        if (item.errors) {
                            $.each(item.errors, function (key, value) {
                                aymakanShipping.aymakanAdminNotice(value[0], key, item.id)
                            });
                        }

                        if (item.error) {
                            aymakanShipping.aymakanAdminNotice(item.message, 'error', item.id)
                        }

                    });

                    el.removeClass('aymakan-disable-btn');
                    form.removeClass('aymakan-loading');
                },
                error: function (error) {
                    console.log(error);
                    el.removeClass('aymakan-disable-btn');
                    form.removeClass('aymakan-loading');
                },

            });
        },

        aymakanAdminNotice: function (message, key, id, type = 'error') {
            id = id ? 'Order #' + id + ' ' : '';
            $('<div class="notice is-dismissible notice-' + type + ' noti-' + key + '"><p> ' + id + message + '</p><button type="button" class="notice-dismiss aymakan-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>').insertAfter('.wp-header-end');
            $('.noti-' + key).delay(50000).fadeOut(1000, function () {
                $(this).remove();
            });
        },

        aymakanNoticeDismiss: function () {
            $(this).parent().fadeOut(1000, function () {
                $(this).remove();
            });
        }

    };
    aymakanShipping.init();
});
