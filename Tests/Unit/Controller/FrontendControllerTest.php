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
namespace OxidEsales\PayPalModule\Tests\Unit\Controller;

/**
 * Testing OxidEsales\PayPalModule\Controller\FrontendController class.
 */
class FrontendControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test case for \OxidEsales\PayPalModule\Controller\FrontendController::getRequest()
     */
    public function testGetRequest()
    {
        $controller = new \OxidEsales\PayPalModule\Controller\FrontendController();
        $this->assertTrue($controller->getRequest() instanceof \OxidEsales\PayPalModule\Core\Request);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\FrontendController::getLogger()
     */
    public function testGetLogger()
    {
        $controller = new \OxidEsales\PayPalModule\Controller\FrontendController();
        $this->assertTrue($controller->getLogger() instanceof \OxidEsales\PayPalModule\Core\Logger);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\FrontendController::getPayPalConfig()
     */
    public function testGetPayPalConfig()
    {
        $controller = new \OxidEsales\PayPalModule\Controller\FrontendController();
        $this->assertTrue($controller->getPayPalConfig() instanceof \OxidEsales\PayPalModule\Core\Config);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\FrontendController::log()
     */
    public function testLog_LoggingEnabled()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', true);

        $payPalLogger = $this->getMock(\OxidEsales\PayPalModule\Core\Logger::class, array('log'));
        $payPalLogger->expects($this->once())->method('log');

        $controller = $this->getMock(\OxidEsales\PayPalModule\Controller\FrontendController::class, array('getLogger'));
        $controller->expects($this->once())->method('getLogger')->will($this->returnValue($payPalLogger));

        $controller->log('logMessage');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\FrontendController::log()
     */
    public function testLog_LoggingDisabled()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', false);

        $payPalLogger = $this->getMock(\OxidEsales\PayPalModule\Core\Logger::class, array('log'));
        $payPalLogger->expects($this->never())->method('log');

        $controller = $this->getMock(\OxidEsales\PayPalModule\Controller\FrontendController::class, array('getLogger'));
        $controller->expects($this->never())->method('getLogger')->will($this->returnValue($payPalLogger));

        $controller->log('logMessage');
    }
}
