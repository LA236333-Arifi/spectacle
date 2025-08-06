CREATE TABLE Type_Spectacle(
   type_spectacle_id INT,
   nom_type_spectacle VARCHAR(50),
   PRIMARY KEY(type_spectacle_id)
);

CREATE TABLE Statut_Seance(
   statut_seance_id INT,
   nom_statut_seance VARCHAR(50),
   PRIMARY KEY(statut_seance_id)
);

CREATE TABLE Groupe_Spectacle(
   groupe_id INT,
   nom_groupe VARCHAR(50),
   date_formation_groupe DATE,
   PRIMARY KEY(groupe_id)
);

CREATE TABLE Role_Performeur(
   role_performeur_id INT,
   nom_role_performeur VARCHAR(50),
   PRIMARY KEY(role_performeur_id)
);

CREATE TABLE Role_Utilisateur(
   role_utilisateur_id INT,
   nom_role_utilisateur VARCHAR(50),
   PRIMARY KEY(role_utilisateur_id)
);

CREATE TABLE Statut_Spectacle(
   statut_spectacle_id INT,
   nom_statut_spectacle VARCHAR(50),
   PRIMARY KEY(statut_spectacle_id)
);

CREATE TABLE Statut_Utilisateur(
   statut_utilisateur_id INT,
   nom_statut_utilisateur VARCHAR(50),
   PRIMARY KEY(statut_utilisateur_id)
);

CREATE TABLE Performeur_Spectacle(
   performeur_id INT,
   nom_performeur VARCHAR(50),
   prenom_performeur VARCHAR(50),
   role_performeur_id INT NOT NULL,
   PRIMARY KEY(performeur_id),
   FOREIGN KEY(role_performeur_id) REFERENCES Role_Performeur(role_performeur_id)
);

CREATE TABLE Utilisateur(
   utilisateur_id INT,
   nom_utilisateur VARCHAR(50),
   prenom_utilisateur VARCHAR(50),
   mail_utilisateur VARCHAR(50) NOT NULL,
   mdp_utilisateur VARCHAR(128),
   valide_utilisateur LOGICAL,
   token_utilisateur VARCHAR(64),
   date_exp_token_utilisateur DATETIME,
   statut_utilisateur_id INT NOT NULL,
   role_utilisateur_id INT NOT NULL,
   PRIMARY KEY(utilisateur_id),
   UNIQUE(mail_utilisateur),
   FOREIGN KEY(statut_utilisateur_id) REFERENCES Statut_Utilisateur(statut_utilisateur_id),
   FOREIGN KEY(role_utilisateur_id) REFERENCES Role_Utilisateur(role_utilisateur_id)
);

CREATE TABLE Spectacle(
   spectacle_id INT,
   nom_spectacle VARCHAR(50),
   texte_accroche_spectacle VARCHAR(50),
   prix_spectacle DECIMAL(10,2),
   duree_minutes_spectacle REAL,
   date_creation_spectacle DATE,
   derniere_date_modification_spectacle DATETIME,
   date_cloture_spectacle DATETIME,
   statut_spectacle_id INT NOT NULL,
   utilisateur_id INT NOT NULL,
   groupe_id INT NOT NULL,
   type_spectacle_id INT NOT NULL,
   PRIMARY KEY(spectacle_id),
   FOREIGN KEY(statut_spectacle_id) REFERENCES Statut_Spectacle(statut_spectacle_id),
   FOREIGN KEY(utilisateur_id) REFERENCES Utilisateur(utilisateur_id),
   FOREIGN KEY(groupe_id) REFERENCES Groupe_Spectacle(groupe_id),
   FOREIGN KEY(type_spectacle_id) REFERENCES Type_Spectacle(type_spectacle_id)
);

CREATE TABLE Seance(
   seance_id INT,
   date_soiree_seance DATE,
   date_ajout_seance DATE,
   utilisateur_id INT NOT NULL,
   statut_seance_id INT NOT NULL,
   spectacle_id INT NOT NULL,
   PRIMARY KEY(seance_id),
   FOREIGN KEY(utilisateur_id) REFERENCES Utilisateur(utilisateur_id),
   FOREIGN KEY(statut_seance_id) REFERENCES Statut_Seance(statut_seance_id),
   FOREIGN KEY(spectacle_id) REFERENCES Spectacle(spectacle_id)
);

CREATE TABLE Auteur_Spectacle(
   auteur_id INT,
   nom_auteur VARCHAR(50),
   nom_metteur_en_scene VARCHAR(50),
   spectacle_id INT NOT NULL,
   PRIMARY KEY(auteur_id),
   UNIQUE(spectacle_id),
   FOREIGN KEY(spectacle_id) REFERENCES Spectacle(spectacle_id)
);

CREATE TABLE Liaison_Groupe(
   groupe_id INT,
   performeur_id INT,
   PRIMARY KEY(groupe_id, performeur_id),
   FOREIGN KEY(groupe_id) REFERENCES Groupe_Spectacle(groupe_id),
   FOREIGN KEY(performeur_id) REFERENCES Performeur_Spectacle(performeur_id)
);

-- Statuts de séance
INSERT INTO Statut_Seance (statut_seance_id, nom_statut_seance) VALUES
(1, 'Planifié'),
(2, 'Annulé');

-- Types de spectacle
INSERT INTO Type_Spectacle (type_spectacle_id, nom_type_spectacle) VALUES
(1, 'Theatre'),
(2, 'Concert Rock'),
(3, 'Concert Classique'),
(4, 'Humouriste'),
(5, 'Danse');

-- Statuts de spectacle
INSERT INTO Statut_Spectacle (statut_spectacle_id, nom_statut_spectacle) VALUES
(1, 'Sans séance'),
(2, 'En cours'),
(3, 'Cloturé');

-- Rôles de performeurs
INSERT INTO Role_Performeur (role_performeur_id, nom_role_performeur) VALUES
(1, 'Danseur'),
(2, 'Humouriste'),
(3, 'Acteur'),
(4, 'Chanteur'),
(5, 'Musicien');

-- Rôles utilisateurs
INSERT INTO Role_Utilisateur (role_utilisateur_id, nom_role_utilisateur) VALUES
(1, 'Gérant'),
(2, 'Secrétaire');

-- Statuts utilisateurs
INSERT INTO Statut_Utilisateur (statut_utilisateur_id, nom_statut_utilisateur) VALUES
(1, 'Non validé et inactif'),
(2, 'Validé et inactif'),
(3, 'Validé et actif');
