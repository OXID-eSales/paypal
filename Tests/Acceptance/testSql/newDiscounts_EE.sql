# Discount demo data
INSERT IGNORE INTO `oxdiscount` (`OXID`,           `OXMAPID`, `OXSHOPID`, `OXACTIVE`, `OXTITLE`,                 `OXTITLE_1`,                         `OXAMOUNT`, `OXAMOUNTTO`, `OXPRICETO`, `OXPRICE`, `OXADDSUMTYPE`, `OXADDSUM`, `OXITMARTID`, `OXITMAMOUNT`, `OXITMMULTIPLE`, `OXSORT`) VALUES
                         ('testcatdiscount', 101,        1,          1,         'discount for category', 	'discount for category',            1,            999999,       999999,      1,       'abs',   	      5,        '',             0,             0, 1),
                         ('discount1',	     102,        1,          1,         'discount for product',     'discount from 10 till 20', 		 1,            999999,       20,          15,      '%',              2,        '',             0,             0, 2),
                         ('diskount2',       103,        1,          1,         '1 DE test discount',       'discount from 20 till 50',   	 1,            999999,       50,          20,      '%',              5,         '',            0,             0, 3),
                         ('diskount3',  	   104,        1,          1,         '1 DE test discount',       'discount from 50 till 999',    	 1,            999999,       999,         50,      'abs',            5,         '',            0,             0, 4),
                         ('itmdiscount',     105,        1,          1,         'Itm discount',             'Itm discount',         		 1,            999999,       0,           0,       'itm',            0,         '1001',        1,             0, 5);

REPLACE INTO `oxdiscount2shop` (`OXSHOPID`, `OXMAPOBJECTID`) VALUES
  (1, 101), (1, 102), (1, 103), (1, 104), (1, 105);


# object2discount
INSERT IGNORE INTO `oxobject2discount` (`OXID`,                       `OXDISCOUNTID`,    `OXOBJECTID`,                 `OXTYPE`) VALUES
                                ('bde47a823db7d82f5.99715633', 'testcatdiscount', 'testcategory0',              'oxcategories');