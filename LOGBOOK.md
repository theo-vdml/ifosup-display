# Logbook

À l'attention des professeurs, de la direction et de la pédagogie, dans le cadre du stage de BES Web Developer.

Ce document reprend, par date, les étapes réalisées sur le projet.

Totaux:

- présentiel : 28h
- distancuel : 16h

---

## 2 mars 2026

_Présentiel, 8h_

### Base de données

- Configuration de l'environnement avec mise à jour de `.env.example`.
- Création du modèle `Teacher` et de sa migration.
- Création du modèle `Course` et de sa migration.
- Création du modèle `Room` et de sa migration.
- Ajustement de la migration des salles.
- Création du modèle `Group` et de sa migration.
- Mise en place de la table pivot `course_group` et des relations entre cours et groupes.
- Création du modèle `Schedule` et de sa migration.
- Fusion de la branche `feat/database-setup` dans `development`.

### Interfaces de gestion

- Mise en place du CRUD des enseignants : contrôleur, validations, vues, types et routes.
- Mise en place du CRUD des salles.
- Mise en place du CRUD des groupes.

---

## 4 mars 2026

_Distanciel, 4h_

### Refactorisation front-end

- Création d'un layout partagé pour les pages de détail avec `ResourceShowLayout`.
- Création d'un layout partagé pour les listes avec `ResourceIndexLayout`.

- Création d'un layout partagé pour les formulaires avec `ResourceFormLayout`.

### Gestion des cours

- Ajout du CRUD des cours : vues, routes, types et navigation.
- Ajout d'une combobox basée sur Headless UI pour les formulaires.

---

## 11 mars 2026

_Présentiel, 4h_

### Améliorations

- Refonte du composant combobox pour une version plus complète.
- Ajout de la sélection des enseignants et des groupes dans les formulaires de cours.
- Amélioration de la vue détail des enseignants.
- Amélioration des vues détail des salles et des groupes.
- Correction de la sélection par défaut dans la combobox avec la propriété `by`.
- Amélioration de la vue détail des cours.
- Fusion de la branche `feat/resource-management` dans `development`.

---

## 12 mars 2026

_Présentiel, 4h_

### Logbook

- Création de ce logbook
- Publication du logbook sur github

### Planning

- Création d'un composant de planning en tableau a deux entrées (date/periodes et locaux)
- Affichage d'attributions "dummy" dans le scheduler
- Ajout de la possibilitée de déplacer un cours vers un autre local / une autre date avec un glissé/déposé

---

## 13 mars 2026

_Présentiel, 4h_

### Planning

- Amélioration du systeme de thème dynamique dans le component Scheduler
- Refactor du Scheduler pour utiliser les props plutôt que des valeurs d'example
- Préparation de l'intégration du Scheduler avec la base de donnée
- Recherches sur la meilleur aproche pour la gestion des attributions

---

## 16 mars 2026

_Distanciel, 8h_

### Développement du composant Planning

- Refactoring technique : Extraction des logiques de navigation (scroll, zoom, plein écran) vers des composables dédiés afin d'alléger le composant et d'améliorer la réutilisabilité du code.
- Architecture de données : Conception du modèle de données et création des migrations nécessaires pour la gestion des Assignments
- Intégration Backend : Mise à jour du Scheduler pour assurer le passage de données dynamiques issues de la base de données via les props.
- Environnement de test : Développement de factories et de seeders pour générer des jeux de données de test réalistes et faciliter les phases de recette.

---

## 17 mars 2026

_Distanciel, 4h_

### Développement du composant Planning

- Répartition des tâches entre plusieurs composants pour éviter la répétition de code
- Préparation eux opération CRUD (ajouter un cours, déplacer un cours, ...)

---

## 18 mars 2026

_Présentiel, 8h_

### Développement du composant Planning

- Ajout de la possibilité d'ajouter un cours dans le planning
- Possibilité de chercher un cours dans la bibliothèque de cours
- Synchornisation des ajouts/déplacement de cours avec la base de données
- Ajout d'un DateRangePicker pour définir la periode a afficher dans le planning
- Utilisation de cookies pour garder en mémoire les préférences (zoom, ...) de l'utilisateur sur le planning
