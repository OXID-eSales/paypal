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

require_once realpath(".") . '/unit/OxidTestCase.php';
require_once realpath(".") . '/unit/test_config.inc.php';

/**
 * Testing oePayPalOrderVoidAction class.
 */
class Unit_oePayPal_Models_Actions_Handlers_oePayPalOrderVoidActionHandlerTest extends OxidTestCase
{

    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sAuthId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;
        $sComment = "Comment";

        $oData = $this->_createStub('Data', array(
            'getAuthorizationId' => $sAuthId,
            'getAmount' => $dAmount,
            'getComment' => $sComment,
            'getCurrency' => $sCurrency,
        ));
        $oActionHandler = $this->_getActionHandler($oData);

        $oBuilder = $this->getMock('oePayPalPayPalRequestBuilder', array('setAuthorizationId', 'setAmount', 'setCompleteType', 'getRequest', 'setComment'));
        $oBuilder->expects($this->once())->method('setAuthorizationId')->with($this->equalTo($sAuthId));
        $oBuilder->expects($this->once())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));
        $oBuilder->expects($this->once())->method('setComment')->with($this->equalTo($sComment));
        $oBuilder->expects($this->once())->method('getRequest')->will($this->returnValue(new oePayPalPayPalRequest()));

        $oActionHandler->setPayPalRequestBuilder($oBuilder);

        $oActionHandler->getPayPalRequest();
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $oPayPalRequest = $this->getMock('oePayPalPayPalRequest');

        $oActionHandler = $this->_getActionHandler();
        $oActionHandler->setPayPalRequest($oPayPalRequest);

        $oCheckoutService = $this->getMock('oePayPalService', array('doVoid'));
        $oCheckoutService->expects($this->once())
            ->method('doVoid')
            ->with($this->equalTo($oPayPalRequest))
            ->will($this->returnValue(null));

        $oActionHandler->setPayPalService($oCheckoutService);

        $oActionHandler->getPayPalResponse();
    }

    /**
     * @return oePayPalService
     */
    protected function _getService()
    {
        return $this->getMock('oePayPalService');
    }

    /**
     * Returns capture action object
     *
     * @param $oData
     * @return oePayPalOrderCaptureAction
     */
    protected function _getActionHandler($oData = null)
    {
        $oAction = new oePayPalOrderVoidActionHandler($oData);
        $oAction->setPayPalService($this->_getService());

        return $oAction;
    }
}