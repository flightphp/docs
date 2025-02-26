# Migrationen

Eine Migration für dein Projekt verfolgt alle Datenbankänderungen, die mit deinem Projekt verbunden sind.  
[byjg/php-migration](https://github.com/byjg/php-migration) ist eine wirklich hilfreiche Kernbibliothek, um  
dir den Einstieg zu erleichtern.

## Installation

### PHP-Bibliothek

Wenn du nur die PHP-Bibliothek in deinem Projekt verwenden möchtest:

```bash
composer require "byjg/migration"
```

### Befehlszeilen-Schnittstelle

Die Befehlszeilen-Schnittstelle ist eigenständig und erfordert keine Installation mit deinem Projekt.

Du kannst sie global installieren und einen symbolischen Link erstellen.

```bash
composer require "byjg/migration-cli"
```

Bitte besuche [byjg/migration-cli](https://github.com/byjg/migration-cli), um weitere Informationen zur Migration CLI zu erhalten.

## Unterstützte Datenbanken

| Datenbank      | Treiber                                                                          | Verbindungszeichenfolge                                        |
| -------------- | ------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                 | sqlite:///path/to/file                                        |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                   | mysql://benutzer:passwort@hostname:port/datenbank            |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                   | pgsql://benutzer:passwort@hostname:port/datenbank            |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://benutzer:passwort@hostname:port/datenbank            |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows    | sqlsrv://benutzer:passwort@hostname:port/datenbank           |

## Wie funktioniert es?

Die Datenbankmigration verwendet reines SQL, um das Datenbankversioning zu verwalten.  
Um es zum Laufen zu bringen, musst du:

* Die SQL-Skripte erstellen
* Verwenden Sie die Befehlszeile oder die API.

### Die SQL-Skripte

Die Skripte sind in drei Sätze von Skripten unterteilt:

* Das BASE-Skript enthält alle SQL-Befehle zum Erstellen einer neuen Datenbank;
* Die UP-Skripte enthalten alle SQL-Migrationsbefehle, um die Datenbankversion "nach oben" zu erhöhen;
* Die DOWN-Skripte enthalten alle SQL-Migrationsbefehle, um die Datenbankversion "nach unten" zurückzusetzen;

Das Verzeichnis der Skripte ist:

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
* Der "up"-Ordner enthält die Skripte zum Hochmigrieren der Version.  
   Zum Beispiel: 00002.sql ist das Skript, um die Datenbank von Version '1' auf '2' zu verschieben.
* Der "down"-Ordner enthält die Skripte zum Heruntermigrieren der Version.  
   Zum Beispiel: 00001.sql ist das Skript, um die Datenbank von Version '2' auf '1' zu verschieben.  
   Der "down"-Ordner ist optional.

### Multientwicklungsumgebung

Wenn du mit mehreren Entwicklern und mehreren Zweigen arbeitest, ist es schwierig zu bestimmen, welche die nächste Nummer ist.

In diesem Fall hast du das Suffix "-dev" nach der Versionsnummer.

Siehe das Szenario:

* Entwickler 1 erstellt einen Zweig und die aktuellste Version ist z.B. 42.
* Entwickler 2 erstellt gleichzeitig einen Zweig und hat die gleiche Datenbankversionsnummer.

In beiden Fällen werden die Entwickler eine Datei mit dem Namen 43-dev.sql erstellen. Beide Entwickler werden problemlos nach oben und nach unten migrieren können, und deine lokale Version wird 43 sein.

Aber Entwickler 1 hat deine Änderungen zusammengeführt und eine endgültige Version 43.sql erstellt (`git mv 43-dev.sql 43.sql`). Wenn Entwickler 2  
deinen lokalen Zweig aktualisiert, hat er eine Datei 43.sql (von dev 1) und deine Datei 43-dev.sql.  
Wenn er versucht, nach oben oder nach unten zu migrieren, wird das Migrationsskript heruntergefahren und ihn warnen, dass es ZWEI Versionen 43 gibt. In diesem Fall muss Entwickler 2 seine  
Datei in 44-dev.sql aktualisieren und weiterarbeiten, bis er deine Änderungen zusammenführt und eine endgültige Version generiert.

## Verwendung der PHP-API und Integration in deine Projekte

Die grundlegende Verwendung ist:

* Erstelle eine Verbindung zu einem ConnectionManagement-Objekt. Für weitere Informationen siehe die Komponente "byjg/anydataset"
* Erstelle ein Migrationsobjekt mit dieser Verbindung und dem Ordner, in dem sich die SQL-Skripte befinden.
* Verwende den entsprechenden Befehl für "reset", "up" oder "down" der Migrationsskripte.

Siehe ein Beispiel:

```php
<?php
// Erstelle die Verbindungs-URI
// Siehe mehr: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Registriere die Datenbank oder Datenbanken, die diese URI handhaben können:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Erstelle die Migrationsinstanz
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Füge eine Callback-Fortschrittsfunktion hinzu, um Informationen aus der Ausführung zu erhalten
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Stelle die Datenbank mit dem "base.sql"-Skript wieder her
// und führe ALLE vorhandenen Skripte aus, um die Datenbankversion auf die neueste Version zu erhöhen
$migration->reset();

// Führe ALLE vorhandenen Skripte aus, um die Datenbankversion nach oben oder unten zu ändern
// von der aktuellen Version bis zur $version-Nummer;
// Wenn die Versionsnummer nicht angegeben ist, migriere bis zur letzten Datenbankversion
$migration->update($version = null);
```

Das Migrationsobjekt steuert die Datenbankversion.

### Erstellen einer Versionskontrolle in deinem Projekt

```php
<?php
// Registriere die Datenbank oder Datenbanken, die diese URI handhaben können:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Erstelle die Migrationsinstanz
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Dieser Befehl erstellt die Versionstabelle in deiner Datenbank
$migration->createVersion();
```

### Aktuelle Version abrufen

```php
<?php
$migration->getCurrentVersion();
```

### Callback hinzufügen, um den Fortschritt zu steuern

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Befehl ausführen: $command bei Version $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Die Db-Treiberinstanz abrufen

```php
<?php
$migration->getDbDriver();
```

Um es zu verwenden, besuche bitte: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Verhindern von teilweiser Migration (nicht verfügbar für MySQL)

Eine partielle Migration liegt vor, wenn das Migrationsskript während des Prozesses aufgrund eines Fehlers oder einer manuellen Unterbrechung unterbrochen wird.

Die Migrationstabelle hat den Status `partial up` oder `partial down` und muss manuell behoben werden, bevor sie erneut migrieren kann.

Um diese Situation zu vermeiden, kannst du angeben, dass die Migration in einem transaktionalen Kontext durchgeführt wird.  
Wenn das Migrationsskript fehlschlägt, wird die Transaktion zurückgerollt und die Migrationstabelle als `complete` markiert, und  
die Version wird die unmittelbar vorhergehende Version vor dem Skript sein, das den Fehler verursacht hat.

Um diese Funktion zu aktivieren, musst du die Methode `withTransactionEnabled` aufrufen und `true` als Parameter übergeben:

```php
<?php
$migration->withTransactionEnabled(true);
```

**HINWEIS: Diese Funktion ist nicht für MySQL verfügbar, da es DDL-Befehle innerhalb einer Transaktion nicht unterstützt.**  
Wenn du diese Methode mit MySQL verwendest, wird die Migration stillschweigend ignoriert.  
Weitere Informationen: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Tipps zum Schreiben von SQL-Migrationen für Postgres

### Zum Erstellen von Triggern und SQL-Funktionen

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Überprüfen, ob empname und Gehalt angegeben sind
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

        -- Erinnern, wer die Gehaltsabrechnung wann geändert hat
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Überprüfen, ob empname und Gehalt angegeben sind
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

        -- Erinnern, wer die Gehaltsabrechnung wann geändert hat
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Da die `PDO`-Datenbankabstraktionsschicht keine Batches von SQL-Anweisungen ausführen kann,  
muss `byjg/migration` beim Lesen einer Migrationsdatei den gesamten Inhalt der SQL-Datei am Semikolon aufteilen und die Anweisungen einzeln ausführen.  
Es gibt jedoch eine Art von Anweisung, die mehrere Semikolons innerhalb ihres Körpers haben kann: Funktionen.

Um Funktionen korrekt analysieren zu können, begann `byjg/migration` 2.1.0, Migrationsdateien am `Semikolon + EOL`-Sequenz  
anstatt nur am Semikolon zu splitten. Auf diese Weise kann `byjg/migration` es analysieren, wenn du einen leeren  
Kommentar nach jedem inneren Semikolon einer Funktionsdefinition anfügst.

Leider wird die Bibliothek, wenn du vergisst, einen dieser Kommentare hinzuzufügen, das `CREATE FUNCTION`-Statement in  
mehrere Teile aufteilen und die Migration wird fehlschlagen.

### Vermeide das Doppelpunkt-Zeichen (`:`)

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

Da `PDO` das Doppelpunkt-Zeichen verwendet, um benannte Parameter in vorbereiteten Anweisungen voranzustellen, wird seine Verwendung in anderen Kontexten Probleme verursachen.

Zum Beispiel können PostgreSQL-Anweisungen `::` verwenden, um Werte zwischen Typen zu konvertieren.  
Auf der anderen Seite wird `PDO` dies als ungültigen benannten Parameter in einem ungültigen Kontext lesen und fehlschlagen, wenn es versucht, ihn auszuführen.

Die einzige Möglichkeit, diese Inkonsistenz zu beheben, besteht darin, Doppelpunkte ganz zu vermeiden (in diesem Fall hat PostgreSQL auch eine alternative  
Syntax: `CAST(value AS type)`).

### Verwende einen SQL-Editor

Schließlich kann das Schreiben manueller SQL-Migrationen mühsam sein, aber es wird erheblich einfacher, wenn du einen Editor verwendest,  
der die SQL-Syntax verstehen kann, Autocomplete bietet, dein aktuelles Datenbankschema inspizieren kann und/oder deinen Code automatisch formatieren kann.

## Handhabung verschiedener Migrationen innerhalb eines Schemas

Wenn du verschiedene Migrationsskripte und Versionen im selben Schema erstellen musst, ist dies möglich,  
aber es ist zu riskant und ich **empfehle es auf keinen Fall**.

Um dies zu tun, musst du verschiedene "Migrationstabellen" erstellen, indem du den Parameter an den Konstruktor übergibst.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Aus Sicherheitsgründen ist diese Funktion in der Befehlszeile nicht verfügbar, aber du kannst die Umgebungsvariable  
`MIGRATION_VERSION` verwenden, um den Namen zu speichern.

Es wird unbedingt empfohlen, diese Funktion nicht zu verwenden. Die Empfehlung ist eine Migration für ein Schema.

## Ausführen von Unit-Tests

Basis-Unit-Tests können ausgeführt werden durch:

```bash
vendor/bin/phpunit
```

## Ausführen von Datenbanktests

Das Ausführen von Integrationstests erfordert, dass du die Datenbanken hochfährst. Wir haben eine grundlegende `docker-compose.yml` bereitgestellt, und du  
kannst sie verwenden, um die Datenbanken für Tests zu starten.

### Ausführen der Datenbanken

```bash
docker-compose up -d postgres mysql mssql
```

### Führe die Tests aus

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Optional kannst du den Host und das Passwort festlegen, das für die Unit-Tests verwendet wird.

```bash
export MYSQL_TEST_HOST=localhost     # standardmäßig localhost
export MYSQL_PASSWORD=newpassword    # verwende '.' , wenn du ein leeres Passwort haben möchtest
export PSQL_TEST_HOST=localhost      # standardmäßig localhost
export PSQL_PASSWORD=newpassword     # verwende '.' , wenn du ein leeres Passwort haben möchtest
export MSSQL_TEST_HOST=localhost     # standardmäßig localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # standardmäßig /tmp/test.db
```