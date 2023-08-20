<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326103555 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fonts (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, file_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, tags VARCHAR(255) NOT NULL, INDEX IDX_7303E8FBF675F31B (author_id), UNIQUE INDEX UNIQ_7303E8FB93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logotypes (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, cover_id INT DEFAULT NULL, author_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, tags LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_7D3465C093CB796C (file_id), UNIQUE INDEX UNIQ_7D3465C0922726E9 (cover_id), INDEX IDX_7D3465C0F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mails (id INT AUTO_INCREMENT NOT NULL, author VARCHAR(255) NOT NULL, receiver VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, template VARCHAR(255) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', sendAt DATETIME NOT NULL, sendedAt DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, bucket VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, temporal TINYINT(1) NOT NULL, UNIQUE INDEX url_idx (bucket, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, last_login DATETIME NOT NULL, secretToken VARCHAR(64) DEFAULT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fonts ADD CONSTRAINT FK_7303E8FBF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE fonts ADD CONSTRAINT FK_7303E8FB93CB796C FOREIGN KEY (file_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE logotypes ADD CONSTRAINT FK_7D3465C093CB796C FOREIGN KEY (file_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE logotypes ADD CONSTRAINT FK_7D3465C0922726E9 FOREIGN KEY (cover_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE logotypes ADD CONSTRAINT FK_7D3465C0F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonts DROP FOREIGN KEY FK_7303E8FB93CB796C');
        $this->addSql('ALTER TABLE logotypes DROP FOREIGN KEY FK_7D3465C093CB796C');
        $this->addSql('ALTER TABLE logotypes DROP FOREIGN KEY FK_7D3465C0922726E9');
        $this->addSql('ALTER TABLE fonts DROP FOREIGN KEY FK_7303E8FBF675F31B');
        $this->addSql('ALTER TABLE logotypes DROP FOREIGN KEY FK_7D3465C0F675F31B');
        $this->addSql('DROP TABLE fonts');
        $this->addSql('DROP TABLE logotypes');
        $this->addSql('DROP TABLE mails');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE users');
    }
}
