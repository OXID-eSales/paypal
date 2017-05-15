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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * Price enter mode: netto / brutto
 * Price view mode: netto / brutto
 * Product count: count of used products
 * VAT info: count of used vat's (list)
 * Currency rate: 1.0 (change if needed)
 * Discounts: count
 *  1. shop / basket; abs / %; bargain;
 *  2. ...
 *  ...
 * Vouchers: count
 *  1. voucher rule
 *  2 ...
 *  ...
 * Wrapping: + / -
 * Gift cart: + / -;
 * Costs VAT caclulation rule: max / proportional
 * Costs:
 *  1. Payment + / -
 *  2. Delivery + / -
 *  3. TS + / -
 * Actions with basket or order:
 *  1. update / delete / change config
 *  2. ...
 *  ...
 * Short description: bug entry / support case other info;
 */
$aData = array(
    // Articles
    'articles' => array (
        0 => array (
                // oxarticles db fields
                'oxid'                     => 1001,
                'oxprice'                  => 0.00,
                'oxvat'                    => 0,
                // Amount in basket
                'amount'                   => 1,
                'scaleprices' => array(
                        'oxaddabs'     => 0.00,
                        'oxamount'     => 1,
                        'oxamountto'   => 3,
                        'oxartid'      => 1001
                ),
        ),
        1 => array (

        ),
    ),
    // Categories
    'categories' => array (
            0 =>  array (
                    'oxid'     => '30e44ab8593023055.23928895',
                    'oxactive' => 1,
                    'oxtitle'  => 'Bar-Equipment',
                    'articles' => ( 1126 )
            ),
    ),
    // User
    'user' => array(
            'oxactive' => 1,
            'oxusername' => 'basketUser',
            // country id, for example this is United States, make sure country with specified ID is active
            'oxcountryid' => '8f241f11096877ac0.98748826',
            'address' => array(
                'oxfname' => 'user address name'
            )
    ),
    // user will not be created. If not set - default user parameters is used
    'user' => false,
    // Group
    'group' => array (
            0 => array (
                    'oxid' => 'oxidpricea',
                    'oxactive' => 1,
                    'oxtitle' => 'Price A',
                    'oxobject2group' => array (
                            'oxobjectid' => array( 1001, 'basketUser' ),
                    ),
            ),
            1 => array (
                    'oxid' => 'oxidpriceb',
                    'oxactive' => 1,
                    'oxtitle' => 'Price B',
                    'oxobject2group' => array (
                            'oxobjectid' => array( '30e44ab8593023055.23928895' ),
                    ),
            ),
    ),
    // Discounts
    'discounts' => array (
        // oxdiscount DB fields
        0 => array (
            // ID needed for expectation later on, specify meaningful name
            'oxid'         => 'absolutediscount',
            'oxaddsum'     => 1,
            'oxaddsumtype' => '%',
            'oxamount' => 1,
            'oxamountto' => 99999,
            'oxactive' => 1,
            '...' => '',
            // If for article, specify here
            'oxarticles' => array ( 9001 ),
        ),
        1 => array (

        ),
    ),
    // Additional costs
    'costs' => array(
        // oxwrapping db fields
        'wrapping' => array(
            // Wrapping
            0 => array(
                'oxtype' => 'WRAP',
                'oxname' => 'testWrap9001',
                'oxprice' => 9,
                'oxactive' => 1,
                '...' => '',
                // If for article, specify here
                'oxarticles' => array( 9001 )
            ),
            // Giftcard
            1 => array(
                'oxtype' => 'CARD',
                'oxname' => 'testCard',
                'oxprice' => 0.30,
                'oxactive' => 1,
            ),
        ),
        // Delivery
        'delivery' => array(
            0 => array(
                // oxdelivery DB fields
                'oxactive' => 1,
                'oxaddsum' => 1,
                'oxaddsumtype' => 'abs',
                'oxdeltype' => 'p',
                'oxfinalize' => 1,
                'oxparamend' => 99999,
                '...' => ''
            ),
        ),
        // Payment
        'payment' => array(
            0 => array(
                // oxpayments DB fields
                'oxaddsum' => 1,
                'oxaddsumtype' => 'abs',
                'oxfromamount' => 0,
                'oxtoamount' => 1000000,
                'oxchecked' => 1,
                '...' => ''
            ),
        ),
        // VOUCHERS
        'voucherserie' => array (
            0 => array (
                // oxvoucherseries DB fields
                'oxdiscount' => 1.00,
                'oxdiscounttype' => 'absolute',
                'oxallowsameseries' => 1,
                'oxallowotherseries' => 1,
                'oxallowuseanother' => 1,
                'oxshopincl' => 1,
                '...' => '',
                // voucher of this voucherserie count
                'voucher_count' => 1
            ),
        ),
    ),
    'options' => array (
        'config' => array(
            'configParam' => true,
            'configParam2' => false
        ),
        'activeCurrencyRate' => 1.47,
    ),
    // TEST EXPECTATIONS
    'expected' => array (
        'request'  => '',
        'response' => '',
    ),
);
