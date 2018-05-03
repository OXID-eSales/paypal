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
 * Base logger class
 */
class Logger
{
    /**
     * Logger session id.
     *
     * @var string
     */
    protected $loggerSessionId;

    /**
     * Log title
     */
    protected $logTitle = '';

    /**
     * Sets logger session id.
     *
     * @param string $id session id
     */
    public function setLoggerSessionId($id)
    {
        $this->loggerSessionId = $id;
    }

    /**
     * Returns loggers session id.
     *
     * @return string
     */
    public function getLoggerSessionId()
    {
        return $this->loggerSessionId;
    }

    /**
     * Returns full log file path.
     *
     * @return string
     */
    protected function getLogFilePath()
    {
        $logDirectoryPath = \OxidEsales\Eshop\Core\Registry::getConfig()->getLogsDir();

        return $logDirectoryPath . 'oepaypal.log';
    }

    /**
     * Set log title.
     *
     * @param string $title Log title
     */
    public function setTitle($title)
    {
        $this->logTitle = $title;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->logTitle;
    }

    /**
     * Writes log message.
     *
     * @param mixed $logData logger data
     */
    public function log($logData)
    {
        $handle = fopen($this->getLogFilePath(), "a+");
        if ($handle !== false) {
            if (is_string($logData)) {
                parse_str($logData, $result);
            } else {
                $result = $logData;
            }

            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    if (is_string($value)) {
                        $result[$key] = urldecode($value);
                    }
                }
            }

            fwrite($handle, "======================= " . $this->getTitle() . " [" . date("Y-m-d H:i:s") . "] ======================= #\n\n");
            fwrite($handle, "SESS ID: " . $this->getLoggerSessionId() . "\n");
            fwrite($handle, trim(var_export($result, true)) . "\n\n");
            fclose($handle);
        }

        //resetting log title
        $this->setTitle('');
    }
}
