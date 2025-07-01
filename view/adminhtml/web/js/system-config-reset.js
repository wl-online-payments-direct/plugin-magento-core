define(['jquery'], function ($) {
    'use strict';

    $(document).ready(function () {
        const exemptionTypeSelect = $('#worldline_payment_general_settings_authentication_exemption_type');
        const exemptionLimit30Input = $('#worldline_payment_general_settings_authentication_exemption_limit_30')[0];
        const exemptionLimit100Input = $('#worldline_payment_general_settings_authentication_exemption_limit_100')[0];

        if (exemptionTypeSelect.length === 0) {
            return;
        }

        exemptionTypeSelect.on('change', function () {
            if ($(this).val() === 'low-value') {
                if (exemptionLimit30Input.value === "" && exemptionLimit100Input.value) {
                    exemptionLimit30Input.value = exemptionLimit100Input.value > 30 ? 30 : exemptionLimit100Input.value;
                }
            }

            if ($(this).val() === 'transaction-risk-analysis') {
                if (exemptionLimit100Input.value === "" && exemptionLimit30Input.value) {
                    exemptionLimit100Input.value = exemptionLimit30Input.value > 100 ? 100 : exemptionLimit30Input.value;
                }
            }
        });
    });
});
