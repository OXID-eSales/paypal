[{if !isset($size) }]
    [{assign var="size" value="20x1"}]
[{/if}]

[{assign var="currency" value=$oView->getActCurrency()}]

[{oxscript include="https://www.paypal.com/sdk/js?client-id="|cat:$oViewConf->getPayPalClientId()|cat:"&components=messages"}]

[{capture assign="installmentBanners"}]
    // Create installment banner holder
    var newNode = document.createElement('div');
    newNode.setAttribute('id', 'paypal-installment-banner-container');
    var referenceNode = document.querySelector('[{$selector}]');

    if (referenceNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    } else {
        console.warn('Installment banners was not added due to missing element `[{$selector}]`');
    }

    var PayPalMessage = function () {
        var windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var bannerLayout = windowWidth <= 400 ? 'text' : 'flex';

        paypal.Messages({
            amount: [{$amount}],
            currency: '[{$currency->name}]',
            countryCode: '[{$oViewConf->getActLanguageAbbr()|upper}]',
            style: {
                layout: bannerLayout,
                color: '[{$oViewConf->getPayPalBannersColorScheme()}]',
                ratio: '[{$size}]'
            }
        }).render('#paypal-installment-banner-container');
    };

    var initWhenPayPalMessageAvailable = function (){
        if (typeof paypal !== 'undefined' && typeof paypal.Messages !== 'undefined') {
            PayPalMessage();
        } else {
            setTimeout(function(){
                initWhenPayPalMessageAvailable();
            }, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenPayPalMessageAvailable);
    } else {
        initWhenPayPalMessageAvailable();
    }

    window.onresize = function () {
        initWhenPayPalMessageAvailable();
    }
[{/capture}]
[{oxscript add=$installmentBanners}]