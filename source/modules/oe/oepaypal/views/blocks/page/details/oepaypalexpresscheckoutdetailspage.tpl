[{$smarty.block.parent}]

[{if $oViewConf->isExpressCheckoutEnabledInDetails() && !$oDetailsProduct->isNotBuyable()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal.css')}]

    <div id="paypalExpressCheckoutDetailsBox" class="paypalExpressCheckoutBox paypalExpressCheckoutDetailsBox">
        [{ $oViewConf->getHiddenSid() }]
        <input type="hidden" name="oePayPalCancelURL" value="[{$oView->oePayPalGetCancelUrl()}]"/>
        <input type="image" name="paypalExpressCheckoutButton" id="paypalExpressCheckoutDetailsButton" class="paypalExpressCheckoutDetailsButton" [{if !$blCanBuy}]disabled="disabled"[{/if}] src="[{$oViewConf->getModuleUrl('oepaypal','out/img/')}][{$oViewConf->getActLanguageAbbr()}]-btn-expresscheckout.png" title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">

        <div class="paypalExpressCheckoutMsg">
            [{if $oViewConf->sendOrderInfoToPayPal()}]
                <a href="#" class="paypalHelpIcon small">?</a>

                <div class="paypalHelpBox popupBox corners FXgradGreyLight shadow">
                    [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL_HELPTEXT"}]
                </div>

                <input type="checkbox" name="displayCartInPayPal" value="1" [{if $oViewConf->sendOrderInfoToPayPalDefault()}]checked[{/if}]>
                [{oxmultilang ident="OEPAYPAL_DISPLAY_BASKET_IN_PAYPAL"}]
            [{/if}]
        </div>
    </div>

    [{oxscript add='$(".paypalHelpIcon").hover(function (){$(this).parent(".paypalExpressCheckoutMsg").children(".paypalHelpBox").toggle();});'}]
    [{oxscript add='$(".paypalHelpIcon").click(function (){return false;});'}]
    [{oxscript add='$("#paypalExpressCheckoutDetailsButton").click(function (){$("<input />").attr("type", "hidden").attr("name", "doPaypalExpressCheckoutFromDetailsPage").attr("value", "true").appendTo(".js-oxProductForm");return true;});'}]
    [{oxscript add='$("#toBasket").prependTo($("#paypalExpressCheckoutDetailsBox"))'}]
    [{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/src/js/oepaypalonclickproceedaction.js') priority=10 }]
    [{oxscript add='$( "#paypalExpressCheckoutDetailsButton" ).oePayPalOnClickProceedAction( {sAction: "actionExpressCheckoutFromDetailsPage"} );'}]

[{/if}]
