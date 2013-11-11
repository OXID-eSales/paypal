<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 */

/**
 * PayPal order action refund class
 */
class oePayPalOrderRefundActionHandler extends oePayPalOrderActionHandler
{

    /**
     * PayPal Request
     *
     * @var oePayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * Returns PayPal response; calls PayPal if not set
     *
     * @return mixed
     */
    public function getPayPalResponse()
    {
        $oService = $this->getPayPalService();
        $oRequest = $this->getPayPalRequest();

        return $oService->refundTransaction( $oRequest );
    }

    /**
     * Returns PayPal request; initiates if not set
     *
     * @return oePayPalPayPalRequest
     */
    public function getPayPalRequest()
    {
        if ( is_null( $this->_oPayPalRequest ) ) {
            $oRequestBuilder = $this->getPayPalRequestBuilder();

            $oData = $this->getData();

            $oRequestBuilder->setTransactionId( $oData->getTransactionId() );
            $oRequestBuilder->setAmount( $oData->getAmount(), $oData->getCurrency() );
            $oRequestBuilder->setRefundType( $oData->getType() );
            $oRequestBuilder->setComment( $oData->getComment() );

            $this->_oPayPalRequest = $oRequestBuilder->getRequest();
        }

        return $this->_oPayPalRequest;
    }

    /**
     * Sets PayPal request
     *
     * @param $oPayPalRequest
     */
    public function setPayPalRequest( $oPayPalRequest )
    {
        $this->_oPayPalRequest = $oPayPalRequest;
    }

}