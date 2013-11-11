[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxscript include="js/libs/jquery.min.js"}]
[{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/admin/src/js/paypal_order.js')}]
[{oxscript add="jQuery.noConflict();" priority=10}]

<script type="text/javascript">
    window.onload = function () {
        top.oxid.admin.updateList('[{ $sOxid }]')
    };
</script>

<style>
    .paypalActionsTable {
        border : 1px #A9A9A9;
        border-style : solid solid solid solid;
        padding: 5px;
    }
    #paypalOverlay {
        position: fixed;
        width:100%;
        height:2000px;
        opacity:0.5;
        background:#ccc;
        top:0;
        left:0;
        display: none;
    }
    .paypalPopUp {
        position: fixed;
        background: #fff;
        left: 0;
        top: 100px;
        padding:10px;
        min-width: 350px;
        max-width: 550px;
        z-index: 91;
        display: none;
        white-space:normal;
    }
    .paypalPopUp .paypalPopUpClose {
        position: absolute;
        top: 0;
        right: 0;
        border: 1px solid #000;
        padding: 3px 6px;
    }
    .paypalActionsButtons {
        text-align: right;
        margin-bottom: 0;
    }
    .paypalActionsBlockNotice textarea {
        width: 100%;
        height: 80px;
        margin: 0;
    }
    #paypalStatusList {
        display: none;
    }
    #paypalActionsBlocks {
        display: none;
    }
    #historyTable {
        border-spacing:0;
        border-collapse:collapse;
        width:98%;
    }
    .paypalHistoryComments p{
        margin-top: 0;
    }
    a.popUpLink, a.actionLink {
        text-decoration: underline;
    }
</style>

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="oxidCopy" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="delivery_main">
    <input type="hidden" name="language" value="[{ $actlang }]">
</form>

[{if $oOrder}]


    [{assign var="oPayPalOrder" value=$oOrder->getPayPalOrder()}]
    [{assign var="oOrderActionManager" value=$oView->getOrderActionManager()}]
    [{assign var="oOrderPaymentActionManager" value=$oView->getOrderPaymentActionManager()}]
    [{assign var="oOrderPaymentStatusCalculator" value=$oView->getOrderPaymentStatusCalculator()}]
    [{assign var="oOrderPaymentStatusList" value=$oView->getOrderPaymentStatusList()}]

    [{assign var="currency" value=$oPayPalOrder->getCurrency()}]

    <table width="98%" cellspacing="0" cellpadding="0" border="0" >
        <tbody>
        <tr>
            <td class="edittext" valign="top" >
                <table class="paypalActionsTable" width="98%">
                    [{if $error}]
                    <tr>
                        <td colspan="2">
                            <div class="errorbox">[{$error}]</div>
                        </td>
                    </tr>
                    [{/if}]
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_SHOP_PAYMENT_STATUS"}]:</td>
                        <td class="edittext">
                            <b>[{oxmultilang ident='OEPAYPAL_STATUS_'|cat:$oPayPalOrder->getPaymentStatus()}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_ORDER_PRICE"}]:</td>
                        <td class="edittext">
                            <b>[{$oView->formatPrice($oPayPalOrder->getTotalOrderSum())}] [{$currency}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_CAPTURED_AMOUNT"}]:</td>
                        <td class="edittext">
                            <b>[{$oView->formatPrice($oPayPalOrder->getCapturedAmount())}] [{$currency}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_REFUNDED_AMOUNT"}]:</td>
                        <td class="edittext">
                            <b>[{$oView->formatPrice($oPayPalOrder->getRefundedAmount())}] [{$currency}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_CAPTURED_NET"}]:</td>
                        <td class="edittext">
                            <b>[{$oView->formatPrice($oPayPalOrder->getRemainingRefundAmount())}] [{$currency}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_VOIDED_AMOUNT"}]:</td>
                        <td class="edittext">
                            <b>[{$oView->formatPrice($oPayPalOrder->getVoidedAmount())}] [{$currency}]</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_AUTHORIZATIONID"}]:</td>
                        <td class="edittext">
                            <b>[{$oOrder->getAuthorizationId()}]</b>
                        </td>
                    </tr>
                    [{if $oOrderActionManager->isActionAvailable('capture')}]
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_MONEY_CAPTURE"}]:</td>
                        <td class="edittext">
                            <button id="captureButton" class="actionLink"
                               data-action="capture"
                               data-type="Complete"
                               data-amount="[{ $oPayPalOrder->getRemainingOrderSum() }]"
                               data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('capture')|@json_encode}]'
                               data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('capture')}]"
                               href="#" >
                                [{oxmultilang ident="OEPAYPAL_CAPTURE"}]
                            </button>
                        </td>
                    </tr>
                    </tr>
                    [{/if}]
                    [{if $oOrderActionManager->isActionAvailable('void')}]
                    <tr>
                        <td class="edittext">[{oxmultilang ident="OEPAYPAL_AUTHORIZATION"}]:</td>
                        <td class="edittext">
                            <button id="voidButton" class="actionLink"
                               data-action="void"
                               data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('void')|@json_encode}]'
                               data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('void')}]"
                               href="#">
                                [{oxmultilang ident="OEPAYPAL_CANCEL_AUTHORIZATION"}]
                            </button>
                        </td>
                    </tr>
                    [{/if}]
                </table>
                </br>
                <b>[{oxmultilang ident="OEPAYPAL_PAYMENT_HISTORY"}]: </b>
                <table id="historyTable" >
                    <colgroup>
                        <col width="20%">
                        <col width="20%">
                        <col width="20%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <tr>
                        <td class="listheader first">[{oxmultilang ident="OEPAYPAL_HISTORY_DATE"}]</td>
                        <td class="listheader">[{oxmultilang ident="OEPAYPAL_HISTORY_ACTION"}]</td>
                        <td class="listheader">[{oxmultilang ident="OEPAYPAL_AMOUNT"}]</td>
                        <td class="listheader">
                            [{oxmultilang ident="OEPAYPAL_HISTORY_PAYPAL_STATUS"}]
                            [{ oxinputhelp ident="OEPAYPAL_HISTORY_PAYPAL_STATUS_HELP" }]
                        </td>
                        <td class="listheader">[{oxmultilang ident="OEPAYPAL_HISTORY_ACTIONS"}]</td>
                    </tr>
                    [{foreach from=$oPayPalOrder->getPaymentList() item=listitem name=paypalHistory}]
                    [{cycle values='listitem,listitem2' assign='class'}]
                    <tr>
                        <td valign="top" class="[{ $class}]">[{ $listitem->getDate() }]</td>
                        <td valign="top" class="[{ $class}]">[{ $listitem->getAction() }]</td>
                        <td valign="top" class="[{ $class}]">
                            [{ $listitem->getAmount() }] <small>[{$currency}]</small>
                        </td>
                        <td valign="top" class="[{ $class}]">[{ $listitem->getStatus() }]</td>
                        <td valign="top" class="[{ $class}]">
                            <a class="popUpLink" href="#" data-block="historyDetailsBlock[{$smarty.foreach.paypalHistory.index}]"><img src="[{$oViewConf->getModuleUrl('oepaypal','out/admin/src/bg/ico-details.png')}]" title="[{oxmultilang ident="OEPAYPAL_DETAILS"}]" /></a>
                            <div id="historyDetailsBlock[{$smarty.foreach.paypalHistory.index}]" class="paypalPopUp">
                                <h3>[{oxmultilang ident="OEPAYPAL_DETAILS"}] ([{ $listitem->getDate() }])</h3>
                                <p>
                                    [{oxmultilang ident="OEPAYPAL_HISTORY_ACTION"}]: <b>[{ $listitem->getAction() }]</b><br/>
                                    [{oxmultilang ident="OEPAYPAL_HISTORY_PAYPAL_STATUS"}]: <b>[{ $listitem->getStatus() }]</b><br/>
                                </p>
                                <p>
                                    [{if $listitem->getRefundedAmount() > 0}]
                                    [{oxmultilang ident="OEPAYPAL_CAPTURED"}]: </label><b>[{ $listitem->getAmount() }] <small>[{$currency}]</small></b><br/>
                                    [{oxmultilang ident="OEPAYPAL_REFUNDED"}]: <b>[{ $listitem->getRefundedAmount() }] <small>[{$currency}]</small></b><br/>
                                    [{oxmultilang ident="OEPAYPAL_CAPTURED_NET"}]: <b>[{ $listitem->getRemainingRefundAmount() }] <small>[{$currency}]</small></b><br/>
                                    [{else}]
                                    [{oxmultilang ident="OEPAYPAL_AMOUNT"}]: </label><b>[{ $listitem->getAmount() }] <small>[{$currency}]</small></b><br/>
                                    [{/if}]
                                </p>
                                <p>
                                    <label>[{oxmultilang ident="OEPAYPAL_TRANSACTIONID"}]: </label><b>[{ $listitem->getTransactionId() }]</b><br/>
                                    <label>[{oxmultilang ident="OEPAYPAL_CORRELATIONID"}]: </label><b>[{ $listitem->getCorrelationId() }]</b><br/>
                                </p>
                                [{assign var="aComments" value=$listitem->getCommentList()}]
                                [{if $aComments->getArray() }]
                                <div class="paypalHistoryComments">
                                    <span>[{oxmultilang ident="OEPAYPAL_COMMENT"}]: </span>
                                    [{foreach from=$aComments item=oComment}]
                                    <p>
                                        <small>[{$oComment->getDate()}]</small></br>
                                        [{$oComment->getComment()}]
                                    </p>
                                    [{/foreach}]
                                </div>
                                [{/if}]
                            </div>
                            [{if $oOrderPaymentActionManager->isActionAvailable('refund', $listitem)}]
                            <a id="refundButton[{$smarty.foreach.paypalHistory.index}]" class="actionLink"
                               data-action="refund"
                               data-type="[{if $listitem->getRefundedAmount() > 0}]Partial[{else}]Full[{/if}]"
                               data-amount="[{ $listitem->getRemainingRefundAmount() }]"
                               data-transid="[{ $listitem->getTransactionId() }]"
                               data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('refund')|@json_encode}]'
                               data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('refund')}]"
                               href="#">
                                <img src="[{$oViewConf->getModuleUrl('oepaypal','out/admin/src/bg/ico-refund.png')}]" title="[{oxmultilang ident="OEPAYPAL_REFUND"}]" />
                            </a>
                            [{/if}]
                        </td>
                    </tr>
                    [{/foreach}]
                </table>
                <p><b>[{oxmultilang ident="OEPAYPAL_HISTORY_NOTICE"}]: </b>[{oxmultilang ident="OEPAYPAL_HISTORY_NOTICE_TEXT"}]</p>
            </td>
            <td class="edittext" valign="top" align="left">
                <b>[{oxmultilang ident="OEPAYPAL_ORDER_PRODUCTS"}]: </b>
                <table cellspacing="0" cellpadding="0" border="0" width="98%">
                    <tr>
                        <td class="listheader first">[{ oxmultilang ident="GENERAL_SUM" }]</td>
                        <td class="listheader" height="15">&nbsp;&nbsp;&nbsp;[{ oxmultilang ident="GENERAL_ITEMNR" }]</td>
                        <td class="listheader">&nbsp;&nbsp;&nbsp;[{ oxmultilang ident="GENERAL_TITLE" }]</td>
                        [{if $oOrder->isNettoMode() }]
                        <td class="listheader">[{ oxmultilang ident="ORDER_ARTICLE_ENETTO" }]</td>
                        [{else}]
                        <td class="listheader">[{ oxmultilang ident="ORDER_ARTICLE_EBRUTTO" }]</td>
                        [{/if}]
                        <td class="listheader">[{ oxmultilang ident="GENERAL_ATALL" }]</td>
                        <td class="listheader" colspan="3">[{ oxmultilang ident="ORDER_ARTICLE_MWST" }]</td>
                    </tr>
                    [{assign var="blWhite" value=""}]
                    [{foreach from=$oOrder->getOrderArticles() item=listitem name=orderArticles}]
                        [{if $listitem->oxorderarticles__oxstorno->value == 1 }]
                            [{assign var="listclass" value=listitem3 }]
                        [{else}]
                            [{assign var="listclass" value=listitem$blWhite }]
                        [{/if}]
                        <tr id="art.[{$smarty.foreach.orderArticles.iteration}]">
                            <td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxamount->value }]</td>
                            <td valign="top" class="[{ $listclass}]" height="15">[{ $listitem->oxorderarticles__oxartnum->value }]</td>
                            <td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxtitle->value|oxtruncate:20:""|strip_tags }]</td>
                            [{if $oOrder->isNettoMode() }]
                                <td valign="top" class="[{ $listclass}]">[{ $listitem->getNetPriceFormated() }] <small>[{ $oOrder->oxorder__oxcurrency->value }]</small></td>
                                <td valign="top" class="[{ $listclass}]">[{ $listitem->getTotalNetPriceFormated() }] <small>[{ $oOrder->oxorder__oxcurrency->value }]</small></td>
                            [{else}]
                                <td valign="top" class="[{ $listclass}]">[{ $listitem->getBrutPriceFormated() }] <small>[{ $oOrder->oxorder__oxcurrency->value }]</small></td>
                                <td valign="top" class="[{ $listclass}]">[{ $listitem->getTotalBrutPriceFormated() }] <small>[{ $oOrder->oxorder__oxcurrency->value }]</small></td>
                            [{/if}]
                            <td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxvat->value}]</td>
                        </tr>
                        [{if $blWhite == "2"}]
                            [{assign var="blWhite" value=""}]
                        [{else}]
                            [{assign var="blWhite" value="2"}]
                        [{/if}]
                    [{/foreach}]
                </table>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="paypalOverlay"></div>

    <div id="paypalActions" class="paypalPopUp" >
        <form name="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="oepaypalorder_paypal">
            <input type="hidden" name="fnc" value="processAction">
            <input type="hidden" name="oxid" value="[{$oxid}]">
            <input type="hidden" name="editval[category__oxid]" value="[{$oxid}]">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="transaction_id" value="">
            <input type="hidden" name="full_amount" value="">
            <div id="paypalActionsContent"></div>
        </form>
    </div>
    <div id="paypalActionsBlocks">
        <div id="captureBlock" class="paypalActionsBlock">
            <h3>[{oxmultilang ident="OEPAYPAL_MONEY_CAPTURE"}]</h3>
            <p class="paypalActionsBlockOptions">
                <label for="captureAmountInput">[{oxmultilang ident="OEPAYPAL_AMOUNT"}]</label>:
                <select class="amountSelect" name="capture_type" data-input="captureAmountInput">
                    <option value="Complete"
                            data-disabled="1"
                            data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('capture')|@json_encode}]'
                            data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('capture')}]">
                        [{oxmultilang ident="OEPAYPAL_MONEY_ACTION_FULL"}]
                    </option>
                    <option value="NotComplete"
                            data-disabled="0"
                            data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('capture_partial')|@json_encode}]'
                            data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('capture_partial')}]">
                        [{oxmultilang ident="OEPAYPAL_MONEY_ACTION_PARTIAL"}]
                    </option>
                </select>
                <input id="captureAmountInput" type="text" class="editinput" name="capture_amount" size="10" value="" disabled="disabled"> [{$currency}]
            </p>
            <div class="paypalStatusListPlaceholder"></div>
            <p class="paypalActionsBlockNotice">
                <label>[{oxmultilang ident="OEPAYPAL_COMMENT"}]</label></br>
                <textarea name="action_comment"></textarea>
            </p>
            <p class="paypalActionsButtons">
                <input id="captureSubmit" type="submit" class="edittext" name="action_submit" value="[{oxmultilang ident="OEPAYPAL_CAPTURE"}]" >
            </p>
        </div>

        <div id="voidBlock" class="paypalActionsBlock">
            <h3>[{oxmultilang ident="OEPAYPAL_AUTHORIZATION"}]</h3>
            <div class="paypalStatusListPlaceholder"></div>
            <p class="paypalActionsBlockNotice">
                <label>[{oxmultilang ident="OEPAYPAL_COMMENT"}]</label></br>
                <textarea name="action_comment"></textarea>
            </p>
            <p class="paypalActionsButtons">
                <input id="voidSubmit" type="submit" class="edittext" name="action_submit" value="[{oxmultilang ident="OEPAYPAL_CANCEL_AUTHORIZATION"}]" >
            </p>
        </div>

        <div id="refundBlock" class="paypalActionsBlock">
            <h3>[{oxmultilang ident="OEPAYPAL_MONEY_REFUND"}]:</h3>
            <p class="paypalActionsBlockOptions">
                <select class="amountSelect" name="refund_type" data-input="refundAmountInput">
                    <option value="Full"
                            data-disabled="1"
                            data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('refund')|@json_encode}]'
                            data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('refund')}]">
                        [{oxmultilang ident="OEPAYPAL_MONEY_ACTION_FULL"}]
                    </option>
                    <option value="Partial"
                            data-disabled="0"
                            data-statuslist='[{$oOrderPaymentStatusList->getAvailableStatuses('refund_partial')|@json_encode}]'
                            data-activestatus="[{$oOrderPaymentStatusCalculator->getSuggestStatus('refund_partial')}]">
                        [{oxmultilang ident="OEPAYPAL_MONEY_ACTION_PARTIAL"}]
                    </option>
                </select>
                <input id="refundAmountInput" type="text" class="editinput" name="refund_amount" size="10" value="" disabled="disabled"> [{$currency}]
            </p>
            <div class="paypalStatusListPlaceholder"></div>
            <p class="paypalActionsBlockNotice">
                <label>[{oxmultilang ident="OEPAYPAL_COMMENT"}]</label></br>
                <textarea name="action_comment"></textarea>
            </p>
            <p class="paypalActionsButtons">
                <input id="refundSubmit" type="submit" class="edittext" name="action_submit" value="[{oxmultilang ident="OEPAYPAL_REFUND"}]">
            </p>
        </div>

        <div id="paypalStatusList">
            [{oxmultilang ident="OEPAYPAL_SHOP_PAYMENT_STATUS"}]
            [{foreach from=$oOrderPaymentStatusList item=status}]
                <span id="[{$status}]Status">
                    <input id="[{$status}]StatusCheckbox" type="radio" name="order_status" value="[{$status}]">
                    <label for="[{$status}]StatusCheckbox">[{oxmultilang ident='OEPAYPAL_STATUS_'|cat:$status}]</label>
                </span>
            [{/foreach}]
        </div>
    </div>
[{else}]
    <div class="messagebox">[{ $sMessage }]</div>
[{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]