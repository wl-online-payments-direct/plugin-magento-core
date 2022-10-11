define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            count: 0,
            storeCode: null,
            incrementId: null,
            checkOrderUrl: null,
            successUrl: null,
            failUrl: null,
            pendingOrderUrl: null
        },

        initialize: function () {
            this._super();

            this.sendRequest(this);
        },

        showMessage: function(message) {
            $('.message').html(message);
            $('.message').show();
        },

        sendRequest: function(uiClass) {
            let self = uiClass;
            self.count++;

            $.ajax({
                type: 'POST',
                url: self.checkOrderUrl,
                data: {
                    "incrementId": self.incrementId
                }
            })
            .done(function(result) {
                if (result.status) {
                    window.location.replace(self.successUrl);
                } else {
                    if (self.count < 15) {
                        setTimeout(self.sendRequest, 1000, self);
                    } else {
                        self.processPendingOrder(self);
                    }

                }
            })
            .fail(function(result) {
                self.showMessage('The payment has failed, please, try again.');
            });
        },

        processPendingOrder: function (currentComponentObject) {
            let self = currentComponentObject;
            $.ajax({
                type: 'POST',
                url: self.pendingOrderUrl,
                data: {
                    "incrementId": self.incrementId
                }
            })
            .done(function(result) {
                if (result.status) {
                    window.location.replace(self.successUrl);
                } else {
                    window.location.replace(self.failUrl);
                }
            })
            .fail(function(result) {
                self.showMessage('The payment has failed, please, try again.');
            });
        }
    });
});
