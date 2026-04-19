# 📺 IFOSUP Display

Ce projet est un **Backoffice de gestion d'affichage dynamique** réalisé dans le cadre de mon **stage**.

Il permet de piloter à distance les informations diffusées sur les télévisions de l'école (annonces, plannings des cours et actualités).

### 🛠️ Technologies

- **Framework :** Laravel + Inertia.js (Vue.js 3)
- **Style :** Tailwind CSS
- **Package Manager :** pnpm

---

### 🚀 Installation rapide

1. **Installer les dépendances PHP :**

```bash
composer install

```

2. **Installer les dépendances JS :**

```bash
pnpm install
pnpm approve-builds

```

3. **Préparer l'environnement :**

- Copier le fichier `.env.example` en `.env`
- Configurer la base de données dans le `.env`
- Lancer : `php artisan key:generate` et `php artisan migrate`

support@ifosup.wavre.be
