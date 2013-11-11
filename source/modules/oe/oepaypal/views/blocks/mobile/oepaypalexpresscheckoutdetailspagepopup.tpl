[{$smarty.block.parent}]

[{if $oViewConf->oePayPalIsModuleActive('oethemeswitcher', '1.1') && $oViewConf->isExpressCheckoutEnabledInDetails() && !$oDetailsProduct->isNotBuyable()}]

    [{if $oView->oePayPalShowECSPopup()}]
        <div id="ECConfirmation" class="container">
            <ul class="nav nav-list main-nav-list">
                <li>
                    <a class="back hideECConfirmation" href="#">
                        <span>[{oxmultilang ident="OEPAYPAL_BACK"}]</span>
                        <i class="glyphicon-chevron-left"></i>
                    </a>
                </li>
            </ul>
            <div class="content">
                <p>
                    [{if $oView->oePayPalGetArticleAmount() gt 1}]
                    [{oxmultilang ident="OEPAYPAL_SAME_ITEMS_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                    [{else}]
                    [{oxmultilang ident="OEPAYPAL_SAME_ITEM_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                    [{/if}]
                </p>
                <ul class="form">
                    <li>
                        <button id="actionAddToBasketAndGoToCheckout" type="submit" class="btn">
                            [{oxmultilang ident="OEPAYPAL_BUTTON_ADD_ITEM"}]
                        </button>
                    </li>
                    <li>
                        <button id="actionNotAddToBasketAndGoToCheckout" type="submit" class="btn">
                            [{oxmultilang ident="OEPAYPAL_BUTTON_DO_NOT_ADD_ITEM"}]
                        </button>
                    </li>
                    <li>
                        <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=basket"}]" class="btn">[{oxmultilang ident="OEPAYPAL_MINIBASKET_DISPLAY_BASKET"}]</a>
                    </li>
                    <li>
                        <button class="btn hideECConfirmation">[{oxmultilang ident="OEPAYPAL_CANCEL"}]</button>
                    </li>
                </ul>
            </div>
        </div>

        [{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/src/js/oepaypalonclickproceedaction.js') priority=10 }]
        [{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/mobile/src/js/ecconfirmation.js') priority=10 }]
        [{*Change actions on button click*}]
        [{oxscript add='$( "#actionNotAddToBasketAndGoToCheckout" ).oePayPalOnClickProceedAction( {sAction: "actionNotAddToBasketAndGoToCheckout", sFormContainer: "#productinfo"} );'}]
        [{oxscript add='$( "#actionAddToBasketAndGoToCheckout" ).oePayPalOnClickProceedAction( {sAction: "actionAddToBasketAndGoToCheckout", sFormContainer: "#productinfo"} );'}]
        [{*Add same label to #persistentParam input*}]
        [{if $oView->oePayPalGetPersistentParam()}]
            [{oxscript add='$( "#persistentParam" ).val( "'|cat:$oView->oePayPalGetPersistentParam()|cat:'" );'}]
        [{/if}]
        [{*Add same values to selection list*}]
        [{if $oView->oePayPalGetSelection()}]
            [{foreach from=$oView->oePayPalGetSelection() item=sSelection key=iKey}]
                [{oxscript add='
                    $( "#productSelections input[name=\'sel['|cat:$iKey|cat:']\']" ).val( "'|cat:$sSelection|cat:'" );
                    var sSelectionName'|cat:$iKey|cat:' = $( "#productSelections div.dropdown:eq('|cat:$iKey|cat:') ul li a[data-selection-id='|cat:$sSelection|cat:']" ).text();
                    $( "#productSelections div.dropdown:eq('|cat:$iKey|cat:') a span" ).text( sSelectionName'|cat:$iKey|cat:' );
                '}]
            [{/foreach}]
        [{/if}]
        [{*EC confirmation*}]
        [{oxscript add='oECConfirmation.init()'}]
    [{/if}]

[{/if}]
