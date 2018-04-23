# turn off PayPal options: PayPal Basis; PayPal Express; Enable Express Checkout in mini cart; Show express checkout in product details page;
DELETE FROM `oxconfig` WHERE `OXVARNAME` IN ('blOEPayPalStandardCheckout','blOEPayPalExpressCheckout','blOEPayPalECheckoutInMiniBasket','blOEPayPalECheckoutInDetails');

INSERT IGNORE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('blOEPayPalStandardCheckout', '1', 'module:oepaypal', 'blOEPayPalStandardCheckout', 'bool', ''),
('blOEPayPalExpressCheckout', '1', 'module:oepaypal', 'blOEPayPalExpressCheckout', 'bool', ''),
('blOEPayPalECheckoutInMiniBasket', '1', 'module:oepaypal', 'blOEPayPalECheckoutInMiniBasket', 'bool', ''),
('blOEPayPalECheckoutInDetails', '1', 'module:oepaypal', 'blOEPayPalECheckoutInDetails', 'bool', '');
