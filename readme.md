# Gestion de la Programmation d'une Salle de Spectacle

Ce projet permet de gérer la programmation d'une salle de spectacle. Il permet au gestionnaire de la salle de planifier des spectacles, consulter la programmation, générer des fichiers PDF pour la publicité, et bien plus. Le système propose également une gestion d'utilisateurs pour différencier les rôles (gestionnaire, secrétaire).

## Fonctionnalités

### 1. Planification des Spectacles
- **Ajouter un spectacle** : Planification d'un spectacle pour une date donnée (avec vérification qu'aucune autre programmation n'existe à cette date).
- **Déplacer un spectacle** : Possibilité de modifier les dates de programmation d'un spectacle déjà prévu.
- **Supprimer un spectacle** : Enlever un spectacle de la programmation.

### 2. Consultation de la Programmation
- **Vue d'ensemble de la programmation** : Permet de visualiser la programmation sur toute la saison.
- **Consultation par type de spectacle** : Affichage des spectacles regroupés par genre (théâtre, danse, musique, etc.).
- **Consultation des dates d'un spectacle** : Accès rapide à toutes les dates de programmation d'un spectacle spécifique.

### 3. Génération de Fichiers PDF
- **Fichier PDF individuel** : Génération d'un PDF pour un spectacle, avec les informations détaillées (titre, acteurs, dates, description).
- **Liste complète des spectacles** : Génération d'un PDF contenant tous les spectacles programmés, triés chronologiquement.

### 4. Gestion des Utilisateurs
- **Système d'authentification** : Accès sécurisé pour les gestionnaires et les secrétaires.
- **Gestion des rôles** : Les secrétaires peuvent ajouter, modifier et supprimer des spectacles, tandis que les gestionnaires ont un contrôle total sur la programmation.

## Prérequis

Avant de commencer, il faut s'assurer d'avoir les outils suivants installés :

- **PHP** : Ce projet nécessite PHP pour fonctionner.
- **Composer** : Un gestionnaire de dépendances pour PHP.

## Installation

### 1. Cloner le projet

Commencer par cloner ce repo avec la commande suivante :

```bash
git clone https://github.com/LA236333-Arifi/spectacle.git
```

Ensuite mettre à jour les dépendances avec la commande suivante :
```bash
composer update
```
