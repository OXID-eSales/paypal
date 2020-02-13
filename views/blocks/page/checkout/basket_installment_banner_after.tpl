[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnCheckoutPage()}]
    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal_installment.css')}]
    [{assign var="basketAmount" value=$oxcmp_basket->getPrice()}]
    [{include file="installment_banners.tpl" amount=$basketAmount->getPrice() selector=$oViewConf->getPayPalBannerCartPageSelector()}]
[{/if}]
