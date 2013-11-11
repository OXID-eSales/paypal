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
 * Abstract PayPal Dispatcher class
 */
abstract class oePayPalDispatcher extends oePayPalController
{
    /**
     * Service type identifier - Standard Checkout = 1
     * @var int
     */
    protected $_iServiceType = 1;

    /**
     * PayPal checkout service
     * @var oePayPalCheckoutService
     */
    protected $_oPayPalCheckoutService;

    /**
     * Default user action for checkout process
     * @var string
     */
    protected $_sUserAction = "continue";

    /**
     * Executes "GetExpressCheckoutDetails" and on SUCCESS response - saves
     * user information and redirects to order page, on failure - sets error
     * message and redirects to basket page
     *
     * @return string
     */
    abstract public function getExpressCheckoutDetails();

    /**
     * @param oePayPalService $oPayPalCheckoutService
     */
    public function setPayPalCheckoutService( $oPayPalCheckoutService )
    {
        $this->_oPayPalCheckoutService = $oPayPalCheckoutService;
    }

    /**
     * Returns PayPal service
     *
     * @return oePayPalService
     */
    public function getPayPalCheckoutService()
    {
        if ( $this->_oPayPalCheckoutService === null ) {
            $this->_oPayPalCheckoutService = oxNew( "oePayPalService" );
        }
        return $this->_oPayPalCheckoutService;
    }

    /**
     * Returns oxUtilsView instance
     *
     * @return oxUtilsView
     */
    protected function _getUtilsView()
    {
        return oxRegistry::get("oxUtilsView");
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $fIn value to format
     *
     * @return string
     */
    protected function _formatFloat( $fIn )
    {
        return sprintf( "%.2f", $fIn );
    }

    /**
     * Returns oxUtils instance
     *
     * @return oxUtils
     */
    protected function _getUtils()
    {
        return oxRegistry::getUtils();
    }

    /**
     * Returns base url, which is used to construct Callback, Return and Cancel Urls
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        $oSession = $this->getSession();
        $sUrl = $this->getConfig()->getSslShopUrl() . "index.php?lang=" . oxRegistry::getLang()->getBaseLanguage() . "&sid=" . $oSession->getId() . "&rtoken=". $oSession->getRemoteAccessToken();
        $sUrl .= "&shp=" . $this->getConfig()->getShopId();

        return $sUrl;
    }

    /**
     * Returns PayPal order object
     *
     * @return oxOrder
     */
    protected function _getPayPalOrder()
    {
        $oOrder = oxNew( "oxOrder" );
        if ( $oOrder->loadPayPalOrder() ) {
            return $oOrder;
        }
    }

    /**
     * Returns PayPal payment object
     *
     * @return oxPayment
     */
    protected function _getPayPalPayment()
    {
        if ( ( $oOrder = $this->_getPayPalOrder() ) ) {
            $oUserPayment = oxNew( 'oxUserPayment' );
            $oUserPayment->load( $oOrder->oxorder__oxpaymentid->value );
            return $oUserPayment;
        }
    }
}