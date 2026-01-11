# AppChantier (Gestion des Chantiers)

Application web PHP (MVC) pour la gestion des chantiers : utilisateurs, chantiers, employés, matériaux, finances (dépenses/paiements) et rapports.

## Prérequis

- PHP 8.x (recommandé)
- Extensions PHP : `pdo` et `pdo_sqlite`
- Un serveur web (Apache via WAMP/XAMPP/Laragon)

## Installation en local (WAMP)

1. Clone le projet dans ton dossier web :

   - Exemple WAMP : `c:\wamp64\www\chantiers`

2. Démarre Apache (et PHP) via WAMP.

3. Accède à l’installateur pour créer la base SQLite :

   - `http://localhost/chantiers/install.php`

   Cela crée le fichier : `data/chantiers.db`.

4. Connecte-toi à l’application :

   - `http://localhost/chantiers/`

### Identifiants par défaut

Après installation (`install.php`), tu peux te connecter avec :

- Admin : `admin` / `admin123`
- Chef de chantier : `chef1` / `admin123`
- Comptable : `comptable` / `admin123`

## Structure (résumé)

- `index.php` : front controller (router)
- `controllers/` : contrôleurs
- `models/` : accès base de données (SQLite)
- `views/` : vues
- `data/` : base SQLite (`chantiers.db`)

## Utilisation

- Tableau de bord : `index.php?controller=dashboard&action=index`
- Chantiers : `index.php?controller=chantier&action=index`
- Employés : `index.php?controller=employe&action=index`
- Matériaux : `index.php?controller=materiau&action=index`
- Finances : `index.php?controller=finance&action=index`
- Rapports : `index.php?controller=rapport&action=index`

## Déploiement gratuit sur Render

Render peut héberger des applications PHP via **Docker**. Le plus simple est de :

1. Créer un repo GitHub (déjà fait) et pousser le code.
2. Sur Render : `dashboard.render.com` → **New** → **Web Service**
3. Connecter le repo GitHub.
4. Choisir un déploiement **Docker** (recommandé pour PHP).

### Important (SQLite)

L’application utilise SQLite : `data/chantiers.db`.

- Sur Render, le disque par défaut est **éphémère** : la DB peut être perdue lors d’un redéploiement/restart.
- Pour garder les données, il faut configurer un **Persistent Disk** sur Render et y stocker le dossier `data/`.

### Exemple de configuration Docker (à ajouter si besoin)

Si Render te demande un Dockerfile, tu peux en créer un à la racine (exemple) :

```dockerfile
FROM php:8.2-apache

RUN a2enmod rewrite \
 && docker-php-ext-install pdo pdo_sqlite

COPY . /var/www/html/

# Apache: autoriser .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
```

Ensuite, sur Render :

- **Environment** : `Docker`
- **Port** : `80`

### Première installation en production

Après déploiement, lance une fois :

- `https://TON-SERVICE.onrender.com/install.php`

Puis connecte-toi sur :

- `https://TON-SERVICE.onrender.com/`

## Notes

- Base URL locale attendue : `http://localhost/chantiers/`
- La monnaie dans l’application est affichée en **Franc Burundais (BIF/FBU)**.
