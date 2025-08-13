CREATE TABLE Type_Spectacle(
    type_spectacle_id INT AUTO_INCREMENT, 
    nom_type_spectacle VARCHAR(50),
    PRIMARY KEY(type_spectacle_id)
);

CREATE TABLE Statut_Seance(
    statut_seance_id INT AUTO_INCREMENT, 
    nom_statut_seance VARCHAR(50),
    PRIMARY KEY(statut_seance_id)
);

CREATE TABLE Groupe_Spectacle(
    groupe_id INT AUTO_INCREMENT, 
    nom_groupe VARCHAR(50),
    date_formation_groupe DATE,
    PRIMARY KEY(groupe_id)
);

CREATE TABLE Role_Performeur(
    role_performeur_id INT AUTO_INCREMENT, 
    nom_role_performeur VARCHAR(50),
    PRIMARY KEY(role_performeur_id)
);

CREATE TABLE Role_Utilisateur(
    role_utilisateur_id INT AUTO_INCREMENT, 
    nom_role_utilisateur VARCHAR(50),
    PRIMARY KEY(role_utilisateur_id)
);

CREATE TABLE Statut_Spectacle(
    statut_spectacle_id INT AUTO_INCREMENT, 
    nom_statut_spectacle VARCHAR(50),
    PRIMARY KEY(statut_spectacle_id)
);

CREATE TABLE Statut_Utilisateur(
    statut_utilisateur_id INT AUTO_INCREMENT, 
    nom_statut_utilisateur VARCHAR(50),
    PRIMARY KEY(statut_utilisateur_id)
);

CREATE TABLE Auteur_Spectacle(
    auteur_id INT AUTO_INCREMENT, 
    nom_auteur VARCHAR(50),
    prenom_auteur VARCHAR(50),
    PRIMARY KEY(auteur_id)
);

CREATE TABLE MetteurScene_Spectacle(
    metteur_scene_id INT AUTO_INCREMENT, 
    nom_metteur_scene VARCHAR(50),
    prenom_metteur_scene VARCHAR(50),
    PRIMARY KEY(metteur_scene_id)
);

CREATE TABLE Performeur_Spectacle(
    performeur_id INT AUTO_INCREMENT, 
    nom_performeur VARCHAR(50),
    prenom_performeur VARCHAR(50),
    role_performeur_id INT NOT NULL,
    PRIMARY KEY(performeur_id),
    FOREIGN KEY(role_performeur_id) REFERENCES Role_Performeur(role_performeur_id)
);

CREATE TABLE Utilisateur(
    utilisateur_id INT AUTO_INCREMENT, 
    nom_utilisateur VARCHAR(50),
    prenom_utilisateur VARCHAR(50),
    mail_utilisateur VARCHAR(50) NOT NULL,
    mdp_utilisateur VARCHAR(128),
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
    spectacle_id INT AUTO_INCREMENT,
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
    seance_id INT AUTO_INCREMENT,
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

CREATE TABLE Auteur_MetteurScene_Spectacle(
    auteur_metteur_scene_id INT AUTO_INCREMENT, 
    auteur_id INT NOT NULL,
    metteur_scene_id INT NOT NULL,
    spectacle_id INT NOT NULL,
    PRIMARY KEY(auteur_metteur_scene_id),
    UNIQUE(spectacle_id),
    FOREIGN KEY(auteur_id) REFERENCES Auteur_Spectacle(auteur_id),
    FOREIGN KEY(metteur_scene_id) REFERENCES MetteurScene_Spectacle(metteur_scene_id),
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
INSERT INTO Statut_Seance (nom_statut_seance) VALUES ('Planifié'), ('Annulé');

-- Types de spectacle
INSERT INTO Type_Spectacle (nom_type_spectacle) VALUES ('Theatre'), ('Concert Rock'), ('Concert Classique'), ('Humouriste'), ('Danse');

-- Statuts de spectacle
INSERT INTO Statut_Spectacle (nom_statut_spectacle) VALUES ('Sans séance'), ('En cours'), ('Cloturé');

-- Rôles de performeurs
INSERT INTO Role_Performeur (nom_role_performeur) VALUES ('Danseur'), ('Humouriste'), ('Acteur'), ('Chanteur'), ('Musicien');

-- Rôles utilisateurs
INSERT INTO Role_Utilisateur (nom_role_utilisateur) VALUES ('Gérant'), ('Secrétaire');

-- Statuts utilisateurs
INSERT INTO Statut_Utilisateur (nom_statut_utilisateur) VALUES ('Non validé et inactif'), ('Validé et inactif'), ('Validé et actif');
