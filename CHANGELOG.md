# Change Log for OXID eSales PayPal module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).



## [Unreleased]

### Added
- Added class oePayPalIpnConfig.
- Added methods
  * oePayPalService::setPayPalIpnConfig()
  * oePayPalService::getPayPalIpnConfig()

### Changed

### Deprecated
- Deprecated the following methods: oePayPalConfig::getIPNResponseUrl().

### Removed

### Fixed
- Fixed 0006122 IPN postback DNS issue. Introduced \OxidEsales\PayPalModule\Core\IpnConfig class to 
  provide the necessary IPN parameters for host and url.  

### Security

## [3.3.2] - 2018-03-26

### Changed
- New partnercode Oxid_Cart_ECS_Shortcut is used for BUTTONSOURCE parameter in 
  PayPal's DoExpressCheckoutPayment API Operation (NVP) when PayPal payment was triggered 
  via shortcut button.

## [3.3.1] - 2017-11-28

### Changed
- Update PayPal button pictures.

## [3.3.0] - 2017-08-04

### Added
- Additional PayPal express checkout button in user checkout step in case no user is logged in.

[Unreleased]: https://github.com/OXID-eSales/paypal/compare/v3.3.2...HEAD
[v3.3.2]: https://github.com/OXID-eSales/paypal/compare/3.3.1...v3.3.2
[v3.3.1]: https://github.com/OXID-eSales/paypal/compare/3.3.0...v3.3.1
[v3.3.0]: https://github.com/OXID-eSales/paypal/compare/v3.2.4...v3.3.0
