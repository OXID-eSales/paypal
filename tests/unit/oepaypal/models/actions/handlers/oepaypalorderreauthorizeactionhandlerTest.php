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


/**
 * Testing oePayPalOrderReauthorizeAction class.
 */
class Unit_oePayPal_Models_Actions_Handlers_oePayPalOrderReauthorizeActionHandlerTest extends OxidTestCase
{

    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sAuthId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;

        $oData = $this->_createStub(
            'Data', array(
                'getAuthorizationId' => $sAuthId,
                'getAmount'          => $dAmount,
                'getCurrency'        => $sCurrency,
            )
        );
        $oActionHandler = $this->_getActionHandler($oData);

        $oBuilder = $this->getMock('oePayPalPayPalRequestBuilder', array('setAuthorizationId', 'setAmount', 'setCompleteType'));
        $oBuilder->expects($this->once())->method('setAuthorizationId')->with($this->equalTo($sAuthId));
        $oBuilder->expects($this->once())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));

        $oActionHandler->setPayPalRequestBuilder($oBuilder);

        $this->assertTrue($oActionHandler->getPayPalRequest() instanceof oePayPalPayPalRequest);
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $oActionHandler = $this->_getActionHandler();

        $oPayPalRequest = $this->getMock('oePayPalPayPalRequest');
        $oActionHandler->setPayPalRequest($oPayPalRequest);

        $oCheckoutService = $this->getMock('oePayPalService', array('doReAuthorization'));
        $oCheckoutService->expects($this->once())->method('doReAuthorization')->with($this->equalTo($oPayPalRequest));
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
     * Returns reauthorize action object
     *
     * @param $oData
     *
     * @return oePayPalOrderReauthorizeAction
     */
    protected function _getActionHandler($oData = null)
    {
        $oAction = new oePayPalOrderReauthorizeActionHandler($oData);

        $oAction->setPayPalService($this->_getService());

        return $oAction;
    }
}