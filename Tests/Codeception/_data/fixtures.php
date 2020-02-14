<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

return [
    'adminUser' => [
        "userId" => "admin",
        "userLoginName" => "admin@myoxideshop.com",
        "userPassword" => "admin0303",
        "userName" => "John",
        "userLastName" => "Doe",
    ],
    // This user will be created and will be available in ce|pe|ee demodata
    'user' => [
        'oxid'         => 'pptestuser',
        'oxactive'     => 1,
        'oxrights'     => 'user',
        'oxshopid'     => 1,
        'oxusername'   => 'testing_account@oxid-esales.dev',
        'oxpassword'   => '$2y$10$lj90Q/CaB0IB8PZemQW4Xu1/EWvAhkW9SOZ1Sr3JBx8DOmd3qz7bu',
        'oxfname'      => 'User',
        'oxlname'      => 'User',
        'oxstreet'     => 'Street',
        'oxstreetnr'   => 'Street Number',
        'oxzip'        => 'ZIP',
        'oxcity'       => 'City',
        'oxcountryid'  => 'a7c40f631fc920687.20179984',
        'oxboni'       => '600',
        'oxcreate'     => date("Y-m-d H:i:s"),
        'oxregister'   => date("Y-m-d H:i:s"),
        'oxbirthdate'  => date("Y-m-d"),
        'oxpasssalt'   => ''
    ],
    'userPassword'  => 'useruser',
    // This product is available in ce|pe|ee demodata
    'product' => [
        'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
        'title' => 'Kuyichi leather belt JEVER',
        'amount' => '4',
        'price' => '119,60 €'
    ],
    'variant' => [
        'id' => '531b537118f5f4d7a427cdb825440922',
        'maxNettoPrice' => 83.95,
        'maxBruttoPrice' => 99.9,
        'minNettoPrice' => 78.07,
        'minBruttoPrice' => 92.9,
    ],
];
