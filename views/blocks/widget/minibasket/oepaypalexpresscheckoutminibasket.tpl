[{$smarty.block.parent}]

[{if $oViewConf->isExpressCheckoutEnabledInMiniBasket()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal.css')}]
    <div id="paypalExpressCheckoutMiniBasketBox" class="paypalExpressCheckoutBox">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <div>
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="oepaypalexpresscheckoutdispatcher"/>
                <input type="hidden" name="fnc" value="setExpressCheckout"/>
                <input type="hidden" name="oePayPalCancelURL" value="[{$oViewConf->getCurrentURL()}]"/>
                <input type="image" name="paypalExpressCheckoutButton" id="paypalExpressCheckoutMiniBasketImage"
                       src="[{$oViewConf->getModuleUrl('oepaypal','out/img/')}][{$oViewConf->getActLanguageAbbr()}]-btn-expresscheckout.png"
                       title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">

                <div class="paypalExpressCheckoutMsg">
                    [{if $oViewConf->sendOrderInfoToPayPal()}]
                    <a href="#" class="paypalHelpIcon small" id="paypalHelpIconMiniBasket">?</a>

                    <div id="paypalHelpBoxMiniBasket" class="paypalHelpBox popupBox corners FXgradGreyLight shadow">
                        [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL_HELPTEXT"}]
                    </div>
                <input type="checkbox" name="displayCartInPayPal"
                       value="1" [{if $oViewConf->sendOrderInfoToPayPalDefault()}]checked[{/if}]>
                    [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL"}]
                    [{/if}]
                </div>
            </div>
        </form>
    </div>
    [{oxscript add='$("#paypalHelpIconMiniBasket").hover(function (){$("#paypalHelpBoxMiniBasket").show();},function (){$("#paypalHelpBoxMiniBasket").hide();});'}]
    [{oxscript add='$("#paypalHelpIconMiniBasket").click(function (){return false;});'}]
    [{oxscript add='$("#paypalExpressCheckoutMiniBasketBox").appendTo($("#paypalExpressCheckoutMiniBasketBox").parent().children("p.functions"))'}]
    [{/if}]
