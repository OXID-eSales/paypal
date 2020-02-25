[{capture append="oxidBlock_content"}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
    [{assign var='rsslinks' value=$oView->getRssLinks()}]
    [{oxscript include="js/pages/start.min.js"}]

    [{oxifcontent ident="oxstartwelcome" object="oCont"}]
    <div class="welcome-teaser">[{$oCont->oxcontents__oxcontent->value}]</div>
    [{/oxifcontent}]

    [{* PayPal Installment Banners start*}]
    [{if $oViewConf->isModuleActive('oepaypal') && $oViewConf->showPayPalBannerOnStartPage()}]
    [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getBruttoSum()}]
    [{if $oxcmp_basket->isPriceViewModeNetto()}]
    [{assign var="paypalInstallmentPrice" value=$oxcmp_basket->getNettoSum()}]
    [{/if}]
    [{* PayPal Installment Banners end*}]

    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal_installment.css')}]
    [{include file="installment_banners.tpl" amount=$paypalInstallmentPrice selector=$oViewConf->getPayPalBannerStartPageSelector()}]
    [{/if}]

    [{assign var="oBargainArticles" value=$oView->getBargainArticleList()}]
    [{assign var="oNewestArticles" value=$oView->getNewestArticles()}]
    [{assign var="oTopArticles" value=$oView->getTop5ArticleList()}]

    [{if $oBargainArticles && $oBargainArticles->count()}]
    [{include file="widget/product/list.tpl" type=$oViewConf->getViewThemeParam('sStartPageListDisplayType') head="START_BARGAIN_HEADER"|oxmultilangassign subhead="START_BARGAIN_SUBHEADER"|oxmultilangassign listId="bargainItems" products=$oBargainArticles rsslink=$rsslinks.bargainArticles rssId="rssBargainProducts" showMainLink=true iProductsPerLine=4}]
    [{/if}]

    [{if $oViewConf->getViewThemeParam('bl_showManufacturerSlider')}]
    [{include file="widget/manufacturersslider.tpl"}]
    [{/if}]

    [{if $oNewestArticles && $oNewestArticles->count()}]
    [{include file="widget/product/list.tpl" type=$oViewConf->getViewThemeParam('sStartPageListDisplayType') head="START_NEWEST_HEADER"|oxmultilangassign subhead="START_NEWEST_SUBHEADER"|oxmultilangassign listId="newItems" products=$oNewestArticles rsslink=$rsslinks.newestArticles rssId="rssNewestProducts" showMainLink=true iProductsPerLine=4}]
    [{/if}]

    [{if $oNewestArticles && $oNewestArticles->count() && $oTopArticles && $oTopArticles->count()}]
    <div class="row">
        <hr>
    </div>
    [{/if}]

    [{if $oTopArticles && $oTopArticles->count()}]
    [{include file="widget/product/list.tpl" type="infogrid" head="START_TOP_PRODUCTS_HEADER"|oxmultilangassign subhead="START_TOP_PRODUCTS_SUBHEADER"|oxmultilangassign listId="topBox" products=$oTopArticles rsslink=$rsslinks.topArticles rssId="rssTopProducts" showMainLink=true iProductsPerLine=2}]
    [{/if}]


    [{insert name="oxid_tracker"}]
    [{/capture}]
[{include file="layout/page.tpl"}]