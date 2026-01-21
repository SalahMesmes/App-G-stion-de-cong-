-- ============================================
-- DONNÉES DE TEST - GESTION DES CONGÉS
-- BTS SIO SLAM
-- ============================================

USE gestion_conges;

-- Types de congés par défaut
INSERT INTO type_conge (code, libelle, justificatif_obligatoire, actif) VALUES
('CP', 'Congés Payés', 0, 1),
('RTT', 'Récupération du Temps de Travail', 0, 1),
('MALADIE', 'Arrêt Maladie', 1, 1),
('MATERNITE', 'Congé Maternité', 1, 1),
('PATERNITE', 'Congé Paternité', 1, 1),
('EXCEPTIONNEL', 'Congé Exceptionnel', 0, 1);

-- Manager de test
-- Email: manager@test.com
-- Mot de passe: Manager123!
INSERT INTO utilisateur (nom, prenom, email, service, poste, date_embauche, role, mot_de_passe) VALUES
('Dupont', 'Jean', 'manager@test.com', 'Direction', 'Manager RH', '2020-01-15', 'MANAGER', '$2y$10$cab0GCQzQUC7GsfaLydwV.MJyDvgFih01GQFK0yUH9ARPzEWmegwS');

-- Employé de test
-- Email: employe@test.com
-- Mot de passe: Employe123!
INSERT INTO utilisateur (nom, prenom, email, service, poste, date_embauche, role, mot_de_passe) VALUES
('Martin', 'Sophie', 'employe@test.com', 'Informatique', 'Développeur', '2023-06-01', 'EMPLOYE', '$2y$10$tjon3LunRGfhoMsYUyW/oOqHaQNYbXvP3q/oJepMvxDamN4PopEnK');
