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
 * PayPal request builder class for get express checkout details
 */
class oePayPalGetExpressCheckoutDetailsRequestBuilder
{
    /**
     * PayPal Request
     *
     * @var oePayPalPayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * Session object
     *
     * @var oxSession
     */
    protected $_oSession = null;

    /**
     * Sets PayPal request object
     *
     * @param oePayPalPayPalRequest $oRequest
     */
    public function setPayPalRequest( $oRequest )
    {
        $this->_oPayPalRequest = $oRequest;
    }

    /**
     * Returns PayPal request object
     */
    public function getPayPalRequest()
    {
        if ( $this->_oPayPalRequest === null ) {
            $this->_oPayPalRequest = oxNew( 'oePayPalPayPalRequest' );
        }
        return $this->_oPayPalRequest;
    }
    /**
     * Sets Session
     *
     * @param oxSession $oSession
     */
    public function setSession( $oSession )
    {
        $this->_oSession = $oSession;
    }

    /**
     * Returns Session
     *
     * @return oxSession
     *
     * @throws oePayPalMissingParameterException
     */
    public function getSession()
    {
        if ( !$this->_oSession ) {
            /**
             * @var oePayPalMissingParameterException $oException
             */
            $oException = oxNew( 'oePayPalMissingParameterException' );
            throw $oException;
        }
        return $this->_oSession;
    }

    /**
     * Builds Request
     *
     * @return oePayPalPayPalRequest
     */
    public function buildRequest()
    {
        $oRequest = $this->getPayPalRequest();
        $oRequest->setParameter( 'TOKEN', $this->getSession()->getVariable( 'oepaypal-token' ) );

        return $oRequest;
    }
}