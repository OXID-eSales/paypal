[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnSearchResultsPage()}]
    [{assign var="basketAmount" value=$oxcmp_basket->getPrice()}]

    [{include file="installment_banners.tpl" amount=$basketAmount->getPrice()}]
    <div id="paypal-installment-banner-container"></div>
[{/if}]
