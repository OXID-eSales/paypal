SET @@session.sql_mode = '';

#Users demodata
REPLACE INTO `oxuser` SET
    OXID = 'paypaluser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXSHOPID = 1,
    OXUSERNAME = 'paypaluser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'TestUserName',
    OXLNAME = 'TestUserSurname',
    OXSTREET = 'Musterstr.šÄßüл',
    OXSTREETNR = '12',
    OXCITY = 'City',
    OXZIP = '12345',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1985-02-05 14:42:42',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';

REPLACE INTO `oxuser` SET
    OXID = 'defaultuser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXSHOPID = 1,
    OXUSERNAME = 'defaultuser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'UserName',
    OXLNAME = 'UserSurname',
    OXSTREET = 'MeineStrasse',
    OXSTREETNR = '12',
    OXCITY = 'Hamburg',
    OXZIP = '22001',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1985-02-05 14:42:42',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';

REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCUSTNR`, `OXUSTID`, `OXCOMPANY`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXADDINFO`, `OXCITY`, `OXCOUNTRYID`, `OXSTATEID`, `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`, `OXREGISTER`, `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXUPDATEKEY`, `OXUPDATEEXP`, `OXPOINTS`) VALUES
('oxdefaultadmin', 1, 'malladmin', 1, 'admin', 'e3a8a383819630e42d9ef90be2347ea70364b5efbb11dfc59adbf98487e196fffe4ef4b76174a7be3f2338581e507baa61c852b7d52f4378e21bd2de8c1efa5e', '61646D696E61646D696E61646D696E', 1, '', 'Your Company Name', 'John', 'Doe', 'Maple Street', '2425', '', 'Any City', 'a7c40f631fc920687.20179984', '', '9041', '217-8918712', '217-8918713', 'MR', 1000, '2003-01-01 00:00:00', '2003-01-01 00:00:00', '', '', '0000-00-00', '', '', 0, 0);


REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('660b8f058paypal6ada9ee7586d946ef', 'oxidpaypal', 'a7c40f6320aeb2ec2.72885259', 'oxcountry');
REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('26586trf09oiu927b50ed832f76feed4', 'oxidpaypal', '1b842e732a23255b1.91207750', 'oxdelset');
REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('26586trf09oiu927b50ed832f76feeg5', 'oxidpaypal', '1b842e732a23255b1.91207751', 'oxdelset');
