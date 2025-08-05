define([
    'jquery',
    'jquery/validate',
    'mage/validation'
], function($) {
    'use strict';

    if ($.validator) {
        $.validator.addMethod(
            'validate-multiple-emails',
            function(value, element) {
                if ($.trim(value) === '') {
                    return true;
                }

                var emails = value.split(',');

                for (var i = 0; i < emails.length; i++) {
                    var email = $.trim(emails[i]);

                    if (email !== '') {
                        if (!$.validator.methods['validate-email'].call(this, email, element)) {
                            return false;
                        }
                    }
                }

                return true;
            },
            $.mage.__('Please enter valid email addresses, separated by commas.')
        );

        $.extend($.validator.messages, {
            'validate-multiple-emails': $.mage.__('Please enter valid email addresses, separated by commas.')
        });
    }

    return {};
});
