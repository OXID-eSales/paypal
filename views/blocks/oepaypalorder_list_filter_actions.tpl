<td valign="top" class="listfilter first" height="20">
    <div class="r1">
        <div class="b1">
            <select name="paypalpayment" onChange="document.search.submit();">
                <option value="-1" style="color: #000000;">[{oxmultilang ident="OEPAYPAL_LIST_STATUS_ALL"}]</option>
                [{foreach from=$oPayments item=payment}]
            <option value="[{$payment->getId()}]" [{if $paypalpayment == $payment->getId()}]SELECTED[{/if}] >[{$payment->oxpayments__oxdesc->value}]</option>
                [{/foreach}]
            </select>
        </div>
    </div>
</td>
<td valign="top" class="listfilter first" height="20">
    <div class="r1">
        <div class="b1">
            <select name="paypalpaymentstatus" onChange="document.search.submit();">
                <option value="-1" style="color: #000000;">[{oxmultilang ident="OEPAYPAL_LIST_STATUS_ALL"}]</option>
                [{foreach from=$opaypalpaymentstatuslist item=field}]
            <option value="[{$field}]" [{if $spaypalpaymentstatus == $field}]SELECTED[{/if}] >[{oxmultilang ident='OEPAYPAL_STATUS_'|cat:$field}]</option>
                [{/foreach}]
            </select>
        </div>
    </div>
</td>
[{$smarty.block.parent}]
