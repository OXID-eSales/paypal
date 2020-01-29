[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnStartPage()}]
    [{include file="installment_banners.tpl" amount=3000}]
    <div id="paypal-installment-banner-container"></div>
[{/if}]
[{$smarty.block.parent}]