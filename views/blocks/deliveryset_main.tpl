[{$smarty.block.parent}]
[{if $readonly }]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]
<tr>
    <td class="edittext">
        [{oxmultilang ident="OEPAYPAL_MOBILE_DEFAULT_PAYMENT"}]
    </td>
    <td class="edittext">
        <input class="edittext" type="checkbox" name="isPayPalDefaultMobilePayment" value='1'
        [{if $isPayPalDefaultMobilePayment}]checked[{/if}] [{$readonly}]>
        [{oxinputhelp ident="OEPAYPAL_HELP_MOBILE_DEFAULT_PAYMENT"}]
    </td>
</tr>