define([
    'uiElement',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'mage/validation'
], function (Element, $, alert) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Worldline_PaymentCore/system/config/feature-request',
            isShowForm: false,
            sendRequestUrl: '',
            formElement: '[data-worldline-js="feature-form"]'
        },

        initObservable: function () {
            this._super();
            this.observe(['isShowForm']);

            return this;
        },

        openCloseForm: function () {
            this.isShowForm(!this.isShowForm());
        },

        submitForm: function () {
            let form = $(this.formElement),
                isValid = form.validation() && form.validation('isValid');

            if (!isValid) {
                return false;
            }

            $.ajax({
                url: this.sendRequestUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: FORM_KEY,
                    store_id: this.storeId,
                    pspid: $('#pspid').val(),
                    company_name: $('#company-name').val(),
                    contact_email: $('#contact-email').val(),
                    body_message: $('#body-message').val()
                }
            }).done($.proxy(function (response) {
                if (response.success) {
                    alert({
                        title: $.mage.__('Request has been sent'),
                    });
                } else {
                    alert({
                        content: $.mage.__(response.errorMessage)
                    });
                }
            })).always($.proxy(function () {
                $('#pspid').val('');
                $('#company-name').val('');
                $('#contact-email').val('');
                $('#body-message').val('');
            }, this));
        },
    });
});
