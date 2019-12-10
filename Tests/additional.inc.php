<?php

# Set default user ID to fit expectation of old tests.
\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("UPDATE oxuser SET OXID='oxdefaultadmin' WHERE oxusername='admin'");
# Activate Azure theme
\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute("UPDATE `oxconfig` SET `OXVARVALUE` = 'azure' WHERE `OXVARNAME` = 'sTheme'");