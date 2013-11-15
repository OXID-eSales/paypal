[{if $oViewConf->isExpressCheckoutEnabled()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal.css')}]
    
    [{$smarty.block.parent}]

    <div class="paypalExpressCheckoutBox">
        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post">
            <div>
                [{ $oViewConf->getHiddenSid() }]
                <input type="hidden" name="cl" value="oePayPalExpressCheckoutDispatcher">
                <input type="hidden" name="fnc" value="setExpressCheckout">
                <input type="image" name="paypalExpressCheckoutButton" class="paypalCheckoutBtn" src="[{$oViewConf->getModuleUrl('oepaypal','out/img/')}][{$oViewConf->getActLanguageAbbr()}]-btn-expresscheckout.png" title="[{$oViewConf->getPayPalPaymentDescription()|strip_tags:false|trim|oxescape}]">

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
        </form>
    </div>

    [{oxscript add='$(".paypalHelpIcon").hover(function (){$(this).parent(".paypalExpressCheckoutMsg").children(".paypalHelpBox").toggle();});'}]
    [{oxscript add='$(".paypalHelpIcon").click(function (){return false;});'}]

[{else}]
    [{$smarty.block.parent}]
[{/if}]
