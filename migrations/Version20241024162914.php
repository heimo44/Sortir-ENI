<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024162914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F25D83CC1');
        $this->addSql('DROP INDEX IDX_3C3FD3F25D83CC1 ON sortie');
        $this->addSql('ALTER TABLE sortie DROP state_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie ADD state_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F25D83CC1 FOREIGN KEY (state_id) REFERENCES etat (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3C3FD3F25D83CC1 ON sortie (state_id)');
    }
}
