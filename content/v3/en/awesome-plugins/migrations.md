# Migrations

A migration for your project is keeping track of all the database changes involved in your project.
[byjg/php-migration](https://github.com/byjg/php-migration) is a really helpful core library to
get you started.

## Installing

### PHP Library

If you want to use only the PHP Library in your project:

```bash
composer require "byjg/migration"
```

### Command Line Interface

The command line interface is standalone and does not require you install with your project.

You can install global and create a symbolic lynk

```bash
composer require "byjg/migration-cli"
```

Please visit [byjg/migration-cli](https://github.com/byjg/migration-cli) to get more informations about Migration CLI.

## Supported databases

| Database      | Driver                                                                          | Connection String                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## How It Works?

The Database Migration uses PURE SQL to manage the database versioning.
In order to get working you need to:

* Create the SQL Scripts
* Manage using Command Line or the API.

### The SQL Scripts

The scripts are divided in three set of scripts:

* The BASE script contains ALL sql commands for create a fresh database;
* The UP scripts contain all sql migration commands for "up" the database version;
* The DOWN scripts contain all sql migration commands for "down" or revert the database version;

The directory scripts is :

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

* "base.sql" is the base script
* "up" folder contains the scripts for migrate up the version.
   For example: 00002.sql is the script for move the database from version '1' to '2'.
* "down" folder contains the scripts for migrate down the version.
   For example: 00001.sql is the script for move the database from version '2' to '1'.
   The "down" folder is optional.

### Multi Development environment

If you work with multiple developers and multiple branches it is to difficult to determine what is the next number.

In that case you have the suffix "-dev" after the version number.

See the scenario:

* Developer 1 create a branch and the most recent version in e.g. 42.
* Developer 2 create a branch at the same time and have the same database version number.

In both case the developers will create a file called 43-dev.sql. Both developers will migrate UP and DOWN with
no problem and your local version will be 43.

But developer 1 merged your changes and created a final version 43.sql (`git mv 43-dev.sql 43.sql`). If the developer 2
update your local branch he will have a file 43.sql (from dev 1) and your file 43-dev.sql.
If he is try to migrate UP or DOWN
the migration script will down and alert him there a TWO versions 43. In that case, developer 2 will have to update your
file do 44-dev.sql and continue to work until merge your changes and generate a final version.

## Using the PHP API and Integrate it into your projects

The basic usage is

* Create a connection a ConnectionManagement object. For more information see the "byjg/anydataset" component
* Create a Migration object with this connection and the folder where the scripts sql are located.
* Use the proper command for "reset", "up" or "down" the migrations scripts.

See an example:

```php
<?php
// Create the Connection URI
// See more: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Register the Database or Databases can handle that URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Create the Migration instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Add a callback progress function to receive info from the execution
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Restore the database using the "base.sql" script
// and run ALL existing scripts for up the database version to the latest version
$migration->reset();

// Run ALL existing scripts for up or down the database version
// from the current version until the $version number;
// If the version number is not specified migrate until the last database version
$migration->update($version = null);
```

The Migration object controls the database version.

### Creating a version control in your project

```php
<?php
// Register the Database or Databases can handle that URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Create the Migration instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// This command will create the version table in your database
$migration->createVersion();
```

### Getting the current version

```php
<?php
$migration->getCurrentVersion();
```

### Add Callback to control the progress

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Doing Command: $command at version $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Getting the Db Driver instance

```php
<?php
$migration->getDbDriver();
```

To use it, please visit: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Avoiding Partial Migration (not available for MySQL)

A partial migration is when the migration script is interrupted in the middle of the process due to an error or a manual interruption.

The migration table will be with the status `partial up` or `partial down` and it needs to be fixed manually before be able to migrate again. 

To avoid this situation you can specify the migration will be run in a transactional context. 
If the migration script fails, the transaction will be rolled back and the migration table will be marked as `complete` and 
the version will be the immediately previous version before the script that causes the error.

To enable this feature you need to call the method `withTransactionEnabled` passing `true` as parameter:

```php
<?php
$migration->withTransactionEnabled(true);
```

**NOTE: This feature isn't available for MySQL as it doesn't support DDL commands inside a transaction.**
If you use this method with MySQL the Migration will ignore it silently. 
More info: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Tips on writing SQL migrations for Postgres

### On creating triggers and SQL functions

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Check that empname and salary are given
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- it doesn't matter if these comments are blank or not
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- Who works for us when they must pay for it?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- Remember who changed the payroll when
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Check that empname and salary are given
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- Who works for us when they must pay for it?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- Remember who changed the payroll when
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Since the `PDO` database abstraction layer cannot run batches of SQL statements,
when `byjg/migration` reads a migration file it has to split up the whole contents of the SQL
file at the semicolons, and run the statements one by one. However, there is one kind of
statement that can have multiple semicolons in-between its body: functions.

In order to be able to parse functions correctly, `byjg/migration` 2.1.0 started splitting migration
files at the `semicolon + EOL` sequence instead of just the semicolon. This way, if you append an empty
comment after every inner semicolon of a function definition `byjg/migration` will be able to parse it.

Unfortunately, if you forget to add any of these comments the library will split the `CREATE FUNCTION` statement in
multiple parts and the migration will fail.

### Avoid the colon character (`:`)

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

Since `PDO` uses the colon character to prefix named parameters in prepared statements, its use will trip it
up in other contexts.

For instance, PostgreSQL statements can use `::` to cast values between types. On the other hand `PDO` will
read this as an invalid named parameter in an invalid context and fail when it tries to run it.

The only way to fix this inconsistency is avoiding colons altogether (in this case, PostgreSQL also has an alternative
 syntax: `CAST(value AS type)`).

### Use an SQL editor

Finally, writing manual SQL migrations can be tiresome, but it is significantly easier if
you use an editor capable of understanding the SQL syntax, providing autocomplete,
introspecting your current database schema and/or autoformatting your code.

## Handling different migration inside one schema

If you need to create different migration scripts and version inside the same schema it is possible
but is too risky and I **do not** recommend at all.

To do this, you need to create different "migration tables" by passing the parameter to the constructor.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

For security reasons, this feature is not available at command line, but you can use the environment variable
`MIGRATION_VERSION` to store the name.

We really recommend do not use this feature. The recommendation is one migration for one schema.

## Running Unit tests

Basic unit tests can be running by:

```bash
vendor/bin/phpunit
```

## Running database tests

Run integration tests require you to have the databases up and running. We provided a basic `docker-compose.yml` and you
can use to start the databases for test.

### Running the databases

```bash
docker-compose up -d postgres mysql mssql
```

### Run the tests

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Optionally you can set the host and password used by the unit tests

```bash
export MYSQL_TEST_HOST=localhost     # defaults to localhost
export MYSQL_PASSWORD=newpassword    # use '.' if want have a null password
export PSQL_TEST_HOST=localhost      # defaults to localhost
export PSQL_PASSWORD=newpassword     # use '.' if want have a null password
export MSSQL_TEST_HOST=localhost     # defaults to localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # defaults to /tmp/test.db
```