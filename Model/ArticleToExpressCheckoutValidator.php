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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal Current item Article validator class.
 */
class ArticleToExpressCheckoutValidator
{
    /**
     * Item that will be validated.
     *
     * @var object
     */
    protected $itemToValidate;

    /**
     * User basket
     *
     * @var \OxidEsales\Eshop\Application\Model\Basket
     */
    protected $basket;

    /**
     *Sets current item of details page.
     *
     * @param object $itemToValidate
     */
    public function setItemToValidate($itemToValidate)
    {
        $this->itemToValidate = $itemToValidate;
    }

    /**
     * Returns details page current item.
     *
     * @return \OxidEsales\PayPalModule\Model\ArticleToExpressCheckoutCurrentItem|object
     */
    public function getItemToValidate()
    {
        return $this->itemToValidate;
    }

    /**
     * Method sets basket object.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * Methods returns basket object.
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * Method returns if article valid
     *
     * @return bool
     */
    public function isArticleValid()
    {
        $valid = true;
        if ($this->isArticleAmountZero() || $this->isSameItemInBasket()) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * Check if same article is in basket.
     *
     * @return bool
     */
    protected function isSameItemInBasket()
    {
        $basketContents = $this->getBasket()->getContents();
        foreach ($basketContents as $basketItem) {
            if ($this->isArticleParamsEqual($basketItem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if Article params equals with current items params.
     *
     * @param \OxidEsales\Eshop\Application\Model\BasketItem $basketItem
     *
     * @return bool
     */
    protected function isArticleParamsEqual($basketItem)
    {
        return ($basketItem->getProductId() == $this->getItemToValidate()->getArticleId() &&
                $basketItem->getPersParams() == $this->getItemToValidate()->getPersistParam() &&
                $basketItem->getSelList() == $this->getItemToValidate()->getSelectList());
    }

    /**
     * Checks if article amount 0.
     *
     * @return bool
     */
    protected function isArticleAmountZero()
    {
        $articleAmount = $this->getItemToValidate()->getArticleAmount();

        return 0 == $articleAmount;
    }
}
