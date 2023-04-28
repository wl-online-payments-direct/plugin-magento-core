let config = {
    map: {
        '*': {
            checkConnection: 'Worldline_PaymentCore/js/testconnection'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Worldline_PaymentCore/js/system/config/validation-mixin': true
            }
        }
    }
};
