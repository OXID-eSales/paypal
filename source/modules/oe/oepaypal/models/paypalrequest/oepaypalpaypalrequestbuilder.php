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
 * PayPal request builder class
 */
class oePayPalPayPalRequestBuilder
{
    /**
     * Request object
     *
     * @var oePayPalPayPalRequest
     */
    protected $_oRequest = null;

    /**
     * Sets Authorization id
     *
     * @param string $sAuthorizationId
     */
    public function setAuthorizationId( $sAuthorizationId )
    {
        $this->getRequest()->setParameter( 'AUTHORIZATIONID', $sAuthorizationId );
    }

    /**
     * Sets Transaction id
     *
     * @param string $sTransactionId
     */
    public function setTransactionId( $sTransactionId )
    {
        $this->getRequest()->setParameter( 'TRANSACTIONID', $sTransactionId );
    }

    /**
     * Set amount
     *
     * @param double $dAmount
     * @param string $sCurrencyCode
     */
    public function setAmount( $dAmount, $sCurrencyCode = null )
    {
        $this->getRequest()->setParameter( 'AMT', $dAmount );
        if ( !$sCurrencyCode ) {
            $sCurrencyCode = oxRegistry::getConfig()->getActShopCurrencyObject()->name;
        }
        $this->getRequest()->setParameter( 'CURRENCYCODE', $sCurrencyCode );
    }

    /**
     * Set Capture type
     *
     * @param string $sType
     */
    public function setCompleteType( $sType )
    {
        $this->getRequest()->setParameter( 'COMPLETETYPE', $sType );
    }

    /**
     * Set Refund type
     *
     * @param string $sType
     */
    public function setRefundType( $sType )
    {
        $this->getRequest()->setParameter( 'REFUNDTYPE', $sType );
    }

    /**
     * Set Refund type
     *
     * @param string $sComment
     */
    public function setComment( $sComment )
    {
        $this->getRequest()->setParameter( 'NOTE', $sComment );
    }


    /**
     * Return request object
     *
     * Returns request object
     */
    public function getRequest()
    {
        if ( $this->_oRequest === null ) {
            $this->_oRequest = oxNew( 'oePayPalPayPalRequest' );
        }
        return $this->_oRequest;
    }

    /**
     * Sets Request object
     *
     * @param oePayPalPayPalRequest $oRequest
     */
    public function setRequest( $oRequest )
    {
        $this->_oRequest = $oRequest;
    }

}