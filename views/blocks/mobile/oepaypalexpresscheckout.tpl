[{if $oViewConf->isExpressCheckoutEnabled()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/mobile/src/css/paypal_mobile.css')}]
    <div class="paypalExpressCheckoutBox">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="oepaypalexpresscheckoutdispatcher">
            <input type="hidden" name="fnc" value="setExpressCheckout">
            <input class="paypalCheckoutBtn" type="image" name="paypalExpressCheckoutButton"
                   src="[{$oViewConf->getModuleUrl('oepaypal','out/mobile/src/img/')}]checkout-paypal-medium-[{$oViewConf->getActLanguageAbbr()}].png"
                   title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">
            [{if $oViewConf->sendOrderInfoToPayPal()}]
                <input id="displayCartInPayPal" type="hidden" name="displayCartInPayPal" value="[{if $oViewConf->sendOrderInfoToPayPalDefault()}]1[{else}]0[{/if}]"/>
            [{/if}]
        </form>
        <p class="paypalExpressCheckoutMsg">[{oxmultilang ident="OEPAYPAL_OR"}]</p>
    </div>
[{/if}]
[{$smarty.block.parent}]
