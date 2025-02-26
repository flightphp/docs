# Migrācijas

Migrācija jūsu projektam ir visu datu bāzes izmaiņu uzskaites process, kas saistīts ar jūsu projektu.  
[byjg/php-migration](https://github.com/byjg/php-migration) ir patiesi noderīga kodola bibliotēka, kas palīdzēs jums uzsākt darbu.

## Instalēšana

### PHP bibliotēka

Ja vēlaties izmantot tikai PHP bibliotēku savā projektā:

```bash
composer require "byjg/migration"
```

### Komandu rindas interfeiss

Komandu rindas interfeiss ir patstāvīgs un neprasa, lai jūs to instalētu kopā ar savu projektu.

Jūs varat to instalēt globāli un izveidot simbolisku saiti

```bash
composer require "byjg/migration-cli"
```

Lūdzu, apmeklējiet [byjg/migration-cli](https://github.com/byjg/migration-cli), lai iegūtu sīkāku informāciju par Migration CLI.

## Atbalstītās datu bāzes

| Datu bāze     | Draiveris                                                                      | Savienojuma virkne                                         |
| -------------- | ------------------------------------------------------------------------------- | --------------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                 | sqlite:///path/to/file                                    |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                   | mysql://lietotājvārds:parole@hostname:ports/datu_bāze    |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                   | pgsql://lietotājvārds:parole@hostname:ports/datu_bāze    |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://lietotājvārds:parole@hostname:ports/datu_bāze    |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://lietotājvārds:parole@hostname:ports/datu_bāze   |

## Kā tas darbojas?

Datu bāzes migrācija izmanto TĪRU SQL, lai pārvaldītu datu bāzes versiju.  
Lai tas darbotos, jums jāveic šādas darbības:

* Izveidot SQL skriptus
* Pārvaldīt, izmantojot komandu rindu vai API.

### SQL skripti

Skripti ir sadalīti trīs skriptu kopās:

* BAZES skripts satur VISAS sql komandas, lai izveidotu jaunu datu bāzi;
* UP skripti satur visas sql migrācijas komandas, lai "paaugstinātu" datu bāzes versiju;
* DOWN skripti satur visas sql migrācijas komandas, lai "samazinātu" vai atgrieztu datu bāzes versiju;

Direktorija ar skriptiem ir:

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
* "up" mape satur skriptus, lai migrētu uz augstāku versiju.  
   Piemēram: 00002.sql ir skripts, lai pārvietotu datu bāzi no versijas '1' uz '2'.
* "down" mape satur skriptus, lai migrētu uz zemāku versiju.  
   Piemēram: 00001.sql ir skripts, lai pārvietotu datu bāzi no versijas '2' uz '1'.  
   "down" mape ir opcija.

### Vairāku izstrādes vide

Ja strādājat ar vairākiem izstrādātājiem un vairākiem zariem, ir grūti noteikt, kas ir nākamais numurs.

Tajā gadījumā jums jāizmanto sufikss "-dev" pēc versijas numura.

Apskatiet scenāriju:

* Izstrādātājs 1 izveido zaru, un jaunākā versija, piemēram, ir 42.
* Izstrādātājs 2 izveido zaru tajā pašā laikā un tam ir tas pats datu bāzes versijas numurs.

Abos gadījumos izstrādātāji izveidos failu ar nosaukumu 43-dev.sql. Abi izstrādātāji migrēs UP un DOWN bez problēmām, un jūsu lokālā versija būs 43.

Bet izstrādātājs 1 sapludināja jūsu izmaiņas un izveidoja galīgo versiju 43.sql (`git mv 43-dev.sql 43.sql`). Ja izstrādātājs 2 atjauninās savu lokālo zaru, viņam būs fails 43.sql (no dev 1) un jūsu fails 43-dev.sql.  
Ja viņš mēģina migrēt UP vai DOWN, migrācijas skripti pāries un paziņos, ka ir divas versijas 43. Tādā gadījumā izstrādātājam 2 būs jāatjaunina jūsu fails uz 44-dev.sql un jāturpina strādāt, līdz sapludinās jūsu izmaiņas un ģenerēs galīgo versiju.

## PHP API izmantošana un integrācija jūsu projektos

Pamatlietošana ir

* Izveidot savienojumu ar ConnectionManagement objektu. Lai iegūtu vairāk informācijas, skatiet "byjg/anydataset" komponentu
* Izveidot migrācijas objektu ar šo savienojumu un mapi, kur atrodas sql skripti.
* Lieto pareizo komandu, lai "atjaunotu", "paaugstinātu" vai "samazinātu" migrācijas skriptus.

Skatiet piemēru:

```php
<?php
// Izveidot savienojuma URI
// Skatiet vairāk: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Reģistrēt datu bāzi vai datu bāzes, kas spēj apstrādāt šo URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Izveidot migrācijas instanci
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Pievienot progresēšanas atgriezenisko saiti, lai saņemtu informāciju par izpildi
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Atjaunot datu bāzi, izmantojot "base.sql" skriptu
// un izpildīt VISUS esošos skriptus, lai paaugstinātu datu bāzes versiju līdz jaunākai versijai
$migration->reset();

// Izpildīt VISUS esošos skriptus, lai paaugstinātu vai samazinātu datu bāzes versiju
// no pašreizējās versijas līdz $version numuram;
// Ja versijas numurs netiek norādīts, migrēt līdz pēdējai datu bāzes versijai
$migration->update($version = null);
```

Migrācijas objekts kontrolē datu bāzes versiju.

### Versiju kontroles izveide jūsu projektā

```php
<?php
// Reģistrēt datu bāzi vai datu bāzes, kas spēj apstrādāt šo URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Izveidot migrācijas instanci
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Šī komanda izveidos versiju tabulu jūsu datu bāzē
$migration->createVersion();
```

### Pašreizējās versijas iegūšana

```php
<?php
$migration->getCurrentVersion();
```

### Pievienot atgriezenisko saiti, lai kontrolētu progresu

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Izpildām komandu: $command uz versiju $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Iegūt DB draivera instanci

```php
<?php
$migration->getDbDriver();
```

Lai to izmantotu, lūdzu, apmeklējiet: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Daļēju migrāciju izvairīšanās (nav pieejama MySQL)

Daļēja migrācija ir, kad migrācijas skripts tiek pārtraukts procesa vidū kļūdas vai manuālas pārtraukšanas dēļ.

Migrācijas tabula būs ar statusu `partial up` vai `partial down`, un to ir jālabo manuāli, pirms varat migrēt atkal.

Lai izvairītos no šīs situācijas, jūs varat norādīt, ka migrācija tiks veikta transakcijas kontekstā.  
Ja migrācijas skripts neizdodas, transakcija tiks atgriezta, un migrācijas tabula tiks atzīmēta kā `complete`, un versija būs uzreiz iepriekšējā versija pirms skripta, kas izraisīja kļūdu.

Lai aktivizētu šo funkciju, jums jāizsauc metode `withTransactionEnabled`, pārsūtot `true` kā parametrs:

```php
<?php
$migration->withTransactionEnabled(true);
```

**Piezīme: Šī funkcija nav pieejama MySQL, jo tā neatbalsta DDL komandas transakcijas ietvaros.**  
Ja jūs izmantojat šo metodi ar MySQL, migrācija to klusi ignorēs.  
Vairāk informācijas: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Padomi SQL migrāciju rakstīšanai Postgres

### Trigeru un SQL funkciju izveide

```sql
-- DARĪT
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Pārbaudiet, vai empname un alga ir norādītas
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- nav nozīmes, ja šie komentāri ir tukši vai nē
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- Kas strādā pie mums, kad viņiem par to jāmaksā?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- Atcerieties, kas mainīja algu, kad
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- NEDARĪT
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Pārbaudiet, vai empname un alga ir norādītas
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- Kas strādā pie mums, kad viņiem par to jāmaksā?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- Atcerieties, kas mainīja algu, kad
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Tā kā `PDO` datu bāzes abstrakcijas slānis nevar izpildīt SQL paziņojumu partijas,  
kad `byjg/migration` lasa migrācijas failu, tam jādalās ar visām SQL faila saturu pie semikola un jānolasa paziņojumus vienu pēc otra. Taču pastāv vienas veida paziņojums, kas var saturēt vairākus semikola savu ķermeni: funkcijas.

Lai varētu pareizi parsert funkcijas, `byjg/migration` 2.1.0 sāka dalīt migrācijas failus pie `semikola + EOL` secības, nevis tikai pie semikola. Tādējādi, ja jūs pievienojat tukšu komentāru pēc katra iekšēja semikola funkciju definīcijā, `byjg/migration` būs spēj izprast to.

Diemžēl, ja jūs aizmirstat pievienot kādu no šiem komentāriem, bibliotēka sadalīs `CREATE FUNCTION` paziņojumu vairākās daļās, un migrācija neizdosies.

### Izvairieties no kolonnas rakstzīmes (`:`)

```sql
-- DARĪT
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- NEDARĪT
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Tā kā `PDO` izmanto kolonnas rakstzīmi, lai prefiksētu nosauktos parametrus sagatavotajos paziņojumos, tās lietošana izraisīs kļūdu citos kontekstos.

Piemēram, Postgres paziņojumos var izmantot `::`, lai pārvērstu vērtības starp tipiem. Citādi `PDO` to lasīs kā nederīgu nosaukto parametru nederīgā kontekstā un neizdosies, kad mēģinās to izpildīt.

Vienīgais veids, kā labot šo nesakritību, ir izvairīties no kolonnām vispār (šajā gadījumā Postgres arī ir alternatīva sintakse: `CAST(value AS type)`).

### Izmantojiet SQL editoru

Visbeidzot, manuāla SQL migrāciju rakstīšana var būt apgrūtināta, bet to ir būtiski vieglāk, ja
izmantojat redaktoru, kas spēj saprast SQL sintaksi, piedāvā automātiskā pabeigšanu, izpēta jūsu pašreizējo datu bāzes shēmu un / vai automātiski formatē jūsu kodu.

## Dažādu migrāciju apstrāde vienā shēmā

Ja jums ir jāizveido dažādi migrācijas skripti un versijas vienā un tajā pašā shēmā, tas ir iespējams,  
bet tas ir pārāk riskants, un es **neiesaku** to darīt.

Lai to izdarītu, jums jāizveido dažādas "migrācijas tabulas", pārsūtot parametru konstruktoram.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Drošības apsvērumu dēļ šī funkcija nav pieejama komandu rindā, bet jūs varat izmantot vides mainīgo  
`MIGRATION_VERSION`, lai uzglabātu nosaukumu.

Mēs patiešām iesakām neizmantot šo funkciju. Ieteikums ir viena migrācija vienai shēmā.

## Vienību testu izpilde

Pamata vienību testus var veikt, palaižot:

```bash
vendor/bin/phpunit
```

## Datu bāzu testu izpilde

Integrācijas testu veikšanai ir nepieciešams, lai datu bāzes tiktu palaistas un darbinātas. Mēs nodrošinājām pamata `docker-compose.yml`, un jūs varat to izmantot, lai uzsāktu datu bāzes testēšanai.

### Datu bāzu palaišana

```bash
docker-compose up -d postgres mysql mssql
```

### Testu veikšana

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Pēc vēlēšanās varat iestatīt hostu un paroli, ko izmanto vienību testi

```bash
export MYSQL_TEST_HOST=localhost     # noklusējums ir localhost
export MYSQL_PASSWORD=newpassword      # lietojiet '.' ja vēlaties, lai parole būtu tukša
export PSQL_TEST_HOST=localhost        # noklusējums ir localhost
export PSQL_PASSWORD=newpassword       # lietojiet '.' ja vēlaties, lai parole būtu tukša
export MSSQL_TEST_HOST=localhost       # noklusējums ir localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db    # noklusējums ir /tmp/test.db
```