PayPal - The OXID eFire extension
======

![OXID eFire extension paypal](paypal_banner.jpg)

### Features

* PayPal is available as a regular payment method in the appropriate checkout step.
* PayPal express is already included at the checkout (registration step), at the so called “mini cart” and at the product detail pages.
* The module is highly customizable through many options at the admin panel, for example the step to transfer the money (at the order date or on delivery).
* Options are adjustable in different ways for each multishop (OXID eShop Enterprise Edition only).
* The payment page at PayPal can be customized by your own logo and the name of your online store.
* If you wish, you can transfer the content of the cart (purchased product items) to PayPal. Your customer has to decide whether this data shall be transferred or not.
* Partial payment is possible.
* The admin panel provides an overview of the PayPal payments per order.

### Setup

System requirements and installation instructions are described in the module documentation: https://docs.oxid-esales.com/modules/paypal/en/6.0/installation.html.

### Module installation via composer

In order to install the module via composer run one of the following commands in commandline in your shop base directory 
(where the shop's composer.json file resides).
* **composer require oxid-esales/paypal-module:^5.0.0** to install the released version compatible with OXID eShop Compilation 6.1
* **composer require oxid-esales/paypal-module:6.2.3** to install the released version compatible with OXID eShop Compilation 6.2
* **composer require oxid-esales/paypal-module:^6.3.0** to install the released version compatible with OXID eShop Compilation 6.3
  * Please note that 6.3.1 patch release is also compatible with OXID eShop Compilation 6.2 (PHP 7.1 and higher).
  * Please note that PayPal ^v6.3.0 is compatible with GraphQL Storefront ^v1.0.0.
* **composer require oxid-esales/paypal-module:^6.4.0** to install the released version compatible with OXID eShop Compilation 6.4
  * Please note that PayPal ^v6.4.0 is compatible with GraphQL Storefront ^v2.0.0.
* **composer require oxid-esales/paypal-module:dev-master** to install the latest unreleased version from github

### Running tests locally

To run this module tests locally, ensure the `test_config.yml` values are correct:
- Set `partial_module_paths` to `oe/oepaypal`
- Set `activate_all_modules` to `true`
- Set `run_tests_for_modules` to `true`
- Set `run_tests_for_shop` to `false`
- Set `additional_test_paths` to `''`
- Set `retry_times_after_test_fail` to `0`

For running acceptance tests you need to provide sandbox credentials data in `oepaypal/Tests/Acceptance/oepaypalData.php` file:
- Set `sOEPayPalSandboxUsername`
- Set `sOEPayPalSandboxPassword`
- Set `sOEPayPalSandboxSignature`
- Set `sBuyerLogin`
- Set `sBuyerPassword`
- Set `OEPayPalClientId`

For running codeception tests you need to provide sandbox credentials data in `oepaypal/Tests/Codeception/_data/oepaypalData.php` file:
- Set `sOEPayPalSandboxUsername`
- Set `sOEPayPalSandboxPassword`
- Set `sOEPayPalSandboxSignature`
- Set `sBuyerLogin`
- Set `sBuyerPassword`
- Set `OEPayPalClientId`

For running codeception tests in test group **paypal_graphql**, you need the [GraphQL Storefront module](https://github.com/OXID-eSales/graphql-storefront-module/) installed
and the following settings in the `test_config.yml`: 
- Set `partial_module_paths` to `oe/graphql-base,oe/graphql-storefront,oe/oepaypal`
- Set `activate_all_modules` to `true`
- Set `run_tests_for_modules` to `true`
- Set `run_tests_for_shop` to `false`
- Set `additional_test_paths` to `''`

For running the tests and more configuration options, follow the instructions from [here](https://github.com/OXID-eSales/testing_library#running-tests).

### Bugs and Issues

If you experience any bugs or issues, please report them in the section **module PayPal** of https://bugs.oxid-esales.com.

### Documentation

The module documentation can be found on our documentation platform: https://docs.oxid-esales.com/modules/paypal/en/6.3/index.html.