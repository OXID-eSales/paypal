[{capture name="paypalExpressCheckoutFormContent"}]
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="oepaypalexpresscheckoutdispatcher">
    <input type="hidden" name="fnc" value="setExpressCheckout">
    <input type="hidden" name="oePayPalRequestedControllerKey" value="[{$oView->getClassKey()}]">
    <input type="image" name="paypalExpressCheckoutButtonECS" class="paypalCheckoutBtn"
           src="[{$oViewConf->getModuleUrl('oepaypal','out/img/')}][{$oViewConf->getActLanguageAbbr()}]-btn-expresscheckout.png"
           title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">
        <div class="paypalExpressCheckoutMsg">
            [{if $oViewConf->sendOrderInfoToPayPal()}]
                <a href="#" class="paypalHelpIcon small"
                   style="position: relative; float:left; display:inline-block; left: 0; margin-right:8px;">?</a>
                <div class="paypalHelpBox popupBox corners FXgradGreyLight shadow">
                    [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL_HELPTEXT"}]
                </div>
                <input type="checkbox" name="displayCartInPayPal"
                   value="1" [{if $oViewConf->sendOrderInfoToPayPalDefault()}]checked[{/if}]>
                [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL"}]
            [{/if}]
        </div>
[{/capture}]

[{if $oViewConf->isExpressCheckoutEnabled() && (('user' != $oView->getClassKey()) || (('user' == $oView->getClassKey() && !$oxcmp_user)))}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal.css')}]
    [{$smarty.block.parent}]
    [{if 'user' == $oView->getClassKey()}]
        <div class="clearfix"></div>
        <div class="lineBox paypalExpressCheckoutBoxUser">
            <div id="paypalExpressCheckoutBox" class="col-xs-12">
                <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                    <div class="panel panel-default oePayPalECSForm">
                        [{$smarty.capture.paypalExpressCheckoutFormContent}]
                    </div>
                </form>
             </div>
        </div>
    [{else}]
        <div id="paypalExpressCheckoutBox" class="paypalExpressCheckoutBox">
            <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                <div>
                    [{$smarty.capture.paypalExpressCheckoutFormContent}]
                </div>
            </form>
        </div>
    [{/if}]
    [{oxscript add='$(".paypalHelpIcon").hover(function (){$(this).parent(".paypalExpressCheckoutMsg").children(".paypalHelpBox").toggle();});'}]
    [{oxscript add='$(".paypalHelpIcon").click(function (){return false;});'}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]