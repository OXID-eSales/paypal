SET @@session.sql_mode = '';

# Delivery set demo data
REPLACE INTO `oxdeliveryset` (`OXID`,      `OXMAPID`,  `OXSHOPID`, `OXACTIVE`, `OXTITLE`,      `OXTITLE_1`,   `OXPOS`) VALUES
                            ('testdelset', 101,        1,            1,         'Test ä S&H set', 'Test ä S&H set', 0);

# assign PayPal payment method to Standard delivery method
REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES  ('660f0f63epaypald63a78af0a4b44', 'oxidpaypal', 'testdelset', 'oxdelset');

# change test article price
Update `oxarticles` set `OXPRICE` = '0.99' WHERE `OXID` = '1001';