define([], function () {
    'use strict';

    return {
        /**
         * @returns {Object}
         */
        getData: function () {
            return {
                color_depth: window.screen.colorDepth,
                java_enabled: window.navigator.javaEnabled(),
                locale: window.navigator.language,
                screen_height: window.screen.height,
                screen_width: window.screen.width,
                timezone_offset_utc_minutes: (new Date()).getTimezoneOffset()
            };
        }
    }
});
