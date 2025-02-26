# Міграції

Міграція для вашого проекту відстежує всі зміни бази даних, пов'язані з вашим проектом. 
[byjg/php-migration](https://github.com/byjg/php-migration) - це дійсно корисна бібліотека ядра, щоб 
допомогти вам розпочати.

## Встановлення

### PHP Бібліотека

Якщо ви хочете використовувати лише PHP бібліотеку у вашому проекті:

```bash
composer require "byjg/migration"
```

### Інтерфейс командного рядка

Інтерфейс командного рядка є автономним і не вимагає встановлення разом з вашим проектом.

Ви можете встановити його глобально та створити символьне посилання

```bash
composer require "byjg/migration-cli"
```

Будь ласка, відвідайте [byjg/migration-cli](https://github.com/byjg/migration-cli), щоб отримати більше інформації про Migration CLI.

## Підтримувані бази даних

| База даних    | Драйвер                                                                           | Рядок з'єднання                                         |
| --------------| -------------------------------------------------------------------------------- | ------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                  |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database        |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database        |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database        |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows    | sqlsrv://username:password@hostname:port/database       |

## Як це працює?

Міграція бази даних використовує ЧИСТИЙ SQL для управління версією бази даних. 
Щоб вона працювала, вам потрібно:

* Створити SQL Скрипти
* Керувати за допомогою командного рядка або API.

### SQL Скрипти

Скрипти поділяються на три набори скриптів:

* БАЗОВИЙ скрипт містить ВСІ SQL команди для створення нової бази даних;
* UP скрипти містять усі SQL команди міграції для "підняття" версії бази даних;
* DOWN скрипти містять усі SQL команди міграції для "зниження" або скасування версії бази даних;

Директорія скриптів виглядає так:

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

* "base.sql" є базовим скриптом
* папка "up" містить скрипти для підняття версії.
   Наприклад: 00002.sql є скриптом для переходу бази даних з версії '1' на '2'.
* папка "down" містить скрипти для зниження версії.
   Наприклад: 00001.sql є скриптом для переходу бази даних з версії '2' на '1'.
   Папка "down" є необов'язковою.

### Багатокористувацьке середовище розвитку

Якщо ви працюєте з кількома розробниками та кількома гілками, важко визначити, яке наступне число.

У цьому випадку у вас є суфікс "-dev" після номера версії.

Дивіться сценарій:

* Розробник 1 створює гілку, і найновіша версія, наприклад, 42.
* Розробник 2 створює гілку одночасно і має той же номер версії бази даних.

У обох випадках розробники створять файл з назвою 43-dev.sql. Обидва розробники здійснять міграцію UP і DOWN без 
проблем, і ваша локальна версія буде 43.

Але розробник 1 об'єднав ваші зміни та створив фінальну версію 43.sql (`git mv 43-dev.sql 43.sql`). Якщо розробник 2 
оновить свою локальну гілку, він отримає файл 43.sql (від розробника 1) і ваш файл 43-dev.sql.
Якщо він спробує здійснити міграцію UP або DOWN, 
скрипт міграції повідомить про зміни та попередить його про те, що існує ДВІ версії 43. У такому випадку розробник 2 повинен оновити 
ваш файл до 44-dev.sql і продовжити працювати, поки не об'єднає ваші зміни та не згенерує фінальну версію.

## Використання PHP API та інтеграція його у ваші проекти

Основне використання виглядає так:

* Створіть з'єднання об'єкта ConnectionManagement. Для отримання докладнішої інформації див. компонент "byjg/anydataset"
* Створіть об'єкт Migration з цим з'єднанням та папкою, в якій розташовані SQL скрипти.
* Використовуйте відповідну команду для "скидання", "підйому" або "зниження" міграційних скриптів.

Дивіться приклад:

```php
<?php
// Створіть URI з'єднання
// Див. більше: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Зареєструйте базу даних або бази даних, які можуть обробляти цей URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Створіть екземпляр Migration
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Додайте функцію зворотного виклику для отримання інформації про виконання
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Відновіть базу даних за допомогою скрипту "base.sql"
// і виконайте ВСІ існуючі скрипти для підняття версії бази даних до останньої версії
$migration->reset();

// Виконайте ВСІ існуючі скрипти для підняття або зниження версії бази даних
// з поточної версії до номера $version;
// Якщо номер версії не вказано, мігруйте до останньої версії бази даних
$migration->update($version = null);
```

Об'єкт Migration контролює версію бази даних.

### Створення контролю версій у вашому проекті

```php
<?php
// Зареєструйте базу даних або бази даних, які можуть обробляти цей URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Створіть екземпляр Migration
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Ця команда створить таблицю версій у вашій базі даних
$migration->createVersion();
```

### Отримання поточної версії

```php
<?php
$migration->getCurrentVersion();
```

### Додати зворотний виклик для контролю прогресу

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Виконання команди: $command на версії $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Отримання екземпляра Db Driver

```php
<?php
$migration->getDbDriver();
```

Щоб використовувати, будь ласка, відвідайте: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Уникнення часткових міграцій (не доступно для MySQL)

Часткова міграція - це коли скрипт міграції переривається в середині процесу через помилку або ручне переривання.

Таблиця міграції буде зі статусом `partial up` або `partial down`, і її потрібно виправити вручну, перш ніж знову зможете мігрувати.

Щоб уникнути цієї ситуації, ви можете вказати, що міграція буде виконуватися в транзакційному контексті. 
Якщо скрипт міграції зазнає невдачі, транзакція буде скасована, а таблиця міграцій буде позначена як `complete`, а версія буде відразу попередньою версією перед скриптом, який спричинив помилку.

Для активації цієї функції потрібно викликати метод `withTransactionEnabled`, передавши `true` як параметр:

```php
<?php
$migration->withTransactionEnabled(true);
```

**ПРИМІТКА: Ця функція недоступна для MySQL, оскільки вона не підтримує DDL команди всередині транзакції.**
Якщо ви використовуєте цей метод з MySQL, міграція проігнорує його мовчки. 
Більше інформації: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Поради щодо написання SQL міграцій для Postgres

### При створенні тригерів та SQL функцій

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Перевірте, що empname та salary вказані
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- не має значення, чи ці коментарі порожні чи ні
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- Хто працює на нас, коли вони повинні платити за це?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- Пам'ятайте, хто змінив зарплатний список, коли
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Перевірте, що empname та salary вказані
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- Хто працює на нас, коли вони повинні платити за це?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- Пам'ятайте, хто змінив зарплатний список, коли
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Оскільки абстрактний шар бази даних `PDO` не може виконувати партії SQL-інструкцій, 
коли `byjg/migration` читає файл міграції, він повинен розділяти весь вміст SQL 
файлу за крапками з комою та виконувати інструкції по одній. Однак є один вид 
інструкцій, які можуть містити кілька крапок з комою між своїм тілом: функції.

Для того, щоб правильно розпарсити функції, `byjg/migration` 2.1.0 почала розділяти файли міграцій 
по послідовності `крапка з комою + EOL`, а не просто за крапкою з комою. Таким чином, якщо ви додасте порожній 
коментар після кожної внутрішньої крапки з комою в визначенні функції, `byjg/migration` зможе її розпарсити.

На жаль, якщо ви забудете додати будь-який з цих коментарів, бібліотека розділить 
інструкцію `CREATE FUNCTION` на кілька частин, і міграція зазнає невдачі.

### Уникнення символу двокрапки (`:`)

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

Оскільки `PDO` використовує символ двокрапки для префікса іменованих параметрів у підготовлених інструкціях, 
використання його поставить його в незручне положення в інших контекстах.

Наприклад, інструкції PostgreSQL можуть використовувати `::` для перетворення значень між типами. З іншого боку, 
`PDO` трактуватиме це як недійсний іменований параметр у недійсному контексті і зазнає невдачі, коли намагатиметься його виконати.

Єдиний спосіб виправити цю невідповідність - уникати двокрапок зовсім (у цьому випадку у PostgreSQL також є альтернативний 
синтаксис: `CAST(value AS type)`).

### Використовуйте SQL редактор

Нарешті, написання ручних SQL міграцій може бути виснажливим, але значно легше, якщо 
ви використовуєте редактор, здатний розуміти синтаксис SQL, надаючи автодоповнення, 
інтроспектуючи вашу поточну схему бази даних та/або автоформатуючи ваш код.

## Обробка різних міграцій в одній схемі

Якщо вам потрібно створити різні скрипти міграції та версії в одній схемі, це можливо, 
але це занадто ризиковано, і я **не** рекомендую цього зовсім.

Для цього вам потрібно створити різні "таблиці міграції", передаючи параметр 
в конструктор.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

З міркувань безпеки ця функція не доступна в командному рядку, але ви можете 
використати змінну середовища `MIGRATION_VERSION`, щоб зберегти ім'я.

Ми дійсно рекомендуємо не використовувати цю функцію. Рекомендація - одна міграція для однієї схеми.

## Запуск юніт-тестів

Основні юніт-тести можна виконати за допомогою:

```bash
vendor/bin/phpunit
```

## Запуск тестів бази даних

Запуск інтеграційних тестів вимагає, щоб бази даних були запущені. Ми надали базовий `docker-compose.yml`, і ви 
можете використовувати його для запуску баз даних для тестування.

### Запуск баз даних

```bash
docker-compose up -d postgres mysql mssql
```

### Запуск тестів

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Опційно ви можете вказати хост і пароль, які використовуються юніт-тестами

```bash
export MYSQL_TEST_HOST=localhost     # за замовчуванням localhost
export MYSQL_PASSWORD=newpassword    # використовуйте '.' якщо хочете, щоб пароль був нульовим
export PSQL_TEST_HOST=localhost      # за замовчуванням localhost
export PSQL_PASSWORD=newpassword     # використовуйте '.' якщо хочете, щоб пароль був нульовим
export MSSQL_TEST_HOST=localhost     # за замовчуванням localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # за замовчуванням /tmp/test.db
```