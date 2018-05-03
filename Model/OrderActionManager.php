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
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal order action manager class
 */
class OrderActionManager
{
    /**
     * States related with transaction mode
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $availableActions = array(
        'Sale'          => array(),
        'Authorization' => array('capture', 'reauthorize', 'void'),
    );

    /**
     * Order object
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $order = null;

    /**
     * Sets order
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Returns order
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Return state for given transaction mode
     *
     * @param string $mode transaction mode
     *
     * @return array
     */
    protected function getAvailableAction($mode)
    {
        $actions = $this->availableActions[$mode];

        return $actions ? $actions : array();
    }

    /**
     * Checks whether action is available for given order
     *
     * @param string $action
     *
     * @return bool
     */
    public function isActionAvailable($action)
    {
        $order = $this->getOrder();

        $availableActions = $this->getAvailableAction($order->getTransactionMode());

        $isAvailable = in_array($action, $availableActions);

        if ($isAvailable) {
            $isAvailable = false;

            switch ($action) {
                case 'capture':
                case 'reauthorize':
                case 'void':
                    if ($order->getRemainingOrderSum() > 0 && $order->getVoidedAmount() < $order->getRemainingOrderSum()) {
                        $isAvailable = true;
                    }
                    break;
            }
        }

        return $isAvailable;
    }
}
