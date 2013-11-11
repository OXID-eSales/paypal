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
 * Payment gateway manager.
 * Checks and sets payment method data, executes payment.
 */
class oePayPalOxPaymentGateway extends oePayPalOxPaymentGateway_parent
{
    /**
     * PayPal config.
     *
     * @var null
     */
    protected $_oPayPalConfig = null;

    /**
     * PayPal config.
     *
     * @var null
     */
    protected $_oCheckoutService = null;

    /**
     * Order.
     *
     * @var oxOrder
     */
    protected $_oPayPalOxOrder;

    /**
     * Sets order.
     *
     * @param oxOrder $oOrder
     */
    public function setPayPalOxOrder( $oOrder )
    {
        $this->_oPayPalOxOrder = $oOrder;
    }

    /**
     * Gets order.
     *
     * @return oxOrder
     */
    public function getPayPalOxOrder()
    {
        if ( is_null( $this->_oPayPalOxOrder ) ) {
            $oOrder = oxNew( 'oxOrder' );
            $oOrder->loadPayPalOrder();
            $this->setPayPalOxOrder( $oOrder );
        }

        return $this->_oPayPalOxOrder;
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object &$oOrder User ordering object
     *
     * @return bool
     */
    public function executePayment( $dAmount, & $oOrder )
    {
        $blSuccess = parent::executePayment( $dAmount, $oOrder );

        if ( $this->getSession()->getVariable( 'paymentid' ) == 'oxidpaypal' ) {
            $this->setPayPalOxOrder( $oOrder );
            $blSuccess = $this->doExpressCheckoutPayment();
        }

        return $blSuccess;
    }

    /**
     * Executes "DoExpressCheckoutPayment" to PayPal
     *
     * @return bool
     */
    public function doExpressCheckoutPayment()
    {
        $blSuccess = false;
        $oOrder = $this->_getPayPalOrder();

        try {
            // updating order state
            if ( $oOrder ) {

                $oOrder->oePayPalUpdateOrderNumber();
                $oSession = $this->getSession();
                $oBasket  = $oSession->getBasket();

                $sTransactionMode = $this->_getTransactionMode( $oBasket );

                $oBuilder = oxNew( 'oePayPalDoExpressCheckoutPaymentRequestBuilder' );
                $oBuilder->setPayPalConfig( $this->getPayPalConfig() );
                $oBuilder->setSession( $oSession );
                $oBuilder->setBasket( $oBasket );
                $oBuilder->setTransactionMode( $sTransactionMode );
                $oBuilder->setUser( $this->_getPayPalUser() );
                $oBuilder->setOrder( $oOrder );

                $oRequest = $oBuilder->buildRequest();

                $oPayPalService = $this->getPayPalCheckoutService();
                $oResult = $oPayPalService->doExpressCheckoutPayment( $oRequest );

                $oOrder->finalizePayPalOrder(
                    $oResult,
                    $oSession->getBasket(),
                    $sTransactionMode
                );

                $blSuccess = true;
            } else {
                /**
                 * @var $oEx oxException
                 */
                $oEx = oxNew( 'oxException' );
                $oEx->setMessage( 'OEPAYPAL_ORDER_ERROR' );
                throw $oEx;
            }

        } catch ( oxException $oException ) {

            // deleting order on error
            if ( $oOrder ) {
                $oOrder->deletePayPalOrder();
            }

            $this->_iLastErrorNo = oxOrder::ORDER_STATE_PAYMENTERROR;
            oxRegistry::get('oxUtilsView')->addErrorToDisplay( $oException );
        }

        return $blSuccess;
    }

    /**
     * @param $oBasket
     * @return string
     */
    protected function _getTransactionMode( $oBasket )
    {
        $sTransactionMode = $this->getPayPalConfig()->getTransactionMode();

        if ( $sTransactionMode == "Automatic" ) {

            $oOutOfStockValidator = new oePayPalOutOfStockValidator();
            $oOutOfStockValidator->setBasket( $oBasket );
            $oOutOfStockValidator->setEmptyStockLevel( $this->getPayPalConfig()->getEmptyStockLevel() );

            $sTransactionMode = ( $oOutOfStockValidator->hasOutOfStockArticles() ) ? "Authorization" : "Sale";
            return $sTransactionMode;
        }
        return $sTransactionMode;
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
     * Sets PayPal service
     *
     * @param oePayPalService $oCheckoutService
     */
    public function setPayPalCheckoutService( $oCheckoutService )
    {
        $this->_oCheckoutService = $oCheckoutService;
    }

    /**
     * Returns PayPal service
     *
     * @return oePayPalService
     */
    public function getPayPalCheckoutService()
    {
        if ( is_null( $this->_oCheckoutService ) ) {
            $this->setPayPalCheckoutService( oxNew( "oePayPalService" ) );
        }
        return $this->_oCheckoutService;
    }

    /**
     * Returns PayPal order object
     *
     * @return oxOrder
     */
    protected function _getPayPalOrder()
    {
        return $this->getPayPalOxOrder();
    }

    /**
     * Returns PayPal user
     *
     * @return oxUser
     */
    protected function _getPayPalUser()
    {
        $oUser = oxNew( 'oxUser' );
        if (! $oUser->loadUserPayPalUser() ){
            $oUser = $this->getUser();
        }
        return $oUser;
    }
}
