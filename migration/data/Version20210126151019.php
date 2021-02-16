<?php declare(strict_types=1);

namespace OxidEsales\PayPalModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210126151019 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `oxuserbaskets`
          ADD COLUMN `OEPAYPAL_PAYMENT_TOKEN` char(32)
          character set latin1 collate latin1_general_ci NOT NULL DEFAULT ''
          COMMENT 'Paypal payment token';");
    }

    public function down(Schema $schema): void
    {
    }
}
