# Міграції

Міграція для вашого проєкту – це відстеження всіх змін бази даних, пов’язаних з вашим проєктом. [byjg/php-migration](https://github.com/byjg/php-migration) – це справді корисна основна бібліотека, яка допоможе вам розпочати.

## Встановлення

### PHP Бібліотека

Якщо ви хочете використовувати тільки PHP бібліотеку у вашому проєкті:

```bash
composer require "byjg/migration"
```

### Інтерфейс командного рядка

Інтерфейс командного рядка є самостійним і не вимагає, щоб ви встановлювали його разом із вашим проєктом.

Ви можете встановити його глобально і створити символічне посилання

```bash
composer require "byjg/migration-cli"
```

Будь ласка, відвідайте [byjg/migration-cli](https://github.com/byjg/migration-cli), щоб отримати більше інформації про Migration CLI.

## Підтримувані бази даних

| База даних    | Драйвер                                                                          | Строка з’єднання                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## Як це працює?

Міграція бази даних використовує ЧИСТИЙ SQL для управління версіонуванням бази даних. Щоб це працювало, вам потрібно:

* Створити SQL скрипти
* Керувати за допомогою командного рядка або API.

### SQL Скрипти

Скрипти поділені на три набори скриптів:

* БАЗОВИЙ скрипт містить УСІ sql команди для створення нової бази даних;
* UP скрипти містять усі sql команди міграції для "підняття" версії бази даних;
* DOWN скрипти містять усі sql команди міграції для "зниження" або повернення версії бази даних;

Директорія зі скриптами виглядає так:

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

* "base.sql" – це базовий скрипт
* Папка "up" містить скрипти для підняття версії.
   Наприклад: 00002.sql – це скрипт для переходу бази даних з версії '1' на '2'.
* Папка "down" містить скрипти для зниження версії.
   Наприклад: 00001.sql – це скрипт для повернення бази даних з версії '2' на '1'.
   Папка "down" є необов'язковою.

### Багаторазове середовище розробки

Якщо ви працюєте з кількома розробниками та кількома гілками, важко визначити, яке наступне число.

У цьому випадку ви можете додати суфікс "-dev" після номера версії.

Погляньте на сценарій:

* Розробник 1 створює гілку, а найновіша версія, наприклад, 42.
* Розробник 2 одночасно створює гілку і має те саме число версії бази даних.

У обох випадках розробники створять файл під назвою 43-dev.sql. Обидва розробники зможуть мігрувати UP і DOWN без проблем, а ваша локальна версія буде 43.

Але розробник 1 об'єднав свої зміни і створив фінальну версію 43.sql (`git mv 43-dev.sql 43.sql`). Якщо розробник 2 оновить вашу локальну гілку, він отримає файл 43.sql (від розробника 1) і ваш файл 43-dev.sql.
Якщо він спробує мігрувати UP або DOWN, скрипт міграції повідомить про помилку і сповістить його про те, що існує ДВІ версії 43. У такому випадку розробник 2 повинен оновити свій файл на 44-dev.sql і продовжити працювати, поки не об’єднає свої зміни і не створить фінальну версію.

## Використання PHP API та інтеграція його у ваші проєкти

Основне використання:

* Створити з'єднання з об'єктом ConnectionManagement. Для отримання додаткової інформації див. компонент "byjg/anydataset"
* Створити об'єкт Migration з цим з’єднанням та папкою, в якій знаходяться sql скрипти.
* Використати відповідну команду для "скидання", "підняття" або "зниження" скриптів міграцій.

Дивіться приклад:

```php
<?php
// Створіть URI з'єднання
// Дивіться більше: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Зареєструйте базу даних або бази даних, які можуть обробляти цей URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Створіть екземпляр Migration
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Додайте функцію зворотного виклику для отримання інформації про виконання
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Відновіть базу даних за допомогою скрипта "base.sql"
// і виконайте УСІ існуючі скрипти, щоб підняти версію бази даних до останньої
$migration->reset();

// Виконайте УСІ існуючі скрипти для підняття чи зниження версії бази даних
// з поточної версії до номера $version;
// Якщо номер версії не вказано, мігруйте до останньої версії бази даних
$migration->update($version = null);
```

Об'єкт Migration контролює версію бази даних.

### Створення контролю версій у вашому проєкті

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

### Отримання екземпляра драйвера бази даних

```php
<?php
$migration->getDbDriver();
```

Щоб використовувати це, будь ласка, відвідайте: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Уникнення часткової міграції (не доступно для MySQL)

Часткова міграція – це коли скрипт міграції переривається посеред процесу через помилку або ручну переривання.

Таблиця міграції буде мати статус `partial up` або `partial down`, і її потрібно буде виправити вручну, перш ніж можна буде мігрувати знову.

Щоб уникнути цієї ситуації, ви можете вказати, що міграція буде виконуватись у транзакційному контексті. Якщо скрипт міграції не вдасться, транзакція буде скасована, а таблиця міграції буде позначена як `complete`, і версія буде одразу попередньою версією до скрипта, який спричинив помилку.

Щоб увімкнути цю функцію, вам потрібно викликати метод `withTransactionEnabled`, передавши `true` як параметр:

```php
<?php
$migration->withTransactionEnabled(true);
```

**ПРИМІТКА: Ця функція недоступна для MySQL, оскільки він не підтримує DDL команди всередині транзакції.** Якщо ви використовуєте цей метод з MySQL, Migration проігнорує його тихо. 
Більше інформації: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Поради щодо написання SQL міграцій для Postgres

### При створенні тригерів і SQL-функцій

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Перевірте, що ім'я працівника та зарплата вказані
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname не може бути null'; -- не має значення, якщо ці коментарі пусті
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не може мати null зарплату', NEW.empname; --
        END IF; --

        -- Хто працює на нас, коли вони повинні це оплачувати?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не може мати негативну зарплату', NEW.empname; --
        END IF; --

        -- Запам'ятайте, хто змінив платіж від коли
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Перевірте, що ім'я працівника та зарплата вказані
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname не може бути null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не може мати null зарплату', NEW.empname;
        END IF;

        -- Хто працює на нас, коли вони повинні це оплачувати?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не може мати негативну зарплату', NEW.empname;
        END IF;

        -- Запам'ятайте, хто змінив платіж від коли
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Оскільки абстрактний рівень бази даних `PDO` не може виконувати партії SQL заявок, коли `byjg/migration` читає файл міграції, він повинен розділити весь вміст SQL файлу на частини за крапками з коми та виконати заяви одну за одною. Однак є один вид заяви, який може містити кілька крапок з коми між його тілом: функції.

Щоб мати можливість правильно парсити функції, `byjg/migration` 2.1.0 почав розділяти файли міграцій за послідовністю `semicolon + EOL` замість лише за крапкою з комою. Таким чином, якщо ви додасте пустий коментар після кожної внутрішньої крапки з комою у визначенні функції, `byjg/migration` зможе їх правильно розпізнати.

На жаль, якщо ви забудете додати будь-який з цих коментарів, бібліотека розділить заяву `CREATE FUNCTION` на кілька частин, і міграція завершиться невдачею.

### Уникнення символа двокрапки (`:`)

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

Оскільки `PDO` використовує символ двокрапки для префікса названих параметрів у підготовлених запитах, його використання призведе до помилки в інших контекстах.

Наприклад, заяви PostgreSQL можуть використовувати `::` для приведення значень між типами. З іншого боку, `PDO` прочитає це як недійсний названий параметр в недійсному контексті і завершить спробу його виконання з помилкою.

Єдиний спосіб виправити цю невідповідність – це уникати двокрапок взагалі (у цьому випадку PostgreSQL також має альтернативний синтаксис: `CAST(value AS type)`).

### Використовуйте SQL редактор

Нарешті, написання ручних SQL міграцій може бути виснажливим, але це набагато простіше, якщо ви використовуєте редактор, здатний розуміти синтаксис SQL, надавати автозаповнення, досліджувати вашу поточну схему бази даних та/або автоматично форматувати ваш код.

## Обробка різних міграцій всередині однієї схеми

Якщо вам потрібно створити різні скрипти міграцій та версії в одній схемі, це можливо, але надто ризиковано, і я **не рекомендую** цього зовсім.

Для цього вам потрібно створити різні "таблиці міграцій", передаючи параметр конструктору.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

З міркувань безпеки ця функція недоступна з командного рядка, але ви можете використовувати змінну середовища `MIGRATION_VERSION`, щоб зберегти ім'я.

Ми справді рекомендуємо не використовувати цю функцію. Рекомендація – одна міграція для однієї схеми.

## Запуск юніт-тестів

Основні юніт-тести можна запускати за допомогою:

```bash
vendor/bin/phpunit
```

## Запуск тестів бази даних

Запуск інтеграційних тестів вимагає, щоб бази даних були запущені. Ми надали базовий `docker-compose.yml`, який ви можете використовувати для запуску баз даних для тестування.

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

Опціонально, ви можете встановити хост і пароль, які використовуються юніт-тестами

```bash
export MYSQL_TEST_HOST=localhost     # за замовчуванням localhost
export MYSQL_PASSWORD=newpassword    # використовуйте '.' якщо хочете мати порожній пароль
export PSQL_TEST_HOST=localhost      # за замовчуванням localhost
export PSQL_PASSWORD=newpassword     # використовуйте '.' якщо хочете мати порожній пароль
export MSSQL_TEST_HOST=localhost     # за замовчуванням localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # за замовчуванням /tmp/test.db
```