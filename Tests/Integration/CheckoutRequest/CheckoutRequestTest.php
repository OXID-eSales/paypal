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

namespace OxidEsales\PayPalModule\Tests\Integration\CheckoutRequest;

use OxidEsales\Eshop\Application\Model\Order;

class CheckoutRequestTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test cases directory
     *
     * @var string
     */
    protected $_sTestCasesPath = '/testcases/';

    /* Specified test cases (optional) */
    protected $_aTestCases = array(
        //'standard/caseSetExpressCheckout_AdditionalItems.php',
        //'standard/caseSetExpressCheckout_Config2.php',
    );

    /**
     * Initialize the fixture.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->reset();
        $this->cleanTmpDir();
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);
    }

    /**
     * Basket startup data and expected calculations results
     */
    public function providerDoExpressCheckoutPayment()
    {
        $parser = new \OxidEsales\PayPalModule\Tests\Integration\Library\TestCaseParser();
        $parser->setDirectory(__DIR__ . $this->_sTestCasesPath);
        if (isset($this->_aTestCases)) {
            $parser->setTestCases($this->_aTestCases);
        }
        $parser->setReplacements($this->getReplacements());

        return $parser->getData();
    }

    /**
     * @dataProvider providerDoExpressCheckoutPayment
     */
    public function testExpressCheckoutPaymentRequest($testCase)
    {
        if ($testCase['skipped']) {
            return;
        }

        if (isset($testCase['session'])) {
            foreach ($testCase['session'] as $key=> $value) {
                \OxidEsales\Eshop\Core\Registry::getSession()->setVariable($key, $value);
            }
        }

        if (isset($testCase['config'])) {
            foreach ($testCase['config'] as $key=> $value) {
                $this->getConfig()->setConfigParam($key, $value);
            }
        }

        $communicationHelper = new \OxidEsales\PayPalModule\Tests\Integration\Library\CommunicationHelper();
        $curl = $communicationHelper->getCurl(array());

        $dispatcher = $this->getDispatcher($testCase);
        $this->setCurlToDispatcher($dispatcher, $curl);

        $dispatcher->{$testCase['action']}();

        $curlParameters = $curl->getParameters();

        $expected = $testCase['expected'];

        $asserts = new \OxidEsales\PayPalModule\Tests\Integration\Library\ArrayAsserts();

        $asserts->assertArraysEqual($expected['requestToPayPal'], $curlParameters);

        if (isset($expected['header'])) {
            $curlHeader = $curl->getHeader();
            $asserts->assertArraysEqual($expected['header'], $curlHeader);
        }
    }


    /**
     * Return dispatcher object
     *
     * @param array $testCase
     *
     * @return \OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher::class
     */
    protected function getDispatcher($testCase)
    {
        $basketConstruct = new \OxidEsales\PayPalModule\Tests\Integration\Library\ShopConstruct();
        $basketConstruct->setParams($testCase);

        $basket = $basketConstruct->getBasket();
        $session = $this->getSessionMock();
        $session->setBasket($basket);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $this->setMockedUtils();

        $mockBuilder = $this->getMockBuilder($testCase['class']);
        $mockBuilder->setMethods(['getPayPalOrder']);
        $dispatcher = $mockBuilder->getMock();

        $dispatcher->expects($this->any())->method('getPayPalOrder')->will($this->returnValue($this->getOrder()));
        $dispatcher->setUser($basketConstruct->getUser());

        return $dispatcher;
    }

    /**
     *
     */
    protected function getSessionMock()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Session::class);
        $mockBuilder->setMethods(['getRemoteAccessToken']);
        $session = $mockBuilder->getMock();

        $session->expects($this->any())->method('getRemoteAccessToken')->will($this->returnValue('token'));

        return $session;
    }

    /**
     * Sets Curl to dispatcher
     *
     * @param $dispatcher
     * @param $curl
     */
    protected function setCurlToDispatcher($dispatcher, $curl)
    {
        $communicationService = $dispatcher->getPayPalCheckoutService();
        $caller = $communicationService->getCaller();
        $oldCurl = $caller->getCurl();

        $curl->setHost($oldCurl->getHost());
        $curl->setDataCharset($oldCurl->getDataCharset());
        $curl->setUrlToCall($oldCurl->getUrlToCall());

        $caller->setCurl($curl);
    }

    /**
     * Returns mocked order object
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     */
    protected function getOrder()
    {
        /** @var \OxidEsales\Eshop\Application\Model\Order $order */
        $mockBuilder = $this->getMockBuilder(Order::class);
        $mockBuilder->setMethods(['finalizePayPalOrder']);
        $order = $mockBuilder->getMock();

        $order->expects($this->any())->method('finalizePayPalOrder')->will($this->returnValue(null));
        $order->oxorder__oxid = new \OxidEsales\Eshop\Core\Field('_test_order');
        $order->save();

        return $order;
    }

    /**
     * Mocks oxUtils redirect method so that no redirect would be made
     */
    protected function setMockedUtils()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Utils::class);
        $mockBuilder->setMethods(['redirect']);
        $utils = $mockBuilder->getMock();

        $utils->expects($this->any())->method('redirect')->will($this->returnValue(null));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Utils::class, $utils);
    }

    /**
     * Returns array of replacements in test data
     *
     * @return array
     */
    protected function getReplacements()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $buttonSource = 'OXID_Cart_CommunityECS';

        if ('EE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_EnterpriseECS';
        }
        if ('PE' == $facts->getEdition()) {
            $buttonSource = 'OXID_Cart_ProfessionalECS';
        }

        $replacements = array(
            '{SHOP_URL}' => $this->getConfig()->getShopUrl(),
            '{SHOP_ID}'  => $this->getConfig()->getShopId(),
            '{BN_ID}'    => $buttonSource,
            '{BN_ID_SHORTCUT}' => 'Oxid_Cart_ECS_Shortcut'
        );

        return $replacements;
    }

    /**
     * Resets db tables, required configs
     */
    protected function reset()
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $db->execute("TRUNCATE oxarticles");
        $db->execute("TRUNCATE oxcategories");
        $db->execute("TRUNCATE oxcounters");
        $db->execute("TRUNCATE oxdiscount");
        $db->execute("TRUNCATE oxobject2discount");
        $db->execute("TRUNCATE oxgroups");
        $db->execute("TRUNCATE oxobject2group");
        $db->execute("TRUNCATE oxwrapping");
        $db->execute("TRUNCATE oxdelivery");
        $db->execute("TRUNCATE oxdel2delset");
        $db->execute("TRUNCATE oxobject2payment");
        $db->execute("TRUNCATE oxvouchers");
        $db->execute("TRUNCATE oxvoucherseries");
        $db->execute("TRUNCATE oxobject2delivery");
        $db->execute("TRUNCATE oxdeliveryset");
        $db->execute("TRUNCATE oxuser");
        $db->execute("TRUNCATE oxprice2article");
        $config->setConfigParam("blShowVATForDelivery", true);
        $config->setConfigParam("blShowVATForPayCharge", true);
        $db->execute("UPDATE oxpayments SET oxaddsum=0 WHERE oxid = 'oxidpaypal'");
    }
}
