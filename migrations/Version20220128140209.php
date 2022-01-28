<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220128140209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ad DROP FOREIGN KEY FK_77E0ED588D7B4FB4');
        $this->addSql('ALTER TABLE ad ADD CONSTRAINT FK_77E0ED588D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tag (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B783989D9B62 ON tag (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ad DROP FOREIGN KEY FK_77E0ED588D7B4FB4');
        $this->addSql('ALTER TABLE ad ADD CONSTRAINT FK_77E0ED588D7B4FB4 FOREIGN KEY (tags_id) REFERENCES ad (id)');
        $this->addSql('DROP INDEX UNIQ_389B783989D9B62 ON tag');
    }
}
