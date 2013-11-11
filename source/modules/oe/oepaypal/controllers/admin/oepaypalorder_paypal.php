<?php
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

/**
 * Order class wrapper for PayPal module
 */
class oePayPalOrder_PayPal extends oxAdminDetails
{
    /**
     * Executes parent method parent::render(), creates oxOrder object,
     * passes it's data to Smarty engine and returns
     * name of template file "order_paypal.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData["sOxid"] =  $this->getEditObjectId();
        if ( $this->isNewPayPalOrder() ) {
            $this->_aViewData['oOrder'] = $this->getEditObject();
        } else {
            $this->_aViewData['sMessage'] = $this->isPayPalOrder() ? oxRegistry::getLang()->translateString( "OEPAYPAL_ONLY_FOR_NEW_PAYPAL_PAYMENT" ) :
                oxRegistry::getLang()->translateString( "OEPAYPAL_ONLY_FOR_PAYPAL_PAYMENT" );
        }

        return "order_paypal.tpl";
    }

    /**
     * Processes PayPal actions
     *
     */
    public function processAction()
    {
        try {
            $oRequest = oxNew( 'oePayPalRequest' );
            $sAction = $oRequest->getRequestParameter('action');

            $oOrder = $this->getEditObject();

            $oActionFactory = oxNew( 'oePayPalOrderActionFactory', $oRequest, $oOrder );
            $oAction = $oActionFactory->createAction( $sAction );

            $oAction->process();

        } catch ( oxException $oException ) {
            $this->_aViewData["error"] = $oException->getMessage();
        }
    }

    /**
     * Returns PayPal order action manager
     *
     * @return oePayPalOrderActionManager
     */
    public function getOrderActionManager()
    {
        $oManager = oxNew( 'oePayPalOrderActionManager' );
        $oManager->setOrder( $this->getEditObject()->getPayPalOrder() );

        return $oManager;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return oePayPalOrderPaymentActionManager
     */
    public function getOrderPaymentActionManager()
    {
        $oManager = oxNew( 'oePayPalOrderPaymentActionManager' );

        return $oManager;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return oePayPalOrderPaymentActionManager
     */
    public function getOrderPaymentStatusCalculator()
    {
        $oStatusCalculator = oxNew( 'oePayPalOrderPaymentStatusCalculator' );
        $oStatusCalculator->setOrder( $this->getEditObject()->getPayPalOrder() );

        return $oStatusCalculator;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return oePayPalOrderPaymentActionManager
     */
    public function getOrderPaymentStatusList()
    {
        $oList = oxNew( 'oePayPalOrderPaymentStatusList' );

        return $oList;
    }

    /**
     * Returns editable order object
     *
     * @return oePayPalOxOrder
     */
    public function getEditObject()
    {
        $soxId = $this->getEditObjectId();
        if ( $this->_oEditObject === null && isset( $soxId ) && $soxId != '-1' ) {
            $this->_oEditObject = oxNew( 'oxOrder' );
            $this->_oEditObject->load( $soxId );
        }
        return $this->_oEditObject;
    }

    /**
     * Method checks if order was made with current PayPal module, but not eFire PayPal module
     *
     * @return bool
     */
    public function isNewPayPalOrder()
    {
        $blActive = false;

        $oOrder = $this->getEditObject();
        $oOrderPayPal = $oOrder->getPayPalOrder();
        if ( $this->isPayPalOrder() && $oOrderPayPal->isLoaded() ) {
            $blActive = true;
        }

        return $blActive;
    }

    /**
     * Method checks is order was made with any PayPal module
     *
     * @return bool
     */
    public function isPayPalOrder()
    {
        $blActive = false;

        $oOrder = $this->getEditObject();
        if ( $oOrder && $oOrder->getFieldData( 'oxpaymenttype' ) == 'oxidpaypal' ) {
            $blActive = true;
        }

        return $blActive;
    }

    /**
     * Template getter for price formatting
     *
     * @param double $dPrice price
     *
     * @return string
     */
    public function formatPrice( $dPrice )
    {
        return oxRegistry::getLang()->formatCurrency( $dPrice );
    }

}