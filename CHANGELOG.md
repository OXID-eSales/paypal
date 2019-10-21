# Change Log for OXID eSales PayPal module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [6.0.1] - 2019-10-21

### Fixed
- Stabilize acceptance tests.

## [6.0.0] - 2019-07-23

### Changed
- Adapt tests to work with new phpunit.
- Replace deprecated calls to getConfig();
- Ensure support for PHP 7.2, drop support for PHP 5.6.
- Replace deprecated calls to getSession();

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


[v6.0.1]: https://github.com/OXID-eSales/paypal/compare/v6.0.0...v6.0.1
[v6.0.0]: https://github.com/OXID-eSales/paypal/compare/v5.2.5...v6.0.0
[v5.2.5]: https://github.com/OXID-eSales/paypal/compare/v5.2.4...v5.2.5
[v5.2.4]: https://github.com/OXID-eSales/paypal/compare/v5.2.3...v5.2.4
[v5.2.3]: https://github.com/OXID-eSales/paypal/compare/v5.2.2...v5.2.3
[v5.2.2]: https://github.com/OXID-eSales/paypal/compare/v5.2.1...v5.2.2
[v5.2.1]: https://github.com/OXID-eSales/paypal/compare/v5.2.0...v5.2.1
[v5.2.0]: https://github.com/OXID-eSales/paypal/compare/v5.1.6...v5.2.0
[v5.1.6]: https://github.com/OXID-eSales/paypal/compare/v5.1.5...v5.1.6
[v5.1.5]: https://github.com/OXID-eSales/paypal/compare/v5.1.4...v5.1.5
[v5.1.4]: https://github.com/OXID-eSales/paypal/compare/v5.1.3...v5.1.4
[v5.1.3]: https://github.com/OXID-eSales/paypal/compare/v5.1.2...v5.1.3
[v5.1.2]: https://github.com/OXID-eSales/paypal/compare/v5.1.1...v5.1.2
[v5.1.1]: https://github.com/OXID-eSales/paypal/compare/v5.1.0...v5.1.1
[v5.1.0]: https://github.com/OXID-eSales/paypal/compare/v5.0.5...v5.1.0
[v5.0.5]: https://github.com/OXID-eSales/paypal/compare/v5.0.4...v5.0.5
[v5.0.4]: https://github.com/OXID-eSales/paypal/compare/v5.0.3...v5.0.4
[v5.0.3]: https://github.com/OXID-eSales/paypal/compare/v5.0.2...v5.0.3
[v5.0.2]: https://github.com/OXID-eSales/paypal/compare/v5.0.1...v5.0.2
[v5.0.1]: https://github.com/OXID-eSales/paypal/compare/v5.0.0...v5.0.1
[v5.0.0]: https://github.com/OXID-eSales/paypal/compare/v4.0.0...v5.0.0
