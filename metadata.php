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
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = array(
    'id'           => 'oepaypal',
    'title'        => 'PayPal',
    'description'  => array(
        'de' => 'Modul für die Zahlung mit PayPal.',
        'en' => 'Module for PayPal payment.',
    ),
    'thumbnail'    => 'logo.jpg',
    'version'      => '6.0.1',
    'author'       => 'OXID eSales AG',
    'url'          => 'https://www.oxid-esales.com',
    'email'        => 'info@oxid-esales.com',
    'extend'       => array(
        \OxidEsales\Eshop\Core\ViewConfig::class                              => \OxidEsales\PayPalModule\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Component\BasketComponent::class        => \OxidEsales\PayPalModule\Component\BasketComponent::class,
        \OxidEsales\Eshop\Application\Component\Widget\ArticleDetails::class  => \OxidEsales\PayPalModule\Component\Widget\ArticleDetails::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class       => \OxidEsales\PayPalModule\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class     => \OxidEsales\PayPalModule\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\WrappingController::class    => \OxidEsales\PayPalModule\Controller\WrappingController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class       => \OxidEsales\PayPalModule\Controller\Admin\OrderList::class,
        \OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain::class => \OxidEsales\PayPalModule\Controller\Admin\DeliverySetMain::class,
        \OxidEsales\Eshop\Application\Model\Address::class                    => \OxidEsales\PayPalModule\Model\Address::class,
        \OxidEsales\Eshop\Application\Model\User::class                       => \OxidEsales\PayPalModule\Model\User::class,
        \OxidEsales\Eshop\Application\Model\Order::class                      => \OxidEsales\PayPalModule\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\Basket::class                     => \OxidEsales\PayPalModule\Model\Basket::class,
        \OxidEsales\Eshop\Application\Model\Article::class                    => \OxidEsales\PayPalModule\Model\Article::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class             => \OxidEsales\PayPalModule\Model\PaymentGateway::class,
    ),
    'controllers' => array(
        'oepaypalexpresscheckoutdispatcher' => \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class,
        'oepaypalstandarddispatcher'        => \OxidEsales\PayPalModule\Controller\StandardDispatcher::class,
        'oepaypalipnhandler'                => \OxidEsales\PayPalModule\Controller\IPNHandler::class,
        'oepaypalorder_paypal'              => \OxidEsales\PayPalModule\Controller\Admin\OrderController::class
    ),
    'events'       => array(
        'onActivate'   => '\OxidEsales\PayPalModule\Core\Events::onActivate',
        'onDeactivate' => '\OxidEsales\PayPalModule\Core\Events::onDeactivate'
    ),
    'templates' => array(
        'order_paypal.tpl' => 'oe/oepaypal/views/admin/tpl/order_paypal.tpl',
        'ipnhandler.tpl'   => 'oe/oepaypal/views/tpl/ipnhandler.tpl',
    ),
    'blocks' => array(
        array('template' => 'deliveryset_main.tpl',               'block'=>'admin_deliveryset_main_form',           'file'=>'/views/blocks/deliveryset_main.tpl'),
        array('template' => 'widget/sidebar/partners.tpl',        'block'=>'partner_logos',                         'file'=>'/views/blocks/widget/sidebar/oepaypalpartnerbox.tpl'),
        array('template' => 'widget/minibasket/minibasket.tpl',   'block'=>'widget_minibasket_total',               'file'=>'/views/blocks/widget/minibasket/oepaypalexpresscheckoutminibasket.tpl'),
        array('template' => 'page/checkout/basket.tpl',           'block'=>'basket_btn_next_top',                   'file'=>'/views/blocks/page/checkout/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/basket.tpl',           'block'=>'basket_btn_next_bottom',                'file'=>'/views/blocks/page/checkout/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/payment.tpl',          'block'=>'select_payment',                        'file'=>'/views/blocks/page/checkout/oepaypalpaymentselector.tpl'),
        array('template' => 'order_list.tpl',                     'block'=>'admin_order_list_filter',               'file'=>'/views/blocks/oepaypalorder_list_filter_actions.tpl'),
        array('template' => 'order_list.tpl',                     'block'=>'admin_order_list_sorting',              'file'=>'/views/blocks/oepaypalorder_list_sorting_actions.tpl'),
        array('template' => 'order_list.tpl',                     'block'=>'admin_order_list_item',                 'file'=>'/views/blocks/oepaypalorder_list_items_actions.tpl'),
        array('template' => 'order_list.tpl',                     'block'=>'admin_order_list_colgroup',             'file'=>'/views/blocks/oepaypalorder_list_colgroup_actions.tpl'),
        array('template' => 'page/details/inc/productmain.tpl',   'block'=>'details_productmain_tobasket',          'file'=>'/views/blocks/page/details/oepaypalexpresscheckoutdetailspage.tpl'),
        array('template' => 'page/details/inc/productmain.tpl',   'block'=>'details_productmain_morepics',          'file'=>'/views/blocks/page/details/oepaypalexpresscheckoutdetailspagepopup.tpl'),
        array('template' => 'page/checkout/basket.tpl',           'block'=>'mb_basket_btn_next_top',                'file'=>'/views/blocks/mobile/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/basket.tpl',           'block'=>'mb_basket_btn_next_bottom',             'file'=>'/views/blocks/mobile/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/payment.tpl',          'block'=>'mb_select_payment_dropdown',            'file'=>'/views/blocks/mobile/oepaypalpaymentdropdown.tpl'),
        array('template' => 'page/checkout/payment.tpl',          'block'=>'mb_select_payment',                     'file'=>'/views/blocks/mobile/oepaypalpaymentselector.tpl'),
        array('template' => 'page/details/inc/productmain.tpl',   'block'=>'mb_details_productmain_tobasket',       'file'=>'/views/blocks/mobile/oepaypalexpresscheckoutdetailspage.tpl'),
        array('template' => 'page/details/inc/productmain.tpl',   'block'=>'mb_details_productmain_morepics',       'file'=>'/views/blocks/mobile/oepaypalexpresscheckoutdetailspagepopup.tpl'),
        array('template' => 'page/checkout/user.tpl',             'block'=>'checkout_user_main',                    'file'=>'/views/blocks/page/checkout/oepaypalexpresscheckout.tpl'),
     ),
    'settings' => array(
        // functionality is currently not available
        //array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalGuestBuyRole',           'type' => 'bool', 'value' => 'false'),//customizedcheckout_paypalguestbuyrole
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalStandardCheckout',      'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalExpressCheckout',       'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalECheckoutInMiniBasket', 'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalECheckoutInDetails',    'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalFinalizeOrderOnPayPal', 'type' => 'bool',   'value' => 'true'),

        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalBrandName',                     'type' => 'str',  'value' => 'PayPal Testshop'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalBorderColor',                   'type' => 'str',  'value' => '2b8da4'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalLogoImageOption',               'type' => 'select', 'constraints' => 'noLogo|shopLogo|customLogo', 'value' => 'noLogo'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalCustomShopLogoImage',           'type' => 'str',    'value' => ''),

        array('group' => 'oepaypal_payment',     'name' => 'blOEPayPalSendToPayPal',          'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_payment',     'name' => 'blOEPayPalDefaultUserChoice',     'type' => 'bool',   'value' => 'true'),

        array('group' => 'oepaypal_transaction', 'name' => 'sOEPayPalTransactionMode',        'type' => 'select', 'constraints' => 'Sale|Authorization|Automatic', 'value' => 'Sale'),
        array('group' => 'oepaypal_transaction', 'name' => 'sOEPayPalEmptyStockLevel',        'type' => 'str',    'value' => '0'),

        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalUserEmail',              'type' => 'str',      'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalUsername',               'type' => 'str',      'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalPassword',               'type' => 'password', 'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalSignature',              'type' => 'str',      'value' => ''),

        array('group' => 'oepaypal_development', 'name' => 'blPayPalLoggerEnabled',           'type' => 'bool',     'value' => 'false'),
        array('group' => 'oepaypal_development', 'name' => 'blOEPayPalSandboxMode',           'type' => 'bool',     'value' => 'false'),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxUserEmail',       'type' => 'str',      'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxUsername',        'type' => 'str',      'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxPassword',        'type' => 'password', 'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxSignature',       'type' => 'str',      'value' => ''),
    )
);
