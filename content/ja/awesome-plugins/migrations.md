# マイグレーション

プロジェクトのマイグレーションは、プロジェクトに関与するすべてのデータベース変更を追跡します。
[byjg/php-migration](https://github.com/byjg/php-migration) は、あなたが始めるのに非常に役立つコアライブラリです。

## インストール

### PHP ライブラリ

プロジェクトで PHP ライブラリのみを使用したい場合:

```bash
composer require "byjg/migration"
```

### コマンドラインインターフェース

コマンドラインインターフェースはスタンドアロンであり、プロジェクトにインストールする必要はありません。

グローバルにインストールし、シンボリックリンクを作成できます。

```bash
composer require "byjg/migration-cli"
```

マイグレーション CLI に関する詳細情報は、[byjg/migration-cli](https://github.com/byjg/migration-cli)をご覧ください。

## サポートされているデータベース

| データベース      | ドライバー                                                                          | 接続文字列                                        |
| ---------------- | ------------------------------------------------------------------------------- | -------------------------------------------------- |
| Sqlite           | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                             |
| MySql/MariaDb    | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database   |
| Postgres         | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database   |
| Sql Server       | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database   |
| Sql Server       | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows     | sqlsrv://username:password@hostname:port/database  |

## どのように機能しますか？

データベースマイグレーションは、データベースのバージョン管理に純粋な SQL を使用します。
機能させるためには、次のことを行う必要があります。

* SQL スクリプトを作成する
* コマンドラインまたは API を使用して管理する。

### SQL スクリプト

スクリプトは、3 つのセットのスクリプトに分かれています。

* BASE スクリプトは、新しいデータベースを作成するためのすべての SQL コマンドを含みます。
* UP スクリプトは、データベースのバージョンを "up" するためのすべての SQL マイグレーションコマンドを含みます。
* DOWN スクリプトは、データベースのバージョンを "down" するためのすべての SQL マイグレーションコマンドを含みます。

スクリプトディレクトリは次のとおりです：

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

* "base.sql" はベーススクリプトです
* "up" フォルダーには、バージョンをアップするためのスクリプトが含まれています。
   例えば: 00002.sql は、データベースをバージョン '1' から '2' へ移動させるためのスクリプトです。
* "down" フォルダーには、バージョンをダウンするためのスクリプトが含まれています。
   例えば: 00001.sql は、データベースをバージョン '2' から '1' へ移動させるためのスクリプトです。
   "down" フォルダーはオプションです。

### マルチ開発環境

複数の開発者や複数のブランチで作業する場合、次の番号を特定するのは難しいです。

その場合、バージョン番号の後にサフィックス "-dev" を付けます。

シナリオを見てみましょう：

* 開発者 1 がブランチを作成し、最新のバージョンが e.g. 42 です。
* 開発者 2 が同時にブランチを作成し、同じデータベースバージョン番号を持っています。

どちらの場合も、開発者は 43-dev.sql というファイルを作成します。 両方の開発者は UP と DOWN を問題なく移行し、あなたのローカルバージョンは 43 になります。

しかし、開発者 1 が変更をマージし、最終バージョン 43.sql を作成しました（`git mv 43-dev.sql 43.sql`）。 開発者 2 がローカルブランチを更新すると、彼はファイル 43.sql（dev 1 から）とファイル 43-dev.sql を持ちます。
彼が UP または DOWN に移行しようとすると、マイグレーションスクリプトはダウンし、バージョンが 43 の 2 つが存在することを警告します。その場合、開発者 2 はファイルを 44-dev.sql に更新し、変更をマージして最終バージョンを生成するまで作業を続行しなければなりません。

## PHP API を使用してプロジェクトに統合する

基本的な使用法は

* 接続管理オブジェクトの接続を作成する。詳細については、"byjg/anydataset" コンポーネントを参照してください。
* この接続と SQL スクリプトがあるフォルダーを使用してマイグレーションオブジェクトを作成します。
* マイグレーションスクリプトを "reset"、"up" または "down" のための適切なコマンドを使用します。

例えばを見る：

```php
<?php
// 接続 URI を作成
// 詳細: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// データベースまたはデータベースをその URI で処理するように登録します:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// マイグレーションインスタンスを作成する
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 実行からの情報を受け取るためにコールバック進捗関数を追加します
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// "base.sql" スクリプトを使用してデータベースを復元し
// データベースのバージョンを最新バージョンまでアップグレードするためのすべてのスクリプトを実行します
$migration->reset();

// 現在のバージョンから $version 番号までのデータベースのバージョンのためのすべてのスクリプトを実行します;
// バージョン番号が指定されていない場合、最後のデータベースバージョンまで移行します
$migration->update($version = null);
```

マイグレーションオブジェクトは、データベースのバージョンを制御します。

### プロジェクト内のバージョン管理作成

```php
<?php
// データベースまたはデータベースをその URI で処理するように登録します:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// マイグレーションインスタンスを作成する
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// このコマンドは、データベース内にバージョンテーブルを作成します
$migration->createVersion();
```

### 現在のバージョンの取得

```php
<?php
$migration->getCurrentVersion();
```

### コールバックを追加して進捗を制御

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "コマンドを実行中: $command バージョン $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Db ドライバーインスタンスの取得

```php
<?php
$migration->getDbDriver();
```

使用するには、次を訪れてください: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 部分的なマイグレーションを避ける（MySQL では利用できません）

部分的なマイグレーションは、エラーや手動の中断によりマイグレーションスクリプトがプロセスの途中で中断される場合です。

マイグレーションテーブルは `partial up` または `partial down` の状態になり、再度移行できるようになる前に手動で修正する必要があります。

この状況を避けるために、マイグレーションがトランザクショナルコンテキストで実行されることを指定できます。
マイグレーションスクリプトが失敗すると、トランザクションはロールバックされ、マイグレーションテーブルは `complete` とマークされ、
バージョンはエラーを引き起こしたスクリプトの直前のバージョンになります。

この機能を有効にするには、`withTransactionEnabled` メソッドを呼び出して、`true` をパラメータとして渡す必要があります：

```php
<?php
$migration->withTransactionEnabled(true);
```

**注: この機能は、MySQL では利用できません。DDL コマンドをトランザクション内でサポートしていないためです。**
このメソッドを MySQL で使用した場合、マイグレーションは静かに無視します。 
詳細情報: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Postgres 用の SQL マイグレーションを書く際のヒント

### トリガーと SQL 関数の作成時

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname と salary が指定されていることを確認します
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- これらのコメントが空でも関係ありません
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- 誰が私たちのために働いているのか、彼らはそれのために支払わなければなりませんか?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- 誰が給与を変更したのかを記憶して
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname と salary が指定されていることを確認します
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- 誰が私たちのために働いているのか、彼らはそれのために支払わなければなりませんか?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- 誰が給与を変更したのかを記憶して
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

`PDO` データベース抽象層は SQL ステートメントのバッチを実行できないため、`byjg/migration` がマイグレーションファイルを読み込むと、ファイルの内容全体をセミコロンで分割し、ステートメントを個別に実行する必要があります。ただし、1 つの種類のステートメントはその本体の間に複数のセミコロンを持つことがあります: 関数です。

関数を正しく解析できるようにするために、`byjg/migration` 2.1.0 からマイグレーションファイルを `セミコロン + EOL` シーケンスで分割するようになりました。これにより、関数定義の各内部セミコロンの後に空のコメントを追加すると、`byjg/migration` がそれを解析できるようになります。

不幸にも、これらのコメントのいずれかを追加するのを忘れると、ライブラリは `CREATE FUNCTION` ステートメントを複数の部分に分割し、マイグレーションは失敗します。

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

`PDO` は名前付きパラメータのプレフィックスとしてコロン文字を使用するため、他のコンテキストでの使用は問題を引き起こします。

例えば、PostgreSQL ステートメントは `::` を使用して型の間で値をキャストできます。 一方で、`PDO` はこれを無効な名前付きパラメータとして解釈し、無効なコンテキストで失敗します。

この不一致を修正する唯一の方法は、コロンを完全に避けることです（この場合、PostgreSQL にも代替構文があります: `CAST(value AS type)`）。

### SQL エディタを使用する

最後に、手動で SQL マイグレーションを書くことは面倒ですが、SQL 構文を理解し、オートコンプリートを提供し、現在のデータベーススキーマを調査しているエディタを使用すれば、格段に簡単になります。

## 1 つのスキーマ内での異なるマイグレーションの処理

同じスキーマ内で異なるマイグレーションスクリプトやバージョンを作成する必要がある場合、可能ですがリスクが高く、私は **全く推奨しません**。

これを行うには、コンストラクタにパラメータを渡して異なる "マイグレーションテーブル" を作成する必要があります。

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

セキュリティ上の理由から、この機能はコマンドラインでは利用できませんが、環境変数 `MIGRATION_VERSION` を使用して名前を保存できます。

この機能を使用しないことを強く推奨します。推奨は、1 つのスキーマにつき 1 つのマイグレーションです。

## ユニットテストの実行

基本的なユニットテストは次のように実行できます：

```bash
vendor/bin/phpunit
```

## データベーステストの実行

統合テストを実行するには、データベースを立ち上げておく必要があります。基本的な `docker-compose.yml` を提供しており、テストのためにデータベースを起動する際に使用できます。

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

オプションとして、ユニットテストで使用されるホストとパスワードを設定できます。

```bash
export MYSQL_TEST_HOST=localhost     # デフォルトは localhost
export MYSQL_PASSWORD=newpassword    # null パスワードが必要な場合は '.' を使用
export PSQL_TEST_HOST=localhost      # デフォルトは localhost
export PSQL_PASSWORD=newpassword     # null パスワードが必要な場合は '.' を使用
export MSSQL_TEST_HOST=localhost     # デフォルトは localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # デフォルトは /tmp/test.db
```