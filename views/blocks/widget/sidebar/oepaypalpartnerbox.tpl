[{$smarty.block.parent}]

[{if $oViewConf->isPayPalActive()}]

    [{oxstyle include=$oViewConf->getModuleUrl('oepaypal','out/src/css/paypal.css')}]

    <div id="paypalPartnerLogo">
        <a href="#">
            <img src="[{$oViewConf->getModuleUrl('oepaypal','out/img/')}][{oxmultilang ident="OEPAYPAL_LOGO_IMG"}]"
                 border="0" alt="[{oxmultilang ident="OEPAYPAL_PAYMENT_HELP_LINK_TEXT"}]">
        </a>

        [{assign var="paypalHelpLink" value="OEPAYPAL_PAYMENT_HELP_LINK"|oxmultilangassign}]
        [{oxscript add="$('#paypalPartnerLogo').click(function (){window.open('`$paypalHelpLink`','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=500, height=450');return false;});"}]
    </div>
    [{/if}]
