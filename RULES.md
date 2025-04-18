# Règles du Projet de Reporting Bancaire

## Règles Techniques

### 1. Architecture
- Utilisation de Laravel 12 comme framework backend
- Frontend avec React et Inertia.js
- Interface utilisateur avec TailwindCSS et Material-UI
- Base de données MySQL pour le stockage des données

### 2. Sécurité
- Authentification obligatoire pour toutes les routes
- Validation stricte des fichiers importés
- Chiffrement des données sensibles
- Journalisation des actions importantes
- Respect des normes de sécurité bancaire

### 3. Gestion des Fichiers
- Formats acceptés : Excel, CSV (spécifiques à la Banque Centrale)
- Validation des structures de fichiers avant import
- Stockage sécurisé des fichiers importés
- Archivage des fichiers transmis

### 4. API et Transmission
- Utilisation des endpoints Swagger fournis par la Banque Centrale
- Gestion des erreurs de transmission
- Système de retry en cas d'échec
- Vérification des statuts de transmission

### 5. Interface Utilisateur
- Design responsive et adaptatif
- Messages d'erreur clairs et explicites
- Indicateurs de progression pour les opérations longues
- Confirmation avant les actions importantes

## Règles Métier

### 1. Import des Fichiers
- Validation des formats de fichiers balance et SITU
- Vérification des données obligatoires
- Calcul automatique des totaux et vérifications
- Alertes en cas d'anomalies détectées

### 2. Transmission
- Vérification de la complétude des données avant transmission
- Génération des fichiers au format requis par la Banque Centrale
- Suivi des statuts de transmission
- Gestion des rejets et corrections

### 3. Reporting
- Génération de rapports au format PDF
- Historique des transmissions
- Statistiques et tableaux de bord
- Export des données au format demandé

### 4. Gestion des Utilisateurs
- Rôles distincts (admin, utilisateur, etc.)
- Droits d'accès spécifiques
- Journalisation des actions
- Gestion des sessions

## Règles de Développement
- Utilisation de Git pour le versionnement
- Documentation du code
- Tests unitaires et fonctionnels
- Revue de code avant intégration
- Respect des standards PSR 