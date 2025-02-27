# Миграции

Миграция для вашего проекта отслеживает все изменения базы данных, связанные с вашим проектом. [byjg/php-migration](https://github.com/byjg/php-migration) — это действительно полезная основная библиотека, с которой вы можете начать.

## Установка

### PHP библиотека

Если вы хотите использовать только PHP библиотеку в вашем проекте:

```bash
composer require "byjg/migration"
```

### Интерфейс командной строки

Интерфейс командной строки является отдельным и не требует установки вместе с вашим проектом.

Вы можете установить его глобально и создать символическую ссылку.

```bash
composer require "byjg/migration-cli"
```

Пожалуйста, посетите [byjg/migration-cli](https://github.com/byjg/migration-cli), чтобы получить больше информации о Migration CLI.

## Поддерживаемые базы данных

| База данных   | Драйвер                                                                          | Строка соединения                                         |
| --------------| ------------------------------------------------------------------------------- | --------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                    |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database          |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database          |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database          |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database         |

## Как это работает?

Миграция базы данных использует ЧИСТЫЙ SQL для управления версионностью базы данных. Чтобы это заработало, вам необходимо:

* Создать SQL скрипты
* Управлять, используя командную строку или API.

### SQL скрипты

Скрипты делятся на три группы:

* БАЗОВЫЙ скрипт содержит ВСЕ sql команды для создания новой базы данных;
* UP скрипты содержат все sql миграционные команды для "повышения" версии базы данных;
* DOWN скрипты содержат все sql миграционные команды для "понижения" или возврата версии базы данных;

Директория скриптов:

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

* "base.sql" — это базовый скрипт
* Папка "up" содержит скрипты для миграции вверх.
   Например: 00002.sql — это скрипт для перемещения базы данных с версии '1' на '2'.
* Папка "down" содержит скрипты для миграции вниз.
   Например: 00001.sql — это скрипт для перемещения базы данных с версии '2' на '1'.
   Папка "down" является необязательной.

### Многоразвивающая среда

Если вы работаете с несколькими разработчиками и несколькими ветками, будет сложно определить, какое число следующее.

В этом случае вы добавляете суффикс "-dev" после номера версии.

Смотрите сценарий:

* Разработчик 1 создает ветку, и самая последняя версия, например, 42.
* Разработчик 2 создаёт ветку одновременно и имеет тот же номер версии базы данных.

В обоих случаях разработчики создадут файл под названием 43-dev.sql. Оба разработчика будут мигрировать UP и DOWN без проблем, и ваша локальная версия будет 43.

Но разработчик 1 объединил ваши изменения и создал окончательную версию 43.sql (`git mv 43-dev.sql 43.sql`). Если разработчик 2 обновит свою локальную ветку, он получит файл 43.sql (от dev 1) и ваш файл 43-dev.sql. Если он попытается мигрировать UP или DOWN, скрипт миграции упадет и предупредит его, что есть ДВЕ версии 43. В этом случае разработчик 2 должен будет обновить ваш файл до 44-dev.sql и продолжить работать, пока не объединит ваши изменения и не сгенерирует окончательную версию.

## Использование PHP API и интеграция его в ваши проекты

Основное использование:

* Создайте объект ConnectionManagement для соединения. Для получения дополнительной информации смотрите компонент "byjg/anydataset".
* Создайте объект миграции с этим соединением и папкой, в которой находятся sql-скрипты.
* Используйте соответствующую команду для "сброса", "up" или "down" миграционных скриптов.

Смотрите пример:

```php
<?php
// Создайте URI соединения
// Подробнее: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Зарегистрируйте Базу данных или Базы данных, которые могут обрабатывать этот URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Создайте экземпляр миграции
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Добавьте функцию обратного вызова прогресса для получения информации о выполнении
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Восстановите базу данных с использованием скрипта "base.sql"
// и выполните ВСЕ существующие скрипты для повышения версии базы данных до последней версии
$migration->reset();

// Выполните ВСЕ существующие скрипты для вверх или вниз версии базы данных
// от текущей версии до номера $version;
// Если номер версии не указан, мигрируйте до последней версии базы данных
$migration->update($version = null);
```

Объект миграции контролирует версию базы данных.

### Создание контроля версий в вашем проекте

```php
<?php
// Зарегистрируйте Базу данных или Базы данных, которые могут обрабатывать этот URI:
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

### Добавить обратный вызов для контроля прогресса

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Выполняем команду: $command на версии $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Получение экземпляра драйвера Db

```php
<?php
$migration->getDbDriver();
```

Чтобы использовать это, пожалуйста, посетите: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Избежание частичной миграции (недоступно для MySQL)

Частичная миграция возникает, когда скрипт миграции прерывается в середине процесса из-за ошибки или ручного прерывания.

Таблица миграции будет иметь статус `partial up` или `partial down`, и ее необходимо исправить вручную перед тем, как снова мигрировать.

Чтобы избежать этой ситуации, вы можете указать, что миграция будет выполняться в транзакционном контексте. Если скрипт миграции не удастся выполнить, транзакция будет отменена, а таблица миграции будет отмечена как `complete`, и версия будет сразу же предыдущей версией перед скриптом, который вызвал ошибку.

Чтобы включить эту функцию, вам необходимо вызвать метод `withTransactionEnabled`, передав `true` как параметр:

```php
<?php
$migration->withTransactionEnabled(true);
```

**ПРИМЕЧАНИЕ: Эта функция недоступна для MySQL, так как она не поддерживает DDL команды внутри транзакции.** Если вы используете этот метод с MySQL, миграция проигнорирует его без уведомления. Дополнительная информация: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Советы по написанию SQL миграций для Postgres

### О создании триггеров и SQL функций

```sql
-- ДЕЛАЙТЕ
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Проверьте, что empname и зарплата указаны
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname не может быть пустым'; -- не имеет значения, пустые ли эти комментарии
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не может иметь пустую зарплату', NEW.empname; --
        END IF; --

        -- Кто работает на нас, когда они должны за это платить?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не может иметь отрицательную зарплату', NEW.empname; --
        END IF; --

        -- Запомните, кто изменял зарплату, когда
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
            RAISE EXCEPTION 'empname не может быть пустым';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% не может иметь пустую зарплату', NEW.empname;
        END IF;

        -- Кто работает на нас, когда они должны за это платить?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% не может иметь отрицательную зарплату', NEW.empname;
        END IF;

        -- Запомните, кто изменял зарплату, когда
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Поскольку уровень абстракции базы данных `PDO` не может выполнять пачки SQL операторов, когда `byjg/migration` читает файл миграции, он должен разбить все содержимое SQL файла по точкам с запятой и выполнять операторы один за другим. Однако существует один вид оператора, который может содержать несколько точек с запятой между его телом: функции.

Чтобы иметь возможность корректно парсить функции, `byjg/migration` 2.1.0 начал разбивать файлы миграции по последовательности `точка с запятой + EOL` вместо обычной точки с запятой. Таким образом, если вы добавите пустой комментарий после каждой внутренней точки с запятой в определении функции, `byjg/migration` сможет корректно его разобрать.

К сожалению, если вы забудете добавить хотя бы один из этих комментариев, библиотека разобьет оператор `CREATE FUNCTION` на несколько частей, и миграция потерпит неудачу.

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

Поскольку `PDO` использует двоеточие, чтобы обозначать именованные параметры в подготовленных операторов, его использование вызовет проблемы в других контекстах.

Например, операторы PostgreSQL могут использовать `::` для приведения значений между типами. С другой стороны, `PDO` воспримет это как недопустимый именованный параметр в недопустимом контексте и выдаст ошибку, когда попытается его выполнить.

Единственный способ исправить это несоответствие — это полностью избегать двоеточий (в этом случае у PostgreSQL также есть альтернативный синтаксис: `CAST(value AS type)`).

### Используйте SQL редактор

Наконец, написание ручных SQL миграций может быть утомительным, но это значительно проще, если вы используете редактор, способный понимать синтаксис SQL, предоставляющий автозавершение, интуитивно исследующий вашу текущую схему базы данных и/или автоматически форматирующий ваш код.

## Обработка различных миграций внутри одной схемы

Если вам нужно создать различные миграционные скрипты и версии в одной схеме, это возможно, но слишком рискованно, и я **категорически не** рекомендую это.

Для этого вам нужно создать разные "миграционные таблицы", передавая параметр в конструктор.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

По соображениям безопасности эта функция недоступна в командной строке, но вы можете использовать переменную среды `MIGRATION_VERSION`, чтобы сохранить имя.

Мы действительно рекомендуем не использовать эту функцию. Рекомендация — одна миграция для одной схемы.

## Запуск модульных тестов

Основные модульные тесты можно запустить с помощью:

```bash
vendor/bin/phpunit
```

## Запуск тестов базы данных

Запуск интеграционных тестов требует, чтобы базы данных были запущены и работали. Мы предоставили базовый `docker-compose.yml`, и вы можете использовать его для запуска баз данных для тестов.

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

Опционально вы можете установить хост и пароль, используемые модульными тестами.

```bash
export MYSQL_TEST_HOST=localhost     # по умолчанию localhost
export MYSQL_PASSWORD=newpassword    # используйте '.' если хотите иметь пустой пароль
export PSQL_TEST_HOST=localhost      # по умолчанию localhost
export PSQL_PASSWORD=newpassword     # используйте '.' если хотите иметь пустой пароль
export MSSQL_TEST_HOST=localhost     # по умолчанию localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # по умолчанию /tmp/test.db
```