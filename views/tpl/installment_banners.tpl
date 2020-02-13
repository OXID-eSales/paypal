[{if !isset($size) }]
    [{assign var="size" value="20x1"}]
[{/if}]

[{assign var="currency" value=$oView->getActCurrency()}]

[{oxscript include="https://www.paypal.com/sdk/js?client-id="|cat:$oViewConf->getPayPalClientId()|cat:"&components=messages"}]
<script type="application/javascript">
  // Create installment banner holder
  var newNode = document.createElement('div');
  newNode.setAttribute('id', 'paypal-installment-banner-container');
  var referenceNode = document.querySelector('[{$selector}]');
  referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);

  var PayPalMessage = function () {
    paypal.Messages({
      amount: [{$amount}],
      currency: '[{$currency->name}]',
      countryCode: '[{$oViewConf->getActLanguageAbbr()|upper}]',
      style: {
        layout: 'flex',
        color: '[{$oViewConf->getPayPalBannersColorScheme()}]',
        ratio: '[{$size}]'
      }
    }).render('#paypal-installment-banner-container');
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', PayPalMessage);
  } else {
    PayPalMessage();
  }
</script>
