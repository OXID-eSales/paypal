# change payment method price
UPDATE `oxpayments` SET `OXADDSUM` = '10.5' WHERE `OXID` = 'oxidpaypal';

# change shipping  method price
UPDATE `oxdelivery` SET `OXADDSUM` = '13' WHERE `OXID` = 'testdel';