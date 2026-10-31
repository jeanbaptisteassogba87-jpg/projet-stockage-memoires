# Gestion des mémoires — UATM GASA Formation

Application PHP et MySQL pour le dépôt, la vérification, la publication et la consultation des mémoires.

## Parcours

- Les étudiants de L3 et M2 déposent et suivent leurs mémoires.
- Un professeur vérifie le document et peut le valider ou demander des corrections.
- Après la soutenance, le directeur enregistre la note finale communiquée par le jury. La mention est calculée à partir de la note.
- Le directeur gère ensuite la publication. Les visiteurs peuvent consulter les mémoires publiés sans compte.

Le barème initial est le suivant : moins de 10, Ajourné ; de 10 à moins de 12, Passable ; de 12 à moins de 14, Assez bien ; de 14 à moins de 16, Bien ; de 16 à 20, Très bien. Les seuils sont définis dans `models/MentionJury.php`.

## Installation locale

1. Créer le schéma et les données de démonstration :

   ```bash
   mysql -u root -p < sql/database.sql
   mysql -u root -p gestion_memoires < sql/data_test.sql
   ```

   Les données de démonstration créent quatre comptes et un mémoire fictif avec son PDF. À utiliser sur une base de développement vide.

2. Créer `config/database.local.php` avec les accès MySQL locaux. Ce fichier est ignoré par Git :

   ```php
   <?php
   return [
       'host' => '127.0.0.1',
       'dbname' => 'gestion_memoires',
       'username' => 'root',
       'password' => '',
       // 'port' => 3306, // facultatif
   ];
   ```

3. Lancer le serveur depuis la racine du projet :

   ```bash
   php -S 127.0.0.1:8000 router.php
   ```

   Ouvrir <http://127.0.0.1:8000>. L’accueil permet d’explorer la bibliothèque en visiteur.

### Comptes de démonstration

Mot de passe : `password`

| Rôle | Adresse |
| --- | --- |
| Technicien | `tech@uatm.bj` |
| Directeur | `directeur@uatm.bj` |
| Professeur | `prof@uatm.bj` |
| Étudiant | `etud@uatm.bj` |

## Base existante

Pour une base créée avec une ancienne version du schéma, appliquer une seule fois la migration :

```bash
mysql -u root -p gestion_memoires < sql/ajout_note_jury.sql
```

Le schéma actuel contient déjà les champs de binôme ; ne pas rejouer `sql/ajout_binome_memoire.sql` dessus.

## Configuration d’hébergement

La connexion accepte `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` et `DB_PASSWORD`. Les secrets restent dans les variables privées de l’hébergeur ou dans le fichier local ignoré par Git.

Le dépôt ne contient pas encore le paquetage de déploiement Vercel. L’application écrit les PDF dans `uploads/memoires/` et utilise les sessions PHP locales ; il faudra connecter un stockage persistant pour les fichiers et les sessions avant de mettre le service en production.

## Tests existants

```bash
composer install
./vendor/bin/phpunit tests/
```
