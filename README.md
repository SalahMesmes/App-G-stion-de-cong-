# Application de Gestion des Congés
Comment lancer l'application

- Installer les dépendances Composer :
  ```bash
  cd "/Applications/MAMP/htdocs/App-Géstion de congé"
  composer install
  ```

- Démarrer MAMP (Apache + MySQL)

- Créer la base de données :
  - Ouvrir phpMyAdmin : http://localhost:8888/phpMyAdmin
  - Importer `database/schema.sql`
  - Importer `database/seed.sql`

- Accéder à l'application :
  ```
  http://localhost:8888/App-Géstion%20de%20congé/public/
  ```
Comptes de test

- **Manager** : `manager@test.com` / `Manager123!`
- **Employé** : `employe@test.com` / `Employe123!`

# Présentation de l’application

Cette application web de gestion des congés a été développée en PHP (architecture MVC) avec Twig pour les vues et MySQL pour la base de données.
Elle permet aux employés de déposer leurs demandes de congés et aux managers de les gérer et de les valider via une interface sécurisée et intuitive.

L’application distingue clairement les rôles EMPLOYÉ et MANAGER, chacun disposant de fonctionnalités adaptées.
La sécurité est assurée par une authentification par session, le hashage des mots de passe

# Fonctionnement général

- Côté employé

L’employé peut se connecter à son espace personnel et accéder à un tableau de bord récapitulant ses demandes de congés (en attente, acceptées, refusées).
Il peut créer une demande en choisissant le type de congé, les dates, une éventuelle demi-journée, ajouter un motif, un commentaire et, si nécessaire, un justificatif.
Le nombre de jours ouvrés est calculé automatiquement (du lundi au vendredi) et les chevauchements de dates sont bloqués.
Une demande en attente peut être annulée par l’employé.

- Côté manager

Le manager dispose d’un espace de gestion lui permettant de visualiser toutes les demandes, notamment celles en attente de validation.
Il peut accepter ou refuser une demande, en ajoutant un commentaire.
Il gère également les employés (création, modification, suppression) ainsi que les types de congés (activation, justificatif obligatoire, libellé).

