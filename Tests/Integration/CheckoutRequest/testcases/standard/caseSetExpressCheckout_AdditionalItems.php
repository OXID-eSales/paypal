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

$data = array(
    'class'         => \OxidEsales\PayPalModule\Controller\StandardDispatcher::class,
    'action'        => 'setExpressCheckout',
    'articles'      => array(
        0 => array(
            'oxid'    => 'testAddItems1',
            'oxprice' => 10,
            'oxvat'   => 0,
            'amount'  => 1,
        ),
        1 => array(
            'oxid'    => 'testAddItems2',
            'oxprice' => 10,
            'oxvat'   => 0,
            'amount'  => 1,
        ),
    ),
    'costs'         => array(
        'wrapping' => array(
            0 => array(
                'oxtype'     => 'WRAP',
                'oxname'     => 'testWrap9001',
                'oxprice'    => 0.50,
                'oxactive'   => 1,
                'oxarticles' => array('testAddItems1', 'testAddItems2')
            ),
            1 => array(
                'oxtype'   => 'CARD',
                'oxname'   => 'testCard9001',
                'oxprice'  => 0.30,
                'oxactive' => 1,
            ),
        ),
    ),
    'config'        => array(
        'blSeoMode' => false
    ),
    'requestToShop' => array(
        'displayCartInPayPal' => true,
    ),
    'expected'      => array(
        'requestToPayPal' => array(
            'VERSION'                            => '84.0',
            'PWD'                                => '',
            'USER'                               => '',
            'SIGNATURE'                          => '',
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
            'PAYMENTREQUEST_0_AMT'               => '21.30',
            'PAYMENTREQUEST_0_CURRENCYCODE'      => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT'           => '21.30',
            'PAYMENTREQUEST_0_SHIPPINGAMT'       => '0.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT'       => '0.00',
            'L_SHIPPINGOPTIONISDEFAULT0'         => 'true',
            'L_SHIPPINGOPTIONNAME0'              => '#1',
            'L_SHIPPINGOPTIONAMOUNT0'            => '0.00',
            'PAYMENTREQUEST_0_DESC'              => 'Ihre Bestellung bei PayPal Testshop in Höhe von 21,30 EUR',
            'PAYMENTREQUEST_0_CUSTOM'            => 'Ihre Bestellung bei PayPal Testshop in Höhe von 21,30 EUR',
            'MAXAMT'                             => '22.30',
            'L_PAYMENTREQUEST_0_NAME0'           => '',
            'L_PAYMENTREQUEST_0_AMT0'            => '10.00',
            'L_PAYMENTREQUEST_0_QTY0'            => '1',
            'L_PAYMENTREQUEST_0_ITEMURL0'        => '{SHOP_URL}index.php?cl=details&amp;anid=testAddItems1',
            'L_PAYMENTREQUEST_0_NUMBER0'         => '',
            'L_PAYMENTREQUEST_0_NAME1'           => '',
            'L_PAYMENTREQUEST_0_AMT1'            => '10.00',
            'L_PAYMENTREQUEST_0_QTY1'            => '1',
            'L_PAYMENTREQUEST_0_ITEMURL1'        => '{SHOP_URL}index.php?cl=details&amp;anid=testAddItems2',
            'L_PAYMENTREQUEST_0_NUMBER1'         => '',
            'L_PAYMENTREQUEST_0_NAME2'           => 'Geschenkverpackung',
            'L_PAYMENTREQUEST_0_AMT2'            => '1.00',
            'L_PAYMENTREQUEST_0_QTY2'            => '1',
            'L_PAYMENTREQUEST_0_NAME3'           => 'Grußkarte',
            'L_PAYMENTREQUEST_0_AMT3'            => '0.30',
            'L_PAYMENTREQUEST_0_QTY3'            => '1',
            'EMAIL'                              => 'admin',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'John Doe',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Maple Street 10',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'Any City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => '9041',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => '217-8918712',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'DE',
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
