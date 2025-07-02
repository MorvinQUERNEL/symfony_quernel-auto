<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702092758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, sender_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, send_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', conversation_id VARCHAR(50) NOT NULL, is_from_user TINYINT(1) NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_DB021E96F624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', order_status VARCHAR(50) NOT NULL, total_price BIGINT NOT NULL, delivery_city VARCHAR(100) NOT NULL, delivery_postal_code INT NOT NULL, delivery_country VARCHAR(50) NOT NULL, delivery_adress VARCHAR(100) NOT NULL, INDEX IDX_E52FFDEE67B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payement (id INT AUTO_INCREMENT NOT NULL, orders_id INT DEFAULT NULL, amount INT NOT NULL, payement_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', payement_status VARCHAR(50) DEFAULT NULL, INDEX IDX_B20A7885CFFE9AD6 (orders_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pictures (id INT AUTO_INCREMENT NOT NULL, vehicules_id INT DEFAULT NULL, description VARCHAR(50) DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_8F7C2FC08D8BD7E2 (vehicules_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE preference (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT NULL, preferenece_brand VARCHAR(50) DEFAULT NULL, preference_model VARCHAR(50) DEFAULT NULL, min_year DATE DEFAULT NULL, max_price BIGINT NOT NULL, preference_fuel_type VARCHAR(30) NOT NULL, INDEX IDX_5D69B05367B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, phone_number VARCHAR(20) NOT NULL, address VARCHAR(100) NOT NULL, city VARCHAR(50) NOT NULL, postal_code INT NOT NULL, country VARCHAR(50) NOT NULL, registration_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vehicules (id INT AUTO_INCREMENT NOT NULL, orders_id INT DEFAULT NULL, brand VARCHAR(50) NOT NULL, model VARCHAR(50) NOT NULL, year DATE NOT NULL, mileage INT NOT NULL, fuel_type VARCHAR(50) NOT NULL, trasmission VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, door_count SMALLINT NOT NULL, description VARCHAR(255) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, sale_price BIGINT NOT NULL, INDEX IDX_78218C2DCFFE9AD6 (orders_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payement ADD CONSTRAINT FK_B20A7885CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pictures ADD CONSTRAINT FK_8F7C2FC08D8BD7E2 FOREIGN KEY (vehicules_id) REFERENCES vehicules (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference ADD CONSTRAINT FK_5D69B05367B3B43D FOREIGN KEY (users_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicules ADD CONSTRAINT FK_78218C2DCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payement DROP FOREIGN KEY FK_B20A7885CFFE9AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pictures DROP FOREIGN KEY FK_8F7C2FC08D8BD7E2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference DROP FOREIGN KEY FK_5D69B05367B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicules DROP FOREIGN KEY FK_78218C2DCFFE9AD6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orders
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pictures
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE preference
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vehicules
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
