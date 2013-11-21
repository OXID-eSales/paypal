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
 * Metadata version
 */
$sMetadataVersion = '1.1';

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
    'version'      => '3.1.1',
    'author'       => 'OXID eSales AG',
    'url'          => 'http://www.oxid-esales.com',
    'email'        => 'info@oxid-esales.com',
    'extend'       => array(
        'oxviewconfig'      => 'oe/oepaypal/core/oepaypaloxviewconfig',
        'oxcmp_basket'      => 'oe/oepaypal/components/oepaypaloxcmp_basket',
        'oxwarticledetails' => 'oe/oepaypal/components/widgets/oepaypaloxwarticledetails',
        'order'             => 'oe/oepaypal/controllers/oepaypalorder',
        'payment'           => 'oe/oepaypal/controllers/oepaypalpayment',
        'wrapping'          => 'oe/oepaypal/controllers/oepaypalwrapping',
        'order_list'        => 'oe/oepaypal/controllers/admin/oepaypalorder_list',
        'deliveryset_main'  => 'oe/oepaypal/controllers/admin/oepaypaldeliveryset_main',
        'oxaddress'         => 'oe/oepaypal/models/oepaypaloxaddress',
        'oxuser'            => 'oe/oepaypal/models/oepaypaloxuser',
        'oxorder'           => 'oe/oepaypal/models/oepaypaloxorder',
        'oxbasket'          => 'oe/oepaypal/models/oepaypaloxbasket',
        'oxbasketitem'      => 'oe/oepaypal/models/oepaypaloxbasketitem',
        'oxarticle'         => 'oe/oepaypal/models/oepaypaloxarticle',
        'oxpaymentgateway'  => 'oe/oepaypal/models/oepaypaloxpaymentgateway',
    ),
    'files' => array(
        // Core
        'oePayPalException'                 => 'oe/oepaypal/core/exception/oepaypalexception.php',
        'oePayPalMissingParameterException' => 'oe/oepaypal/core/exception/oepaypalmissingparameterexception.php',
        'oePayPalResponseException'         => 'oe/oepaypal/core/exception/oepaypalresponseexception.php',
        'oePayPalInvalidActionException'    => 'oe/oepaypal/core/exception/oepaypalinvalidactionexception.php',
        'oePayPalService'                   => 'oe/oepaypal/core/oepaypalservice.php',
        'oePayPalCheckValidator'            => 'oe/oepaypal/core/oepaypalcheckvalidator.php',
        'oePayPalLogger'                    => 'oe/oepaypal/core/oepaypallogger.php',
        'oePayPalConfig'                    => 'oe/oepaypal/core/oepaypalconfig.php',
        'oePayPalShopLogo'                  => 'oe/oepaypal/core/oepaypalshoplogo.php',
        'oePayPalCaller'                    => 'oe/oepaypal/core/oepaypalcaller.php',
        'oePayPalExtensionChecker'          => 'oe/oepaypal/core/oepaypalextensionchecker.php',
        'oePayPalEvents'                    => 'oe/oepaypal/core/oepaypalevents.php',
        'oePayPalCurl'                      => 'oe/oepaypal/core/oepaypalcurl.php',
        'oePayPalModelDbGateway'            => 'oe/oepaypal/core/oepaypalmodeldbgateway.php',
        'oePayPalModel'                     => 'oe/oepaypal/core/oepaypalmodel.php',
        'oePayPalRequest'                   => 'oe/oepaypal/core/oepaypalrequest.php',
        'oePayPalEscape'                    => 'oe/oepaypal/core/oepaypalescape.php',
        'oePayPalList'                      => 'oe/oepaypal/core/oepaypallist.php',
        'oePayPalUserAgent'                 => 'oe/oepaypal/core/oepaypaluseragent.php',
        'oePayPalFullName'                  => 'oe/oepaypal/core/oepaypalfullname.php',

        // Controllers
        'oePayPalDispatcher'                => 'oe/oepaypal/controllers/oepaypaldispatcher.php',
        'oePayPalExpressCheckoutDispatcher' => 'oe/oepaypal/controllers/oepaypalexpresscheckoutdispatcher.php',
        'oePayPalStandardDispatcher'        => 'oe/oepaypal/controllers/oepaypalstandarddispatcher.php',
        'oePayPalIPNHandler'                => 'oe/oepaypal/controllers/oepaypalipnhandler.php',
        'oePayPalController'                => 'oe/oepaypal/controllers/oepaypalcontroller.php',
        // Admin
        'oePayPalOrder_PayPal' => 'oe/oepaypal/controllers/admin/oepaypalorder_paypal.php',
        // Models
        'oePayPalOrderPaymentStatusList'                  => 'oe/oepaypal/models/oepaypalorderpaymentstatuslist.php',
        'oePayPalOrderPayment'                            => 'oe/oepaypal/models/oepaypalorderpayment.php',
        'oePayPalOrderPaymentList'                        => 'oe/oepaypal/models/oepaypalorderpaymentlist.php',
        'oePayPalOrderActionManager'                      => 'oe/oepaypal/models/oepaypalorderactionmanager.php',
        'oePayPalPayPalOrder'                             => 'oe/oepaypal/models/oepaypalpaypalorder.php',
        'oePayPalOrderPaymentStatusCalculator'            => 'oe/oepaypal/models/oepaypalorderpaymentstatuscalculator.php',
        'oePayPalOrderPaymentActionManager'               => 'oe/oepaypal/models/oepaypalorderpaymentactionmanager.php',
        'oePayPalIPNRequestPaymentSetter'                 => 'oe/oepaypal/models/oepaypalipnrequestpaymentsetter.php',
        'oePayPalIPNRequestValidator'                     => 'oe/oepaypal/models/oepaypalipnrequestvalidator.php',
        'oePayPalIPNPaymentValidator'                     => 'oe/oepaypal/models/oepaypalipnpaymentvalidator.php',
        'oePayPalArticleToExpressCheckoutValidator'       => 'oe/oepaypal/models/oepaypalarticletoexpresscheckoutvalidator.php',
        'oePayPalArticleToExpressCheckoutCurrentItem'     => 'oe/oepaypal/models/oepaypalarticletoexpresscheckoutcurrentitem.php',
        'oePayPalPayPalOrderDbGateway'                    => 'oe/oepaypal/models/dbgateways/oepaypalpaypalorderdbgateway.php',
        'oePayPalOrderPaymentCommentDbGateway'            => 'oe/oepaypal/models/dbgateways/oepaypalorderpaymentcommentdbgateway.php',
        'oePayPalOrderPaymentDbGateway'                   => 'oe/oepaypal/models/dbgateways/oepaypalorderpaymentdbgateway.php',
        'oePayPalOrderPaymentComment'                     => 'oe/oepaypal/models/oepaypalorderpaymentcomment.php',
        'oePayPalOrderPaymentCommentList'                 => 'oe/oepaypal/models/oepaypalorderpaymentcommentlist.php',
        'oePayPalOrderManager'                            => 'oe/oepaypal/models/oepaypalordermanager.php',
        'oePayPalIPNPaymentBuilder'                       => 'oe/oepaypal/models/oepaypalipnpaymentbuilder.php',
        'oePayPalIPNRequestVerifier'                      => 'oe/oepaypal/models/oepaypalipnrequestverifier.php',
        'oePayPalIPNProcessor'                            => 'oe/oepaypal/models/oepaypalipnprocessor.php',
        'oePayPalOutOfStockValidator'                     => 'oe/oepaypal/models/oepaypaloutofstockvalidator.php',
        'oePayPalPayPalRequest'                           => 'oe/oepaypal/models/paypalrequest/oepaypalpaypalrequest.php',
        'oePayPalPayPalRequestBuilder'                    => 'oe/oepaypal/models/paypalrequest/oepaypalpaypalrequestbuilder.php',
        'oePayPalSetExpressCheckoutRequestBuilder'        => 'oe/oepaypal/models/paypalrequest/oepaypalsetexpresscheckoutrequestbuilder.php',
        'oePayPalDoExpressCheckoutPaymentRequestBuilder'  => 'oe/oepaypal/models/paypalrequest/oepaypaldoexpresscheckoutpaymentrequestbuilder.php',
        'oePayPalGetExpressCheckoutDetailsRequestBuilder' => 'oe/oepaypal/models/paypalrequest/oepaypalgetexpresscheckoutdetailsrequestbuilder.php',
        'oePayPalResponse'                                => 'oe/oepaypal/models/responses/oepaypalresponse.php',
        'oePayPalResponseDoVoid'                          => 'oe/oepaypal/models/responses/oepaypalresponsedovoid.php',
        'oePayPalResponseDoRefund'                        => 'oe/oepaypal/models/responses/oepaypalresponsedorefund.php',
        'oePayPalResponseDoCapture'                       => 'oe/oepaypal/models/responses/oepaypalresponsedocapture.php',
        'oePayPalResponseDoReAuthorize'                   => 'oe/oepaypal/models/responses/oepaypalresponsedoreauthorize.php',
        'oePayPalResponseSetExpressCheckout'              => 'oe/oepaypal/models/responses/oepaypalresponsesetexpresscheckout.php',
        'oePayPalResponseGetExpressCheckoutDetails'       => 'oe/oepaypal/models/responses/oepaypalresponsegetexpresscheckoutdetails.php',
        'oePayPalResponseDoExpressCheckoutPayment'        => 'oe/oepaypal/models/responses/oepaypalresponsedoexpresscheckoutpayment.php',
        'oePayPalResponseDoVerifyWithPayPal'              => 'oe/oepaypal/models/responses/oepaypalresponsedoverifywithpaypal.php',
        'oePayPalOrderActionFactory'                      => 'oe/oepaypal/models/actions/oepaypalorderactionfactory.php',
        'oePayPalOrderAction'                             => 'oe/oepaypal/models/actions/oepaypalorderaction.php',
        'oePayPalOrderCaptureAction'                      => 'oe/oepaypal/models/actions/oepaypalordercaptureaction.php',
        'oePayPalOrderRefundAction'                       => 'oe/oepaypal/models/actions/oepaypalorderrefundaction.php',
        'oePayPalOrderVoidAction'                         => 'oe/oepaypal/models/actions/oepaypalordervoidaction.php',
        'oePayPalOrderActionData'                         => 'oe/oepaypal/models/actions/data/oepaypalorderactiondata.php',
        'oePayPalOrderCaptureActionData'                  => 'oe/oepaypal/models/actions/data/oepaypalordercaptureactiondata.php',
        'oePayPalOrderRefundActionData'                   => 'oe/oepaypal/models/actions/data/oepaypalorderrefundactiondata.php',
        'oePayPalOrderReauthorizeActionData'              => 'oe/oepaypal/models/actions/data/oepaypalorderreauthorizeactiondata.php',
        'oePayPalOrderVoidActionData'                     => 'oe/oepaypal/models/actions/data/oepaypalordervoidactiondata.php',
        'oePayPalOrderActionHandler'                      => 'oe/oepaypal/models/actions/handlers/oepaypalorderactionhandler.php',
        'oePayPalOrderCaptureActionHandler'               => 'oe/oepaypal/models/actions/handlers/oepaypalordercaptureactionhandler.php',
        'oePayPalOrderRefundActionHandler'                => 'oe/oepaypal/models/actions/handlers/oepaypalorderrefundactionhandler.php',
        'oePayPalOrderReauthorizeActionHandler'           => 'oe/oepaypal/models/actions/handlers/oepaypalorderreauthorizeactionhandler.php',
        'oePayPalOrderVoidActionHandler'                  => 'oe/oepaypal/models/actions/handlers/oepaypalordervoidactionhandler.php',
        'oePayPalPaymentValidator'                        => 'oe/oepaypal/models/oepaypalpaymentvalidator.php',
    ),
    'events'       => array(
        'onActivate'   => 'oePayPalEvents::onActivate',
        'onDeactivate' => 'oePayPalEvents::onDeactivate'
    ),
    'templates' => array(
        'order_paypal.tpl' => 'oe/oepaypal/views/admin/tpl/order_paypal.tpl',
        'ipnhandler.tpl'   => 'oe/oepaypal/views/tpl/ipnhandler.tpl',
    ),
    'blocks' => array(
        array('template' => 'deliveryset_main.tpl',             'block'=>'admin_deliveryset_main_form',         'file'=>'/views/blocks/deliveryset_main.tpl'),
        array('template' => 'widget/sidebar/partners.tpl',      'block'=>'partner_logos',                       'file'=>'/views/blocks/widget/sidebar/oepaypalpartnerbox.tpl'),
        array('template' => 'widget/minibasket/minibasket.tpl', 'block'=>'widget_minibasket_total',             'file'=>'/views/blocks/widget/minibasket/oepaypalexpresscheckoutminibasket.tpl'),
        array('template' => 'page/checkout/basket.tpl',         'block'=>'basket_btn_next_top',                 'file'=>'/views/blocks/page/checkout/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/basket.tpl',         'block'=>'basket_btn_next_bottom',              'file'=>'/views/blocks/page/checkout/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/payment.tpl',        'block'=>'select_payment',                      'file'=>'/views/blocks/page/checkout/oepaypalpaymentselector.tpl'),
        array('template' => 'order_list.tpl',                   'block'=>'admin_order_list_filter',             'file'=>'/views/blocks/oepaypalorder_list_filter_actions.tpl'),
        array('template' => 'order_list.tpl',                   'block'=>'admin_order_list_sorting',            'file'=>'/views/blocks/oepaypalorder_list_sorting_actions.tpl'),
        array('template' => 'order_list.tpl',                   'block'=>'admin_order_list_item',               'file'=>'/views/blocks/oepaypalorder_list_items_actions.tpl'),
        array('template' => 'order_list.tpl',                   'block'=>'admin_order_list_colgroup',           'file'=>'/views/blocks/oepaypalorder_list_colgroup_actions.tpl'),
        array('template' => 'page/details/inc/productmain.tpl', 'block'=>'details_productmain_tobasket',        'file'=>'/views/blocks/page/details/oepaypalexpresscheckoutdetailspage.tpl'),
        array('template' => 'page/details/inc/productmain.tpl', 'block'=>'details_productmain_morepics',        'file'=>'/views/blocks/page/details/oepaypalexpresscheckoutdetailspagepopup.tpl'),
        array('template' => 'page/checkout/basket.tpl',         'block'=>'mb_basket_btn_next_top',              'file'=>'/views/blocks/mobile/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/basket.tpl',         'block'=>'mb_basket_btn_next_bottom',           'file'=>'/views/blocks/mobile/oepaypalexpresscheckout.tpl'),
        array('template' => 'page/checkout/payment.tpl',        'block'=>'mb_select_payment_dropdown',          'file'=>'/views/blocks/mobile/oepaypalpaymentdropdown.tpl'),
        array('template' => 'page/checkout/payment.tpl',        'block'=>'mb_select_payment',                   'file'=>'/views/blocks/mobile/oepaypalpaymentselector.tpl'),
        array('template' => 'page/details/inc/productmain.tpl', 'block'=>'mb_details_productmain_tobasket',     'file'=>'/views/blocks/mobile/oepaypalexpresscheckoutdetailspage.tpl'),
        array('template' => 'page/details/inc/productmain.tpl', 'block'=>'mb_details_productmain_morepics',     'file'=>'/views/blocks/mobile/oepaypalexpresscheckoutdetailspagepopup.tpl'),
    ),
    'settings' => array(
        // functionality is currently not available
        //array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalGuestBuyRole',           'type' => 'bool', 'value' => 'false'),//customizedcheckout_paypalguestbuyrole
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalStandardCheckout',      'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalExpressCheckout',       'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalECheckoutInMiniBasket', 'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_checkout', 'name' => 'blOEPayPalECheckoutInDetails',    'type' => 'bool',   'value' => 'true'),

        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalBrandName',                     'type' => 'str',  'value' => 'PayPal Testshop'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalBorderColor',                   'type' => 'str',  'value' => '2b8da4'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalLogoImageOption',               'type' => 'select', 'constrains' => 'noLogo|shopLogo|customLogo', 'value' => 'noLogo'),
        array('group' => 'oepaypal_display', 'name' => 'sOEPayPalCustomShopLogoImage',           'type' => 'str',    'value' => ''),

        array('group' => 'oepaypal_payment',     'name' => 'blOEPayPalSendToPayPal',          'type' => 'bool',   'value' => 'true'),
        array('group' => 'oepaypal_payment',     'name' => 'blOEPayPalDefaultUserChoice',     'type' => 'bool',   'value' => 'true'),

        array('group' => 'oepaypal_transaction', 'name' => 'sOEPayPalTransactionMode',        'type' => 'select', 'constrains' => 'Sale|Authorization|Automatic', 'value' => 'Sale'),
        array('group' => 'oepaypal_transaction', 'name' => 'sOEPayPalEmptyStockLevel',        'type' => 'str',    'value' => '0'),

        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalUserEmail',              'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalUsername',               'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalPassword',               'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_api',         'name' => 'sOEPayPalSignature',              'type' => 'str',    'value' => ''),

        array('group' => 'oepaypal_development', 'name' => 'blPayPalLoggerEnabled',           'type' => 'bool',   'value' => 'false'),
        array('group' => 'oepaypal_development', 'name' => 'blOEPayPalSandboxMode',           'type' => 'bool',   'value' => 'false'),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxUserEmail',       'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxUsername',        'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxPassword',        'type' => 'str',    'value' => ''),
        array('group' => 'oepaypal_development', 'name' => 'sOEPayPalSandboxSignature',       'type' => 'str',    'value' => ''),
    )
);