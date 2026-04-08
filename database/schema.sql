CREATE DATABASE IF NOT EXISTS gestion_conges CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_conges;

CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    service VARCHAR(100) NOT NULL,
    poste VARCHAR(100) NOT NULL,
    date_embauche DATE NOT NULL,
    role ENUM('EMPLOYE', 'MANAGER') NOT NULL DEFAULT 'EMPLOYE',
    mot_de_passe VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS type_conge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(255) NOT NULL,
    justificatif_obligatoire BOOLEAN NOT NULL DEFAULT 0,
    actif BOOLEAN NOT NULL DEFAULT 1,
    INDEX idx_code (code),
    INDEX idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS demande_conge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type_conge_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    demi_journee ENUM('NONE', 'AM', 'PM') NOT NULL DEFAULT 'NONE',
    motif TEXT,
    commentaire TEXT,
    justificatif VARCHAR(255) NULL,
    statut ENUM('EN_ATTENTE', 'ACCEPTE', 'REFUSE', 'ANNULE') NOT NULL DEFAULT 'EN_ATTENTE',
    jours_ouvres DECIMAL(4,1) NOT NULL DEFAULT 0,
    decision_par INT NULL,
    decision_date DATETIME NULL,
    commentaire_decision TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (type_conge_id) REFERENCES type_conge(id) ON DELETE RESTRICT,
    FOREIGN KEY (decision_par) REFERENCES utilisateur(id) ON DELETE SET NULL,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_statut (statut),
    INDEX idx_dates (date_debut, date_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO type_conge (code, libelle, justificatif_obligatoire, actif) VALUES
('CP', 'Congés Payés', 0, 1),
('RTT', 'Récupération du Temps de Travail', 0, 1),
('MALADIE', 'Arrêt Maladie', 1, 1),
('MATERNITE', 'Congé Maternité', 1, 1),
('PATERNITE', 'Congé Paternité', 1, 1),
('EXCEPTIONNEL', 'Congé Exceptionnel', 0, 1);