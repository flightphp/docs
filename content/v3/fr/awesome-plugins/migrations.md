# Migrations

Une migration pour votre projet suit toutes les modifications de la base de données impliquées dans votre projet. 
[byjg/php-migration](https://github.com/byjg/php-migration) est une bibliothèque centrale très utile pour vous aider à démarrer.

## Installing

### PHP Library

Si vous souhaitez utiliser uniquement la bibliothèque PHP dans votre projet :

```bash
composer require "byjg/migration"
```

### Command Line Interface

L'interface de ligne de commande est autonome et ne nécessite pas d'installation avec votre projet.

Vous pouvez l'installer globalement et créer un lien symbolique

```bash
composer require "byjg/migration-cli"
```

Veuillez visiter [byjg/migration-cli](https://github.com/byjg/migration-cli) pour obtenir plus d'informations sur Migration CLI.

## Bases de données prises en charge

| Base de données | Pilote                                                                          | Chaîne de connexion                                        |
| ---------------- | ------------------------------------------------------------------------------ | --------------------------------------------------------- |
| Sqlite          | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb   | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres        | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server      | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server      | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## Comment ça fonctionne ?

La migration de base de données utilise le SQL PUR pour gérer la version de la base de données. 
Pour faire fonctionner, vous devez :

* Créer les scripts SQL
* Gérer en utilisant la ligne de commande ou l'API.

### Les scripts SQL

Les scripts sont divisés en trois ensemble de scripts :

* Le script BASE contient TOUS les commandes SQL pour créer une base de données fraîche ;
* Les scripts UP contiennent toutes les commandes de migration SQL pour "augmenter" la version de la base de données ;
* Les scripts DOWN contiennent toutes les commandes de migration SQL pour "reculer" ou revenir à la version de la base de données ;

Le répertoire des scripts est :

```text
 <root dir>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
```

* "base.sql" est le script de base
* Le dossier "up" contient les scripts pour migrer vers la version supérieure.
   Par exemple : 00002.sql est le script pour faire passer la base de données de la version '1' à '2'.
* Le dossier "down" contient les scripts pour migrer vers la version inférieure.
   Par exemple : 00001.sql est le script pour faire passer la base de données de la version '2' à '1'.
   Le dossier "down" est optionnel.

### Environnement de développement multi

Si vous travaillez avec plusieurs développeurs et plusieurs branches, il est trop difficile de déterminer quel est le prochain numéro.

Dans ce cas, vous avez le suffixe "-dev" après le numéro de version.

Voyez le scénario :

* Le développeur 1 crée une branche et la version la plus récente est par exemple 42.
* Le développeur 2 crée une branche en même temps et a le même numéro de version de base de données.

Dans les deux cas, les développeurs créeront un fichier appelé 43-dev.sql. Les deux développeurs migreront VERS LE HAUT et VERS LE BAS sans problème et votre version locale sera 43.

Mais le développeur 1 a fusionné vos modifications et créé une version finale 43.sql (`git mv 43-dev.sql 43.sql`). Si le développeur 2 met à jour sa branche locale, il aura un fichier 43.sql (du dev 1) et son fichier 43-dev.sql. 
S'il essaie de migrer VERS LE HAUT ou VERS LE BAS, le script de migration ne pourra pas se faire et l'alertera qu'il y a DEUX versions 43. Dans ce cas, le développeur 2 devra mettre à jour son fichier pour faire 44-dev.sql et continuer à travailler jusqu'à ce que les modifications soient fusionnées et génèrent une version finale.

## Utiliser l'API PHP et l'intégrer dans vos projets

L'utilisation de base est

* Créer une connexion à un objet ConnectionManagement. Pour plus d'informations, voir le composant "byjg/anydataset"
* Créer un objet Migration avec cette connexion et le dossier où les scripts SQL sont situés.
* Utiliser la commande appropriée pour "réinitialiser", "augmenter" ou "reculer" les scripts de migration.

Voici un exemple :

```php
<?php
// Créer l'URI de connexion
// Voir plus : https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Enregistrer les bases de données ou les bases de données qui peuvent gérer cette URI :
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Créer l'instance Migration
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Ajouter une fonction de rappel de progression pour recevoir des informations sur l'exécution
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Restaurer la base de données en utilisant le script "base.sql"
// et exécuter TOUS les scripts existants pour augmenter la version de la base de données à la dernière version
$migration->reset();

// Exécuter TOUS les scripts existants pour augmenter ou diminuer la version de la base de données
// depuis la version actuelle jusqu'au numéro de version ; 
// Si le numéro de version n'est pas spécifié, effectuez une migration jusqu'à la dernière version de base de données
$migration->update($version = null);
```

L'objet Migration contrôle la version de la base de données.

### Création d'un contrôle de version dans votre projet

```php
<?php
// Enregistrer les bases de données ou les bases de données qui peuvent gérer cette URI :
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Créer l'instance Migration
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Cette commande va créer la table de version dans votre base de données
$migration->createVersion();
```

### Obtenir la version actuelle

```php
<?php
$migration->getCurrentVersion();
```

### Ajouter un rappel pour contrôler la progression

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Exécution de la commande : $command à la version $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Obtenir l'instance du pilote de la base de données

```php
<?php
$migration->getDbDriver();
```

Pour l'utiliser, veuillez visiter : [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Éviter la migration partielle (non disponible pour MySQL)

Une migration partielle se produit lorsque le script de migration est interrompu au milieu du processus en raison d'une erreur ou d'une interruption manuelle.

La table de migration sera avec le statut `partiel en cours` ou `partiel en bas` et doit être corrigée manuellement avant de pouvoir migrer à nouveau.

Pour éviter cette situation, vous pouvez spécifier que la migration sera exécutée dans un contexte transactionnel. 
Si le script de migration échoue, la transaction sera annulée et la table de migration sera marquée comme `complète` et 
la version sera la version immédiatement précédente avant le script qui a causé l'erreur.

Pour activer cette fonctionnalité, vous devez appeler la méthode `withTransactionEnabled` en passant `true` comme paramètre :

```php
<?php
$migration->withTransactionEnabled(true);
```

**REMARQUE : Cette fonctionnalité n'est pas disponible pour MySQL car elle ne prend pas en charge les commandes DDL à l'intérieur d'une transaction.** 
Si vous utilisez cette méthode avec MySQL, la migration l'ignorera silencieusement. 
Plus d'infos : [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Conseils pour écrire des migrations SQL pour Postgres

### À propos de la création de déclencheurs et de fonctions SQL

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Vérifier que empname et salary sont fournis
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname ne peut pas être nul'; -- peu importe que ces commentaires soient vides ou non
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% ne peut pas avoir de salaire nul', NEW.empname; --
        END IF; --

        -- Qui travaille pour nous quand ils doivent le payer ?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% ne peut pas avoir un salaire négatif', NEW.empname; --
        END IF; --

        -- Se souvenir de qui a changé la paie quand
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Vérifier que empname et salary sont fournis
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname ne peut pas être nul';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% ne peut pas avoir de salaire nul', NEW.empname;
        END IF;

        -- Qui travaille pour nous quand ils doivent le payer ?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% ne peut pas avoir un salaire négatif', NEW.empname;
        END IF;

        -- Se souvenir de qui a changé la paie quand
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Puisque le niveau d'abstraction de base de données `PDO` ne peut pas exécuter des lots d'instructions SQL,
lorsque `byjg/migration` lit un fichier de migration, il doit diviser tout le contenu du fichier SQL aux points-virgules et exécuter les instructions une par une. Cependant, il existe un type d'instruction qui peut avoir plusieurs points-virgules dans son corps : les fonctions.

Afin de pouvoir analyser les fonctions correctement, `byjg/migration` 2.1.0 a commencé à diviser les fichiers de migration à la séquence `point-virgule + EOL` au lieu de simplement le point-virgule. De cette façon, si vous ajoutez un commentaire vide après chaque point-virgule interne d'une définition de fonction, `byjg/migration` pourra l'analyser.

Malheureusement, si vous oubliez d'ajouter l'un de ces commentaires, la bibliothèque divisera l'instruction `CREATE FUNCTION` en plusieurs parties et la migration échouera.

### Éviter le caractère deux-points (`:`)

```sql
-- DO
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- DON'T
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Puisque `PDO` utilise le caractère deux-points pour préfixer les paramètres nommés dans les instructions préparées, son utilisation le perturbera dans d'autres contextes.

Par exemple, les instructions PostgreSQL peuvent utiliser `::` pour convertir des valeurs entre types. D'autre part, `PDO` lira cela comme un paramètre nommé invalide dans un contexte invalide et échouera lorsqu'il essaiera de l'exécuter.

Le seul moyen de corriger cette incohérence est d'éviter complètement l'utilisation de double points (dans ce cas, PostgreSQL à également une syntaxe alternative : `CAST(value AS type)`).

### Utiliser un éditeur SQL

Enfin, écrire des migrations SQL manuelles peut être épuisant, mais c'est beaucoup plus facile si 
vous utilisez un éditeur capable de comprendre la syntaxe SQL, d'offrir la saisie semi-automatique, d'explorer votre schéma de base de données actuel et/ou de reformater automatiquement votre code.

## Gérer différentes migrations dans un seul schéma

Si vous devez créer différents scripts de migration et des versions dans le même schéma, cela est possible mais trop risqué et je **ne** recommande **pas** du tout.

Pour ce faire, vous devez créer différentes "tables de migration" en passant le paramètre au constructeur.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Pour des raisons de sécurité, cette fonctionnalité n'est pas disponible en ligne de commande, mais vous pouvez utiliser la variable d'environnement 
`MIGRATION_VERSION` pour stocker le nom.

Nous recommandons vraiment de ne pas utiliser cette fonctionnalité. La recommandation est une migration pour un schéma.

## Exécution des tests unitaires

Des tests unitaires de base peuvent être exécutés par :

```bash
vendor/bin/phpunit
```

## Exécution des tests de base de données

Exécuter des tests d'intégration nécessite que vous ayez les bases de données en marche. Nous avons fourni un `docker-compose.yml` de base et vous 
pouvez l'utiliser pour démarrer les bases de données pour les tests.

### Exécution des bases de données

```bash
docker-compose up -d postgres mysql mssql
```

### Exécuter les tests

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

En option, vous pouvez définir l'hôte et le mot de passe utilisés par les tests unitaires

```bash
export MYSQL_TEST_HOST=localhost     # par défaut localhost
export MYSQL_PASSWORD=newpassword    # utilisez '.' si vous souhaitez avoir un mot de passe nul
export PSQL_TEST_HOST=localhost      # par défaut localhost
export PSQL_PASSWORD=newpassword     # utilisez '.' si vous souhaitez avoir un mot de passe nul
export MSSQL_TEST_HOST=localhost     # par défaut localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # par défaut /tmp/test.db
```