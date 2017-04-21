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
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Model\Action;

/**
 * PayPal order action capture class
 */
class OrderCaptureAction extends \OxidEsales\PayPalModule\Model\Action\OrderAction
{

    /**
     * @var \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler
     */
    protected $_oReauthorizeHandler = null;

    /**
     * Sets dependencies.
     *
     * @param \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler     $oHandler
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder                                  $oOrder
     * @param \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler $oReauthorizeHandler
     */
    public function __construct($oHandler, $oOrder, $oReauthorizeHandler)
    {
        parent::__construct($oHandler, $oOrder);

        $this->_oReauthorizeHandler = $oReauthorizeHandler;
    }

    /**
     * Returns reauthorize action handler.
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler
     */
    public function getReauthorizeHandler()
    {
        return $this->_oReauthorizeHandler;
    }

    /**
     * Processes PayPal response.
     */
    public function process()
    {
        $this->_reauthorize();

        $oHandler = $this->getHandler();

        $oResponse = $oHandler->getPayPalResponse();
        $oData = $oHandler->getData();

        $this->_updateOrder($oResponse, $oData);

        $oPayment = $this->_createPayment($oResponse);
        $oPaymentList = $this->getOrder()->getPaymentList();
        $oPayment = $oPaymentList->addPayment($oPayment);

        $this->_addComment($oPayment, $oData->getComment());
    }

    /**
     * Reauthorizes payment if order was captured at least once.
     */
    protected function _reauthorize()
    {
        $oOrder = $this->getOrder();

        if ($oOrder->getCapturedAmount() > 0) {
            $oHandler = $this->getReauthorizeHandler();
            try {
                $oResponse = $oHandler->getPayPalResponse();

                $oPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
                $oPayment->setDate($this->getDate());
                $oPayment->setTransactionId($oResponse->getAuthorizationId());
                $oPayment->setCorrelationId($oResponse->getCorrelationId());
                $oPayment->setAction('re-authorization');
                $oPayment->setStatus($oResponse->getPaymentStatus());

                $oOrder->getPaymentList()->addPayment($oPayment);
            } catch (\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException $e) {
                // Ignore PayPal response exceptions
            }
        }
    }

    /**
     * Updates order with PayPal response info.
     *
     * @param object $oResponse
     * @param object $oData
     */
    protected function _updateOrder($oResponse, $oData)
    {
        $oOrder = $this->getOrder();
        $oOrder->addCapturedAmount($oResponse->getCapturedAmount());
        $oOrder->setPaymentStatus($oData->getOrderStatus());
        $oOrder->save();
    }

    /**
     * Creates Payment object with PayPal response data.
     *
     * @param object $oResponse
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    protected function _createPayment($oResponse)
    {
        $oPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $oPayment->setDate($this->getDate());
        $oPayment->setTransactionId($oResponse->getTransactionId());
        $oPayment->setCorrelationId($oResponse->getCorrelationId());
        $oPayment->setAction('capture');
        $oPayment->setStatus($oResponse->getPaymentStatus());
        $oPayment->setAmount($oResponse->getCapturedAmount());
        $oPayment->setCurrency($oResponse->getCurrency());

        return $oPayment;
    }

    /**
     * Adds comment to given Payment object.
     *
     * @param object $oPayment
     * @param string $sComment
     */
    protected function _addComment($oPayment, $sComment)
    {
        if ($sComment) {
            $oComment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
            $oComment->setComment($sComment);
            $oPayment->addComment($oComment);
        }
    }
}
