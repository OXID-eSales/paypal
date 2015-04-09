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
 * @copyright (C) OXID eSales AG 2003-2014
 */

//error boolean. Set to true if error appears
$blError = false;

/**
 * Prints error message
 *
 * @param string $sError  Error message to be printed
 * @param string $sAction Suggestion for to be taken action
 */
function printError($sError, $sAction)
{
    echo "<br>";
    echo '&nbsp;&nbsp;&nbsp;<b><font color="red">Fehler:</font></b> ' . $sError . '<br>';
    echo '&nbsp;&nbsp;&nbsp;<b>Ma&szlig;nahme:</b> ' . $sAction . '<br><br>';
}

/**
 * Prints warning message
 *
 * @param string $sError  Error message to be printed
 * @param string $sAction Suggestion for to be taken action
 *
 */
function printWarning($sError, $sAction)
{
    echo "<br>";
    echo '&nbsp;&nbsp;&nbsp;<b><font color="orange">Warnung:</font></b> ' . $sError . '<br>';
    echo '&nbsp;&nbsp;&nbsp;<b>Ma&szlig;nahme:</b> ' . $sAction . '<br><br>';
}

/**
 * Prints OK message
 *
 * @param string $sNote additional note
 *
 */
function printOk($sNote = '')
{
    if ($sNote) {
        $sNote = '&nbsp;' . $sNote;
    }
    echo " <b><font color='green'>OK</font></b>$sNote<br><br>";
}

// check php version and zend decoder
echo 'Teste PHP-Version. ';
if (version_compare('5.3', phpversion()) < 0) {
    printOK();
} else {
    printError("PHP 5.3 oder gr&ouml;&szlig;er wird vorausgesetzt. Installiert ist jedoch: " . phpversion() . ".", "Bitte PHP 5.3.x oder gr&ouml;&szlig;er verwenden.");
    $blError = true;
}

//CURL installed?
echo 'Teste ob CURL installiert ist.';
if (extension_loaded('curl')) {
    printOk();
} else {
    printError('CURL nicht verf&uuml;gbar.', 'Bitte Installieren Sie CURL f&uuml;r PHP. Weitere Hinweise in der <a href="http://de2.php.net/manual/en/book.curl.php" target="_blank">PHP Dokumentation</a>.');
    $blError = true;
}

//OpenSSL installed?
echo 'Teste ob OpenSSL installiert ist.';
if (extension_loaded('openssl')) {
    printOk();
} else {
    printError('OpenSSL ist nicht verf�gbar.', 'Bitte Installieren Sie OpenSSL f&uuml;r PHP. Weitere Hinweise in der <a href="http://de3.php.net/manual/de/book.openssl.php" target="_blank">PHP Dokumentation</a>.');
    $blError = true;
}

if ($blError) {
    echo '<b><font color="red">Die Systemvoraussetzungen sind nicht erf&uuml;llt.</font></b>';
} else {
    echo '<b><font color="green">Gl&uuml;ckwunsch. Die Systemvoraussetzungen sind erf&uuml;llt. Sie k�nnen das PayPal Modul installieren.</font></b>';
}