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
 * Price enter mode: bruto
 * Price view mode:  brutto
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
            'oxid'    => 'testUserLogged1',
            'oxprice' => 10,
            'oxvat'   => 10,
            'amount'  => 3,
        ),
    ),
    'user'      => array(
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
        'address'     => array(
            'oxid'        => 'TestUserAddressId',
            'oxfname'     => 'AddressName',
            'oxlname'     => 'AddressLName',
            'oxstreet'    => 'AddressStreet',
            'oxstreetnr'  => 'AddressStreetNr',
            'oxcity'      => 'AddressCity',
            'oxzip'       => 'AddressZipCode',
            'oxfon'       => 'AddressPhoneNr',
            'oxcountryid' => 'a7c40f6323c4bfb36.59919433', // Italy
        ),
    ),
    'discounts' => array(),
    'costs'     => array(),
    'config'    => array(
        'sOEPayPalSandboxUsername'  => 'testUser',
        'sOEPayPalSandboxPassword'  => 'testPassword',
        'sOEPayPalSandboxSignature' => 'testSignature',
        'sOEPayPalTransactionMode'  => 'Sale',
        'sOEPayPalEmptyStockLevel'  => 10,
    ),
    'session'   => array(
        'deladrid' => 'TestUserAddressId',
        'oepaypal' => 1,
    ),
    'expected'  => array(
        'requestToPayPal' => array(
            'VERSION'                            => '84.0',
            'PWD'                                => 'testPassword',
            'USER'                               => 'testUser',
            'SIGNATURE'                          => 'testSignature',
            'TOKEN'                              => '',
            'PAYERID'                            => '',
            'PAYMENTREQUEST_0_PAYMENTACTION'     => 'Sale',
            'PAYMENTREQUEST_0_AMT'               => '27.27',
            'PAYMENTREQUEST_0_CURRENCYCODE'      => 'EUR',
            'PAYMENTREQUEST_0_NOTIFYURL'         => '{SHOP_URL}index.php?cl=oepaypalipnhandler&fnc=handleRequest&shp={SHOP_ID}',
            'PAYMENTREQUEST_0_DESC'              => 'Bestellnummer 1',
            'PAYMENTREQUEST_0_CUSTOM'            => 'Bestellnummer 1',
            'BUTTONSOURCE'                       => '{BN_ID}',
            'METHOD'                             => 'DoExpressCheckoutPayment',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'AddressName AddressLName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'AddressStreet AddressStreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'AddressCity',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'AddressZipCode',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'AddressPhoneNr',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'IT',
        ),
        'header'          => array(
            0 => 'POST /cgi-bin/webscr HTTP/1.1',
            1 => 'Content-Type: application/x-www-form-urlencoded',
            2 => 'Host: api-3t.sandbox.paypal.com',
            3 => 'Connection: close',
        )
    )
);
