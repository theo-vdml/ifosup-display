# Logbook

À l'attention des professeurs, de la direction et de la pédagogie, dans le cadre du stage de BES Web Developer.

Ce document reprend, par date, les étapes réalisées sur le projet.

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
