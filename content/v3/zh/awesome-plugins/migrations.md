# 迁移

您项目的迁移是在跟踪项目中涉及的所有数据库更改。
[byjg/php-migration](https://github.com/byjg/php-migration) 是一个非常有用的核心库，可以帮助您入门。

## 安装

### PHP 库

如果您只想在项目中使用 PHP 库：

```bash
composer require "byjg/migration"
```

### 命令行界面

命令行界面是独立的，不需要您与项目一起安装。

您可以全局安装并创建符号链接

```bash
composer require "byjg/migration-cli"
```

请访问 [byjg/migration-cli](https://github.com/byjg/migration-cli) 以获取有关迁移 CLI 的更多信息。

## 支持的数据库

| 数据库         | 驱动人                                                                          | 连接字符串                                        |
| --------------- | ------------------------------------------------------------------------------- | ------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                            |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database  |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database  |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database  |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database |

## 它是如何工作的？

数据库迁移使用纯 SQL 来管理数据库版本。
为了使其正常工作，您需要：

* 创建 SQL 脚本
* 使用命令行或 API 进行管理。

### SQL 脚本

脚本分为三组脚本：

* BASE 脚本包含创建新数据库的所有 SQL 命令；
* UP 脚本包含所有数据库版本 "up" 的 SQL 迁移命令；
* DOWN 脚本包含所有数据库版本 "down" 或还原的 SQL 迁移命令；

脚本目录如下：

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
* "up" 文件夹包含迁移提升版本的脚本。
   例如：00002.sql 是将数据库从版本 '1' 移动到 '2' 的脚本。
* "down" 文件夹包含迁移降低版本的脚本。
   例如：00001.sql 是将数据库从版本 '2' 移动到 '1' 的脚本。
   "down" 文件夹是可选的。

### 多开发环境

如果您与多个开发人员和多个分支工作，确定下一个数字将会非常困难。

在这种情况下，您可以在版本号后面加上后缀 "-dev"。

看看这个场景：

* 开发人员 1 创建了一个分支，最新版本为例如 42。
* 开发人员 2 同时创建一个分支，并且有相同的数据库版本号。

在这两种情况下，开发人员将创建一个名为 43-dev.sql 的文件。两个开发人员将毫无问题地进行 UP 和 DOWN 迁移，您的本地版本将是 43。

但是开发人员 1 合并了您的更改并创建了最终版本 43.sql (`git mv 43-dev.sql 43.sql`)。如果开发人员 2 更新您的本地分支，他将拥有一个 43.sql 文件（来自开发人员 1）和您的 43-dev.sql 文件。
如果他试图进行 UP 或 DOWN 迁移，迁移脚本将出现问题并警告他存在两个版本 43。在这种情况下，开发人员 2 将需要更新他的文件为 44-dev.sql，并继续工作，直到合并您的更改并生成最终版本。

## 使用 PHP API 并将其集成到您的项目中

基本用法是

* 创建一个 ConnectionManagement 对象的连接。有关更多信息，请参见 "byjg/anydataset" 组件
* 使用此连接和脚本 SQL 所在文件夹创建 Migration 对象。
* 使用适当的命令进行 "reset"、"up" 或 "down" 迁移脚本。

查看一个示例：

```php
<?php
// 创建连接 URI
// 了解更多： https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// 注册可处理该 URI 的数据库：
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 创建 Migration 实例
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 添加一个回调进度函数以接收执行信息
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// 使用 "base.sql" 脚本恢复数据库
// 并运行所有现有脚本以将数据库版本提升到最新版本
$migration->reset();

// 运行所有现有脚本以向上或向下迁移数据库版本
// 从当前版本到 $version 编号；
// 如果未指定版本编号，则迁移到最后的数据库版本
$migration->update($version = null);
```

迁移对象控制数据库版本。

### 在您的项目中创建版本控制

```php
<?php
// 注册可处理该 URI 的数据库：
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 创建 Migration 实例
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

### 获取 Db 驱动实例

```php
<?php
$migration->getDbDriver();
```

要使用它，请访问： [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 避免部分迁移（不适用于 MySQL）

部分迁移是指因为错误或手动中断而在过程中中断迁移脚本。

迁移表将处于状态 `partial up` 或 `partial down`，需要手动修复才能再次迁移。

为了避免这种情况，您可以指定迁移将在事务上下文中运行。
如果迁移脚本失败，事务将被回滚，迁移表将标记为 `complete`，版本将是导致错误的脚本之前的立即前一个版本。

要启用此功能，您需要调用方法 `withTransactionEnabled`，并传递 `true` 作为参数：

```php
<?php
$migration->withTransactionEnabled(true);
```

**注意：此功能在 MySQL 中不可用，因为它不支持事务中的 DDL 命令**。
如果您在 MySQL 中使用此方法，迁移将悄默忽略它。
更多信息：[https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## 编写 Postgres SQL 迁移的提示

### 创建触发器和 SQL 函数

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- 检查 empname 和薪水是否已给出
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname 不能为空'; -- 这些注释是否为空并不重要
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% 不能有空薪水', NEW.empname; --
        END IF; --

        -- 谁为我们工作时必须为此支付？
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% 不能有负薪水', NEW.empname; --
        END IF; --

        -- 记住谁在何时更改工资单
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- 检查 empname 和薪水是否已给出
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname 不能为空';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% 不能有空薪水', NEW.empname;
        END IF;

        -- 谁为我们工作时必须为此支付？
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% 不能有负薪水', NEW.empname;
        END IF;

        -- 记住谁在何时更改工资单
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

由于 `PDO` 数据库抽象层无法运行SQL语句批处理，
当 `byjg/migration` 读取迁移文件时，必须在分号处分割整个 SQL 文件的内容，并逐个运行语句。然而，有一种语句可以在其主体中包含多个分号：函数。

为了正确解析函数，`byjg/migration` 从 2.1.0 版本开始，在分号 + 行结束符 (EOL) 的序列分割迁移文件，而不仅仅是分号。这样，如果您在每个函数定义的内部分号后附加一个空注释，`byjg/migration` 将能够解析它。

遗憾的是，如果您忘记添加任何这些注释，库将把 `CREATE FUNCTION` 语句分成多个部分，迁移将失败。

### 避免冒号字符（`:`）

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

由于 `PDO` 使用冒号字符作为准备语句中命名参数的前缀，因此在其他上下文中的使用会导致问题。

例如，PostgreSQL 语句可以使用 `::` 在类型之间转换值。另一方面，`PDO` 将把这视为无效命名参数并在无效上下文中失败。

解决这种不一致的唯一方法是完全避免冒号（在这种情况下，PostgreSQL 还有一种替代语法：`CAST(value AS type)`）。

### 使用 SQL 编辑器

最后，编写手动 SQL 迁移可能是繁琐的，但如果您使用能够理解 SQL 语法的编辑器，提供自动完成、检查当前数据库架构和/或自动格式化代码，这将简单得多。

## 处理同一架构中的不同迁移

如果您需要在同一架构内创建不同的迁移脚本和版本，这是可能的，但风险非常大，我 **不** 推荐这样做。

要做到这一点，您需要通过将参数传递给构造函数来创建不同的“迁移表”。

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

出于安全原因，此功能在命令行不可用，但您可以使用环境变量 `MIGRATION_VERSION` 来存储名称。

我们强烈建议不要使用此功能。推荐是一个架构一个迁移。

## 运行单元测试

基本单元测试可以通过以下方式运行：

```bash
vendor/bin/phpunit
```

## 运行数据库测试

运行集成测试需要您确保数据库已启用并正在运行。我们提供了一个基本的 `docker-compose.yml`，您可以用它来启动测试数据库。

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

您可以选择设置单元测试使用的主机和密码

```bash
export MYSQL_TEST_HOST=localhost     # 默认为 localhost
export MYSQL_PASSWORD=newpassword    # 如果想有一个空密码，请使用 '.'
export PSQL_TEST_HOST=localhost      # 默认为 localhost
export PSQL_PASSWORD=newpassword     # 如果想有一个空密码，请使用 '.'
export MSSQL_TEST_HOST=localhost     # 默认为 localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # 默认为 /tmp/test.db
```