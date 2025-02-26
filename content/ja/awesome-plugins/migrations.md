# マイグレーション

プロジェクトのマイグレーションは、プロジェクトに関与するすべてのデータベースの変更を追跡しています。
[byjg/php-migration](https://github.com/byjg/php-migration) は、始めるための非常に便利なコアライブラリです。

## インストール

### PHPライブラリ

プロジェクトでPHPライブラリだけを使用したい場合：

```bash
composer require "byjg/migration"
```

### コマンドラインインターフェース

コマンドラインインターフェースはスタンドアロンで、プロジェクトにインストールする必要はありません。

グローバルにインストールし、シンボリックリンクを作成できます。

```bash
composer require "byjg/migration-cli"
```

マイグレーションCLIについての詳細は、[byjg/migration-cli](https://github.com/byjg/migration-cli)を訪れてください。

## サポートされているデータベース

| データベース   | ドライバー                                                                         | 接続文字列                                             |
| --------------- | -------------------------------------------------------------------------------- | ----------------------------------------------------- |
| Sqlite         | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                     | sqlite:///path/to/file                                |
| MySql/MariaDb  | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                       | mysql://username:password@hostname:port/database      |
| Postgres       | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                       | pgsql://username:password@hostname:port/database      |
| Sql Server     | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux  | dblib://username:password@hostname:port/database      |
| Sql Server     | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows      | sqlsrv://username:password@hostname:port/database     |

## どのように機能しますか？

データベースマイグレーションは、データベースのバージョン管理に純粋なSQLを使用します。
動作させるためには、以下が必要です：

* SQLスクリプトを作成する
* コマンドラインまたはAPIを使用して管理する

### SQLスクリプト

スクリプトは3つのセットに分かれています：

* ベーススクリプトには、新しいデータベースを作成するためのすべてのSQLコマンドが含まれています。
* UPスクリプトには、データベースのバージョンを「アップ」するためのすべてのSQLマイグレーションコマンドが含まれています。
* DOWNスクリプトには、データベースのバージョンを「ダウン」またはリバートするためのすべてのSQLマイグレーションコマンドが含まれています。

ディレクトリスクリプトは次のようになります：

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

* "base.sql" はベーススクリプトです。
* "up"フォルダーには、バージョンをアップグレードするためのスクリプトが含まれています。
   例えば：00002.sqlは、データベースをバージョン '1' から '2' へ移行するためのスクリプトです。
* "down"フォルダーには、バージョンをダウングレードするためのスクリプトが含まれています。
   例えば：00001.sqlは、データベースをバージョン '2' から '1' へ移行するためのスクリプトです。
   "down"フォルダーはオプションです。

### 複数の開発環境

複数の開発者および複数のブランチで作業する場合、次の番号を特定するのが難しいです。

その場合、バージョン番号の後に「-dev」サフィックスを付けることができます。

シナリオを見てみましょう：

* 開発者1がブランチを作成し、最新のバージョンは例として42です。
* 開発者2も同時にブランチを作成し、同じデータベースバージョン番号を持っています。

いずれの場合も、開発者は43-dev.sqlというファイルを作成します。両方の開発者は問題なくUPとDOWNをマイグレートし、ローカルバージョンは43になります。

しかし、開発者1が変更をマージし、最終的なバージョン43.sqlを作成しました（`git mv 43-dev.sql 43.sql`）。開発者2がローカルブランチを更新すると、ファイル43.sql（dev 1のもの）とファイル43-dev.sqlを持つことになります。
もし彼がUPまたはDOWNをマイグレートしようとすると、マイグレーションスクリプトはダウンし、彼にバージョンが2つあることを警告します。その場合、開発者2は自分のファイルを44-dev.sqlに更新し、変更をマージして最終バージョンを生成するまで作業を続ける必要があります。

## PHP APIを使用してプロジェクトに統合する

基本的な使い方は次の通りです。

* 接続をConnectionManagementオブジェクトとして作成します。詳細については、「byjg/anydataset」コンポーネントを参照してください。
* この接続とSQLスクリプトが配置されているフォルダーを持つMigrationオブジェクトを作成します。
* マイグレーションスクリプトの「リセット」、「アップ」または「ダウン」のために適切なコマンドを使用します。

例を見てみましょう：

```php
<?php
// 接続URIを作成します
// 詳しくは：https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// データベースまたはデータベースを登録できます
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// マイグレーションインスタンスを作成します
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 実行からの情報を受け取るためのコールバック進行関数を追加します
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// "base.sql"スクリプトを使用してデータベースを復元し、
// データベースバージョンを最新バージョンまでアップグレードするためのすべての既存のスクリプトを実行します
$migration->reset();

// データベースバージョンを現在のバージョンから$version番号までアップまたはダウンするための
// すべての既存のスクリプトを実行します。
// バージョン番号が指定されていない場合、最後のデータベースバージョンまでマイグレートします
$migration->update($version = null);
```

マイグレーションオブジェクトがデータベースのバージョンを管理します。

### プロジェクト内のバージョン管理を作成する

```php
<?php
// データベースまたはデータベースを登録できます
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// マイグレーションインスタンスを作成します
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// このコマンドは、データベースにバージョンテーブルを作成します
$migration->createVersion();
```

### 現在のバージョン取得

```php
<?php
$migration->getCurrentVersion();
```

### 進行状況を制御するコールバックを追加

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "コマンドを実行中: $command バージョン $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Dbドライバーインスタンスの取得

```php
<?php
$migration->getDbDriver();
```

使用するには、次を訪問してください：[https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 部分的なマイグレーションの防止（MySQLでは使用不可）

部分的なマイグレーションは、マイグレーションスクリプトがエラーや手動による中断のためにプロセスの途中で中断されるときに発生します。

マイグレーションテーブルは「partial up」または「partial down」という状態になり、再度マイグレートする前に手動で修正が必要です。

この状況を避けるために、マイグレーションがトランザクショナルコンテキストで実行されるように指定できます。
マイグレーションスクリプトが失敗した場合、トランザクションはロールバックされ、マイグレーションテーブルは「complete」とマークされ、バージョンはエラーを引き起こしたスクリプトの直前のバージョンになります。

この機能を有効にするには、`withTransactionEnabled` メソッドを呼び出し、`true` をパラメーターとして渡す必要があります。

```php
<?php
$migration->withTransactionEnabled(true);
```

**注：この機能はMySQLでは使用できません。DDLコマンドをトランザクション内でサポートしていないためです。**
このメソッドをMySQLで使用すると、マイグレーションは静かに無視されます。 
詳細情報: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Postgres用のSQLマイグレーションを書くためのヒント

### トリガーとSQL関数の作成時

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empnameとsalaryが与えられていることを確認します
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empnameはnullにできません'; -- これらのコメントが空であっても問題ありません
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% はnullのsalaryを持つことができません', NEW.empname; --
        END IF; --

        -- 誰が私たちのために働かなくてはならないのか
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% は負のsalaryを持つことができません', NEW.empname; --
        END IF; --

        -- 誰がいつ給与を変更したかを覚えておいてください
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empnameとsalaryが与えられていることを確認します
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empnameはnullにできません';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% はnullのsalaryを持つことができません', NEW.empname;
        END IF;

        -- 誰が私たちのために働かなくてはならないのか
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% は負のsalaryを持つことができません', NEW.empname;
        END IF;

        -- 誰がいつ給与を変更したかを覚えておいてください
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

`PDO`データベース抽象化層はSQLステートメントのバッチを実行できないため、
`byjg/migration`はマイグレーションファイルを読み込むと、SQLファイルの内容全体をセミコロンで分割して
ステートメントを1つずつ実行する必要があります。ただし、関数の本体内には複数のセミコロンを持つことができる
ステートメントがあります。

関数を正しく解析できるようにするために、`byjg/migration` 2.1.0ではマイグレーション
ファイルをセミコロン + EOLのシーケンスで分割するようになりました。これにより、関数定義の
各内側のセミコロンの後に空のコメントを追加すれば、`byjg/migration`はそれを解析できるようになります。

残念ながら、これらのコメントを追加するのを忘れると、ライブラリは`CREATE FUNCTION`ステートメントを
複数の部分に分割し、マイグレーションは失敗します。

### コロン文字（`:`）を避ける

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

`PDO`は名前付きパラメーターを準備されたステートメントの接頭辞としてコロン文字を使用するため、他の
コンテキストでの使用は問題を引き起こします。

例えば、PostgreSQLステートメントは値を型間でキャストするために`::`を使用できます。一方、`PDO`は
これを無効な名前付きパラメーターとして読み取り、実行しようとすると失敗します。

この不一致を修正する唯一の方法は、コロンを完全に避けることです（この場合、PostgreSQLには代替構文もあり、
`CAST(value AS type)`があります）。

### SQLエディタを使用する

最後に、手動でSQLマイグレーションを書くことは疲れる可能性がありますが、
SQL構文を理解し、オートコンプリートを提供し、現在のデータベーススキーマを調査し、または
コードを自動整形する向上を提供するエディタを使用することで、はるかに簡単になります。

## 1つのスキーマ内での異なるマイグレーションの処理

同じスキーマ内で異なるマイグレーションスクリプトとバージョンを作成する必要がある場合は可能ですが、
リスクが高く、私は **お勧めしません**。

これを行うには、コンストラクタにパラメーターを渡すことで異なる「マイグレーションテーブル」を作成する必要があります。

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

セキュリティ上の理由から、この機能はコマンドラインでは使用できませんが、環境変数
`MIGRATION_VERSION`を使用して名前を保存できます。

この機能を使用しないことを強くお勧めします。推奨は、1つのスキーマに対して1つのマイグレーションです。

## ユニットテストの実行

基本的なユニットテストは次のように実行できます：

```bash
vendor/bin/phpunit
```

## データベーステストの実行

統合テストを実行するには、データベースが起動している必要があります。テスト用の基本的な `docker-compose.yml`を提供しており、
データベースを起動するために使用できます。

### データベースの実行

```bash
docker-compose up -d postgres mysql mssql
```

### テストを実行

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

オプションでユニットテストで使用されるホストとパスワードを設定できます。

```bash
export MYSQL_TEST_HOST=localhost     # デフォルトはlocalhost
export MYSQL_PASSWORD=newpassword    # nullパスワードが必要な場合は'.'を使用
export PSQL_TEST_HOST=localhost      # デフォルトはlocalhost
export PSQL_PASSWORD=newpassword     # nullパスワードが必要な場合は'.'を使用
export MSSQL_TEST_HOST=localhost     # デフォルトはlocalhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # デフォルトは/tmp/test.db
```