[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnStartPage()}]
    [{assign var="basketAmount" value=$oxcmp_basket->getPrice()}]

    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal_installment.css')}]
    [{include file="installment_banners.tpl" amount=$basketAmount->getPrice()}]

    <div id="paypal-installment-banner-container"></div>
[{/if}]

[{$smarty.block.parent}]