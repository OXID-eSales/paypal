[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnCategoryPage()}]
    [{assign var="basketAmount" value=$oxcmp_basket->getPrice()}]

    [{include file="installment_banners.tpl" amount=$basketAmount->getPrice()}]

    [{oxscript add="$('<div>', { 'id': 'paypal-installment-banner-container'}).appendTo('.page-header');"}]
[{/if}]
