[{assign var="currency" value=$oView->getActCurrency()}]
<script src="https://www.paypal.com/sdk/js?client-id=[{$oViewConf->getPayPalClientId()}]&components=messages"></script>
<script>
  window.onload = function() {
    paypal.Messages({
      amount: [{$amount}],
      currency: '[{$currency->name}]',
      countryCode: '[{$oViewConf->getActLanguageAbbr()|upper}]',
      style: {
        layout: 'flex',
        color: '[{$oViewConf->getPayPalBannersColorScheme()}]',
        ratio: '20x1'
      }
    }).render('#paypal-installment-banner-container');
  };
</script>
