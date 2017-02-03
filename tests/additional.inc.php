<?php

# Set default user ID to fit expectation of old tests.
oxDb::getDb()->execute("UPDATE oxuser SET OXID='oxdefaultadmin' WHERE oxusername='admin'");
# Activate Azure theme
oxDb::getDb()->execute("UPDATE `oxconfig` SET `OXVARVALUE` = 0x4db70f6d1a WHERE `OXVARNAME` = 'sTheme'");