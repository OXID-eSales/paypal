<?php

# Activate Azure theme
\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("UPDATE `oxconfig` SET `OXVARVALUE` = 'azure' WHERE `OXVARNAME` = 'sTheme'");