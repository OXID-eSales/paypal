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
 * @copyright (C) OXID eSales AG 2003-2014
 */

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oePayPalOrderActionFactory class.
 */
class Unit_oePayPal_Models_Actions_Data_oePayPalOrderCaptureActionDataTest extends OxidTestCase
{

    /**
     * Tests setting parameters from request
     */
    public function testSettingParameters_FromRequest()
    {
        $sAmount = '59.92';
        $sType = 'Full';

        $aParams = array(
            'capture_amount' => $sAmount,
            'capture_type'   => $sType,
        );
        $oRequest = $this->_createStub('oePayPalRequest', array('getPost' => $aParams));

        $oOrder = $this->_getOrder();

        $oActionData = new oePayPalOrderCaptureActionData($oRequest, $oOrder);

        $this->assertEquals($sAmount, $oActionData->getAmount());
        $this->assertEquals($sType, $oActionData->getType());
    }

    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrder()
    {
        $sRemainingOrderSum = 59.67;

        $oPayPalOrder = $this->_createStub('oePayPalPayPalOrder', array('getRemainingOrderSum' => $sRemainingOrderSum));
        $oOrder = $this->_createStub('oePayPalOxOrder', array('getPayPalOrder' => $oPayPalOrder));
        $oRequest = $this->_createStub('oePayPalRequest', array('getPost' => array()));

        $oActionData = new oePayPalOrderCaptureActionData($oRequest, $oOrder);

        $this->assertEquals($sRemainingOrderSum, $oActionData->getAmount());
    }

    /**
     *  Returns Request object with given parameters
     *
     * @param $aParams
     *
     * @return mixed
     */
    protected function _getRequest($aParams)
    {
        $oRequest = $this->_createStub('oePayPalRequest', array('getGet' => $aParams));

        return $oRequest;
    }

    /**
     * Returns PayPal order object
     *
     * @return oePayPalPayPalOrder
     */
    protected function _getOrder()
    {
        $oOrder = new oePayPalPayPalOrder();

        return $oOrder;
    }
}