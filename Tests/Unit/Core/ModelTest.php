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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing Model class.
 */
class ModelTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Loading of data by id, returned by getId method
     */
    public function testLoad_LoadByGetId_DataLoaded()
    {
        $id = 'RecordIdToLoad';
        $data = array('testkey' => 'testValue');

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class);
        $mockBuilder->setMethods(['load']);
        $gateway = $mockBuilder->getMock();
        $gateway->expects($this->any())->method('load')->with($id)->will($this->returnValue($data));

        $model = $this->getPayPalModel($gateway, $id);

        $this->assertTrue($model->load());
        $this->assertEquals($data, $model->getData());
    }

    /**
     * Loading of data by passed id
     */
    public function testLoad_LoadByPassedId_DataLoaded()
    {
        $id = 'RecordIdToLoad';
        $data = array('testkey' => 'testValue');

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class);
        $mockBuilder->setMethods(['load']);
        $gateway = $mockBuilder->getMock();
        $gateway->expects($this->any())->method('load')->with($id)->will($this->returnValue($data));

        $model = $this->getPayPalModel($gateway, $id, $id);

        $this->assertTrue($model->load($id));
        $this->assertEquals($data, $model->getData());
    }

    /**
     * Is loaded method returns false when record does not exists in database
     */
    public function testIsLoaded_DatabaseRecordNotFound()
    {
        $gateway = $this->_createStub(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class, array('load' => null));

        $model = $this->getPayPalModel($gateway);
        $model->load();

        $this->assertFalse($model->isLoaded());
    }

    /**
     * Is loaded method returns false when record does not exists in database
     */
    public function testIsLoaded_DatabaseRecordFound()
    {
        $gateway = $this->_createStub(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentDbGateway::class, array('load' => array('oePayPalId' => 'testId')));

        $model = $this->getPayPalModel($gateway);
        $model->load();

        $this->assertTrue($model->isLoaded());
    }

    /**
     * Creates model with mocked abstract methods
     *
     * @param object $gateway
     * @param string $getId
     * @param string $setId
     *
     * @return \OxidEsales\PayPalModule\Core\Model
     */
    protected function getPayPalModel($gateway, $getId = null, $setId = null)
    {
        $model = $this->_createStub(\OxidEsales\PayPalModule\Core\Model::class, array('getDbGateway' => $gateway, 'getId' => $getId), array('setId'));
        if ($setId) {
            $model->expects($this->any())->method('setId')->with($setId);
        }

        return $model;
    }
}

