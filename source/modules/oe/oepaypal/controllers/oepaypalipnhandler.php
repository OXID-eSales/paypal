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
 * PayPal IPN handler class.
 *
 * Handle PayPal notifications.
 *  - Extract data.
 *  - Check if valid.
 *  - Call order methods to save data.
 */
class oePayPalIPNHandler extends oePayPalController
{
    /**
     * Current class default template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'ipnhandler.tpl';

    /**
     * PayPal request handler.
     * @var oePayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * @var oePayPalIPNRequestVerifier
     */
    protected $_oIPNRequestVerifier = null;

    /**
     * @var oePayPalIPNProcessor
     */
    protected $_oProcessor = null;

    /**
     * Set object to handle request.
     *
     * @param oePayPalRequest $oPayPalRequest object to set.
     */
    public function setPayPalRequest( $oPayPalRequest )
    {
        $this->_oPayPalRequest = $oPayPalRequest;
    }

    /**
     * Create object oePayPalRequest to get PayPal request information.
     *
     * @return oePayPalRequest
     */
    public function getPayPalRequest()
    {
        if ( $this->_oPayPalRequest === null ) {
            $this->_oPayPalRequest = oxNew( 'oePayPalRequest' );
        }
        return $this->_oPayPalRequest;
    }

    /**
     * @param oePayPalIPNRequestVerifier $oIPNRequestVerifier
     */
    public function setIPNRequestVerifier( $oIPNRequestVerifier )
    {
        $this->_oIPNRequestVerifier = $oIPNRequestVerifier;
    }

    /**
     * @return oePayPalIPNRequestVerifier
     */
    public function getIPNRequestVerifier()
    {
        if ( is_null( $this->_oIPNRequestVerifier ) ) {
            $oIPNRequestVerifier = oxNew( 'oePayPalIPNRequestVerifier' );
            $this->setIPNRequestVerifier( $oIPNRequestVerifier );
        }
        return $this->_oIPNRequestVerifier;
    }

    /**
     * @param oePayPalIPNProcessor $oProcessor
     */
    public function setProcessor( $oProcessor )
    {
        $this->_oProcessor = $oProcessor;
    }

    /**
     * @return oePayPalIPNProcessor
     */
    public function getProcessor()
    {
        if ( is_null( $this->_oProcessor ) ) {
            $oProcessor = oxNew( 'oePayPalIPNProcessor' );
            $this->setProcessor( $oProcessor );
        }
        return $this->_oProcessor;
    }

    /**
     * IPN handling function.
     *  - Call to check if request is valid (from PayPal and to correct shop).
     *  - Initiate payment status changes according to IPN information.
     *
     * @return null
     */
    public function handleRequest()
    {
        $blRequestHandled = false;
        $blRequestValid = $this->requestValid();

        if ( $blRequestValid ) {
            $oRequest = $this->getPayPalRequest();
            $oLang = $this->getPayPalConfig()->getLang();

            $oProcessor = $this->getProcessor();
            $oProcessor->setRequest( $oRequest );
            $oProcessor->setLang( $oLang );

            $blRequestHandled = $oProcessor->process();
        }
        return $blRequestHandled;
    }

    /**
     * IPN handling function.
     *  - Verify request with PayPal (if request from PayPal and to correct shop).
     *
     * @return bool
     */
    public function requestValid()
    {
        $blRequestValid = true;

        $oRequest = $this->getRequest();

        $oIPNRequestVerifier = $this->getIPNRequestVerifier();
        $oIPNRequestVerifier->setRequest( $oRequest );
        $oIPNRequestVerifier->setShopOwner( $this->getPayPalConfig()->getUserEmail() );
        $blRequestCorrect = $oIPNRequestVerifier->requestCorrect();

        if ( !$blRequestCorrect ) {
            $blRequestValid = false;

            $oLogger = $this->getLogger();
            $oLogger->setTitle( "IPN VERIFICATION FAILURE BY PAYPAL" );
            $oLogger->log( $oIPNRequestVerifier->getFailureMessage() );
        }

        return $blRequestValid;
    }
}