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
