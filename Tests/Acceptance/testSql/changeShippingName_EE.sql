SET @@session.sql_mode = '';

# Delivery set demo data
REPLACE INTO `oxdeliveryset` (`OXID`, `OXSHOPID`, `OXACTIVE`, `OXACTIVEFROM`, `OXACTIVETO`, `OXTITLE`, `OXTITLE_1`, `OXPOS`) VALUES ('testdelset', 1,  1, '1990-01-01 00:00:00', '2099-12-12 23:59:59', 'Test ä S&H set', 'Test ä S&H set', 0);

# Assign delivery set to shop
INSERT IGNORE INTO `oxdeliveryset2shop` (`OXSHOPID`, `OXMAPOBJECTID`) VALUES (1, 904);

# assign PayPal payment method to Standard delivery method
REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES  ('660f0f63epaypald63a78af0a4b44', 'oxidpaypal', 'testdelset', 'oxdelset');

# change test article price
UPDATE `oxarticles` SET `OXPRICE` = '0.99' WHERE `OXID` = '1001';