define(['jquery'], function ($) {
    'use strict';

    $(document).ready(function () {
        const exemptionTypeSelect = $('#worldline_payment_general_settings_authentication_exemption_type');
        const exemptionLimitNoChallengeInput = $('#worldline_payment_general_settings_authentication_exemption_limit_no_challenge')[0];
        const exemptionLimit30Input = $('#worldline_payment_general_settings_authentication_exemption_limit_30')[0];
        const exemptionLimit100Input = $('#worldline_payment_general_settings_authentication_exemption_limit_100')[0];

        if (exemptionTypeSelect.length === 0) {
            return;
        }

        let previousType = exemptionTypeSelect.val();

        exemptionTypeSelect.on('change', function () {
            const selectedType = $(this).val();
            let sourceValue = null;

            if (selectedType === 'none' && exemptionLimitNoChallengeInput.value === "") {
                if (previousType === 'low-value') {
                    sourceValue = exemptionLimit30Input.value;
                }
                if (previousType === 'transaction-risk-analysis') {
                    sourceValue = exemptionLimit100Input.value;
                }
                if (sourceValue) {
                    exemptionLimitNoChallengeInput.value = Math.min(sourceValue, 100);
                }
            }

            if (selectedType === 'low-value' && exemptionLimit30Input.value === "") {
                if (previousType === 'none') {
                    sourceValue = exemptionLimitNoChallengeInput.value;
                }
                if (previousType === 'transaction-risk-analysis') {
                    sourceValue = exemptionLimit100Input.value;
                }
                if (sourceValue) {
                    exemptionLimit30Input.value = Math.min(sourceValue, 30);
                }
            }

            if (selectedType === 'transaction-risk-analysis' && exemptionLimit100Input.value === "") {
                if (previousType === 'none') {
                    sourceValue = exemptionLimitNoChallengeInput.value;
                }
                if (previousType === 'low-value') {
                    sourceValue = exemptionLimit30Input.value;
                }
                if (sourceValue) {
                    exemptionLimit100Input.value = Math.min(sourceValue, 100);
                }
            }

            previousType = selectedType;
        });
    });
});
