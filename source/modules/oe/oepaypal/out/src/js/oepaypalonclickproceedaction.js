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
( function( $ ) {
    /**
     * Changes form function name and submits.
     */
    var oePayPalOnClickProceedAction = {
        options: {
            sAction: 'actionExpressCheckoutFromDetailsPage',
            sForm: '#detailsMain form.js-oxProductForm'
        },

        _create: function() {
            var self = this;

            $( self.element ).click( function() {
                $( self.options.sForm + ' input[name="fnc"]' ).val( self.options.sAction );
                $( self.options.sForm ).submit();
            } );
        }
    };

    $.widget("ui.oePayPalOnClickProceedAction", oePayPalOnClickProceedAction );

} )( jQuery );