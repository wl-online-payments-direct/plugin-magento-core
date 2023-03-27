define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/model/messageList'
], function ($, _, $t, messageList) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();

                let surchargeMessage = window.checkoutConfig?.worldlineCheckoutConfig?.surchargeMessage;

                if (surchargeMessage) {
                    messageList.addSuccessMessage({'message': $.mage.__(surchargeMessage)});
                }

                return this;
            }
        });
    };
});
