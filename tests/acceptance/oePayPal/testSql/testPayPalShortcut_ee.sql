# turn off PayPal options: PayPal Basis; PayPal Express; Enable Express Checkout in mini cart; Show express checkout in product details page;
DELETE FROM `oxconfig` WHERE `OXVARNAME` IN ('blOEPayPalStandardCheckout','blOEPayPalExpressCheckout','blOEPayPalECheckoutInMiniBasket','blOEPayPalECheckoutInDetails');

INSERT INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('__blOEPayPalStandardCheckout', '1', 'module:oepaypal', 'blOEPayPalStandardCheckout', 'bool', ''),
('__blOEPayPalExpressCheckout', '1', 'module:oepaypal', 'blOEPayPalExpressCheckout', 'bool', ''),
('__blOEPayPalECheckoutInMiniBasket', '1', 'module:oepaypal', 'blOEPayPalECheckoutInMiniBasket', 'bool', ''),
('__blOEPayPalECheckoutInDetails', '1', 'module:oepaypal', 'blOEPayPalECheckoutInDetails', 'bool', '');