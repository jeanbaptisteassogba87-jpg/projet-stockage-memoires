# Système de Gestion des Mémoires — UATM GASA Formation

## Stack technique
- PHP 8+ (orienté objet, sans framework)
- MySQL 8 / MariaDB
- Bootstrap 5
- Vanilla JS + PDF.js
- PHPUnit 10

## Architecture
```
MVC + DAO
View → Controller → Model → DAO → BDD
```

## Installation locale

```bash
# 1. Cloner le dépôt
git clone https://github.com/VOTRE_REPO/projet_gestion_memoires.git
cd projet_gestion_memoires

# 2. Installer PHPUnit
composer install

# 3. Créer la base de données
mysql -u root -p < sql/database.sql
mysql -u root -p gestion_memoires < sql/data_test.sql

# 4. Configurer la connexion BDD
# Éditer config/database.php avec vos identifiants

# 5. Lancer avec PHP built-in (dev uniquement)
php -S localhost:8000
```

## Comptes de test (mdp : password123)
| Rôle        | Email               |
|-------------|---------------------|
| Technicien  | tech@uatm.bj        |
| Directeur   | directeur@uatm.bj   |
| Professeur  | prof@uatm.bj        |
| Étudiant L3 | etud@uatm.bj        |

## Lancer les tests
```bash
./vendor/bin/phpunit tests/
```

## Branches Git
| Membre   | Branche              | Responsabilité                        |
|----------|----------------------|---------------------------------------|
| Membre 1 | membre1/auth         | Auth, comptes, import CSV             |
| Membre 2 | membre2/depot        | Dépôt, modification, voir remarques   |
| Membre 3 | membre3/consultation | Recherche, consultation, likes        |
| Membre 4 | membre4/professeur   | Vérification, remarques, validation   |
| Membre 5 | membre5/directeur    | Visibilité, mise en ligne             |
| Membre 6 | membre6/integration  | Tests, déploiement, rapport           |

## Statuts d'un mémoire
```
en_attente → en_verification → valide → publie
                            ↘ rejete → (étudiant modifie) → en_attente
publie ↔ non_public  (géré par le Directeur des études)
```
