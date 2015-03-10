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

require_once __DIR__ . '/../../lib/oepaypalshopconstruct.php';
require_once __DIR__ . '/../../lib/oepaypalcommunicationhelper.php';
require_once __DIR__ . '/../../lib/oepaypaltestcaseparser.php';
require_once __DIR__ . '/../../lib/oepaypalarrayasserts.php';

class Integration_oePayPal_CheckoutRequest_oePayPalCheckoutRequestTest extends OxidTestCase
{
    /**
     * Test cases directory
     *
     * @var string
     */
    protected $_sTestCasesPath = '/integration/oepaypal/checkoutrequest/testcases/';

    /* Specified test cases (optional) */
    protected $_aTestCases = array(
        //'standard/caseSetExpressCheckout_AdditionalItems.php',
    );

    /**
     * Initialize the fixture.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_reset();

        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);
    }

    /**
     * Basket startup data and expected calculations results
     */
    public function providerDoExpressCheckoutPayment()
    {
        $oParser = new oePayPalTestCaseParser();
        $oParser->setDirectory($this->_sTestCasesPath);
        if (isset($this->_aTestCases)) {
            $oParser->setTestCases($this->_aTestCases);
        }
        $oParser->setReplacements($this->_getReplacements());

        return $oParser->getData();
    }

    /**
     * @dataProvider providerDoExpressCheckoutPayment
     */
    public function testExpressCheckoutPaymentRequest($aTestCase)
    {
        if ($aTestCase['skipped']) {
            return;
        }

        $oCommunicationHelper = new oePayPalCommunicationHelper();
        $oCurl = $oCommunicationHelper->getCurl(array());

        $oDispatcher = $this->_getDispatcher($aTestCase);
        $this->_setCurlToDispatcher($oDispatcher, $oCurl);

        $oDispatcher->$aTestCase['action']();

        $aCurlParameters = $oCurl->getParameters();

        $aExpected = $aTestCase['expected'];

        $aAsserts = new oePayPalArrayAsserts();

        $aAsserts->assertArraysEqual($aExpected['requestToPayPal'], $aCurlParameters);

        if (isset($aExpected['header'])) {
            $aCurlHeader = $oCurl->getHeader();
            $aAsserts->assertArraysEqual($aExpected['header'], $aCurlHeader);
        }
    }


    /**
     * Return dispatcher object
     *
     * @param array $aTestCase
     *
     * @return oePayPalExpressCheckoutDispatcher
     */
    protected function _getDispatcher($aTestCase)
    {
        $oBasketConstruct = new oePayPalShopConstruct();
        $oBasketConstruct->setParams($aTestCase);

        $oBasket = $oBasketConstruct->getBasket();
        $oSession = $this->_getSession();
        $oSession->setBasket($oBasket);

        $this->_setMockedUtils();

        $oDispatcher = $this->getMock($aTestCase['class'], array('_getPayPalOrder'));
        $oDispatcher->expects($this->any())->method('_getPayPalOrder')->will($this->returnValue($this->_getOrder()));

        $oDispatcher->setSession($oSession);
        $oDispatcher->setUser($oBasketConstruct->getUser());

        return $oDispatcher;
    }

    /**
     *
     */
    protected function _getSession()
    {
        $oSession = $this->getMock('oxSession', array('getRemoteAccessToken'));
        $oSession->expects($this->any())->method('getRemoteAccessToken')->will($this->returnValue('token'));

        return $oSession;
    }

    /**
     * Sets Curl to dispatcher
     *
     * @param $oDispatcher
     * @param $oCurl
     */
    protected function _setCurlToDispatcher($oDispatcher, $oCurl)
    {
        $oCommunicationService = $oDispatcher->getPayPalCheckoutService();
        $oCaller = $oCommunicationService->getCaller();
        $oOldCurl = $oCaller->getCurl();

        $oCurl->setHost($oOldCurl->getHost());
        $oCurl->setDataCharset($oOldCurl->getDataCharset());
        $oCurl->setUrlToCall($oOldCurl->getUrlToCall());

        $oCaller->setCurl($oCurl);
    }

    /**
     * Returns mocked order object
     *
     * @return oxOrder
     */
    protected function _getOrder()
    {
        $oOrder = $this->getMock('oePayPalOxOrder', array('finalizePayPalOrder'));
        $oOrder->expects($this->any())->method('finalizePayPalOrder')->will($this->returnValue(null));

        return $oOrder;
    }

    /**
     * Mocks oxUtils redirect method so that no redirect would be made
     */
    protected function _setMockedUtils()
    {
        $oUtils = $this->getMock('oxUtils', array('redirect'));
        $oUtils->expects($this->any())->method('redirect')->will($this->returnValue(null));
        oxRegistry::set('oxUtils', $oUtils);
    }

    /**
     * Returns array of replacements in test data
     *
     * @return array
     */
    protected function _getReplacements()
    {
        $oConfig = $this->getConfig();
        if ($oConfig->getEdition() == 'EE') {
            $sResult = 'OXID_Cart_EnterpriseECS';
        } else {
            if ($oConfig->getEdition() == 'PE') {
                $sResult = 'OXID_Cart_ProfessionalECS';
            } else {
                if ($oConfig->getEdition() == 'CE') {
                    $sResult = 'OXID_Cart_CommunityECS';
                }
            }
        }
        $aReplacements = array(
            '{SHOP_URL}' => $this->getConfig()->getShopUrl(),
            '{SHOP_ID}'  => $this->getConfig()->getShopId(),
            '{BN_ID}'    => $sResult,
        );

        return $aReplacements;
    }

    /**
     * Resets db tables, required configs
     */
    protected function _reset()
    {
        $oDb = oxDb::getDb();
        $oConfig = oxRegistry::getConfig();
        $oDb->query("TRUNCATE oxarticles");
        $oDb->query("TRUNCATE oxcategories");
        $oDb->query("TRUNCATE oxcounters");
        $oDb->query("TRUNCATE oxdiscount");
        $oDb->query("TRUNCATE oxobject2discount");
        $oDb->query("TRUNCATE oxgroups");
        $oDb->query("TRUNCATE oxobject2group");
        $oDb->query("TRUNCATE oxwrapping");
        $oDb->query("TRUNCATE oxdelivery");
        $oDb->query("TRUNCATE oxdel2delset");
        $oDb->query("TRUNCATE oxobject2payment");
        $oDb->query("TRUNCATE oxvouchers");
        $oDb->query("TRUNCATE oxvoucherseries");
        $oDb->query("TRUNCATE oxobject2delivery");
        $oDb->query("TRUNCATE oxdeliveryset");
        $oDb->query("TRUNCATE oxuser");
        $oDb->query("TRUNCATE oxprice2article");
        $oConfig->setConfigParam("blShowVATForDelivery", true);
        $oConfig->setConfigParam("blShowVATForPayCharge", true);
        $oDb->query("UPDATE oxpayments SET oxaddsum=0 WHERE oxid = 'oxidpaypal'");
    }
}