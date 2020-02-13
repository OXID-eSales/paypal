[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnSearchResultsPage()}]
    [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getBruttoSum()}]
    [{if $oxcmp_basket->isPriceViewModeNetto()}]
        [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getNettoSum()}]
    [{/if}]

    [{include file="installment_banners.tpl" amount=$paypalInstallmentPrice selector=$oViewConf->getPayPalBannerSearchPageSelector()}]
[{/if}]
