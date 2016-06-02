<?php

oxDb::getDb()->getOne("UPDATE oxuser SET OXID='oxdefaultadmin' WHERE oxusername='admin'");