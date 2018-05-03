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

namespace OxidEsales\PayPalModule\Model\Response;

/**
 * Abstract PayPal Response class
 */
abstract class Response
{
    /**
     * PayPal response data
     *
     * @var array
     */
    protected $data = null;

    /**
     * Set response data
     *
     * @param array $responseData Response data from PayPal
     */
    public function setData($responseData)
    {
        $this->data = $responseData;
    }

    /**
     * Return response data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return value from data by given key
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
}
