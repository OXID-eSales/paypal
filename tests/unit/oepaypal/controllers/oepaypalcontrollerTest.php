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

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';


/**
 * Testing oePayPalController class.
 */
class Unit_oePayPal_Controllers_oePayPalControllerTest extends OxidTestCase
{
    /**
     * Test case for oePayPalController::getRequest()
     */
    public function testGetRequest()
    {
        $oController = new oePayPalController();
        $this->assertTrue( $oController->getRequest() instanceof oePayPalRequest );
    }

    /**
     * Test case for oePayPalController::getLogger()
     */
    public function testGetLogger()
    {
        $oController = new oePayPalController();
        $this->assertTrue( $oController->getLogger() instanceof oePayPalLogger );
    }

    /**
     * Test case for oePayPalController::getPayPalConfig()
     */
    public function testGetPayPalConfig()
    {
        $oController = new oePayPalController();
        $this->assertTrue( $oController->getPayPalConfig() instanceof oePayPalConfig );
    }

    /**
     * Test case for oePayPalController::log()
     */
    public function testLog_LoggingEnabled()
    {
        $this->getConfig()->setConfigParam( 'blPayPalLoggerEnabled', true );

        $oPayPalLogger = $this->getMock( 'oePayPalLogger', array( 'log' ) );
        $oPayPalLogger->expects( $this->once() )->method( 'log' );

        $oController = $this->getMock( 'oePayPalController', array( 'getLogger' ) );
        $oController->expects( $this->once() )->method( 'getLogger' )->will( $this->returnValue( $oPayPalLogger ) );

        $oController->log( 'logMessage' );

    }

    /**
     * Test case for oePayPalController::log()
     */
    public function testLog_LoggingDisabled()
    {
        $this->getConfig()->setConfigParam( 'blPayPalLoggerEnabled', false );

        $oPayPalLogger = $this->getMock( 'oePayPalLogger', array( 'log' ) );
        $oPayPalLogger->expects( $this->never() )->method( 'log' );

        $oController = $this->getMock( 'oePayPalController', array( 'getLogger' ) );
        $oController->expects( $this->never() )->method( 'getLogger' )->will( $this->returnValue( $oPayPalLogger ) );

        $oController->log( 'logMessage' );
    }
}