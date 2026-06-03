define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    return function (Component) {
        return Component.extend({
            placeOrder: function (data, event) {
                customerData.invalidate(['cart']);

                return this._super(data, event);
            }
        });
    };
});
