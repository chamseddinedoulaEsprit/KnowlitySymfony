<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224103344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, descrption VARCHAR(255) NOT NULL, icone VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chapitre (id INT AUTO_INCREMENT NOT NULL, cours_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, chap_order INT NOT NULL, contenu VARCHAR(255) NOT NULL, INDEX IDX_8C62B0257ECF78B0 (cours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, matiere_id INT DEFAULT NULL, enseignant_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, url_image VARCHAR(255) NOT NULL, langue VARCHAR(255) NOT NULL, prix INT NOT NULL, INDEX IDX_FDCA8C9CF46CD258 (matiere_id), INDEX IDX_FDCA8C9CE455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cours_user (cours_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5EE5E9A67ECF78B0 (cours_id), INDEX IDX_5EE5E9A6A76ED395 (user_id), PRIMARY KEY(cours_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, max_score INT NOT NULL, create_at DATETIME NOT NULL, deadline DATETIME NOT NULL, cours INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_registration (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, registration_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, status VARCHAR(20) NOT NULL, disabled_parking TINYINT(1) NOT NULL, coming_from VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, places_reserved INT DEFAULT NULL, INDEX IDX_8FBBAD5471F7E88B (event_id), INDEX IDX_8FBBAD54A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, organizer_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(5000) NOT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, max_participants INT DEFAULT NULL, seats_available INT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, image VARCHAR(255) DEFAULT NULL, category JSON NOT NULL, INDEX IDX_5387574A876C4DDA (organizer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE home (id INT AUTO_INCREMENT NOT NULL, chams VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9014574ABCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, evaluation_id INT NOT NULL, text VARCHAR(255) NOT NULL, point INT NOT NULL, ordre_question INT NOT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_B6F7494E456C5646 (evaluation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, evaluation_id INT DEFAULT NULL, resultat_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, is_correct TINYINT(1) NOT NULL, user_id INT DEFAULT NULL, note INT DEFAULT NULL, INDEX IDX_5FB6DEC71E27F6BF (question_id), INDEX IDX_5FB6DEC7456C5646 (evaluation_id), INDEX IDX_5FB6DEC7D233E95C (resultat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resultat (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, submitted_at DATETIME NOT NULL, feedback VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, prenom VARCHAR(30) NOT NULL, date_naissance DATE NOT NULL, email VARCHAR(30) NOT NULL, num_telephone INT NOT NULL, password VARCHAR(254) NOT NULL, image VARCHAR(100) NOT NULL, genre VARCHAR(30) NOT NULL, localisation VARCHAR(30) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login DATE DEFAULT NULL, confirm_password VARCHAR(30) NOT NULL, banned INT NOT NULL, deleted INT NOT NULL, grade_level INT DEFAULT NULL, specialite VARCHAR(30) NOT NULL, roles VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B0257ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE455FCC0 FOREIGN KEY (enseignant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cours_user ADD CONSTRAINT FK_5EE5E9A67ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cours_user ADD CONSTRAINT FK_5EE5E9A6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD5471F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD54A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574ABCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B0257ECF78B0');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CF46CD258');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE455FCC0');
        $this->addSql('ALTER TABLE cours_user DROP FOREIGN KEY FK_5EE5E9A67ECF78B0');
        $this->addSql('ALTER TABLE cours_user DROP FOREIGN KEY FK_5EE5E9A6A76ED395');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD5471F7E88B');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD54A76ED395');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A876C4DDA');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574ABCF5E72D');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E456C5646');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7456C5646');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7D233E95C');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE chapitre');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE cours_user');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE event_registration');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE home');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE resultat');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
