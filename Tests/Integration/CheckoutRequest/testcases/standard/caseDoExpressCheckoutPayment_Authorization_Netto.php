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
use OxidEsales\Eshop\Application\Model\PaymentGateway;

$data = array(
    'class'     => PaymentGateway::class,
    'action'    => 'doExpressCheckoutPayment',
    'articles'  => array(
        0 => array(
            'oxid'    => 9001,
            'oxprice' => 100,
            'oxvat'   => 19,
            'amount'  => 33,
            'oxstock' => 50,
        ),
        1 => array(
            'oxid'    => 9002,
            'oxprice' => 66,
            'oxvat'   => 19,
            'amount'  => 16,
            'oxstock' => 20,
        ),
    ),
    'discounts' => array(),
    'costs'     => array(),
    'config'    => array(
        'sOEPayPalSandboxUsername'  => 'testUser4',
        'sOEPayPalSandboxPassword'  => 'testPassword5',
        'sOEPayPalSandboxSignature' => 'testSignature6',
        'sOEPayPalTransactionMode'  => 'Authorization',
        'blShowNetPrice'            => true,
    ),
    'session' => ['oepaypal' => 1],
    'expected'  => array(
        'requestToPayPal' => array(
            'VERSION'                            => '84.0',
            'PWD'                                => 'testPassword5',
            'USER'                               => 'testUser4',
            'SIGNATURE'                          => 'testSignature6',
            'TOKEN'                              => '',
            'PAYERID'                            => '',
            'PAYMENTREQUEST_0_PAYMENTACTION'     => 'Authorization',
            'PAYMENTREQUEST_0_AMT'               => '4355.82',
            'PAYMENTREQUEST_0_CURRENCYCODE'      => 'EUR',
            'PAYMENTREQUEST_0_NOTIFYURL'         => '{SHOP_URL}index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp={SHOP_ID}',
            'PAYMENTREQUEST_0_DESC'              => 'Bestellnummer 1',
            'PAYMENTREQUEST_0_CUSTOM'            => 'Bestellnummer 1',
            'BUTTONSOURCE'                       => '{BN_ID}',
            'METHOD'                             => 'DoExpressCheckoutPayment',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'John Doe',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Maple Street 10',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'Any City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => '9041',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => '217-8918712',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'DE',
        ),
        'header'          => array(
            0 => 'POST /cgi-bin/webscr HTTP/1.1',
            1 => 'Content-Type: application/x-www-form-urlencoded',
            2 => 'Host: api-3t.sandbox.paypal.com',
            3 => 'Connection: close',
        )
    )
);
