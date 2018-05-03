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
 * Abstract model class
 */
abstract class Model
{
    /**
     * Data base gateway.
     *
     * @var \OxidEsales\PayPalModule\Core\ModelDbGateway
     */
    protected $dbGateway = null;

    /**
     * Model data.
     *
     * @var array
     */
    protected $data = null;

    /**
     * Was object information found in database.
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * Set response data.
     *
     * @param array $data model data
     */
    public function setData($data)
    {
        $data = array_change_key_case($data, CASE_LOWER);
        $this->data = $data;
    }

    /**
     * Return response data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return value from data by given key.
     *
     * @param string $key key of data value
     *
     * @return string
     */
    protected function getValue($key)
    {
        $data = $this->getData();

        return $data[$key];
    }

    /**
     * Return value from data by given key.
     *
     * @param string $key   key of data value
     * @param string $value data value
     */
    protected function setValue($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Returns model database gateway.
     *
     * @var $dbGateway
     */
    abstract protected function getDbGateway();

    /**
     * Set model database gateway.
     *
     * @param \OxidEsales\PayPalModule\Core\ModelDbGateway $dbGateway
     */
    protected function setDbGateway($dbGateway)
    {
        $this->dbGateway = $dbGateway;
    }

    /**
     * Method for model saving (insert and update data).
     *
     * @return int|false
     */
    public function save()
    {
        $id = $this->getDbGateway()->save($this->getData());
        $this->setId($id);

        return $id;
    }

    /**
     * Delete model data from db.
     *
     * @param string $id model id
     *
     * @return bool
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->setId($id);
        }

        return $this->getDbGateway()->delete($this->getId());
    }

    /**
     * Method for loading model, if loaded returns true.
     *
     * @param string $id model id
     *
     * @return bool
     */
    public function load($id = null)
    {
        if (!is_null($id)) {
            $this->setId($id);
        }

        $this->isLoaded = false;
        $data = $this->getDbGateway()->load($this->getId());
        if ($data) {
            $this->setData($data);
            $this->isLoaded = true;
        }

        return $this->isLoaded();
    }

    /**
     * Returns whether object information found in database.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Abstract method for delete model.
     *
     * @param string $id model id
     */
    abstract public function setId($id);

    /**
     * Abstract method for getting id.
     */
    abstract public function getId();
}
