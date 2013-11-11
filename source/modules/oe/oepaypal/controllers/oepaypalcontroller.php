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
 * Main PayPal controller
 */
class oePayPalController extends oxUBase
{
    /**
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalLogger
     */
    protected $_oLogger = null;

    /**
     * @var oePayPalConfig
     */
    protected $_oPayPalConfig = null;

    /**
     * Return request object
     *
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        if( is_null($this->_oRequest) ){
            $this->_oRequest = oxNew( 'oePayPalRequest' );
        }
        return $this->_oRequest;
    }

    /**
     * Return PayPal logger
     *
     * @return oePayPalLogger
     */
    public function getLogger()
    {
        if( is_null($this->_oLogger) ){
            $this->_oLogger = oxNew( 'oePayPalLogger' );
            $this->_oLogger->setLoggerSessionId( $this->getSession()->getId() );
        }
        return $this->_oLogger;
    }

    /**
     * Return PayPal config
     *
     * @return oePayPalConfig
     */
    public function getPayPalConfig()
    {
        if( is_null( $this->_oPayPalConfig ) ){
            $this->setPayPalConfig( oxNew( 'oePayPalConfig' ) );
        }
        return $this->_oPayPalConfig;
    }

    /**
     * Set PayPal config
     *
     * @param oePayPalConfig $oPayPalConfig config
     */
    public function setPayPalConfig( $oPayPalConfig )
    {
        $this->_oPayPalConfig = $oPayPalConfig;
    }


    /**
     * Logs passed value
     */
    public function log( $mValue )
    {
        if ( $this->getPayPalConfig()->isLoggingEnabled() ) {
            $this->getLogger()->log( $mValue );
        }
    }
}