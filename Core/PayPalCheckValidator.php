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

namespace OxidEsales\PayPalModule\Core;

/**
 * Validates changed basket amount. Checks if it is bigger than previous price.
 * Than returns false to recheck new basket amount in PayPal.
 */
class PayPalCheckValidator
{
    /**
     * Basket new amount
     *
     * @var double
     */
    protected $newBasketAmount = null;

    /**
     * Basket old amount
     *
     * @var double
     */
    protected $oldBasketAmount = null;

    /**
     * Returns if order should be rechecked by PayPal
     *
     * @return bool
     */
    public function isPayPalCheckValid()
    {
        $newBasketAmount = $this->getNewBasketAmount();
        $prevBasketAmount = $this->getOldBasketAmount();
        // check only if new price is different and bigger than old price
        if ($newBasketAmount > $prevBasketAmount) {
            return false;
        }

        return true;
    }

    /**
     * Sets new basket amount
     *
     * @param double $newBasketAmount changed basket amount
     */
    public function setNewBasketAmount($newBasketAmount)
    {
        $this->newBasketAmount = $newBasketAmount;
    }

    /**
     * Returns new basket amount
     *
     * @return double
     */
    public function getNewBasketAmount()
    {
        return (float) $this->newBasketAmount;
    }

    /**
     * Sets old basket amount
     *
     * @param double $oldBasketAmount old basket amount
     */
    public function setOldBasketAmount($oldBasketAmount)
    {
        $this->oldBasketAmount = $oldBasketAmount;
    }

    /**
     * Returns old basket amount
     *
     * @return double
     */
    public function getOldBasketAmount()
    {
        return (float) $this->oldBasketAmount;
    }
}
