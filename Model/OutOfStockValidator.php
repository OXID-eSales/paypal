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
 * PayPal out of stock validator class
 */
class OutOfStockValidator
{
    /**
     * Basket object
     *
     * @var object
     */
    private $basket;

    /**
     * Level of empty stock level
     *
     * @var int
     */
    private $emptyStockLevel;

    /**
     * Sets empty stock level.
     *
     * @param int $emptyStockLevel
     */
    public function setEmptyStockLevel($emptyStockLevel)
    {
        $this->emptyStockLevel = $emptyStockLevel;
    }

    /**
     * Returns empty stock level.
     *
     * @return int
     */
    public function getEmptyStockLevel()
    {
        return $this->emptyStockLevel;
    }

    /**
     * Sets basket object.
     *
     * @param object $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * Returns basket object.
     *
     * @return object
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * Checks if basket has Articles that are out of stock.
     *
     * @return bool
     */
    public function hasOutOfStockArticles()
    {
        $result = false;

        $basketContents = $this->getBasket()->getContents();

        foreach ($basketContents as $basketItem) {
            $article = $basketItem->getArticle();
            if (($article->getStockAmount() - $basketItem->getAmount()) < $this->getEmptyStockLevel()) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
