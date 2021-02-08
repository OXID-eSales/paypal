<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\PayPalModule\Tests\Acceptance;

/**
 * Class FinalizeOrderOnPayPalTest
 *
 * @package OxidEsales\PayPalModule\Tests\Acceptance
 */
class FinalizeOrderOnPayPalTest extends BaseAcceptanceTestCase
{

    /**
     * Test finalizing order on PayPal for express checkout.
     * Case that the customer is not logged in to shop but logs in to PayPal.
     * After completing checkout on PayPal side, users ends up on ThankYou page in shop
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_express
     */
    public function testFinalizeOrderOnPayPalSideOnForNotLoggedInUser()
    {
        $this->setFinalizeOrderOnPayPalSide(1);

        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is not logged in
        $this->openShop();
        $this->addToBasketProceedToPayPal();
        $this->payWithPayPal();

        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
        $this->assertElementNotPresent("//button[text()='Order now']");
    }

    /**
     * Test not finalizing order on PayPal for express checkout.
     * Case that the customer is not logged in to shop but logs in to PayPal.
     * After completing checkout on PayPal side, users ends up on order page in shop.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_express
     */
    public function testFinalizeOrderOnPayPalSideOffForNotLoggedInUser()
    {
        $this->setFinalizeOrderOnPayPalSide(0);

        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is not logged in
        $this->openShop();
        $this->addToBasketProceedToPayPal();
        $this->payWithPayPal();

        $this->assertTextNotPresent(self::THANK_YOU_PAGE_IDENTIFIER);
        $this->assertElementPresent("//button[text()='Order now']");
        $this->clickAndWait("//button[text()='Order now']");

        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
    }

    /**
     * Test finalizing order on PayPal for express checkout.
     * Case that the customer is logged in to shop before starting express checkout.
     * After completing checkout on PayPal side, users ends up on ThankYou page in shop
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_express
     */
    public function testFinalizeOrderOnPayPalSideOnForLoggedInUser()
    {
        $this->setFinalizeOrderOnPayPalSide(1);

        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user logged in
        $this->openShop();
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->addToBasketProceedToPayPal();
        $this->payWithPayPal();

        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
        $this->assertElementNotPresent("//button[text()='Order now']");
    }

    /**
     * Test not finalizing order on PayPal for express checkout.
     * Case that the customer is logged in to shop before starting express checkout.
     * After completing checkout on PayPal side, users ends up on order page in shop.
     *
     * @group paypal_standalone
     * @group paypal_external
     * @group paypal_buyerlogin
     * @group paypal_express
     */
    public function testFinalizeOrderOnPayPalSideOffForLoggedInUser()
    {
        $this->setFinalizeOrderOnPayPalSide(0);

        //Assign PayPal to standard shipping method.
        $this->importSql(__DIR__ . '/testSql/assignPayPalToGermanyStandardShippingMethod.sql');

        // Testing when user is not logged in
        $this->openShop();
        $this->loginInFrontend(self::LOGIN_USERNAME, self::LOGIN_USERPASS);
        $this->addToBasketProceedToPayPal();
        $this->payWithPayPal();

        $this->assertTextNotPresent(self::THANK_YOU_PAGE_IDENTIFIER);
        $this->assertElementPresent("//button[text()='Order now']");
        $this->clickAndWait("//button[text()='Order now']");

        $this->assertTextPresent(self::THANK_YOU_PAGE_IDENTIFIER, "Order is not finished successful");
    }

    /**
     * @param bool $flag
     */
    protected function setFinalizeOrderOnPayPalSide($flag = 1)
    {
        $this->callShopSC('oxConfig', null, null,
            [
                'blOEPayPalFinalizeOrderOnPayPal' => [
                    'type' => 'bool',
                    'value' => $flag,
                    'module' => 'module:oepaypal'
                ]
            ]
        );
    }
}