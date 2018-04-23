# assign PayPal payment method to Standard delivery method
REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES  ('660f0f63epaypald63a78af0a4b44', 'oxidpaypal', 'oxidstandard', 'oxdelset');

# change test article price
Update `oxarticles` set `OXPRICE` = '81.0' WHERE `OXID` = '1001';