SET @@session.sql_mode = '';

# making EN as default lang
UPDATE `oxconfig` SET `OXVARVALUE` = 1 WHERE `OXVARNAME` = 'sDefaultLang';
UPDATE `oxconfig` SET `OXVARVALUE` = 'a:2:{s:2:"de";a:4:{s:6:"baseId";i:0;s:6:"active";s:1:"1";s:4:"sort";s:1:"1";s:7:"default";b:0;}s:2:"en";a:4:{s:6:"baseId";i:1;s:6:"active";s:1:"1";s:4:"sort";s:1:"2";s:7:"default";s:1:"1";}}' WHERE `OXVARNAME` = 'aLanguageParams';

# Activate Azure theme
UPDATE `oxconfig` SET `OXVARVALUE` = 'azure' WHERE `OXVARNAME` = 'sTheme';

SET @@session.sql_mode = '';

# Articles demo data
INSERT IGNORE INTO `oxarticles` (`OXID`, `OXSHOPID`, `OXACTIVE`, `OXARTNUM`, `OXTITLE`,        `OXSHORTDESC`,              `OXPRICE`, `OXUNITNAME`, `OXUNITQUANTITY`, `OXVAT`, `OXWEIGHT`, `OXSTOCK`, `OXSTOCKFLAG`, `OXINSERT`,   `OXTIMESTAMP`,        `OXLENGTH`, `OXWIDTH`, `OXHEIGHT`, `OXSEARCHKEYS`, `OXISSEARCH`, `OXVARMINPRICE`, `OXTITLE_1`,      `OXSHORTDESC_1`,             `OXSUBCLASS`) VALUES
                         ('1000',  1,          1,         '1000',     'Test product 0', 'Test product 0 short desc', 10,        'kg',          2,                NULL,    24,         15,        1,            '2008-02-04', '2008-02-04 17:07:29', 1,          2,         2,         'search1000',    1,            50,             'Test product 0', 'Test product 0 short desc', 'oxarticle'),
                         ('1001',  1,          1,         '1001',     'Test product 1', 'Test product 1 short desc', 0.99,      '',            0,                NULL,    0,          0,         1,            '2008-02-04', '2008-02-04 17:35:43', 0,          0,         0,         'search1001',    1,            100,            'Test product 1', 'Test product 1 short desc', 'oxarticle'),
                         ('1003',  1,          1,         '1003',     'Test product 3', 'Test product 3 short desc', 15,        'kg',          2,                NULL,    24,         15,        1,            '2008-02-04', '2008-02-04 17:07:29', 1,          2,         2,         'search1003',    1,            50,             'Test product 3', 'Test product 0 short desc', 'oxarticle'),
                         ('1004',  1,          1,         '1004',     'Test product 4', 'Test product 4 short desc', 15,        'kg',          2,                NULL,    24,         15,        1,            '2008-02-04', '2008-02-04 17:07:29', 1,          2,         2,         'search1004',    1,            50,             'Test product 4', 'Test product 4 short desc', 'oxarticle');


# Articles long desc
INSERT IGNORE INTO `oxartextends` (`OXID`, `OXLONGDESC`,                             `OXLONGDESC_1`) VALUES
                           ('1000', '<p>Test product 0 long description</p>', '<p>Test product 0 long description</p>'),
                           ('1001', '<p>Test product 1 long description</p>', '<p>Test product 1 long description</p>');

# Categories demo data
INSERT IGNORE INTO `oxcategories` (`OXID`,          `OXPARENTID`, `OXLEFT`, `OXRIGHT`, `OXROOTID`,     `OXSORT`, `OXACTIVE`, `OXHIDDEN`, `OXSHOPID`,    `OXTITLE`,         `OXDESC`,               `OXLONGDESC`,                  `OXDEFSORT`, `OXDEFSORTMODE`, `OXACTIVE_1`, `OXTITLE_1`,       `OXDESC_1`,             `OXLONGDESC_1`,                `OXVAT`, `OXSKIPDISCOUNTS`, `OXSHOWSUFFIX`) VALUES
                           ('testcategory0', 'oxrootid',    1,        4,        'testcategory0', 1,        1,          0,          1, 'Test category 0', 'Test category 0 desc', '<p>Category 0 long desc</p>', 'oxartnum',   0,               1,           'Test category 0', 'Test category 0 desc', '<p>Category 0 long desc</p>',  NULL,    0,                 1);

# Payment demo data
REPLACE INTO `oxpayments` (`OXID`,       `OXACTIVE`, `OXDESC`,             `OXADDSUM`, `OXADDSUMTYPE`, `OXFROMBONI`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXCHECKED`, `OXDESC_1`,            `OXLONGDESC`,                `OXLONGDESC_1`,             `OXSORT`) VALUES
                         ('testpayment', 1,         'Test payment method', 0,         'abs',           0,            0,              99999,        0,          'Test payment method', 'Short payment description', 'Short payment description', 0);

# Delivery set demo data
INSERT IGNORE INTO `oxdeliveryset` (`OXID`,      `OXSHOPID`, `OXACTIVE`, `OXTITLE`,      `OXTITLE_1`,   `OXPOS`) VALUES
                            ('testdelset', 1,          1,         'Test S&H set', 'Test S&H set', 0);

# Delivery demo data
INSERT IGNORE INTO `oxdelivery` (`OXID`,   `OXSHOPID`, `OXACTIVE`, `OXTITLE`,       `OXTITLE_1`,     `OXADDSUMTYPE`, `OXADDSUM`, `OXDELTYPE`, `OXPARAM`, `OXPARAMEND`, `OXFIXED`, `OXSORT`, `OXFINALIZE`) VALUES
                         ('testdel', 1,          1,         'Test delivery', 'Test delivery', 'abs',           0,         'a',          1,         99999,        0,         9998,     1);

# User demo data
INSERT IGNORE INTO `oxuser` (`OXID`,     `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`,    `OXUSERNAME`,         `OXPASSWORD`,                       `OXPASSSALT`,        `OXCUSTNR`, `OXUSTID`, `OXCOMPANY`,             `OXFNAME`,                 `OXLNAME`,      `OXSTREET`,         `OXSTREETNR`, `OXADDINFO`,                `OXCITY`,            `OXCOUNTRYID`,                `OXZIP`, `OXFON`, `OXFAX`, `OXSAL`, `OXBONI`, `OXCREATE`,            `OXREGISTER`,          `OXPRIVFON`, `OXMOBFON`, `OXBIRTHDATE`, `OXURL`, `OXUPDATEKEY`, `OXUPDATEEXP`) VALUES
                     ('testuser',  1,         'user',      1, 'testing_account@oxid-esales.dev', 'c9dadd994241c9e5fa6469547009328a', '7573657275736572',   8,         '',        'SeleniumTestCase Äß\'ü', 'Testing user acc Äß\'ü', 'PayPal Äß\'ü', 'Musterstr. Äß\'ü', '1',          'Testing acc for Selenium', 'Musterstadt Äß\'ü', 'a7c40f631fc920687.20179984', '79098', '',      '',      'MR',    500,      '2008-02-05 14:42:42', '2008-02-05 14:42:42', '',          '',         '0000-00-00',  '',      '',            0),
                     ('testusera', 1,         'user',      1, 'testing_account2@oxid-esales.com',    'a233c8b71a465807980f4b2b18f50fec', '757365724175736572', 9,         '',        'SeleniumTestCase',       'Testing user acc',       'PayPal',     'Musterstr.',       '2',          'Testing acc for Selenium', 'Musterstadt',       'a7c40f631fc920687.20179984', '79098', '',      '',      'MRS',   500,      '2008-02-05 14:49:31', '2008-02-05 14:49:31', '',          '',         '0000-00-00',  '',    '',            0);

# Delivery2DeliverySet
INSERT IGNORE INTO `oxdel2delset` (`OXID`,                       `OXDELID`, `OXDELSETID`) VALUES
                           ('15947a84ade6246c1.43630378', 'testdel', 'testdelset');
# article2category
INSERT IGNORE INTO `oxobject2category` (`OXID`,                      `OXOBJECTID`, `OXCATNID`,     `OXPOS`, `OXTIME`) VALUES
                                ('96047a71f4d4e34d9.76958590', '1000',       'testcategory0', 0,       1202134861),
                                ('96047a72713424e14.02408995', '1001',       'testcategory0', 0,       1202136851);

# object2delivery
INSERT IGNORE INTO `oxobject2delivery` (`OXID`,                       `OXDELIVERYID`, `OXOBJECTID`,                 `OXTYPE`) VALUES
                                ('15947a8495c225b22.01517980', 'testdel',      'a7c40f631fc920687.20179984', 'oxcountry'),
                                ('15947a84b002905d3.97006730', 'testdelset',   'a7c40f631fc920687.20179984', 'oxdelset');

# object2group
INSERT IGNORE INTO `oxobject2group` (`OXID`,                      `OXSHOPID`, `OXOBJECTID`,  `OXGROUPSID`) VALUES
                             ('96047a71c6f049988.94873501', 1,         'testpayment', 'oxidnewcustomer'),
                             ('15947a85a7ce23451.42160470', 1,         'testuser',    'oxidnewcustomer'),
                             ('15947a861e1dc7461.03139047', 1,         'testusera',   'oxidnewcustomer');

# object2payment
INSERT IGNORE INTO `oxobject2payment` (`OXID`,                       `OXPAYMENTID`,    `OXOBJECTID`,                 `OXTYPE`) VALUES
                               ('bde47a8223ddc3572.12628821', 'testpayment',    'a7c40f631fc920687.20179984', 'oxcountry'),
                               ('15947a84af5c69698.88858631', 'testpayment',    'testdelset',                 'oxdelset'),
                               ('15947a84af8e08906.22057468', 'oxidcreditcard', 'testdelset',                 'oxdelset'),
                               ('15947a84af8e151b6.25811193', 'oxidcashondel',  'testdelset',                 'oxdelset');
# more active countries
UPDATE `oxcountry` SET `OXACTIVE` = 1 WHERE `OXTITLE_1` = 'Germany';
UPDATE `oxcountry` SET `OXACTIVE` = 1 WHERE `OXTITLE_1` = 'Austria';
UPDATE `oxcountry` SET `OXACTIVE` = 1 WHERE `OXTITLE_1` = 'Switzerland';
UPDATE `oxcountry` SET `OXACTIVE` = 1 WHERE `OXTITLE_1` = 'Liechtenstein';

#updating oxConfig settings
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'bl_perfLoadSelectLists'         AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'bl_perfShowLeftBasket'          AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'bl_perfShowRightBasket'         AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'bl_perfUseSelectlistPrice'      AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'bl_perfLoadSelectListsInAList'  AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = false WHERE `OXVARNAME` = 'blShowVATForDelivery'           AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = false WHERE `OXVARNAME` = 'blCalcSkontoForDelivery'        AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = false WHERE `OXVARNAME` = 'blShowVATForPayCharge'          AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = false WHERE `OXVARNAME` = 'bl_perfShowActionCatArticleCnt' AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'blOtherCountryOrder'            AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = false WHERE `OXVARNAME` = 'blCheckTemplates'               AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'blDisableNavBars'               AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = true   WHERE `OXVARNAME` = 'blAllowUnevenAmounts'           AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = ''           WHERE `OXVARNAME` = 'blTopNaviLayout'                AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = '0'         WHERE `OXVARNAME` = 'iNewBasketItemMessage'          AND `OXSHOPID` = 1;
UPDATE `oxconfig` SET `OXVARVALUE` = '3'         WHERE `OXVARNAME` = 'iTopNaviCatCount'               AND `OXSHOPID` = 1 AND `OXMODULE` = 'theme:azure';
UPDATE `oxconfig` SET `OXVARVALUE` = '3'         WHERE `OXVARNAME` = 'iTopNaviCatCount'               AND `OXSHOPID` = 1 AND `OXMODULE` = 'theme:azure';

INSERT IGNORE INTO `oxconfig` (`OXID`,                      `OXSHOPID`,    `OXVARNAME`,               `OXVARTYPE`, `OXVARVALUE`) VALUES
                       ('a0147ac17160e6556.25324407', 1, 'blAllowNegativeStock',    'bool',       false),
                       ('a0147ac17160fb173.47699884', 1, 'blOverrideZeroABCPrices', 'bool',       false),
                       ('a0147ac1716156ce5.75228443', 1, 'blBidirectCross',         'bool',       false),
                       ('a0147ac1781cb8160.56740074', 1, 'blDisableNavBars',        'bool',       true),
                       ('33bd5512d7d7366681eb850502', 1, 'blOverrideZeroABCPrices', 'bool',       true),
                       ('01d42bbeced070f0aef7aebff4', 1, 'blUseContentCaching',     'bool',       true),
                       ('00fc37d94581704c4ac5a2803d', 1, 'blMallUsers',             'bool',       true);
UPDATE `oxconfig` SET `OXVARVALUE` = 'a:4:{s:8:"Standard";s:2:"10";s:8:"1024x768";s:2:"10";s:9:"1280x1024";s:2:"17";s:9:"1600x1200";s:2:"22";}' WHERE `OXVARNAME` = 'aInterfaceProfiles';
UPDATE `oxconfig` SET `OXVARVALUE` = 'a:6:{i:0;s:2:"10";i:1;s:2:"20";i:2;s:2:"50";i:3;s:3:"100";i:4;s:1:"2";i:5;s:1:"1";}' WHERE `OXVARNAME` = 'aNrofCatArticles';
UPDATE `oxconfig` SET `OXVARVALUE` = 'a:7:{i:0;s:13:"oxrss_topshop";i:1;s:13:"oxrss_bargain";i:2;s:12:"oxrss_newest";i:3;s:16:"oxrss_categories";i:4;s:12:"oxrss_search";i:5;s:17:"oxrss_recommlists";i:6;s:20:"oxrss_recommlistarts";}' WHERE `OXVARNAME` = 'aRssSelected';

REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`,   `OXVARNAME`,                     `OXVARTYPE`, `OXVARVALUE`) VALUES
                       ('1ec42a395d0595ee7741091898848987', 1, 'theme:azure', 'sCatPromotionsize', 'str', 0xb06fb441c2bd94),
                       ('18a9473894d473f6ed28f04e80d929fc', 1, 'theme:azure', 'bl_showCompareList', 'bool', 1),
                       ('18acb2f595da54b5f865e54aa5cdb967', 1, 'theme:azure', 'bl_showListmania', 'bool', 1),
                       ('18a12329124850cd8f63cda6e8e7b4e1', 1, 'theme:azure', 'bl_showWishlist', 'bool', 1),
                       ('18a23429124850cd8f63cda6e8e7b4e1', 1, 'theme:azure', 'bl_showVouchers', 'bool', 1),
                       ('18a34529124850cd8f63cda6e8e7b4e1', 1, 'theme:azure', 'bl_showGiftWrapping', 'bool', 1),
                       ('15342e4cab0ee774acb3905838384984', 1, 'theme:azure', 'blShowBirthdayFields', 'bool', 1),
                       ('11296159b7641d31b93423972af6150b', 1, 'theme:azure', 'iTopNaviCatCount', 'str', 1),
                       ('1ec42a395d0595ee7741091898848989', 1, 'theme:azure', 'sDefaultListDisplayType', 'select', 'infogrid'),
                       ('1ec42a395d0595ee7741091898848992', 1, 'theme:azure', 'sStartPageListDisplayType', 'select', 'infogrid'),
                       ('1ec42a395d0595ee7741091898848990', 1, 'theme:azure', 'blShowListDisplayType', 'bool', 1),
                       ('1545423fe8ce213a0435345552230295', 1, 'theme:azure', 'aNrofCatArticles', 'arr', 'a:6:{i:0;s:2:"10";i:1;s:2:"20";i:2;s:2:"50";i:3;s:3:"100";i:4;s:1:"2";i:5;s:1:"1";}'),
                       ('1ec42a395d0595ee7741091898848991', 1, 'theme:azure', 'aNrofCatArticlesInGrid', 'arr', 'a:4:{i:0;s:2:"12";i:1;s:2:"16";i:2;s:2:"24";i:3;s:2:"32";}');

#updating smtp and emails data
UPDATE `oxshops` SET `OXINFOEMAIL` = 'testing_account@oxid-esales.dev', `OXORDEREMAIL` = 'testing_account@oxid-esales.dev', `OXOWNEREMAIL` = 'testing_account@oxid-esales.dev', `OXSMTP` = 'localhost', `OXDEFCAT` = '' WHERE `OXID` = 1;


#paypal demodata
REPLACE INTO `oxpayments`
(`OXID`, `OXACTIVE`, `OXDESC`, `OXADDSUM`, `OXADDSUMTYPE`, `OXFROMBONI`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXVALDESC`, `OXCHECKED`, `OXDESC_1`, `OXVALDESC_1`, `OXDESC_2`, `OXVALDESC_2`, `OXDESC_3`, `OXVALDESC_3`, `OXLONGDESC`, `OXLONGDESC_1`, `OXLONGDESC_2`, `OXLONGDESC_3`, `OXSORT`)
VALUES
('oxidpaypal', 1, 'PayPal', 0, 'abs', 0, 0, 99999, '', 0, 'PayPal', '', '', '', '', '', '<div>Bei Auswahl der Zahlungsart PayPal werden Sie im nächsten Schritt zu PayPal weitergeleitet. Dort können Sie sich in Ihr PayPal-Konto einloggen oder ein neues PayPal-Konto eröffnen und die Zahlung autorisieren. Sobald Sie Ihre Daten für die Zahlung bestätigt haben, werden Sie automatisch wieder zurück in den Shop geleitet, um die Bestellung abzuschließen.</div>
<div style="margin-top: 5px">Erst dann wird die Zahlung ausgeführt.</div>', '<div>When selecting this payment method you are being redirected to PayPal where you can login into your account or open a new account. In PayPal you are able to authorize the payment. As soon you have authorized the payment, you are again redirected to our shop where you can confirm your order.</div>
<div style="margin-top: 5px">Only after confirming the order, transfer of money takes place.</div>', '', '', 0);

CREATE TABLE IF NOT EXISTS `oepaypal_transactions` (
    `oepaypal_id` int(11) NOT NULL auto_increment,
    `oepaypal_transactiondata` text NOT NULL,
    PRIMARY KEY (`oepaypal_id`))
ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE IF NOT EXISTS `efi_paypal_cfg` ( `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , `paypal_1` VARCHAR( 255 ) NOT NULL , `paypal_2` VARCHAR( 255 ) NOT NULL , `paypal_3` VARCHAR( 255 ) NOT NULL , `paypal_4` VARCHAR( 255 ) NOT NULL , `paypal_5` VARCHAR( 255 ) NOT NULL , `paypal_6` VARCHAR( 255 ) NOT NULL ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('6604d679fpaypal11518196d95f45fd3', 'oxidpaypal', 'a7c40f631fc920687.20179984', 'oxcountry');
INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('660b8f058paypal6ada9ee7586d946ef', 'oxidpaypal', 'a7c40f6320aeb2ec2.72885259', 'oxcountry');
INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('66024d636paypalbb5638b1e80a4e62c', 'oxidpaypal', 'a7c40f6321c6f6109.43859248', 'oxcountry');
INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('660f0f63epaypald63a78af0a4b7d44f', 'oxidpaypal', 'testdelset',                 'oxdelset');
INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('26586trf09oiu927b50ed832f76feed4', 'oxidpaypal', '1b842e732a23255b1.91207750', 'oxdelset');
UPDATE `oxdelivery` SET `OXADDSUM` = '0' WHERE `OXTITLE` = 'Versandkosten für Beispiel Set1: UPS 48 Std.: 9,90.-';
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c463cpaypal0ea6fc4d659ce444e697', 1, 'oxidpaypal', 'oxidforeigncustomer');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c4cc2paypal4ee57047656d8d3be15o', 1, 'oxidpaypal', 'oxidsmallcust');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c4712paypalc96fa79b4e205c4e94f8', 1, 'oxidpaypal', 'oxidgoodcust');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c4b36paypal4bbea41ecda871e33590', 1, 'oxidpaypal', 'oxiddealer');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c40ccpaypal086c42ae2e7c7e3ad4ae', 1, 'oxidpaypal', 'oxidnewcustomer');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c47capaypalfa475e0c6da4fb9d59f9', 1, 'oxidpaypal', 'oxidcustomer');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c40e3paypal8e5c9169dd21851947bb', 1, 'oxidpaypal', 'oxidmiddlecust');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c4a95paypald75bf18f96bb04352720', 1, 'oxidpaypal', 'oxidnotyetordered');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('9c4eb4paypal6cae937f7f7d781d54bb', 1, 'oxidpaypal', 'oxidadmin');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdbeadpaypal295e83ca0a8ac74942a1', 1, 'oxidpaypal', 'oxidblacklist');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdb817paypal4f300982c440d6bff476', 1, 'oxidpaypal', 'oxidblocked');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdb7b8paypal00b89b4debfb8f90c54b', 1, 'oxidpaypal', 'oxidnewsletter');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdb8fbpaypal65b73a73edd935eac4d9', 1, 'oxidpaypal', 'oxidpowershopper');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdbd1dpaypal7ab6a247dc8f5bab3932', 1, 'oxidpaypal', 'oxidpricea');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdbe0epaypalbbef45ec71bcbd6f41ae', 1, 'oxidpaypal', 'oxidpriceb');
INSERT IGNORE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`) VALUES ('cdbfe1paypalc65e3a0e54c2eb83b7ec', 1, 'oxidpaypal', 'oxidpricec');
