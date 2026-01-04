# 🎵 Projet Communauté Musicale

Plateforme de partage de musique communautaire développée en PHP. Ce projet permet aux utilisateurs de publier leurs créations MP3, de découvrir celles des autres, de les noter et de les commenter.

link site: https://michael.rousseau.13h37.io/

## 🚀 Fonctionnalités

### Espace Membre (Front-end)
- **Authentification :** Création de compte avec validation par email, connexion sécurisée.
- **Gestion de profil :** Modification de l'avatar et des informations personnelles.
- **Gestion des MP3 :**
  - Upload de fichiers MP3.
  - Édition des informations (titre, description).
  - Suppression et gestion de la visibilité (public/privé).
- **Interactions Sociales :**
  - Écoute des morceaux via un lecteur audio intégré.
  - Système de commentaires sur les pages musique.
  - Système de notation (1 à 5 étoiles).
  - Partage de liens.

### Administration (Back-end)
- **Modération :**
  - Gestion des utilisateurs (bannissement, rôles).
  - Modération des commentaires (masquer/supprimer les contenus inappropriés).

## 🛠️ Stack Technique

- **Langage Server :** PHP 8+
- **Base de données :** MySQL / MariaDB
- **Front-end :** HTML5, CSS3

-> add sum of comments/ sum of rating/ number of listening

## 📂 Structure de la Base de Données

Le projet repose sur 4 tables principales (voir `database.sql`) :

1.  **`users`** : Stocke les infos de connexion, le rôle (admin/user) et le token de validation.
2.  **`musics`** : Contient les métadonnées des MP3 et le lien vers le fichier physique.
3.  **`comments`** : Les commentaires liés à un utilisateur et une musique.
4.  **`ratings`** : Les notes de 1 à 5 attribuées aux musiques.

## ⚙️ Installation (Local)

1.  Cloner le dépôt :
    ```bash
    git clone [https://github.com/Michael-Rousseau/music_community](git@github.com:Michael-Rousseau/music_community.git)
    ```
2.  Importer la base de données :
    - Ouvrir PhpMyAdmin ou un terminal MySQL.
    - Créer une nouvelle BDD nommée `music_community`.
    - Importer le fichier `database.sql`.
3.  Configurer la connexion :
    - Copier le fichier `config.example.php` vers `config.php` (à créer) et entrer vos identifiants BDD.
4.  Lancer le serveur local (via XAMPP/WAMP ou CLI) :
    ```bash
    php -S localhost:8000
    ```

