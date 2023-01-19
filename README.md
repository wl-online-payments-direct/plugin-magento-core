# Worldline Online Payments

## Core extension
[![M2 Coding Standard](https://github.com/wl-online-payments-direct/plugin-magento-core/actions/workflows/coding-standard.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-core/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/wl-online-payments-direct/plugin-magento-core/actions/workflows/mess-detector.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-core/actions/workflows/mess-detector.yml)

This is a core module that are used with Worldline payment solutions.

To install these solutions, you may use
[adobe commerce marketplace](https://marketplace.magento.com/worldline-module-magento-payment.html)
or install them from the github:
- [all payment methods](https://github.com/wl-online-payments-direct/plugin-magento) at once
- only [credit card](https://github.com/wl-online-payments-direct/plugin-magento-creditcard)
- only [hosted checkout](https://github.com/wl-online-payments-direct/plugin-magento-hostedcheckout)
- [redirect payments (single payment buttons)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments)


### Change log:

2.5.0
- Add the "Mealvouchers" payment method.
- Add the “Update Status” button for “View Memo”. This allows you to refresh in real time the status of your credit memos.
- Render webhooks updates in the order details.
- Add grid with Webhooks for debug purposes.
- Improve cancel and void actions logic.
- Add uninstall script.
- Update release notes.
- General code improvements and bug fixes.

2.4.0
- Raise the version in order to display the actual version for those who install single solutions.
- Add "groupCards" functionality (for hosted checkout) : group all card under one single payment button.
- Add payment method Intersolve and process the split payment.
- Improve Worldline payment box design: split in payment and fraud results.
- Add a feature to request 3DS exemption for transactions below 30 EUR.
- Add translations.
- Add integration tests (for credit card).
- General code improvements and bug fixes.

1.4.0
- Improved design of general settings page
- General code improvements and bug fixes
- Improvements and support for 2.3.x magento versions
- Support the 4.5.0 version of the Worldline SDK

1.3.1
- Improve work for multi-website instances

1.3.0
- Improve the "waiting" page
- Add the "pending" page

1.2.1
- Fix cron run time to prevent order duplication

1.2.0
- Improve waiting page by adding an order summary block so that customers will always see what they have bought
- Improve payment info block within Magento backend. Merchants can now manually refresh the info available to be sure it is always up to date
- General improvements and bug fixes

1.1.1
- Support version 4.3.3 of Worldline SDK
- PWA improvements and support
- Bug fixes and general code improvements

1.1.0
- Waiting page has been added after payment is done to correctly process webhooks and create the order
- Asyncronic order creation through get calls when webhooks suffer delay
- General improvements and bug fixes

1.0.0
- Initial MVP version 
