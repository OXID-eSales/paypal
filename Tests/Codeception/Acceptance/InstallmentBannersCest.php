<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;

/**
 * Class BannerSearchPageCest
 *
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class InstallmentBannersCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function _before(AcceptanceTester $I)
    {
        $I->setPayPalSettingsData();
    }

    /**
     * @param AcceptanceTester $I
     */
    public function searchPageBanner(AcceptanceTester $I)
    {
        $I->wantToTest('PayPal installment banner on search page');

        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', false);

        $I
            ->openShop()
            ->searchFor("1001");

        $I->dontSeeElementInDOM('#paypal-installment-banner-container');

        //Check installment banner body in Flow theme
        $I->updateConfigInDatabase('oePayPalBannersSearchResultsPage', true);
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        //Check installment banner body in Wave theme
        $I->updateConfigInDatabase('sTheme', 'wave');
        $I->reloadPage();
        $I->seePayPalInstallmentBanner();

        // Check banner visibility when oePayPalBannersHideAll setting is set to true
        $I->updateConfigInDatabase('oePayPalBannersHideAll', true);
        $I->reloadPage();
        $I->dontSeeElementInDOM('#paypal-installment-banner-container');
    }
}
