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
namespace OxidEsales\PayPalModule\Controller\Admin;

/**
 * Order class wrapper for PayPal module
 */
class OrderController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
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

        $this->_aViewData["sOxid"] = $this->getEditObjectId();
        if ($this->isNewPayPalOrder()) {
            $this->_aViewData['oOrder'] = $this->getEditObject();
        } else {
            $this->_aViewData['sMessage'] = $this->isPayPalOrder() ? \OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_ONLY_FOR_NEW_PAYPAL_PAYMENT") :
                \OxidEsales\Eshop\Core\Registry::getLang()->translateString("OEPAYPAL_ONLY_FOR_PAYPAL_PAYMENT");
        }

        return "order_paypal.tpl";
    }

    /**
     * Processes PayPal actions.
     */
    public function processAction()
    {
        try {
            /** @var \OxidEsales\PayPalModule\Core\Request $oRequest */
            $oRequest = oxNew(\OxidEsales\PayPalModule\Core\Request::class);
            $sAction = $oRequest->getRequestParameter('action');

            $oOrder = $this->getEditObject();

            /** @var \OxidEsales\PayPalModule\Model\Action\OrderActionFactory $oActionFactory */
            $oActionFactory = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderActionFactory::class, $oRequest, $oOrder);
            $oAction = $oActionFactory->createAction($sAction);

            $oAction->process();
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $oException) {
            $this->_aViewData["error"] = $oException->getMessage();
        }
    }

    /**
     * Returns PayPal order action manager.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderActionManager
     */
    public function getOrderActionManager()
    {
        /** @var \OxidEsales\PayPalModule\Model\OrderActionManager $oManager */
        $oManager = oxNew(\OxidEsales\PayPalModule\Model\OrderActionManager::class);
        $oManager->setOrder($this->getEditObject()->getPayPalOrder());

        return $oManager;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentActionManager
     */
    public function getOrderPaymentActionManager()
    {
        $oManager = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentActionManager::class);

        return $oManager;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    public function getOrderPaymentStatusCalculator()
    {
        /** @var \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator $oStatusCalculator */
        $oStatusCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class);
        $oStatusCalculator->setOrder($this->getEditObject()->getPayPalOrder());

        return $oStatusCalculator;
    }

    /**
     * Returns PayPal order action manager
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusList
     */
    public function getOrderPaymentStatusList()
    {
        $oList = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentStatusList::class);

        return $oList;
    }

    /**
     * Returns editable order object
     *
     * @return \OxidEsales\PayPalModule\Model\Order
     */
    public function getEditObject()
    {
        $soxId = $this->getEditObjectId();
        if ($this->_oEditObject === null && isset($soxId) && $soxId != '-1') {
            $this->_oEditObject = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $this->_oEditObject->load($soxId);
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
        if ($this->isPayPalOrder() && $oOrderPayPal->isLoaded()) {
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
        if ($oOrder && $oOrder->getFieldData('oxpaymenttype') == 'oxidpaypal') {
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
    public function formatPrice($dPrice)
    {
        return \OxidEsales\Eshop\Core\Registry::getLang()->formatCurrency($dPrice);
    }
}
