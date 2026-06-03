let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Worldline_PaymentCore/js/view/payment/list-mixin': true
            },
            'Amasty_CheckoutCore/js/view/payment/list': {
                'Worldline_PaymentCore/js/view/payment/list-mixin': true
            },
            'Worldline_HostedCheckout/js/view/hosted-checkout/worldlinehc-method': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            },
            'Worldline_HostedCheckout/js/view/hosted-checkout/vault': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            },
            'Worldline_RedirectPayment/js/view/redirect-payment/worldlinerp-method': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            },
            'Worldline_RedirectPayment/js/view/redirect-payment/vault': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            },
            'Worldline_CreditCard/js/view/credit-card/worldlinecc-method': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            },
            'Worldline_CreditCard/js/view/credit-card/vault': {
                'Worldline_PaymentCore/js/view/payment/default-mixin': true
            }
        }
    }
};
