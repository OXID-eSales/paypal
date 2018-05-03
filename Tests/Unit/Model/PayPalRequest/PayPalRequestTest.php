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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\PayPalRequest;

/**
 * Testing \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest class.
 */
class PayPalRequestTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetData()
    {
        $data = array(
            'AUTHORIZATIONID' => 'AuthorizationId'
        );

        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setData($data);
        $this->assertEquals($data, $request->getData());
    }

    public function testGetData_NoDataSet()
    {
        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $this->assertEquals(array(), $request->getData());
    }

    public function testSetGetParameter()
    {
        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setParameter('AUTHORIZATIONID', 'AuthorizationId');

        $this->assertEquals('AuthorizationId', $request->getParameter('AUTHORIZATIONID'));
    }

    public function testSetGetParameter_OverwritingOfSetData()
    {
        $data = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
            'TRANSACTIONID'   => 'TransactionId',
        );
        $newId = 'NewAuthorizationId';

        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setData($data);
        $request->setParameter('AUTHORIZATIONID', $newId);

        $data['AUTHORIZATIONID'] = $newId;
        $this->assertEquals($data, $request->getData());
    }
}