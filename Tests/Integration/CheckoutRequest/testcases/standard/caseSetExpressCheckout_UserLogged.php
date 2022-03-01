<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

use OxidEsales\Eshop\Core\Registry as EshopRegistry;

$data = array(
    'class'         => \OxidEsales\PayPalModule\Controller\StandardDispatcher::class,
    'action'        => 'setExpressCheckout',
    'articles'      => array(
        0 => array(
            'oxid'    => 'testUserLogged1',
            'oxprice' => 10,
            'oxvat'   => 10,
            'amount'  => 3,
        ),
    ),
    'groups'        => array(
        0 => array(
            'oxid'           => 'UserLoggedTestGroup',
            'oxactive'       => 1,
            'oxtitle'        => 'checkoutTestGroup',
            'oxobject2group' => array('TestLoggedUser', 'oxidpaypal'),
        ),
    ),
    'user'          => array(
        'oxid'        => 'TestLoggedUser',
        'oxactive'    => 1,
        'oxusername'  => 'testuser@email.com',
        'oxfname'     => 'Name',
        'oxlname'     => 'LName',
        'oxstreet'    => 'Street',
        'oxstreetnr'  => 'StreetNr',
        'oxcity'      => 'City',
        'oxzip'       => 'ZipCode',
        'oxfon'       => 'PhoneNr',
        'oxcountryid' => '8f241f11096877ac0.98748826', // United States
        'oxstateid'   => 'IL', // Illinois
    ),
    'config'        => array(
        'blSeoMode' => false,
        'sOEPayPalTransactionMode' => 'Sale',
    ),
    'requestToShop' => array(
        'displayCartInPayPal' => true,
    ),
    'expected'      => array(
        'requestToPayPal' => array(
            'VERSION'                            => '84.0',
            'PWD'                                => EshopRegistry::getConfig()->getConfigParam('sOEPayPalSandboxPassword'),
            'USER'                               => EshopRegistry::getConfig()->getConfigParam('sOEPayPalSandboxUsername'),
            'SIGNATURE'                          => EshopRegistry::getConfig()->getConfigParam('sOEPayPalSandboxSignature'),
            'CALLBACKVERSION'                    => '84.0',
            'LOCALECODE'                         => 'de_DE',
            'SOLUTIONTYPE'                       => 'Mark',
            'BRANDNAME'                          => 'PayPal Testshop',
            'CARTBORDERCOLOR'                    => '2b8da4',
            'RETURNURL'                          => '{SHOP_URL}index.php?lang=0&sid=&rtoken=token&shp={SHOP_ID}&cl=oepaypalstandarddispatcher&fnc=getExpressCheckoutDetails',
            'CANCELURL'                          => '{SHOP_URL}index.php?lang=0&sid=&rtoken=token&shp={SHOP_ID}&cl=payment',
            'PAYMENTREQUEST_0_PAYMENTACTION'     => 'Sale',
            'NOSHIPPING'                         => '0',
            'ADDROVERRIDE'                       => '1',
            'PAYMENTREQUEST_0_AMT'               => '27.27',
            'PAYMENTREQUEST_0_CURRENCYCODE'      => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT'           => '27.27',
            'PAYMENTREQUEST_0_SHIPPINGAMT'       => '0.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT'       => '0.00',
            'L_SHIPPINGOPTIONISDEFAULT0'         => 'true',
            'L_SHIPPINGOPTIONNAME0'              => '#1',
            'L_SHIPPINGOPTIONAMOUNT0'            => '0.00',
            'PAYMENTREQUEST_0_DESC'              => 'Ihre Bestellung bei PayPal Testshop in Höhe von 27,27 EUR',
            'PAYMENTREQUEST_0_CUSTOM'            => 'Ihre Bestellung bei PayPal Testshop in Höhe von 27,27 EUR',
            'MAXAMT'                             => '28.27',
            'L_PAYMENTREQUEST_0_NAME0'           => '',
            'L_PAYMENTREQUEST_0_AMT0'            => '9.09',
            'L_PAYMENTREQUEST_0_QTY0'            => '3',
            'L_PAYMENTREQUEST_0_ITEMURL0'        => '{SHOP_URL}index.php?cl=details&amp;anid=testUserLogged1',
            'L_PAYMENTREQUEST_0_NUMBER0'         => '',
            'EMAIL'                              => 'testuser@email.com',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'Name LName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street StreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'ZipCode',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'PhoneNr',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'US',
            'PAYMENTREQUEST_0_SHIPTOSTATE'       => 'IL',
            'METHOD'                             => 'SetExpressCheckout',
        ),
        'header'          => array(
            0 => 'POST /cgi-bin/webscr HTTP/1.1',
            1 => 'Content-Type: application/x-www-form-urlencoded',
            2 => 'Host: api-3t.sandbox.paypal.com',
            3 => 'Connection: close',
        )
    )
);
