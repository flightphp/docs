# 迁移

项目的迁移是跟踪您项目中所有数据库更改的方式。 
[byjg/php-migration](https://github.com/byjg/php-migration) 是一个非常有用的核心库，可以帮助您入门。

## 安装

### PHP 库

如果您只想在项目中使用 PHP 库：

```bash
composer require "byjg/migration"
```

### 命令行接口

命令行接口是独立的，不要求您在项目中安装。

您可以全局安装并创建一个符号链接

```bash
composer require "byjg/migration-cli"
```

请访问 [byjg/migration-cli](https://github.com/byjg/migration-cli) 以获取有关迁移 CLI 的更多信息。

## 支持的数据库

| 数据库        | 驱动                                                                               | 连接字符串                                           |
| -------------- | -------------------------------------------------------------------------------- | ---------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                   | sqlite:///path/to/file                                |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                     | mysql://username:password@hostname:port/database      |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                     | pgsql://username:password@hostname:port/database      |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database      |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows     | sqlsrv://username:password@hostname:port/database     |

## 如何工作？

数据库迁移使用纯 SQL 管理数据库版本控制。 
为了使其工作，您需要：

* 创建 SQL 脚本 
* 使用命令行或 API 进行管理。

### SQL 脚本

脚本分为三组：

* 基础脚本包含创建新数据库的所有 SQL 命令；
* 向上脚本包含所有将数据库版本向上的 SQL 迁移命令；
* 向下脚本包含所有将数据库版本向下或还原的 SQL 迁移命令；

脚本目录结构如下：

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

* "base.sql" 是基础脚本 
* "up" 文件夹包含迁移版本的脚本。
   例如：00002.sql 是将数据库从版本 '1' 移动到 '2' 的脚本。
* "down" 文件夹包含迁移回版本的脚本。
   例如：00001.sql 是将数据库从版本 '2' 移动到 '1' 的脚本。
   "down" 文件夹是可选的。

### 多开发环境

如果您与多个开发人员和多个分支一起工作，确定下一个数字会很困难。

在这种情况下，您可以在版本号后面加上后缀 "-dev"。

场景如下：

* 开发者 1 创建一个分支，最近版本为 42。
* 开发者 2 同时创建一个分支，且有相同的数据库版本号。

在这两种情况下，开发者都会创建一个名为 43-dev.sql 的文件。两个开发者都可以顺利进行向上和向下的迁移，您的本地版本将为 43。

但是开发者 1 合并了他的更改并创建了最终版本 43.sql (`git mv 43-dev.sql 43.sql`)。如果开发者 2 更新本地分支，他将拥有开发者 1 的文件 43.sql 和他的文件 43-dev.sql。
如果他尝试进行向上或向下的迁移，迁移脚本将会出现问题并提醒他有两个版本 43。在这种情况下，开发者 2 将需要更新他的文件为 44-dev.sql，并继续工作直到合并他的更改并生成最终版本。

## 使用 PHP API 并将其集成到您的项目中

基本用法是

* 创建一个连接到 ConnectionManagement 对象。有关更多信息，请参见 "byjg/anydataset" 组件
* 使用此连接和 SQL 脚本所在的文件夹创建一个迁移对象。
* 使用适当的命令来重置、向上或向下迁移脚本。

请参见示例：

```php
<?php
// 创建连接 URI
// 详细信息请参见：https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// 注册可以处理该 URI 的数据库：
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 创建迁移实例
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 添加回调进度函数以接收执行信息
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// 使用 "base.sql" 脚本还原数据库
// 并运行所有现有脚本以将数据库版本升级到最新版本
$migration->reset();

// 运行所有现有脚本以将数据库版本升级或降级
// 从当前版本到 $version 号；
// 如果未指定版本号，则迁移到最后的数据库版本
$migration->update($version = null);
```

迁移对象控制数据库版本。

### 在您的项目中创建版本控制

```php
<?php
// 注册可以处理该 URI 的数据库：
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 创建迁移实例
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 此命令将在您的数据库中创建版本表
$migration->createVersion();
```

### 获取当前版本

```php
<?php
$migration->getCurrentVersion();
```

### 添加回调以控制进度

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "执行命令：$command 在版本 $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### 获取数据库驱动实例

```php
<?php
$migration->getDbDriver();
```

要使用它，请访问：[https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 避免部分迁移（MySQL 不可用）

部分迁移是指迁移脚本在处理过程中因错误或手动中断而中断。

迁移表将显示状态为 `partial up` 或 `partial down`，在能够再次迁移之前需要手动修复。

为了避免这种情况，您可以指定迁移将在事务上下文中运行。 
如果迁移脚本失败，事务将被回滚，并且迁移表将被标记为 `complete`，版本将是导致错误的脚本之前的立即版本。

要启用此功能，您需要调用方法 `withTransactionEnabled`，传递 `true` 作为参数：

```php
<?php
$migration->withTransactionEnabled(true);
```

**注意：此功能在 MySQL 下不可用，因为它不支持事务中的 DDL 命令。**
如果您在 MySQL 中使用此方法，迁移将会安静地忽略它。 
更多信息请参阅：[https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## 编写针对 Postgres 的 SQL 迁移的提示

### 创建触发器和 SQL 函数

```sql
-- 请做
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- 检查 empname 和 salary 是否被提供
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname 不能为 null'; -- 这些注释是否为空无所谓
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% 不能有 null 的 salary', NEW.empname; --
        END IF; --

        -- 谁在为我们工作时必须付钱？
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% 不能有负的 salary', NEW.empname; --
        END IF; --

        -- 记住谁在何时更改了工资单
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- 请不要
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- 检查 empname 和 salary 是否被提供
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname 不能为 null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% 不能有 null 的 salary', NEW.empname;
        END IF;

        -- 谁在为我们工作时必须付钱？
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% 不能有负的 salary', NEW.empname;
        END IF;

        -- 记住谁在何时更改了工资单
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

由于 `PDO` 数据库抽象层无法一次性运行批量的 SQL 语句， 
当 `byjg/migration` 读取迁移文件时，它必须在分号处分割整个 SQL 文件的内容，并逐个运行语句。 然而，有一种语句可以在其主体中包含多个分号：函数。

为了能够正确解析函数，`byjg/migration` 2.1.0 开始在 `分号 + EOL` 的序列上分割迁移文件，而不仅仅是分号。 这样，如果您在每个函数定义的内部分号后面添加一个空注释，`byjg/migration` 将能够解析它。

不幸的是，如果您忘记添加这些注释中的任何一个，库将会把 `CREATE FUNCTION` 语句分割成多个部分，迁移将会失败。

### 避免使用冒号字符（`:`）

```sql
-- 请做
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- 请不要
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

由于 `PDO` 使用冒号字符作为命名参数在预处理语句中的前缀，因此在其他上下文中使用它将会导致问题。

例如，PostgreSQL 语句可以使用 `::` 来在类型之间转换值。 
另一方面，`PDO` 会将其视为一个无效的命名参数，并在尝试运行时失败。

解决这种不一致的唯一方法是完全避免使用冒号（在这种情况下，PostgreSQL 还有一个备用语法：`CAST(value AS type)`）。

### 使用 SQL 编辑器

最后，手动编写 SQL 迁移可能很麻烦，但如果您使用一个能够理解 SQL 语法的编辑器，提供自动完成、查看当前数据库架构和/或自动格式化代码，则会容易得多。

## 在一个模式中处理不同的迁移

如果您需要在同一模式中创建不同的迁移脚本和版本，这是可能的，但风险很大，我 **不** 建议这样做。

要做到这一点，您需要通过向构造函数传递参数来创建不同的“迁移表”。

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

出于安全原因，此功能在命令行中不可用，但您可以使用环境变量 `MIGRATION_VERSION` 来存储名称。

我们强烈建议不要使用此功能。 建议是为每个模式创建一个迁移。

## 运行单元测试

基本单元测试可以通过以下方式运行：

```bash
vendor/bin/phpunit
```

## 运行数据库测试

运行集成测试需要您具有正在运行的数据库。 我们提供了一个基本的 `docker-compose.yml`，您可以用它来启动测试的数据库。

### 运行数据库

```bash
docker-compose up -d postgres mysql mssql
```

### 运行测试

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

您还可以设置单元测试使用的主机和密码

```bash
export MYSQL_TEST_HOST=localhost     # 默认为 localhost
export MYSQL_PASSWORD=newpassword      # 如果想要一个 null 密码，请使用 '.'
export PSQL_TEST_HOST=localhost        # 默认为 localhost
export PSQL_PASSWORD=newpassword       # 如果想要一个 null 密码，请使用 '.'
export MSSQL_TEST_HOST=localhost       # 默认为 localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db   # 默认为 /tmp/test.db
```