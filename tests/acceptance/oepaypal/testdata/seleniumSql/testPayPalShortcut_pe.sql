# turn off PayPal options: PayPal Basis; PayPal Express; Enable Express Checkout in mini cart; Show express checkout in product details page;
DELETE FROM `oxconfig` WHERE `OXVARNAME` IN ('blOEPayPalStandardCheckout','blOEPayPalExpressCheckout','blOEPayPalECheckoutInMiniBasket','blOEPayPalECheckoutInDetails');

INSERT INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('__blOEPayPalStandardCheckout', 'oxbaseshop', 'module:oepaypal', 'blOEPayPalStandardCheckout', 'bool', ''),
('__blOEPayPalExpressCheckout', 'oxbaseshop', 'module:oepaypal', 'blOEPayPalExpressCheckout', 'bool', ''),
('__blOEPayPalECheckoutInMiniBasket', 'oxbaseshop', 'module:oepaypal', 'blOEPayPalECheckoutInMiniBasket', 'bool', ''),
('__blOEPayPalECheckoutInDetails', 'oxbaseshop', 'module:oepaypal', 'blOEPayPalECheckoutInDetails', 'bool', '');

