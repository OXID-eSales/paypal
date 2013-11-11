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
 * PayPal Service class
 */
class oePayPalService
{
    /**
     * PayPal Caller
     * @var oePayPalCaller
     */
    protected $_oCaller = null;

    /**
     * PayPal Caller
     * @var oePayPalConfig
     */
    protected $_oPayPalConfig = null;

    /**
     * PayPal config setter
     */
    public function setPayPalConfig( $oPayPalConfig )
    {
        $this->_oPayPalConfig = $oPayPalConfig;
    }

    /**
     * PayPal config getter
     *
     * @return oePayPalConfig
     */
    public function getPayPalConfig()
    {
        if ( is_null( $this->_oPayPalConfig ) ){
            $this->setPayPalConfig( oxNew( 'oePayPalConfig' ) );
        }
        return $this->_oPayPalConfig;
    }

    /**
     * PayPal caller setter
     */
    public function setCaller( $oCaller )
    {
        $this->_oCaller = $oCaller;
    }

    /**
     * PayPal caller getter
     *
     * @return oePayPalCaller
     */
    public function getCaller()
    {
        if ( is_null( $this->_oCaller ) ) {

            /**
             * @var oePayPalCaller $oCaller
             */
            $oCaller = oxNew( 'oePayPalCaller' );

            $oConfig = $this->getPayPalConfig();

            $oCaller->setParameter( 'VERSION', '84.0' );
            $oCaller->setParameter( 'PWD', $oConfig->getPassword() );
            $oCaller->setParameter( 'USER', $oConfig->getUserName() );
            $oCaller->setParameter( 'SIGNATURE', $oConfig->getSignature() );

            $oCurl = oxNew( 'oePayPalCurl' );
            $oCurl->setDataCharset( $oConfig->getCharset() );
            $oCurl->setHost( $oConfig->getHost() );
            $oCurl->setUrlToCall( $oConfig->getApiUrl() );

            $oCaller->setCurl( $oCurl );

            if( $oConfig->isLoggingEnabled() ){
                $oLogger = oxNew( 'oePayPalLogger' );
                $oLogger->setLoggerSessionId( oxRegistry::getSession()->getId() );
                $oCaller->setLogger( $oLogger );
            }

            $this->setCaller( $oCaller );
        }
        return $this->_oCaller;
    }

    /**
     * Executes "SetExpressCheckout". Returns response object from PayPal
     * @var $oRequest
     *
     * @return oePayPalResponseSetExpressCheckout
     */
    public function setExpressCheckout( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew( 'oePayPalResponseSetExpressCheckout' );
        $oResponse->setData( $oCaller->call( 'SetExpressCheckout' ) );

        return $oResponse;
    }

    /**
     * Executes "GetExpressCheckoutDetails". Returns response object from PayPal
     * @var $oRequest
     *
     * @return oePayPalResponseGetExpressCheckoutDetails
     */
    public function getExpressCheckoutDetails( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew( 'oePayPalResponseGetExpressCheckoutDetails' );
        $oResponse->setData( $oCaller->call( 'GetExpressCheckoutDetails' ) );

        return $oResponse;
    }

    /**
     * Executes "DoExpressCheckoutPayment". Returns response object from PayPal
     * @var $oRequest
     *
     * @return oePayPalResponseDoExpressCheckoutPayment
     */
    public function doExpressCheckoutPayment( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew( 'oePayPalResponseDoExpressCheckoutPayment' );
        $oResponse->setData( $oCaller->call( 'DoExpressCheckoutPayment' ) );

        return $oResponse;
    }

    /**
     * Executes PayPal callback request
     *
     * @return null
     */
    public function callbackResponse()
    {
        // cleanup
        $this->getCaller()->setParameter( "VERSION", null );
        $this->getCaller()->setParameter( "PWD", null );
        $this->getCaller()->setParameter( "USER", null );
        $this->getCaller()->setParameter( "SIGNATURE", null );

        return $this->getCaller()->getCallBackResponse( "CallbackResponse" );
    }

    /**
     * Executes "DoVoid". Returns response array from PayPal
     *
     * @param oePayPalPayPalRequest $oRequest
     *
     * @return oePayPalResponse
     */
    public function doVoid( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew( 'oePayPalResponseDoVoid' );
        $oResponse->setData( $oCaller->call( 'DoVoid' ) );

        return $oResponse;
    }

    /**
     * Executes "RefundTransaction". Returns response array from PayPal
     *
     * @param oePayPalPayPalRequest $oRequest
     *
     * @return oePayPalResponse
     */
    public function refundTransaction( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew('oePayPalResponseDoRefund');
        $oResponse->setData( $oCaller->call( 'RefundTransaction' ) );

        return $oResponse;
    }

    /**
     * Executes "DoCapture". Returns response array from PayPal
     *
     * @param oePayPalPayPalRequest $oRequest request
     *
     * @return oePayPalResponse
     */
    public function doCapture( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew('oePayPalResponseDoCapture');
        $oResponse->setData( $oCaller->call( 'DoCapture' ) );
        return $oResponse;
    }

    /**
     * Executes "DoReauthorization". Returns response array from PayPal
     *
     * @param oePayPalPayPalRequest $oRequest
     *
     * @return oePayPalResponse
     */
    public function doReAuthorization( $oRequest )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oResponse = oxNew( 'oePayPalResponseDoReAuthorize' );
        $oResponse->setData( $oCaller->call( 'DoReauthorization' ) );

        return $oResponse;
    }

    /**
     * Executes call to PayPal IPN
     *
     * @param oePayPalPayPalRequest $oRequest
     *
     * @return oePayPalResponse
     */
    public function doVerifyWithPayPal( $oRequest, $sCharset )
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest( $oRequest );

        $oCaller = $this->getCaller();
        $oCurl = $oCaller->getCurl();
        $oCurl->setConnectionCharset( $sCharset );
        $oCurl->setUrlToCall( $this->getPayPalConfig()->getIPNResponseUrl() );

        $oResponse = oxNew( 'oePayPalResponseDoVerifyWithPayPal' );
        $oResponse->setData( $oCaller->call() );

        return $oResponse;
    }

    /**
     * @deprecated still use in callback.
     */
    public function setParameter( $sKey, $sValue  )
    {
        return $this->getCaller()->setParameter( $sKey, $sValue  );
    }

}