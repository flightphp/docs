# Migrationen

Eine Migration für Ihr Projekt verfolgt alle Datenbankänderungen, die mit Ihrem Projekt verbunden sind. 
[byjg/php-migration](https://github.com/byjg/php-migration) ist eine wirklich hilfreiche Kernbibliothek, um 
Ihnen den Einstieg zu erleichtern.

## Installation

### PHP-Bibliothek

Wenn Sie nur die PHP-Bibliothek in Ihrem Projekt verwenden möchten:

```bash
composer require "byjg/migration"
```

### Befehlszeilenschnittstelle

Die Befehlszeilenschnittstelle ist eigenständig und erfordert keine Installation mit Ihrem Projekt.

Sie können global installieren und einen symbolischen Link erstellen.

```bash
composer require "byjg/migration-cli"
```

Bitte besuchen Sie [byjg/migration-cli](https://github.com/byjg/migration-cli) für weitere Informationen zur Migration CLI.

## Unterstützte Datenbanken

| Datenbank      | Treiber                                                                          | Verbindungszeichenfolge                                        |
| -------------- | ------------------------------------------------------------------------------- | ------------------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                 | sqlite:///path/to/file                                       |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                   | mysql://username:password@hostname:port/database             |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                   | pgsql://username:password@hostname:port/database             |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database             |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database            |

## Wie funktioniert es?

Die Datenbankmigration verwendet reines SQL, um das Datenbankversioning zu verwalten.
Um es zum Laufen zu bringen, müssen Sie:

* Die SQL-Skripte erstellen
* Verwenden Sie die Befehlszeile oder die API.

### Die SQL-Skripte

Die Skripte sind in drei Gruppen unterteilt:

* Das BASIS-Skript enthält alle SQL-Befehle zum Erstellen einer frischen Datenbank;
* Die UP-Skripte enthalten alle SQL-Migrationsbefehle, um die Datenbankversion "hoch" zu setzen;
* Die DOWN-Skripte enthalten alle SQL-Migrationsbefehle, um die Datenbankversion "herunter" zu setzen oder zurückzusetzen;

Das Verzeichnis der Skripte ist :

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

* "base.sql" ist das Basisskript
* Der "up"-Ordner enthält die Skripte für das Hochsetzen der Version.
   Zum Beispiel: 00002.sql ist das Skript, um die Datenbank von Version '1' auf '2' zu migrieren.
* Der "down"-Ordner enthält die Skripte für das Heruntersetzen der Version.
   Zum Beispiel: 00001.sql ist das Skript, um die Datenbank von Version '2' auf '1' zu migrieren.
   Der "down"-Ordner ist optional.

### Multi-Entwicklungsumgebung

Wenn Sie mit mehreren Entwicklern und mehreren Branches arbeiten, ist es schwierig zu bestimmen, welche Nummer die nächste ist.

In diesem Fall haben Sie das Suffix "-dev" nach der Versionsnummer.

Sehen Sie sich das Szenario an:

* Entwickler 1 erstellt einen Branch und die aktuellste Version ist z.B. 42.
* Entwickler 2 erstellt gleichzeitig einen Branch und hat die gleiche Datenbankversionsnummer.

In beiden Fällen werden die Entwickler eine Datei mit dem Namen 43-dev.sql erstellen. Beide Entwickler werden ohne Probleme hoch und runter migrieren können, und Ihre lokale Version wird 43 sein.

Aber Entwickler 1 hat seine Änderungen zusammengeführt und eine endgültige Version 43.sql erstellt (`git mv 43-dev.sql 43.sql`). Wenn Entwickler 2 seinen lokalen Branch aktualisiert, hat er eine Datei 43.sql (von dev 1) und seine Datei 43-dev.sql.
Wenn er versucht, hoch oder runter zu migrieren,
wird das Migrationsskript abgebrochen und ihn warnen, dass es zwei Versionen 43 gibt. In diesem Fall muss Entwickler 2 seine Datei in 44-dev.sql aktualisieren und weiterarbeiten, bis er die Änderungen zusammenführt und eine endgültige Version generiert.

## Verwendung der PHP-API und Integration in Ihre Projekte

Die grundlegende Verwendung ist

* Erstellen Sie eine Verbindung zu einem ConnectionManagement-Objekt. Für weitere Informationen siehe die Komponente "byjg/anydataset".
* Erstellen Sie ein Migrationsobjekt mit dieser Verbindung und dem Ordner, in dem sich die SQL-Skripte befinden.
* Verwenden Sie den richtigen Befehl für "reset", "up" oder "down" der Migrationsskripte.

Sehen Sie sich ein Beispiel an:

```php
<?php
// Erstellen Sie die Verbindungs-URI
// Weitere Informationen: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Registrieren Sie die Datenbank oder Datenbanken, die diese URI verarbeiten können:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Erstellen Sie die Migrationsinstanz
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Fügen Sie eine Callback-Fortschrittsfunktion hinzu, um Informationen von der Ausführung zu erhalten
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Stellen Sie die Datenbank mit dem "base.sql"-Skript wieder her
// und führen Sie ALLE vorhandenen Skripte aus, um die Datenbankversion auf die neueste Version zu bringen
$migration->reset();

// Führen Sie ALLE vorhandenen Skripte für hoch oder runter die Datenbankversion aus
// von der aktuellen Version bis zur $version-Nummer;
// Wenn die Versionsnummer nicht angegeben ist, migrieren Sie bis zur letzten Datenbankversion
$migration->update($version = null);
```

Das Migrationsobjekt steuert die Datenbankversion.

### Erstellen einer Versionskontrolle in Ihrem Projekt

```php
<?php
// Registrieren Sie die Datenbank oder Datenbanken, die diese URI verarbeiten können:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Erstellen Sie die Migrationsinstanz
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Dieser Befehl erstellt die Versions-Tabelle in Ihrer Datenbank
$migration->createVersion();
```

### Aktuelle Version abrufen

```php
<?php
$migration->getCurrentVersion();
```

### Callback zum Steuern des Fortschritts hinzufügen

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Befehl ausführen: $command bei Version $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Instanz des Db-Treibers abrufen

```php
<?php
$migration->getDbDriver();
```

Um es zu verwenden, besuchen Sie bitte: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Teilweise Migration vermeiden (nicht verfügbar für MySQL)

Eine partielle Migration ist, wenn das Migrationsskript in der Mitte des Prozesses aufgrund eines Fehlers oder einer manuellen Unterbrechung unterbrochen wird.

Die Migrationstabelle hat den Status `partial up` oder `partial down` und muss manuell behoben werden, bevor sie wieder migrieren kann.

Um diese Situation zu vermeiden, können Sie angeben, dass die Migration in einem Transaktionskontext ausgeführt wird. 
Wenn das Migrationsskript fehlschlägt, wird die Transaktion zurückgesetzt und die Migrationstabelle wird als `complete` markiert und 
die Version wird die unmittelbar vorherige Version vor dem Skript sein, das den Fehler verursacht hat.

Um diese Funktion zu aktivieren, müssen Sie die Methode `withTransactionEnabled` aufrufen und `true` als Parameter übergeben:

```php
<?php
$migration->withTransactionEnabled(true);
```

**HINWEIS: Diese Funktion ist nicht für MySQL verfügbar, da es DDL-Befehle innerhalb einer Transaktion nicht unterstützt.**
Wenn Sie diese Methode mit MySQL verwenden, ignoriert die Migration sie stillschweigend. 
Weitere Informationen: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Tipps zum Schreiben von SQL-Migrationen für Postgres

### Zur Erstellung von Triggern und SQL-Funktionen

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Überprüfen, ob empname und salary angegeben sind
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname darf nicht null sein'; -- es ist egal, ob diese Kommentare leer sind oder nicht
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% darf kein null Gehalt haben', NEW.empname; --
        END IF; --

        -- Wer arbeitet für uns, wenn sie dafür bezahlen müssen?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% darf kein negatives Gehalt haben', NEW.empname; --
        END IF; --

        -- Merken Sie, wer die Gehaltsliste wann geändert hat
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Überprüfen, ob empname und salary angegeben sind
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname darf nicht null sein';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% darf kein null Gehalt haben', NEW.empname;
        END IF;

        -- Wer arbeitet für uns, wenn sie dafür bezahlen müssen?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% darf kein negatives Gehalt haben', NEW.empname;
        END IF;

        -- Merken Sie, wer die Gehaltsliste wann geändert hat
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Da die `PDO`-Datenbank-Abstraktionsschicht keine Batchverarbeitungen von SQL-Anweisungen ausführen kann,
muss `byjg/migration`, wenn es eine Migrationsdatei liest, den gesamten Inhalt der SQL-Datei an den Semikolons aufteilen und die Anweisungen einzeln ausführen. Es gibt jedoch eine Art von 
Anweisung, die mehrere Semikolons in ihrem Körper haben kann: Funktionen.

Um Funktionen korrekt parsen zu können, begann `byjg/migration` 2.1.0 damit, Migrationsdateien an der 
`Semikolon + EOL`-Sequenz anstelle nur am Semikolon aufzuteilen. Auf diese Weise kann `byjg/migration` sie parsen, wenn Sie nach jedem inneren Semikolon einer Funktionsdefinition einen leeren Kommentar anhängen.

Leider wird die Bibliothek die `CREATE FUNCTION`-Anweisung in mehrere Teile aufteilen und die Migration wird fehlschlagen, wenn Sie vergessen, diese Kommentare hinzuzufügen.

### Vermeidung des Doppelpunktzeichens (`:`)

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

Da `PDO` das Doppelpunkt-Zeichen verwendet, um benannte Parameter in vorbereiteten Anweisungen zu kennzeichnen, führt die Verwendung zu Problemen in anderen Kontexten.

Zum Beispiel können PostgreSQL-Anweisungen `::` verwenden, um Werte zwischen Typen zu casten. Auf der anderen Seite wird `PDO` dies als ungültigen benannten Parameter in einem ungültigen Kontext behandeln und fehlschlagen, wenn er versucht, es auszuführen.

Die einzige Möglichkeit, diese Inkonsistenz zu beheben, besteht darin, Doppelpunkte ganz zu vermeiden (in diesem Fall hat PostgreSQL auch eine alternative
Syntax: `CAST(value AS type)`).

### Verwenden Sie einen SQL-Editor

Abschließend kann das Schreiben manueller SQL-Migrationen mühsam sein, aber es ist deutlich einfacher, wenn
Sie einen Editor verwenden, der die SQL-Syntax versteht, Autovervollständigung bietet,
Ihr aktuelles Datenbankschema inspiziert und/oder Ihren Code automatisch formatiert.

## Umgang mit unterschiedlichen Migrationen innerhalb eines Schemas

Wenn Sie unterschiedliche Migrationsskripte und Versionen innerhalb desselben Schemas erstellen müssen, ist dies möglich,
aber es ist zu riskant und ich empfehle es **nicht**.

Um dies zu tun, müssen Sie unterschiedliche "Migrationstabellen" erstellen, indem Sie den Parameter an den Konstruktor übergeben.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEUER_MIGRATIONSTABELLENNAME");
```

Aus Sicherheitsgründen ist diese Funktion nicht über die Befehlszeile verfügbar, aber Sie können die Umgebungsvariable 
`MIGRATION_VERSION` verwenden, um den Namen zu speichern.

Wir empfehlen dringend, diese Funktion nicht zu verwenden. Die Empfehlung ist, eine Migration für ein Schema durchzuführen.

## Ausführen von Unit-Tests

Basis-Unit-Tests können ausgeführt werden mit:

```bash
vendor/bin/phpunit
```

## Ausführen von Datenbanktests

Integrationstests erfordern, dass Sie die Datenbanken online und verfügbar haben. Wir haben ein einfaches `docker-compose.yml` bereitgestellt, und Sie 
können es verwenden, um die Datenbanken für Tests zu starten.

### Ausführen der Datenbanken

```bash
docker-compose up -d postgres mysql mssql
```

### Führen Sie die Tests aus

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Optional können Sie den Host und das Passwort, das von den Unit-Tests verwendet wird, festlegen.

```bash
export MYSQL_TEST_HOST=localhost     # standardmäßig localhost
export MYSQL_PASSWORD=newpassword      # verwenden Sie '.' wenn Sie ein leeres Passwort haben möchten
export PSQL_TEST_HOST=localhost        # standardmäßig localhost
export PSQL_PASSWORD=newpassword       # verwenden Sie '.' wenn Sie ein leeres Passwort haben möchten
export MSSQL_TEST_HOST=localhost       # standardmäßig localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db   # standardmäßig /tmp/test.db
```