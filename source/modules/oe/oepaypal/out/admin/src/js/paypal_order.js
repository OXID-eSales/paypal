/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2013
 */
var PayPalPopUp = (function($) {
    var popUpBlock, popUpOverlay, popUpClose;

    var obj = {
        init: function() {
            popUpOverlay = $('#paypalOverlay');
            popUpOverlay.click( PayPalPopUp.hidePopUp );

            $('.popUpLink').click( PayPalPopUp.actionHandler );
        },

        /**
         * action handler
         * @param e event
         */
        actionHandler: function( e ) {
            var oBlock = $( '#'+$(this).data('block') );

            PayPalPopUp.showPopUp( oBlock );
            e.stopPropagation();
        },

        showPopUp: function( oBlock ) {
            popUpBlock = oBlock;

            popUpOverlay.show();
            popUpBlock.show();

            popUpClose = $('<a class="paypalPopUpClose" href="#">x</a>');
            popUpClose.click( PayPalPopUp.hidePopUp );
            popUpBlock.prepend( popUpClose );

            fixBlockPosition( popUpBlock );
        },

        /**
         * hides popUp
         */
        hidePopUp: function ( e ) {
            popUpClose.remove();
            popUpOverlay.hide();
            popUpBlock.hide();
        }
    }

    function fixBlockPosition( oBlock ) {
        var blockHeight = oBlock.outerHeight();
        var blockWidth = oBlock.outerWidth();
        var domHeight = $(document).height();
        var domWidth = $(document).width();
        var topMargin = Math.max( (domHeight - blockHeight) / 3, 0);
        var leftMargin = Math.max( (domWidth - blockWidth) / 2, 0);

        oBlock.css( { top: topMargin, left: leftMargin } );
    }

    return obj;
})(jQuery);

var PayPalActions = (function($) {

    var popUpBlock, popUpBlockContent, popUpBlocks, popUpOverlay, statusList;

    var obj = {
        actionOptions: {
            'refund': {
                refund_amount: 'amount',
                refund_type: 'type',
                transaction_id: 'transid',
                full_amount: 'amount'
            },
            'capture': {
                capture_amount: 'amount',
                capture_type: 'type',
                full_amount: 'amount'
            }
        },

        /**
         * initiates PayPal actions
         */
        init: function() {
            popUpBlock = $('#paypalActions');
            popUpBlockContent = $('#paypalActionsContent', popUpBlock);
            popUpBlocks = $('#paypalActionsBlocks');
            statusList = $( '#paypalStatusList');

            popUpBlockContent.delegate( 'select.amountSelect', 'change', PayPalActions.amountSelectActionHandler );
            $('.actionLink').click( PayPalActions.actionHandler );
        },

        /**
         * action handler
         * @param e event
         */
        actionHandler: function( e ) {
            var sAction = $(this).data('action');

            PayPalActions.showPopUp( sAction,  $(this).data() );
            e.stopPropagation();
        },

        /**
         * shows popUp
         * @param sAction
         * @param oOptions
         */
        showPopUp: function ( sAction, oOptions ) {
            popUpBlockContent.empty();
            var sBlock = $('#'+sAction+'Block', popUpBlocks);
            popUpBlockContent.html( sBlock.html() );

            var oFormOptions = getFormOptions( sAction, oOptions );
            setFormOptions( popUpBlock, oFormOptions );

            showOrderStatusList( oOptions['statuslist'],  oOptions['activestatus'] );

            PayPalPopUp.showPopUp( popUpBlock );
        },

        /**
         * changes action type in select box
         */
        amountSelectActionHandler: function () {
            var selected = $(this).find(":selected");
            setAmountInputState( $(this) );
            showOrderStatusList( selected.data('statuslist'), selected.data('activestatus') );
        }
    }

    /**
     *
     * @private
     */
    function setAmountInputState( oEl ) {
        var oInput = $( '#'+oEl.data('input') );
        var dAmount = $('input[name=full_amount]', popUpBlock).val();
        var disabled = ( oEl.find(':selected').data('disabled') == 1 );
        oInput.attr('disabled', disabled).val( dAmount );
    }

    /**
     *
     * @private
     */
    function showOrderStatusList( oStatusList, sActiveStatus ) {
        var oPlaceholder = $('.paypalStatusListPlaceholder', popUpBlockContent).empty();
        if ( oStatusList.length <= 1 || $.type(oStatusList) == 'string' ) {
            var sStatus = ($.type(oStatusList) == 'string')? oStatusList : oStatusList[0];
            appendStatusHiddenInput( sStatus, oPlaceholder )
            return;
        }
        oPlaceholder.html( statusList.html() );

        $('span', oPlaceholder).hide();
        $.each( oStatusList, function(key, value) {
            var activeStatusBlock = $('#'+value+'Status', oPlaceholder).show();
            $('input[type=radio]', activeStatusBlock).attr('checked', value == sActiveStatus );
        });
    }

    /**
     * Appends status hidden input to given placeholder
     *
     * @param sStatus
     * @param oPlaceholder
     */
    function appendStatusHiddenInput( sStatus, oPlaceholder )
    {
        var checkbox = $('#'+sStatus+'StatusCheckbox', statusList);
        var input = $('<input type="hidden" />');
        input.attr({ name: checkbox.attr('name'), value: checkbox.attr('value') });
        oPlaceholder.append( input );
    }

    /**
     * Sets given options to given form inputs
     *
     * @param oForm
     * @param oFormOptions
     * @private
     */
    function setFormOptions( oForm, oFormOptions ) {
        $('input, select', oForm).each(function(i) {
            var inputName = $(this).attr('name');
            if ( inputName in oFormOptions ) {
                $(this).val( oFormOptions[inputName] ).change();
            }
        });
    }

    /**
     * forms options array with values for given action
     * @param sAction
     * @param oAllOptions
     * @returns object
     */
    function getFormOptions( sAction, oAllOptions ) {
        var oOptions = {action: sAction};
        if ( sAction in PayPalActions.actionOptions ) {
            $.each( PayPalActions.actionOptions[sAction], function(key, value) {
                oOptions[key] = oAllOptions[value];
            });
        }
        return oOptions;
    }

    return obj;
})(jQuery);

$(document).ready( function() {
    PayPalPopUp.init();
    PayPalActions.init();
});