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
 * Abstract model db gateway class.
 *
 * @todo: maybe this class lives better in the \OxidEsales\PayPalModule\Model namespace as DbGateway?!
 */
abstract class ModelDbGateway
{
    /**
     * Returns data base resource.
     *
     * @return \OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface
     */
    protected function getDb()
    {
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * Abstract method for data saving (insert and update).
     *
     * @param array $data model data
     */
    abstract public function save($data);

    /**
     * Abstract method for loading model data.
     *
     * @param string $id model id
     */
    abstract public function load($id);

    /**
     * Abstract method for delete model data.
     *
     * @param string $id model id
     */
    abstract public function delete($id);
}
