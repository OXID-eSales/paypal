[{$smarty.block.parent}]

[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnCategoryPage()}]
    [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getBruttoSum()}]
    [{if $oxcmp_basket->isPriceViewModeNetto()}]
        [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getNettoSum()}]
    [{/if}]

    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal_installment.css')}]
    [{include file="installment_banners.tpl" amount=$paypalInstallmentPrice selector=$oViewConf->getPayPalBannerCategoryPageSelector()}]
[{/if}]
