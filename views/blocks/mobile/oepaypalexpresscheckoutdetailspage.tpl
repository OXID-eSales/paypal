[{if $oViewConf->isExpressCheckoutEnabledInDetails() && !$oDetailsProduct->isNotBuyable()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/mobile/src/css/paypal_mobile.css')}]
    [{$oViewConf->getHiddenSid()}]
    <div id="paypalExpressCheckoutDetailsBox" class="paypalExpressCheckoutBox paypalExpressCheckoutDetailsBox">
        <input type="hidden" name="oePayPalCancelURL" value="[{$oViewConf->getCurrentURL()}]"/>
        <input id="paypalExpressCheckoutDetailsButton" class="paypalCheckoutBtn"
            [{if !$blCanBuy}]disabled="disabled"[{/if}] type="image" name="paypalExpressCheckoutButton"
            src="[{$oViewConf->getModuleUrl('oepaypal','out/mobile/src/img/')}]checkout-paypal-medium-[{$oViewConf->getActLanguageAbbr()}].png"
            title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">
        [{if $oViewConf->sendOrderInfoToPayPal()}]
            <input id="displayCartInPayPal" type="hidden" name="displayCartInPayPal" value="[{if $oViewConf->sendOrderInfoToPayPalDefault()}]1[{else}]0[{/if}]"/>
        [{/if}]
        <p class="paypalExpressCheckoutMsg">[{oxmultilang ident="OEPAYPAL_OR"}]</p>
    </div>
    [{oxscript add='$("#paypalExpressCheckoutDetailsButton").click(function (){$("<input />").attr("type", "hidden").attr("name", "doPaypalExpressCheckoutFromDetailsPage").attr("value", "true").appendTo(".js-oxProductForm");return true;});'}]
    [{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/src/js/oepaypalonclickproceedaction.js') priority=10}]
    [{oxscript add='$( "#paypalExpressCheckoutDetailsButton" ).oePayPalOnClickProceedAction( {sAction: "actionExpressCheckoutFromDetailsPage", sForm: "#productinfo form.js-oxProductForm" } );'}]
[{/if}]
[{$smarty.block.parent}]
[{oxstyle inWidget=true}]
