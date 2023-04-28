let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Worldline_PaymentCore/js/view/payment/list-mixin': true
            },
            'Amasty_CheckoutCore/js/view/payment/list': {
                'Worldline_PaymentCore/js/view/payment/list-mixin': true
            }
        }
    }
};
