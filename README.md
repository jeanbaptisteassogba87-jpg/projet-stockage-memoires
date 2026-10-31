📚 Système de Gestion des Mémoires — UATM GASA Formation

Application web permettant le dépôt, la validation, la publication et la consultation des mémoires de soutenance.

## 🛠️ Stack technique
- **Backend** : PHP 8+
- **Base de données** : MySQL
- **Frontend** : Bootstrap 5
- **Tests** : PHPUnit

## 🔐 Comptes de test
Mot de passe unique : uatm1234

### Technicien
- koffi@gmail.com

### Directeur des études
- amivi@gmail.com

### Professeurs
- gildas@gmail.com
- aissi@gmail.com
- fifame@gmail.com

### Étudiants diplomés (peuvent déposer)
- jean@gmail.com (L3, Informatique)
- cossi@gmail.com (M2, Informatique)
- roland@gmail.com (L3, Réseaux)
- fructueux@gmail.com (M2, Réseaux)
- arnaud@gmail.com (L3, Gestion)

### Commentateurs (consultation uniquement)
- esperance@gmail.com (L1, Informatique)
- mariatou@gmail.com (L2, Réseaux)
- carine@gmail.com (M1, Gestion)
- benedicte@gmail.com (L1, Informatique)
- serge@gmail.com (L2, Réseaux)

## 🧪 Lancer les tests
```bash
./vendor/bin/phpunit tests/
```

## 👀 Explorer le site sans compte

L'accueil ouvre la bibliothèque publique. Les visiteurs peuvent rechercher et consulter les mémoires publiés, y compris leur PDF. Les actions de dépôt, commentaire et like nécessitent un compte connecté.

En local, configure `config/database.php` avec les identifiants MySQL de ta machine, importe `sql/database.sql`, puis les données de démonstration avec `sql/data_test.sql`. Lance ensuite :

```bash
php -S 127.0.0.1:8000
```

Ouvre `http://127.0.0.1:8000` dans ton navigateur.

## 🚀 Déploiement Vercel

Le projet utilise PHP, MySQL et stocke les PDF dans `uploads/memoires/`. Un déploiement Vercel nécessite une base de données accessible depuis Vercel et un stockage persistant des PDF. Le stockage local du conteneur n'est pas durable. Il faut également un stockage de sessions partagé pour préserver les connexions entre instances. Préparer ces services et externaliser le stockage avant d'activer le déploiement continu GitHub.
