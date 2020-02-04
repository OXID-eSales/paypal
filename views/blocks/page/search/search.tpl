[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnSearchResultsPage()}]
    [{if $oxcmp_basket->isPriceViewModeNetto()}]
        [{assign var="basketAmount" value=$oxcmp_basket->getNettoSum()}]
    [{else}]
        [{assign var="basketAmount" value=$oxcmp_basket->getBruttoSum()}]
    [{/if}]

    [{include file="installment_banners.tpl" amount=$basketAmount}]
    <div id="paypal-installment-banner-container"></div>
[{/if}]
