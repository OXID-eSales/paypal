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
 * Testing oePayPalModel class.
 */
class Unit_oePayPal_core_oePayPalModelTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * Loading of data by id, returned by getId method
     */
    public function testLoad_LoadByGetId_DataLoaded()
    {
        $sId = 'RecordIdToLoad';
        $aData = array('testkey' => 'testValue');
        $oGateway = $this->getMock('oePayPalOrderPaymentDbGateway', array('load'));
        $oGateway->expects($this->any())->method('load')->with($sId)->will($this->returnValue($aData));

        $oModel = $this->_getPayPalModel($oGateway, $sId);

        $this->assertTrue($oModel->load());
        $this->assertEquals($aData, $oModel->getData());
    }

    /**
     * Loading of data by passed id
     */
    public function testLoad_LoadByPassedId_DataLoaded()
    {
        $sId = 'RecordIdToLoad';
        $aData = array('testkey' => 'testValue');
        $oGateway = $this->getMock('oePayPalOrderPaymentDbGateway', array('load'));
        $oGateway->expects($this->any())->method('load')->with($sId)->will($this->returnValue($aData));

        $oModel = $this->_getPayPalModel($oGateway, $sId, $sId);

        $this->assertTrue($oModel->load($sId));
        $this->assertEquals($aData, $oModel->getData());
    }

    /**
     * Is loaded method returns false when record does not exists in database
     */
    public function testIsLoaded_DatabaseRecordNotFound()
    {
        $oGateway = $this->_createStub('oePayPalOrderPaymentDbGateway', array('load' => null));

        $oModel = $this->_getPayPalModel($oGateway);
        $oModel->load();

        $this->assertFalse($oModel->isLoaded());
    }

    /**
     * Is loaded method returns false when record does not exists in database
     */
    public function testIsLoaded_DatabaseRecordFound()
    {
        $oGateway = $this->_createStub('oePayPalOrderPaymentDbGateway', array('load' => array('oePayPalId' => 'testId')));

        $oModel = $this->_getPayPalModel($oGateway);
        $oModel->load();

        $this->assertTrue($oModel->isLoaded());
    }

    /**
     * Creates oePayPalModel with mocked abstract methods
     *
     * @param object $oGateway
     * @param string $sGetId
     * @param string $sSetId
     *
     * @return oePayPalModel
     */
    protected function _getPayPalModel($oGateway, $sGetId = null, $sSetId = null)
    {
        $oModel = $this->_createStub('oePayPalModel', array('_getDbGateway' => $oGateway, 'getId' => $sGetId), array('setId'));
        if ($sSetId) {
            $oModel->expects($this->any())->method('setId')->with($sSetId);
        }

        return $oModel;
    }
}

