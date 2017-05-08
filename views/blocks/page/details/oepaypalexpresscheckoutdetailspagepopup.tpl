[{$smarty.block.parent}]

[{if $oViewConf->isExpressCheckoutEnabledInDetails() && !$oDetailsProduct->isNotBuyable()}]
    [{if $oView->oePayPalShowECSPopup()}]

        [{if $oViewConf->getActiveTheme()=='flow'}]
            <div class="modal fade" id="popup1" tabindex="-1" role="dialog" aria-labelledby="popup1Label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <span class="h4 modal-title" id="popup1Label">&nbsp;</span>
                        </div>
                        <div class="modal-body">
                            [{if $oView->oePayPalGetArticleAmount() gt 1}]
                            [{oxmultilang ident="OEPAYPAL_SAME_ITEMS_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                            [{else}]
                            [{oxmultilang ident="OEPAYPAL_SAME_ITEM_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                            [{/if}]
                        </div>
                        <div class="modal-footer">


                            <button id="actionAddToBasketAndGoToCheckout" type="submit" class="btn btn-default">
                                [{oxmultilang ident="OEPAYPAL_BUTTON_ADD_ITEM"}]
                            </button>
                            <button id="actionNotAddToBasketAndGoToCheckout" type="submit" class="btn btn-default">
                                [{oxmultilang ident="OEPAYPAL_BUTTON_DO_NOT_ADD_ITEM"}]
                            </button>
                            <div class="oePayPalPopupNav">
                                <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=basket"}]"
                                   class="btn btn-default">[{oxmultilang ident="OEPAYPAL_MINIBASKET_DISPLAY_BASKET"}]</a>

                                <button class="btn btn-default" data-dismiss="modal">[{oxmultilang ident="OEPAYPAL_CANCEL"}]</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            [{oxscript add="$('#popup1').modal('show');"}]

        [{else}]

            <div id="popupECS" class="popupBox corners FXgradGreyLight glowShadow">
                <img src="[{$oViewConf->getImageUrl('x.png')}]" alt="" class="closePop">

                <p>
                    [{if $oView->oePayPalGetArticleAmount() gt 1}]
                        [{oxmultilang ident="OEPAYPAL_SAME_ITEMS_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                    [{else}]
                        [{oxmultilang ident="OEPAYPAL_SAME_ITEM_QUESTION" args=$oView->oePayPalGetArticleAmount()}]
                    [{/if}]
                </p>

                <div class="clear">
                    <button id="actionAddToBasketAndGoToCheckout" type="submit" class="submitButton largeButton">
                        [{oxmultilang ident="OEPAYPAL_BUTTON_ADD_ITEM"}]
                    </button>
                    <button id="actionNotAddToBasketAndGoToCheckout" type="submit" class="submitButton largeButton">
                        [{oxmultilang ident="OEPAYPAL_BUTTON_DO_NOT_ADD_ITEM"}]
                    </button>
                    <div class="oePayPalPopupNav">
                        <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=basket"}]"
                           class="textButton largeButton">[{oxmultilang ident="OEPAYPAL_MINIBASKET_DISPLAY_BASKET"}]</a>
                        <button class="textButton largeButton closePop">[{oxmultilang ident="OEPAYPAL_CANCEL"}]</button>
                    </div>
                </div>
            </div>

            [{oxscript include="js/widgets/oxmodalpopup.js" priority=10}]

            [{*Show popup*}]
            [{oxscript add='$(function(){$("body").oxModalPopup({target: "#popupECS", openDialog: true});})'}]
        [{/if}]

        [{oxscript include=$oViewConf->getModuleUrl('oepaypal','out/src/js/oepaypalonclickproceedaction.js') priority=10}]

        [{*Change actions on button click*}]
        [{oxscript add='$("#actionNotAddToBasketAndGoToCheckout").oePayPalOnClickProceedAction({sAction: "actionNotAddToBasketAndGoToCheckout"});'}]
        [{oxscript add='$("#actionAddToBasketAndGoToCheckout").oePayPalOnClickProceedAction({sAction: "actionAddToBasketAndGoToCheckout"});'}]

        [{*Add same amount to #amountToBasket input*}]
        [{oxscript add='$("#amountToBasket").val('|cat:$oView->oePayPalGetArticleAmount()|cat:');'}]

        [{*Add same label to #persistentParam input*}]
        [{if $oView->oePayPalGetPersistentParam()}]
            [{oxscript add='$("#persistentParam").val("'|cat:$oView->oePayPalGetPersistentParam()|cat:'");'}]
        [{/if}]

        [{*Add same values to selection list*}]
        [{if $oView->oePayPalGetSelection()}]
            [{foreach from=$oView->oePayPalGetSelection() item=selection key=key}]
                [{oxscript add='
                    $("#productSelections input[name=\'sel['|cat:$key|cat:']\']").val("'|cat:$selection|cat:'");
                    var sSelectionName'|cat:$key|cat:' = $("#productSelections div:eq('|cat:$key|cat:') ul li a[data-seletion-id='|cat:$selection|cat:']").text();
                    $("#productSelections div:eq('|cat:$key|cat:') p span").text(sSelectionName'|cat:$key|cat:');
                '}]
            [{/foreach}]
        [{/if}]

        [{*Add same value to displayCartInPayPal checkbox*}]
        [{if $oView->oePayPalDisplayCartInPayPal() eq 0}]
            [{oxscript add='$("input[name=displayCartInPayPal]").attr("checked", false);'}]
        [{/if}]
    [{/if}]
[{/if}]
