# Change Log for OXID eSales PayPal module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [6.5.0] - 2022-07-19

### Changed
- Adapt module to work with OXID eShop 6.5.x compilation.
- Namespace pointer changed to use files from vendor directory.

## [6.4.1] - 2022-06-28

### Changed
- Add codeception tests, remove selenium tests. Use .env and environment.yaml to inject test credentials.

## [6.4.0] - 2021-11-29

### Changed
- Update tests and module to work with GraphQL Storefront v2.0.

## [6.3.2] - 2021-07-19

### Fixed
- OrderList::_buildSelectString is giving congruent result after replacing "from order" part [PR-54](https://github.com/OXID-eSales/paypal/pull/54)
- Fix problematic special cases of user loading in User::load() method

## [6.3.1] - 2021-07-19

### Changed
- Updated php version requirements to ^7.1 || ^8.0

## [6.3.0] - 2021-07-13

### Added
- PayPal as payment method for OXID GraphQL Storefront module:
  - services.yaml
  - Code in namespace `\OxidEsales\PayPalModule\GraphQL\`
  - Codeception tests in group `paypal_graphql` resp. `paypal_checkout`.
  - Column `oxuserbaskets.OEPAYPAL_PAYMENT_TOKEN` to hold the PayPal token needed for GraphQL checkout.
  - Column `oxuser.OEPAYPAL_ANON_USERID` to relate an anonymous user to his shop account if existing
  - Class `\OxidEsales\PayPalModule\Model\PaymentManager`
  - Property `\OxidEsales\PayPalModule\Controller\Dispatcher::paymentManager`
  - Method:
    - `\OxidEsales\PayPalModule\Controller\Dispatcher::getPaymentManager()`
    - `\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::processGraphQLCallBack()`
    - `\OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder::setToken()`
    - `\OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder::getToken()`
    - `\OxidEsales\PayPalModule\Model\User::setAnonymousUserId()`
    - `\OxidEsales\PayPalModule\Model\User::getAnonymousId()`
    - `\OxidEsales\PayPalModule\Model\User::load()`
    - `\OxidEsales\PayPalModule\Model\User::hasNoInvoiceAddress()`
    - `\OxidEsales\PayPalModule\Model\User::setGrouspAfterUserCreation()`
    - `\OxidEsales\PayPalModule\Model\User::setInvoiceDataFromPayPalResult()`
- PHP 8 support

### Changed
- Changed visibility from protected to public for the following methods:
  - `\OxidEsales\PayPalModule\Model\Address::prepareDataPayPalAddress()`
  - `\OxidEsales\PayPalModule\Controller\StandardDispatcher::getReturnUrl()`
  - `\OxidEsales\PayPalModule\Controller\StandardDispatcher::getCancelUrl()`
- Updated php version requirements to ^7.3 || ^8.0

### Deprecated
  - Deprecated the following methods:
    - `\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::getTransactionMode()`
    - `\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::makeUniqueNames()`
    - `\OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::reencodeHtmlEntities()`
    - `\OxidEsales\PayPalModule\Controller\StandardDispatcher::getTransactionMode()`
    - `\OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder::setSession()`
    - `\OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder::getSession()`

### Fixed
- LastName field size in admin orders list [PR-52](https://github.com/OXID-eSales/paypal/pull/52)

## [6.2.3] - 2021-04-13

### Fixed
- Improve tests to run with higher phpunit version

## [6.2.2] - 2021-03-04

### Fixed
- Load installment banners javascript with oxscript
- Improve installment banners javascript to be more stable in low connection case
- Improve Codeception tests.

## [6.2.1] - 2020-10-08

### Fixed
- Improve Codeception tests.

## [6.2.0] - 2020-07-03

### Added
- New method Basket::hasProductVariantInBasket();

### Fixed
- Stabilize acceptance tests.
- Banners on product details page for variants.

## [6.1.0] - 2020-03-16

### Added
- PayPal Installment Banners" feature added.
- New settings for configuring the banners added. Its possible to separately configure banners for these page types:
  - Start page
  - Product details page
  - Category page
  - Search result page
  - Checkout page

### Fixed
- [0007077](https://bugs.oxid-esales.com/view.php?id=7077) Shipping Methods with special chars not found by PayPal Express.
- Javascript issues for banners in case of missing client id. 
  
## [6.0.2] - 2020-02-25

### Fixed
- Stabilize acceptance tests.

## [6.0.1] - 2019-10-21

### Fixed
- Stabilize acceptance tests.

## [6.0.0] - 2019-07-23

### Changed
- Adapt tests to work with new phpunit.
- Replace deprecated calls to getConfig();
- Ensure support for PHP 7.2, drop support for PHP 5.6.
- Replace deprecated calls to getSession();

## [5.3.3] - 2020-10-08

### Added
- Additional description for banner settings [PR-51](https://github.com/OXID-eSales/paypal/pull/51)

### Fixed
- [0007168](https://bugs.oxid-esales.com/view.php?id=7168) The method Caller::validateResponse only catches ACK failures, but not internal service errors.

## [5.3.2] - 2020-07-03

### Fixed
- Acceptance tests stability increased.
- Fix and stabilize acceptance tests. Adapt to latest changes in PayPal Gui.
- Fix variant switching issues when installment banners element is missing. 

## [5.3.1] - 2020-03-14

### Fixed
- [0007077](https://bugs.oxid-esales.com/view.php?id=7077) Shipping Methods with special chars not found by PayPal Express.
- Javascript issues for banners in case of missing client id.

## [5.3.0] - 2020-02-27

### Added
- PayPal Installment Banners" feature added.
- New settings for configuring the banners added. Its possible to separately configure banners for these page types:
  - Start page
  - Product details page
  - Category page
  - Search result page
  - Checkout page

## [5.2.6] - 2020-02-25

### Fixed

- Acceptance tests stability increased.
- [0006235](https://bugs.oxid-esales.com/view.php?id=6235) Use comma as decimal separator during capture and refund.
- [0006994](https://bugs.oxid-esales.com/view.php?id=6994) Fix "Finalize order after PayPal checkout" configuration option not working
- [0006995](https://bugs.oxid-esales.com/view.php?id=6995) Fix wrong product quantity after clicking "Add and Checkout".

## [5.2.5] - 2019-04-13

### Fixed
- [0006972](https://bugs.oxid-esales.com/view.php?id=6972) Unfinished order with PayPal Express Checkout in some special constellations.

## [5.2.4] - 2019-04-10

### Fixed
- Fix readonly flag for deliveryset_main.tpl.
- Button in paypal have correct text ("Continue" but not "Pay now" anymore)
- [0006921](https://bugs.oxid-esales.com/view.php?id=6921) Fix downloadlink in user mail.
- Wrap paypal payment block in div with "well well-sm" class to fit other payments [PR-47](https://github.com/OXID-eSales/paypal/pull/47)
- Fixed template variable in IPNHandler controller [PR-49](https://github.com/OXID-eSales/paypal/pull/49)
- [0006955](https://bugs.oxid-esales.com/view.php?id=6955) Fix wrong payment method creation on module deactivation. [PR-50](https://github.com/OXID-eSales/paypal/pull/50)
- [0006132](https://bugs.oxid-esales.com/view.php?id=6132) Backwards compatibility break: Fix wrong response of IPNHandler handleRequest [PR-48](https://github.com/OXID-eSales/paypal/pull/48)
- [0006963](https://bugs.oxid-esales.com/view.php?id=6963) With PayPal Express Checkout the telephone number which is deposited with PayPal in the account is not handed over with to shop

## [5.2.3] - 2018-10-09

### Changed
- Show extra payment costs only if they are not zero [PR-44](https://github.com/OXID-eSales/paypal/pull/44)

### Fixed
- [0006774](https://bugs.oxid-esales.com/view.php?id=6774) In backend, orderlist column 'PAYMENT METHOD' stays empty [PR-45](https://github.com/OXID-eSales/paypal/pull/45)
- Fix and stabilize acceptance tests. Adapt to latest changes in PayPal Gui.

## [5.2.2] - 2018-07-17

### Fixed
- Fix PHP 7.1 compatibility of acceptance tests.

### Security

## [5.2.1] - 2018-07-12

### Fixed
- Adapt acceptance tests to latest changes in PayPal GUI.
- Adapt tests to latest PayPal Sandbox

## [5.2.0] - 2018-05-03

### Changed
- Added class \OxidEsales\PayPalModule\Core\IpnConfig.
- Added methods
  * \OxidEsales\PayPalModule\Core\PayPalService::setPayPalIpnConfig()
  * \OxidEsales\PayPalModule\Core\PayPalService::getPayPalIpnConfig()

### Deprecated
- Deprecated the following methods: \OxidEsales\PayPalModule\Core\Config::getIPNResponseUrl()

### Fixed
- Compatibility of tests with MySQL 5.7.
- Fixed 0006122 IPN postback DNS issue. Introduced \OxidEsales\PayPalModule\Core\IpnConfig class to 
  provide the necessary IPN parameters for host and url.  

## [5.1.6] - 2018-03-26

### Changed
- New partnercode Oxid_Cart_ECS_Shortcut is used for BUTTONSOURCE parameter in 
  PayPal's DoExpressCheckoutPayment API Operation (NVP) when PayPal payment was triggered 
  via shortcut button.
- Updated pictures in documentation.  
  
### Removed
- Unused log directory. Log is written into shop's default log directory. 

## [5.1.5] - 2018-01-23

### Added
- Add hidden configuration parameter OEPayPalDisableIPN and method 
  \OxidEsales\PayPalModule\CoreConfig::suppressIPNCallbackUrl() to be able to suppress 
  sending PAYMENTREQUEST_0_NOTIFYURL optional request parameter to paypal. 
  At the moment used for acceptance tests as they do not test IPN.

### Changed
- Log Acceptance test debug information into log/oepaypal_acceptance_log.txt instead of log/EXCEPTION_LOG.txt.

## [5.1.4] - 2017-11-28

### Changed
- Update PayPal button pictures.

## [5.1.3] - 2017-11-13

### Changed
- Change tables encoding to utf8.

## [5.1.2] - 2017-11-02

### Fixed
- Stabilize Acceptance tests by automatically skipping tests if issues with PayPal Sandbox are detected.

## [5.1.1] - 2017-09-07

### Fixed
- Stabilize Acceptance tests by changing locators.

## [5.1.0] - 2017-08-14

### Added
- Additional PayPal express checkout button in user checkout step in case no user is logged in.

[6.5.0]: https://github.com/OXID-eSales/paypal/compare/v6.4.1...v6.5.0
[6.4.1]: https://github.com/OXID-eSales/paypal/compare/v6.4.0...v6.4.1
[6.4.0]: https://github.com/OXID-eSales/paypal/compare/v6.3.2...v6.4.0
[6.3.2]: https://github.com/OXID-eSales/paypal/compare/v6.3.1...v6.3.2
[6.3.1]: https://github.com/OXID-eSales/paypal/compare/v6.3.0...v6.3.1
[6.3.0]: https://github.com/OXID-eSales/paypal/compare/v6.2.3...v6.3.0
[6.2.3]: https://github.com/OXID-eSales/paypal/compare/v6.2.2...v6.2.3
[6.2.2]: https://github.com/OXID-eSales/paypal/compare/v6.2.1...v6.2.2
[6.2.1]: https://github.com/OXID-eSales/paypal/compare/v6.2.0...v6.2.1
[6.2.0]: https://github.com/OXID-eSales/paypal/compare/v6.1.0...v6.2.0
[6.1.0]: https://github.com/OXID-eSales/paypal/compare/v6.0.2...v6.1.0
[6.0.2]: https://github.com/OXID-eSales/paypal/compare/v6.0.1...v6.0.2
[6.0.1]: https://github.com/OXID-eSales/paypal/compare/v6.0.0...v6.0.1
[6.0.0]: https://github.com/OXID-eSales/paypal/compare/v5.3.1...v6.0.0
[5.3.4]: https://github.com/OXID-eSales/paypal/compare/v5.3.3...b-5.x
[5.3.3]: https://github.com/OXID-eSales/paypal/compare/v5.3.2...v5.3.3
[5.3.2]: https://github.com/OXID-eSales/paypal/compare/v5.3.1...v5.3.2
[5.3.1]: https://github.com/OXID-eSales/paypal/compare/v5.3.0...v5.3.1
[5.3.0]: https://github.com/OXID-eSales/paypal/compare/v5.2.6...v5.3.0
[5.2.6]: https://github.com/OXID-eSales/paypal/compare/v5.2.5...v5.2.6
[5.2.5]: https://github.com/OXID-eSales/paypal/compare/v5.2.4...v5.2.5
[5.2.4]: https://github.com/OXID-eSales/paypal/compare/v5.2.3...v5.2.4
[5.2.3]: https://github.com/OXID-eSales/paypal/compare/v5.2.2...v5.2.3
[5.2.2]: https://github.com/OXID-eSales/paypal/compare/v5.2.1...v5.2.2
[5.2.1]: https://github.com/OXID-eSales/paypal/compare/v5.2.0...v5.2.1
[5.2.0]: https://github.com/OXID-eSales/paypal/compare/v5.1.6...v5.2.0
[5.1.6]: https://github.com/OXID-eSales/paypal/compare/v5.1.5...v5.1.6
[5.1.5]: https://github.com/OXID-eSales/paypal/compare/v5.1.4...v5.1.5
[5.1.4]: https://github.com/OXID-eSales/paypal/compare/v5.1.3...v5.1.4
[5.1.3]: https://github.com/OXID-eSales/paypal/compare/v5.1.2...v5.1.3
[5.1.2]: https://github.com/OXID-eSales/paypal/compare/v5.1.1...v5.1.2
[5.1.1]: https://github.com/OXID-eSales/paypal/compare/v5.1.0...v5.1.1
[5.1.0]: https://github.com/OXID-eSales/paypal/compare/v5.0.5...v5.1.0
[5.0.5]: https://github.com/OXID-eSales/paypal/compare/v5.0.4...v5.0.5
[5.0.4]: https://github.com/OXID-eSales/paypal/compare/v5.0.3...v5.0.4
[5.0.3]: https://github.com/OXID-eSales/paypal/compare/v5.0.2...v5.0.3
[5.0.2]: https://github.com/OXID-eSales/paypal/compare/v5.0.1...v5.0.2
[5.0.1]: https://github.com/OXID-eSales/paypal/compare/v5.0.0...v5.0.1
[5.0.0]: https://github.com/OXID-eSales/paypal/compare/v4.0.0...v5.0.0
