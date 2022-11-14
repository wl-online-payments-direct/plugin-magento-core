define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function (Component, $, confirm) {
    'use strict';

    return Component.extend({
        defaults: {
            incrementId: null,
            updateUrl: null
        },

        updatePaymentDetails: function () {
            let self = this;
            $.ajax({
                type: 'POST',
                url: self.updateUrl,
                data: {
                    "increment_id": self.incrementId,
                    "store_id": self.storeId
                }
            })
            .done(function(response) {
                if (response.result) {
                    confirm({
                        title: $.mage.__('Success!'),
                        content: $.mage.__('Payment details have been updated. The page will be reloaded.'),
                        actions: {
                            confirm: function () {
                                location.reload();
                            }
                        }
                    });
                } else {
                    confirm({
                        title: $.mage.__('Success!'),
                        content: $.mage.__('There is nothing to update.')
                    });
                }
            })
            .fail(function() {
                confirm({
                    title: $.mage.__('Error!'),
                    content: $.mage.__('Something went wrong during getting data from the service.')
                });
            });
        }
    });
});
