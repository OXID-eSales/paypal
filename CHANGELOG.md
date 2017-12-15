# Change Log for OXID eSales PayPal module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).



## [Unreleased]

### Added
- Add hidden configuration parameter OEPayPalDisableIPN and method 
  \OxidEsales\PayPalModule\CoreConfig::suppressIPNCallbackUrl() to be able to suppress 
  sending PAYMENTREQUEST_0_NOTIFYURL optional request parameter to paypal. 
  At the moment used for acceptance tests as they do not test IPN.

### Changed
- Log Acceptance test debug information into log/oepaypal_acceptance_log.txt instead of log/EXCEPTION_LOG.txt.

### Deprecated

### Removed

### Fixed

### Security

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


[Unreleased]: https://github.com/OXID-eSales/paypal/compare/v5.1.4...HEAD
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
