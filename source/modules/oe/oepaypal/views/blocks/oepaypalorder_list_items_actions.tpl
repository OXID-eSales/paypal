[{if $listitem->oxorder__oxstorno->value == 1 }]
    [{assign var="listclass" value=listitem3 }]
[{else}]
    [{if $listitem->blacklist == 1}]
    [{assign var="listclass" value=listitem3 }]
    [{ else}]
    [{assign var="listclass" value=listitem$blWhite }]
    [{/if}]
[{/if}]
[{if $listitem->getId() == $oxid }]
    [{assign var="listclass" value=listitem4 }]
[{/if}]
<td valign="top" height="15" class="[{ $listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->oxorder__oxid->value}]');" class="[{ $listclass}]">[{ $listitem->oxorder__paymentname->value }]</a>
    </div>
</td>
<td valign="top" height="15" class="[{ $listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->oxorder__oxid->value}]');" class="[{ $listclass}]">[{oxmultilang ident='OEPAYPAL_STATUS_'|cat:$listitem->getPayPalPaymentStatus() }]</a>
    </div>
</td>
[{$smarty.block.parent}]