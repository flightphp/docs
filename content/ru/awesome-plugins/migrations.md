# Миграции

Миграция для вашего проекта отслеживает все изменения в базе данных, связанные с вашим проектом. [byjg/php-migration](https://github.com/byjg/php-migration) является действительно полезной основной библиотекой, чтобы начать.

## Установка

### PHP Библиотека

Если вы хотите использовать только PHP библиотеку в вашем проекте:

```bash
composer require "byjg/migration"
```

### Интерфейс командной строки

Интерфейс командной строки является автономным и не требует установки вместе с вашим проектом.

Вы можете установить глобально и создать символическую ссылку

```bash
composer require "byjg/migration-cli"
```

Пожалуйста, посетите [byjg/migration-cli](https://github.com/byjg/migration-cli) для получения дополнительной информации о Migration CLI.

## Поддерживаемые базы данных

| База данных   | Драйвер                                                                         | Строка подключения                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## Как это работает?

Миграция базы данных использует ЧИСТЫЙ SQL для управления версионированием базы данных. Чтобы это работало вам необходимо:

* Создать SQL-скрипты
* Управлять с помощью командной строки или API.

### SQL-скрипты

Скрипты делятся на три набора скриптов:

* Базовый скрипт содержит ВСЕ sql команды для создания новой базы данных;
* Скрипты UP содержат все sql команды миграции для "повышения" версии базы данных;
* Скрипты DOWN содержат все sql команды миграции для "понижения" или возврата версии базы данных;

Каталог скриптов:

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

* "base.sql" является базовым скриптом
* Папка "up" содержит скрипты для повышения версии.
   Например: 00002.sql - это скрипт для перехода базы данных с версии '1' на '2'.
* Папка "down" содержит скрипты для понижения версии.
   Например: 00001.sql - это скрипт для перехода базы данных с версии '2' на '1'.
   Папка "down" является необязательной.

### Многоразвивающая среда

Если вы работаете с несколькими разработчиками и несколькими ветками, трудно определить, какой номер следующий.

В этом случае у вас будет суффикс "-dev" после номера версии.

Смотрите сценарий:

* Разработчик 1 создает ветку, и самая последняя версия, например, 42.
* Разработчик 2 одновременно создает ветку и имеет такой же номер версии базы данных.

В обоих случаях разработчики создадут файл под названием 43-dev.sql. Оба разработчика смогут мигрировать ВВЕРХ и ВНИЗ без проблем, а ваша локальная версия будет 43.

Но разработчик 1 объединил ваши изменения и создал окончательную версию 43.sql (`git mv 43-dev.sql 43.sql`). Если разработчик 2 обновит свою локальную ветку, он получит файл 43.sql (от разработчика 1) и ваш файл 43-dev.sql. Если он попытается мигрировать ВВЕРХ или ВНИЗ, скрипт миграции даст сбой и предупредит его о том, что существуют ДВЕ версии 43. В этом случае разработчику 2 придется обновить ваш файл до 44-dev.sql и продолжить работу до слияния ваших изменений и генерации финальной версии.

## Использование PHP API и интеграция его в ваши проекты

Основное использование:

* Создайте объект ConnectionManagement. Для получения дополнительной информации см. компонент "byjg/anydataset".
* Создайте объект миграции с этим подключением и папкой, где находятся скрипты sql.
* Используйте соответствующую команду для "сброса", "повышения" или "понижения" скриптов миграции.

Вот пример:

```php
<?php
// Создайте URI подключения
// Подробнее см. https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Зарегистрируйте базу данных или базы данных, которые могут обрабатывать этот URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Создайте экземпляр миграции
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Добавьте функцию обратного вызова для получения информации о выполнении
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Восстановите базу данных, используя скрипт "base.sql"
// и выполните ВСЕ существующие скрипты для повышения версии базы данных до последней версии
$migration->reset();

// Выполните ВСЕ существующие скрипты для повышения или понижения версии базы данных
// от текущей версии до номера $version;
// Если номер версии не указан, мигрируйте до последней версии базы данных
$migration->update($version = null);
```

Объект миграции управляет версией базы данных.

### Создание системы контроля версий в вашем проекте

```php
<?php
// Зарегистрируйте базу данных или базы данных, которые могут обрабатывать этот URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Создайте экземпляр миграции
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Эта команда создаст таблицу версий в вашей базе данных
$migration->createVersion();
```

### Получение текущей версии

```php
<?php
$migration->getCurrentVersion();
```

### Добавление колбека для управления прогрессом

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Выполнение команды: $command на версии $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Получение экземпляра драйвера БД

```php
<?php
$migration->getDbDriver();
```

Чтобы использовать его, пожалуйста, посетите: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Избежание частичной миграции (не доступно для MySQL)

Частичная миграция - это когда скрипт миграции прерывается в середине процесса из-за ошибки или ручного прерывания.

Таблица миграции будет иметь статус `partial up` или `partial down`, и ее необходимо вручную исправить, прежде чем снова можно будет мигрировать.

Чтобы избежать этой ситуации, вы можете указать, что миграция будет выполняться в транзакционном контексте. Если скрипт миграции завершится с ошибкой, транзакция будет отменена, и таблица миграции будет отмечена как `complete`, а версия будет равна сразу предыдущей версии перед скриптом, который вызвал ошибку.

Чтобы включить эту функцию, вам нужно вызвать метод `withTransactionEnabled`, передав `true` в качестве параметра:

```php
<?php
$migration->withTransactionEnabled(true);
```

**ПРИМЕЧАНИЕ: Эта функция недоступна для MySQL, так как она не поддерживает DDL команды внутри транзакции.** Если вы используете этот метод с MySQL, миграция проигнорирует его безвозвратно. Более подробная информация: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Советы по написанию SQL миграций для Postgres

### При создании триггеров и SQL функций

```sql
-- ДЕЛАЙТЕ
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Проверьте, что empname и зарплата указаны
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname не может быть нулевым'; -- не имеет значения, пустые ли эти комментарии
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не может иметь нулевую зарплату', NEW.empname; --
        END IF; --

        -- Кто работает на нас, когда им придется за это платить?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не может иметь отрицательную зарплату', NEW.empname; --
        END IF; --

        -- Запомните, кто изменил платежную ведомость, когда
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- НЕ ДЕЛАЙТЕ
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Проверьте, что empname и зарплата указаны
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname не может быть нулевым';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не может иметь нулевую зарплату', NEW.empname;
        END IF;

        -- Кто работает на нас, когда им придется за это платить?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не может иметь отрицательную зарплату', NEW.empname;
        END IF;

        -- Запомните, кто изменил платежную ведомость, когда
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Поскольку уровень абстракции базы данных `PDO` не может выполнять пакеты SQL операторов, когда `byjg/migration` читает файл миграции, ему необходимо разделить все содержимое SQL файла по точкам с запятой и выполнять операторы по одному. Однако есть один вид оператора, который может содержать несколько точек с запятой между своим телом: функции.

Чтобы иметь возможность правильно разбивать функции, `byjg/migration` 2.1.0 начал разбивать файлы миграции по последовательности `точка с запятой + EOL`, а не только по точке с запятой. Таким образом, если вы добавите пустой комментарий после каждой внутренней точки с запятой в определении функции, `byjg/migration` сможет правильно ее разобрать.

К сожалению, если вы забудете добавить любой из этих комментариев, библиотека разделит оператор `CREATE FUNCTION` на несколько частей, и миграция завершится неудачей.

### Избегайте символа двоеточия (`:`)

```sql
-- ДЕЛАЙТЕ
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- НЕ ДЕЛАЙТЕ
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Поскольку `PDO` использует символ двоеточия для префикса именованных параметров в подготовленных операторов, его использование будет сбивать с толку в других контекстах.

Например, операторы PostgreSQL могут использовать `::` для преобразования значений между типами. С другой стороны, `PDO` будет воспринимать это как недопустимый именованный параметр в недопустимом контексте и завершит свою работу с ошибкой, когда попытается его выполнить.

Единственный способ исправить эту несоответствие - полностью избегать двоеточий (в этом случае у PostgreSQL также есть альтернативный синтаксис: `CAST(value AS type)`).

### Используйте SQL-редактор

Наконец, написание ручных SQL миграций может быть утомительным, но значительно легче, если вы используете редактор, способный понять синтаксис SQL, предоставлять автозавершение, анализировать вашу текущую схему базы данных и/или автоформатировать ваш код.

## Обработка различных миграций внутри одной схемы

Если вам нужно создать различные скрипты миграции и версии внутри одной схемы, это возможно, но слишком рискованно, и я **совершенно не** рекомендую это.

Для этого вам необходимо создать разные "таблицы миграции", передав параметр в конструктор.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

По соображениям безопасности эта функция не доступна из командной строки, но вы можете использовать переменную окружения `MIGRATION_VERSION`, чтобы сохранить имя.

Мы настоятельно рекомендуем не использовать эту функцию. Рекомендация - одна миграция для одной схемы.

## Запуск модульных тестов

Базовые модульные тесты можно запустить с помощью:

```bash
vendor/bin/phpunit
```

## Запуск тестов базы данных

Запуск интеграционных тестов требует, чтобы базы данных были запущены и работали. Мы предоставили базовый `docker-compose.yml`, который вы можете использовать для запуска баз данных для тестирования.

### Запуск баз данных

```bash
docker-compose up -d postgres mysql mssql
```

### Запуск тестов

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

По желанию вы можете установить хост и пароль, используемые в модульных тестах

```bash
export MYSQL_TEST_HOST=localhost     # по умолчанию localhost
export MYSQL_PASSWORD=newpassword    # используйте '.' , если хотите иметь нулевой пароль
export PSQL_TEST_HOST=localhost      # по умолчанию localhost
export PSQL_PASSWORD=newpassword     # используйте '.' , если хотите иметь нулевой пароль
export MSSQL_TEST_HOST=localhost     # по умолчанию localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # по умолчанию /tmp/test.db
```