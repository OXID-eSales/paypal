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

namespace OxidEsales\PayPalModule\Tests\Acceptance;

use OxidEsales\TestingLibrary\Services\Files\Remove;
use OxidEsales\TestingLibrary\Services\Library\Request;
use OxidEsales\TestingLibrary\Services\Library\ServiceConfig;

class PayPalLogHelper
{
    /**
     * Holds the parsed log information.
     *
     * @var array
     */
    private $logData = [];

    /**
     * Test helper to get PayPal log path.
     *
     * @return string
     */
    public function getPathToPayPalLog()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getLogsDir() . DIRECTORY_SEPARATOR . 'oepaypal.log';
    }

    /**
     * Make sure log is writable.
     */
    public function setLogPermissions()
    {
        $pathToLog = $this->getPathToPayPalLog();
        if (file_exists($pathToLog) && !is_writable($pathToLog)){
            \OxidEsales\TestingLibrary\Services\Library\CliExecutor::executeCommand("sudo chmod 777 $pathToLog");
        }
    }

    /**
     * Test helper, cleans PayPal log.
     */
    public function cleanPayPalLog()
    {
        $pathToLog = $this->getPathToPayPalLog();
        if (file_exists($pathToLog)) {
            unlink($pathToLog);
        }
    }

    /**
     * Move PayPal log to different name for test debugging.
     */
    public function renamePayPalLog()
    {
        $pathToLog = $this->getPathToPayPalLog();
        if (file_exists($pathToLog)) {
            rename($pathToLog, $pathToLog . ('_' . microtime(true)));
        }
    }

    /**
     * Get data from log.
     *
     * @return array
     */
    public function getLogData()
    {
        $this->logData = [];
        $this->parsePayPalLog();
        return $this->logData;
    }

    /**
     * Test helper, parses PayPal log.
     */
    private function parsePayPalLog()
    {
        $handle = null;
        if (file_exists($this->getPathToPayPalLog())) {
            $handle = fopen($this->getPathToPayPalLog(), 'r');
        }

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (false !== strpos($line, 'Request to PayPal')) {
                    $date = $this->parseDate($line);
                    $handle = $this->handleLogData($handle, $date, 'request');
                }
                if (false !== strpos($line, 'Response from PayPal')) {
                    $date = $this->parseDate($line);
                    $handle = $this->handleLogData($handle, $date, 'response');
                }
                if (false !== strpos($line, 'IPN Process result')) {
                    $date = $this->parseDate($line);
                    $handle = $this->handleLogData($handle, $date, 'ipn_handle_result');
                }
            }
            fclose($handle);
        }
    }

    /**
     * Parse data set until line containing a single ')' is hit.
     *
     * @param ressource $handle log file handle
     * @param string    $date   date of action
     * @param string    $type   type can be request or reponse
     *
     * @return mixed
     */
    private function handleLogData($handle, $date, $type)
    {
        $sessionId = null;
        $object = new \StdClass;
        $object->date = $date;
        $object->type = $type;
        $object->data = [];

        while ((($line = fgets($handle)) !== false) && (false === strpos($line, ')'))) {
            if (false !== strpos($line, 'SESS ID')) {
                $object->sid = $this->parseSessionId($line);
            }
            if (false !== strpos($line, "' =>")) {
                $keyValue = $this->parseArray($line);
                $object->data[$keyValue->key] = $keyValue->value;
            }
        }
        $this->register($object);

        return $handle;
    }

    /**
     * Add result to parsed data.
     *
     * @param $object
     */
    private function register($object)
    {
        $this->logData[] = $object;
    }

    /**
     * Parse for date.
     * Returns first match in given string.
     *
     * @param string $line
     *
     * @return string
     */
    private function parseDate($line)
    {
        $matches=null;
        $ret = null;
        preg_match_all("/\[((\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}))\]/", $line, $matches);
        if (is_array($matches) && !empty($matches)) {
           $ret = $matches[1][0];
        }
        return $ret;
    }

    /**
     * Parse for session id.
     *
     * Returns first match in given string.
     */
    private function parseSessionId($line)
    {
        $tmp = explode('SESS ID:', $line);
        return trim($tmp[1]);
    }

    /**
     * @param string $line
     *
     * @return \StdClass
     */
    private function parseArray($line)
    {
        $matches = null;
        $object = new \StdClass;
        preg_match_all("/\'(.*)\'(\s*=>\s*)\'*(.*)\'*/", $line, $matches);

        if (is_array($matches) && isset($matches[1])) {
            $object->key = $matches[1][0];
            $object->value = rtrim(rtrim($matches[3][0], ','), "'");
        }
        return $object;
    }
}
