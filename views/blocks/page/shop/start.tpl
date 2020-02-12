[{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnStartPage()}]
    [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getBruttoSum()}]
    [{if $oxcmp_basket->isPriceViewModeNetto()}]
        [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getNettoSum()}]
    [{/if}]

    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal_installment.css')}]
    [{include file="installment_banners.tpl" amount=$paypalInstallmentPrice}]

    <div id="paypal-installment-banner-container"></div>
[{/if}]

[{$smarty.block.parent}]