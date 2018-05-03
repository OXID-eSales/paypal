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

/**
 * Price enter mode: netto
 * Price view mode:  netto
 * Product count: count of used products
 * VAT info: 19%
 * Currency rate: 0.68
 * Discounts: count
 *  1. bascet 5 abs
 *  2. shop 5 abs for 9001
 *  3. bascet 1 abs for 9001
 *  4. shop 5% for 9002
 *  5. bascet 6% for 9002
 * Vouchers: count
 *  1. 6 abs
 * Wrapping: +;
 * Gift cart:  -;
 * Costs VAT caclulation rule: max
 * Costs:
 *  1. Payment +
 *  2. Delivery +
 *  3. TS -
 * Actions with basket or order:
 *  1. update / delete / change config
 *  2. ...
 *  ...
 * Short description: bug entry / support case other info;
 */
$data = array(
    'class'         => \OxidEsales\PayPalModule\Controller\StandardDispatcher::class,
    'action'        => 'setExpressCheckout',
    'articles'      => array(
        0 => array(
            'oxid'     => 'test9001',
            'oxprice'  => 10,
            'oxvat'    => 19,
            'oxartnum' => '9001',
            'oxtitle'  => 'Test Title 1',
            'amount'   => 2,
            'oxstock'  => 50,
        ),
        1 => array(
            'oxid'     => 'test9002',
            'oxprice'  => 20,
            'oxvat'    => 19,
            'oxartnum' => '9002',
            'oxtitle'  => 'Test Title 2',
            'amount'   => 1,
            'oxstock'  => 50,
        ),
        2 => array(
            'oxid'     => 'test9003',
            'oxprice'  => 30,
            'oxvat'    => 19,
            'oxartnum' => '9003',
            'oxtitle'  => 'Test Title 3',
            'amount'   => 1,
            'oxstock'  => 50,
        ),
    ),
    'config'        => array(
        'blSeoMode' => true
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
            'PAYMENTREQUEST_0_AMT'               => '70.00',
            'PAYMENTREQUEST_0_CURRENCYCODE'      => 'EUR',
            'PAYMENTREQUEST_0_ITEMAMT'           => '70.00',
            'PAYMENTREQUEST_0_SHIPPINGAMT'       => '0.00',
            'PAYMENTREQUEST_0_SHIPDISCAMT'       => '0.00',
            'L_SHIPPINGOPTIONISDEFAULT0'         => 'true',
            'L_SHIPPINGOPTIONNAME0'              => '#1',
            'L_SHIPPINGOPTIONAMOUNT0'            => '0.00',
            'PAYMENTREQUEST_0_DESC'              => 'Ihre Bestellung bei PayPal Testshop in Höhe von 70,00 EUR',
            'PAYMENTREQUEST_0_CUSTOM'            => 'Ihre Bestellung bei PayPal Testshop in Höhe von 70,00 EUR',
            'MAXAMT'                             => '71.00',
            'L_PAYMENTREQUEST_0_NAME0'           => 'Test Title 1',
            'L_PAYMENTREQUEST_0_AMT0'            => '10.00',
            'L_PAYMENTREQUEST_0_QTY0'            => '2.0',
            'L_PAYMENTREQUEST_0_ITEMURL0'        => '{SHOP_URL}Test-Title-1.html',
            'L_PAYMENTREQUEST_0_NUMBER0'         => '9001',
            'L_PAYMENTREQUEST_0_NAME1'           => 'Test Title 2',
            'L_PAYMENTREQUEST_0_AMT1'            => '20.00',
            'L_PAYMENTREQUEST_0_QTY1'            => '1.0',
            'L_PAYMENTREQUEST_0_ITEMURL1'        => '{SHOP_URL}Test-Title-2.html',
            'L_PAYMENTREQUEST_0_NUMBER1'         => '9002',
            'L_PAYMENTREQUEST_0_NAME2'           => 'Test Title 3',
            'L_PAYMENTREQUEST_0_AMT2'            => '30.00',
            'L_PAYMENTREQUEST_0_QTY2'            => '1.0',
            'L_PAYMENTREQUEST_0_ITEMURL2'        => '{SHOP_URL}Test-Title-3.html',
            'L_PAYMENTREQUEST_0_NUMBER2'         => '9003',
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
