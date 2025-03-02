# Migrācijas

Migrācija jūsu projektam uzrauga visus datu bāzes izmaiņas, kas saistītas ar jūsu projektu.  
[byjg/php-migration](https://github.com/byjg/php-migration) ir ļoti noderīga kodola bibliotēka, lai jūs varētu sākt.

## Instalēšana

### PHP bibliotēka

Ja vēlaties izmantot tikai PHP bibliotēku savā projektā:

```bash
composer require "byjg/migration"
```

### Komandrindas interfeiss

Komandrindas interfeiss ir patstāvīgs un neprasa to instalēt kopā ar jūsu projektu.

Jūs varat instalēt globāli un izveidot simbolisko saiti.

```bash
composer require "byjg/migration-cli"
```

Lūdzu, apmeklējiet [byjg/migration-cli](https://github.com/byjg/migration-cli), lai iegūtu vairāk informācijas par Migrācijas CLI.

## Atbalstītās datu bāzes

| Datu bāze     | Dzinējs                                                                        | Savienojuma virkne                                             |
| -------------- | ------------------------------------------------------------------------------ | ------------------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                 | sqlite:///path/to/file                                        |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                   | mysql://lietotāja_vārds:parole@hostname:ports/datu_bāze      |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                   | pgsql://lietotāja_vārds:parole@hostname:ports/datu_bāze      |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux| dblib://lietotāja_vārds:parole@hostname:ports/datu_bāze      |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://lietotāja_vārds:parole@hostname:ports/datu_bāze     |

## Kā tas darbojas?

Datu bāzes migrācija izmanto TĪRU SQL, lai pārvaldītu datu bāzes versijas.  
Lai tas darbotos, jums nepieciešams:

* Izveidot SQL skriptus
* Pārvaldīt, izmantojot komandrindas vai API.

### SQL skripti

Skripti ir sadalīti trīs skriptu grupās:

* BAZES skripts satur VISAS SQL komandas, lai izveidotu jaunu datu bāzi;
* UP skripti satur visas SQL migrācijas komandas, lai "paaugstinātu" datu bāzes versiju;
* DOWN skripti satur visas SQL migrācijas komandas, lai "samazinātu" vai atgrieztu datu bāzes versiju;

Skriptu direktorija ir:

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

* "base.sql" ir bāzes skripts
* "up" mape satur skriptus, lai migrētu uz augšu versiju.  
  Piemēram: 00002.sql ir skripts, lai pārvietotu datu bāzi no versijas '1' uz '2'.
* "down" mape satur skriptus, lai migrētu uz leju versiju.  
  Piemēram: 00001.sql ir skripts, lai pārvietotu datu bāzi no versijas '2' uz '1'.  
  "down" mape ir opcionala.

### Multi izstrādes vide

Ja strādājat ar vairākiem izstrādātājiem un vairākiem zara, ir grūti noteikt nākamo numuru.

Šajā gadījumā jums ir papildinājums "-dev" pēc versijas numura.

Skatiet scenāriju:

* Izstrādātājs 1 izveido zaru un visjaunākā versija ir, piemēram, 42.
* Izstrādātājs 2 izveido zaru vienlaikus un ir tāds pats datu bāzes versijas numurs.

Abos gadījumos izstrādātāji izveidos failu ar nosaukumu 43-dev.sql. Abi izstrādātāji pārskatīs UP un DOWN bez problēmām, un jūsu lokālā versija būs 43.

Bet izstrādātājs 1 apvieno jūsu izmaiņas un izveido gala versiju 43.sql (`git mv 43-dev.sql 43.sql`). Ja izstrādātājs 2 atjauninās jūsu vietējo zaru, viņam būs fails 43.sql (no izstrādātāja 1) un jūsu fails 43-dev.sql.  
Ja viņš mēģina migrēt UP vai DOWN, migrācijas skripts uzrakstīs un brīdinās viņu, ka ir DIVAS versijas 43. Šajā gadījumā izstrādātājam 2 būs jāatjaunina jūsu fails uz 44-dev.sql un jāturpina strādāt līdz apvienojat jūsu izmaiņas un ģenerējat gala versiju.

## PHP API izmantošana un integrēšana savos projektos

Pamatlietošana ir

* Izveidot savienojumu ar ConnectionManagement objektu. Vairāk informācijas skatiet "byjg/anydataset" komponentā.
* Izveidot migrācijas objektu ar šo savienojumu un mapi, kur atrodas SQL skripti.
* Izmantot pareizo komandu priekš "reset", "up" vai "down" migrācijas skriptiem.

Skatiet piemēru:

```php
<?php
// Izveidojiet savienojuma URI
// Skatiet vairāk: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Reģistrējiet datu bāzi vai datu bāzes, kas var apstrādāt šo URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Izveidojiet migrācijas instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Pievienojiet atgriezenisko saiti progresam, lai saņemtu informāciju par izpildi
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Atjaunojiet datu bāzi, izmantojot "base.sql" skriptu
// un palaidiet VISUS esošos skriptus, lai paaugstinātu datu bāzes versiju līdz jaunākajai versijai
$migration->reset();

// Palaidiet VISUS esošos skriptus, lai paaugstinātu vai samazinātu datu bāzes versiju
// no pašreizējās versijas līdz $version numuram;
// Ja versijas numurs nav noteikts, migrējiet līdz pēdējai datu bāzes versijai
$migration->update($version = null);
```

Migrācijas objekts kontrolē datu bāzes versiju.

### Izstrādājot versiju kontroli savā projektā

```php
<?php
// Reģistrējiet datu bāzi vai datu bāzes, kas var apstrādāt šo URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Izveidojiet migrācijas instance
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Šī komanda izveidos versijas tabulu jūsu datu bāzē
$migration->createVersion();
```

### Iegūstot pašreizējo versiju

```php
<?php
$migration->getCurrentVersion();
```

### Pievienojiet atgriezenisko saiti, lai kontrolētu progresu

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Veicot komandu: $command pie versijas $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Iegūstot Db Dzinēja instanci

```php
<?php
$migration->getDbDriver();
```

Lai to izmantotu, lūdzu, apmeklējiet: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Daļēju migrāciju novēršana (nav pieejama MySQL)

Daļēja migrācija ir tad, ja migrācijas skripts tiek pārtraukts procesa vidū kļūdas vai manuālas pārtraukšanas dēļ.

Migrācijas tabula būs ar statusu `daļējs uz augšu` vai `daļējs uz leju`, un to nepieciešams labot manuāli, pirms var atkārtoti migrēt.

Lai izvairītos no šīs situācijas, jūs varat norādīt, ka migrācija tiks izpildīta transakcijas kontekstā.  
Ja migrācijas skripts neizdodas, transakcija tiks atcelta, un migrācijas tabula tiks iezīmēta kā `pabeigta`, un versija būs tūlītēji iepriekšējā versija pirms skripta, kas izraisīja kļūdu.

Lai šo funkciju aktivizētu, jums jāizsauc metode `withTransactionEnabled`, nododot `true` kā parametru:

```php
<?php
$migration->withTransactionEnabled(true);
```

**PIEZĪME: Šī funkcija nav pieejama MySQL, jo tas neatbalsta DDL komandas transakcijas ietvaros.**  
Ja jūs izmantosiet šo metodi ar MySQL, migrācija to klusi ignorēs.  
Vairāk informācijas: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Ieteikumi SQL migrāciju rakstīšanai Postgres

### Par trigeru un SQL funkciju izveidi

```sql
-- DARI
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Pārbaudiet, vai ir norādīts empname un alga
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname nedrīkst būt null'; -- nav svarīgi, vai šie komentāri ir tukši vai nē
        END IF; --

        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% nedrīkst būt null alga', NEW.empname; --
        END IF; --

        -- Kas strādā pie mums, kad viņiem par to jāmaksā?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% nedrīkst būt negatīva alga', NEW.empname; --
        END IF; --

        -- Atcerieties, kurš izmainīja algu, kad
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- NEDARI
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Pārbaudiet, vai ir norādīts empname un alga
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname nedrīkst būt null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% nedrīkst būt null alga', NEW.empname;
        END IF;

        -- Kas strādā pie mums, kad viņiem par to jāmaksā?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% nedrīkst būt negatīva alga', NEW.empname;
        END IF;

        -- Atcerieties, kurš izmainīja algu, kad
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Tā kā `PDO` datu bāzes abstrakcijas slānis nevar izpildīt SQL komandu partijas, kad `byjg/migration` lasa migrācijas failu, tas ir jāizdala visus SQL faila saturus pie semikolu un jāizpilda komandas viena pa viena.  
Tomēr ir viens veids, kā komanda var saturēt vairākas semikolas tās ķermenī: funkcijas.

Lai pareizi analizētu funkcijas, `byjg/migration` 2.1.0 sāka sadalīt migrācijas failus pēc `semikola + EOL` secības, nevis tikai pēc semikolas.  
Šādā veidā, ja jūs pievienojat tukšu komentāru pēc katra iekšējā semikola funkcijas definīcijā, `byjg/migration` to varēs pareizi analizēt.

Diemžēl, ja jūs aizmirsīsiet pievienot kādu no šiem komentāriem, bibliotēka sadalīs `CREATE FUNCTION` paziņojumu multiple parts, un migrācija neizdosies.

### Izvairieties no kolonnas rakstzīmes (`:`)

```sql
-- DARI
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- NEDARI
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Tā kā `PDO` izmanto kolonnas rakstzīmi, lai prefixētu nosauktos parametrus sagatavotās komandas, tās izmantošana izraisa to, ka tas aptrūkst citos kontekstos.

Piemēram, PostgreSQL komandas var izmantot `::`, lai konvertētu vērtības starp tipiem.  
No otras puses, `PDO` to uztvers kā nederīgu nosauktu parametru nederīgā kontekstā un neizdosies, kad tas mēģinās to izpildīt.

Vienīgā veida, kā izlabot šo neatbilstību, ir pilnībā izvairīties no kolonnām (šajā gadījumā PostgreSQL ir alternatīva sintakse: `CAST(value AS type)`).

### Izmantojiet SQL redaktoru

Visbeidzot, manuālas SQL migrāciju rakstīšana var būt nogurdinoša, taču to ir ievērojami vieglāk izdarīt, ja izmantojat redaktoru, kas spēj saprast SQL sintaksi, piedāvā pabeigšanu, introspektē jūsu pašreizējo datu bāzes shēmu un / vai automātiski formatē jūsu kodu.

## Dažādu migrāciju apstrāde vienā shēmā

Ja jums ir jāizveido dažādi migrācijas skripti un versijas vienā shēmā, tas ir iespējams, bet tas ir ļoti riskanti un es **neieteiktu** to darīt.

Lai to izdarītu, jums jāizveido dažādas "migrācijas tabulas", nododot parametru konstruktora parametrā.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "JAUNA_MIGRĀCIJAS_TABULAS_NOSAUKUMS");
```

Drošības apsvērumu dēļ šī funkcija nav pieejama komandrindā, bet jūs varat izmantot vides mainīgo `MIGRATION_VERSION`, lai glabātu nosaukumu.

Mēs patiešām iesakām neizmantot šo funkciju. Ieteikums ir viena migrācija vienai shēmā.

## Vienības testu izpilde

Pamatvienības testus var izpildīt ar:

```bash
vendor/bin/phpunit
```

## Datu bāzu testu izpilde

Lai veiktu integrācijas testus, jums jābūt datu bāzēm, kas darbojas. Mēs esam nodrošinājuši pamata `docker-compose.yml`, un jūs varat to izmantot, lai uzsāktu datu bāzes testēšanai.

### Datu bāzu palaidīšana

```bash
docker-compose up -d postgres mysql mssql
```

### Testu izpilde

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Pēc izvēles jūs varat iestatīt resursdatora un paroles iestatījumus, ko izmanto vienības testos.

```bash
export MYSQL_TEST_HOST=localhost     # noklusējums uz localhost
export MYSQL_PASSWORD=newpassword    # izmantojiet '.', ja vēlaties nulles paroli
export PSQL_TEST_HOST=localhost      # noklusējums uz localhost
export PSQL_PASSWORD=newpassword     # izmantojiet '.', ja vēlaties nulles paroli
export MSSQL_TEST_HOST=localhost     # noklusējums uz localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # noklusējums uz /tmp/test.db
```