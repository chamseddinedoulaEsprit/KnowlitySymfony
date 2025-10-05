# Knowlity - Plateforme Éducative Innovante

## Description

Knowlity est une plateforme éducative développée avec **Symfony** pour une interface utilisateur immersive, centrée sur l’apprentissage interactif via des quiz, cours, blogs, et événements, avec des fonctionnalités avancées d’IA et de sécurité.

- **Objectif** : Offrir une solution complète pour l’apprentissage en ligne, avec des outils pour les étudiants, enseignants, et administrateurs, en intégrant des technologies modernes comme l’IA et des interfaces créatives.
- **Problème qu’il résout** : Faciliter la création et la gestion de contenu éducatif, améliorer l’engagement des étudiants grâce à des quiz interactifs et des événements, et assurer une expérience utilisateur sécurisée et intuitive.
- **Ses principales fonctionnalités** :
  - **Gestion des Utilisateurs** : Inscription via Google, réinitialisation de mot de passe, verrouillage après 3 tentatives de connexion échouées.
  - **Gestion des Cours** : Création, partage sur Facebook, reconnaissance vocale pour les descriptions, gestion des catégories, chapitres, paiements via API Konnect.
  - **Gestion des Quiz et Évaluations** : Création automatisée via API Gemini, correction automatique/manuelle, musique de fond, text-to-speech, génération de PDF.
  - **Gestion de Blog** : Création, likes, commentaires.
  - **Gestion des Événements** : Création avec API Map, descriptions générées par IA, QR codes pour inscription, chatbot.

## Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. Clonez le repository :
   ```bash
   git clone https://github.com/chamseddinedoulaEsprit/knowlitySymfony.git
  
   ```

2. Installez les dépendances :
   * Pour le backend Symfony :
     ```bash
     composer install
     ```
   

3. Configurez les variables d’environnement :
   * Créez un fichier `.env` à la racine du projet.
   * Ajoutez les clés API pour Google, Konnect, Gemini, Hugging Face, et autres :
     ```
     GOOGLE_CLIENT_ID=votre_google_client_id
     GOOGLE_CLIENT_SECRET=votre_google_client_secret
     KONNECT_API_KEY=votre_konnect_api_key
     GEMINI_API_KEY=votre_gemini_api_key
     HUGGING_FACE_API_KEY=votre_hugging_face_api_key
     ```

4. Configurez la base de données (Symfony) :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Compilez le frontend JavaFX :
   ```bash
   mvn clean package
   ```

6. Lancez l’application :
   * Backend Symfony :
     ```bash
     symfony server:start
     ```
   * Frontend JavaFX :
     ```bash
     java -jar target/knowlity-1.0-SNAPSHOT.jar
     ```

## Utilisation

### Prérequis
- **PHP 8.1+** et **Composer** pour le backend Symfony
- **Java 17+** et **Maven** pour le frontend JavaFX
- **MySQL** pour la base de données
- Clés API pour [Google](https://developers.google.com), [Konnect](https://konnect.api.url), [Gemini](https://gemini.api.url), [Hugging Face](https://huggingface.co)

### Installation de PHP et Java
#### PHP (Backend)
1. Installez PHP 8.1+ :
   * Sur Ubuntu :
     ```bash
     sudo apt update
     sudo apt install php8.1
     ```
   * Vérifiez :
     ```bash
     php -v
     ```

#### Java (Frontend)
1. Installez Java 17+ :
   * Sur Ubuntu :
     ```bash
     sudo apt update
     sudo apt install openjdk-17-jdk
     ```
   * Vérifiez :
     ```bash
     java -version
     ```

### Instructions d’utilisation

#### Gestion des Utilisateurs
1. **Inscription** :
   * Inscrivez-vous via *SignUp.fxml* avec un formulaire ou utilisez *Connexion Google*.
   * Recevez un email de confirmation.
2. **Connexion** :
   * Connectez-vous via *Login.fxml*.
   * Après 3 tentatives échouées, le compte est bloqué 1 minute.
3. **Réinitialisation de Mot de Passe** :
   * Demandez une réinitialisation via *ResetPassword.fxml*.
   * Recevez un lien par email pour réinitialiser.

#### Gestion des Cours
1. **Enseignant** :
   * Ajoutez un cours (*AjouterCours.fxml*), incluant catégories et chapitres.
   * Utilisez la *reconnaissance vocale* (IA) pour générer une description.
   * Partagez le cours sur Facebook via l’API Graph.
   * Ajoutez un *watermark* (logo Knowlity) sur les images des cours.
2. **Administrateur** :
   * Gérez les catégories et matières (*GestionCategories.fxml*).
   * Redimensionnez les icônes des catégories.
3. **Étudiant** :
   * Inscrivez-vous à un cours (*ListeCours.fxml*).
   * Mettez des cours en favoris.
   * Payez via l’API Konnect.
4. **Planification** :
   * Générez un planning étudiant avec l’API Hugging Face (*Planning.fxml*).

#### Gestion des Quiz et Évaluations
1. **Création** :
   * Créez des quiz/évaluations (*AjouterQuiz.fxml*) automatiquement via l’API Gemini.
   * Randomisez les questions et réponses pour éviter la triche.
2. **Passation** :
   * Lancez un quiz (*PassationQuiz.fxml*).
   * Écoutez une *musique douce* pendant la session.
   * Utilisez *text-to-speech* pour lire les questions.
   * Détection des *mots inappropriés* dans les réponses des évaluations.
3. **Correction** :
   * Correction automatique des quiz.
   * Correction manuelle des évaluations par l’enseignant (*CorrigerEvaluation.fxml*).
4. **Résultats** :
   * Affichez les résultats avec *emojis* (🎉 pour >90%, 😕 pour <50%).
   * Générez un PDF avec scores et réponses (*ResultatsQuiz.fxml*).
5. **Statistiques** :
   * Consultez les taux de réussite et questions échouées (*Statistiques.fxml*).

#### Gestion de Blog
1. **Utilisateur** :
   * Créez, modifiez, supprimez des articles (*GestionBlog.fxml*).
   * Ajoutez des *likes*, *dislikes*, et commentaires.
2. **Administrateur** :
   * Modérez les articles et commentaires (*AdminBlog.fxml*).

#### Gestion des Événements
1. **Création** :
   * Ajoutez un événement (*AjouterEvenement.fxml*).
   * Générez une description via API IA.
   * Intégrez une carte avec *API Map*.
2. **Inscription** :
   * Inscrivez-vous avec un *QR code* généré.
   * Exportez les détails en PDF (*DetailsEvenement.fxml*).
3. **Interaction** :
   * Utilisez un *chatbot* pour des questions sur l’événement (*ChatbotEvenement.fxml*).

## Contribution

Nous remercions tous ceux qui ont contribué à ce projet !

### Contributeurs
- Utilisateur1 - Gestion des utilisateurs et cours
- Utilisateur2 - Quiz, évaluations et IA
- Utilisateur3 - Blog et événements

### Comment contribuer ?
1. Fork le projet :
   * Cliquez sur **Fork** sur la page GitHub du projet.
2. Clonez votre fork :
   ```bash
   git clone https://github.com/votre-utilisateur/knowlity.git
   cd knowlity
   ```
3. Créez une nouvelle branche :
   ```bash
   git checkout -b feature/votre-fonctionnalite
   ```
4. Effectuez vos modifications :
   ```bash
   git add .
   git commit -m "Ajout de votre fonctionnalité"
   ```
5. Poussez vers votre fork :
   ```bash
   git push origin feature/votre-fonctionnalite
   ```
6. Soumettez une pull request :
   * Créez une pull request depuis votre branche sur le repository original.

## Licence

Ce projet est sous la licence **MIT**. Pour plus de détails, consultez le fichier [LICENSE](./LICENSE).
