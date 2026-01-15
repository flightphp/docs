# PdoWrapper PDO ヘルパークラス

> **警告**
>
> **非推奨:** `PdoWrapper` は Flight v3.18.0 以降非推奨です。将来的なバージョンで削除されることはありませんが、後方互換性のためメンテナンスされます。代わりに [SimplePdo](/learn/simple-pdo) を使用してください。これは同じ機能を提供し、共通のデータベース操作のための追加のヘルパーメソッドも提供します。

## 概要

Flight の `PdoWrapper` クラスは、PDO を使用してデータベースを操作するための親しみやすいヘルパーです。共通のデータベースタスクを簡素化し、結果を取得するための便利なメソッドを追加し、結果を [Collections](/learn/collections) として返して簡単にアクセスできるようにします。また、クエリログとアプリケーションのパフォーマンス監視 (APM) を高度なユースケースでサポートします。

## 理解

PHP でデータベースを操作する場合、特に PDO を直接使用すると冗長になることがあります。`PdoWrapper` は PDO を拡張し、クエリ、取得、結果の処理をはるかに簡単にします。準備されたステートメントやフェッチモードを扱う代わりに、共通のタスクのためのシンプルなメソッドが得られ、すべての行が Collection として返されるため、配列またはオブジェクト表記を使用できます。

`PdoWrapper` を Flight で共有サービスとして登録し、`Flight::db()` を介してアプリケーションのどこでも使用できます。

## 基本的な使用方法

### PDO ヘルパーの登録

まず、`PdoWrapper` クラスを Flight に登録します：

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

これで、`Flight::db()` をどこでも使用してデータベース接続を取得できます。

### クエリの実行

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

INSERT や UPDATE、または結果を手動で取得したい場合に使用します：

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row は配列です
}
```

書き込みにも使用できます：

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

データベースから単一の値を入手します：

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

単一の行を Collection (配列/オブジェクトアクセス) として入手します：

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// または
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

すべての行を Collection の配列として入手します：

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // または
    echo $user->name;
}
```

### `IN()` プレースホルダーの使用

`IN()` 句で単一の `?` を使用し、配列またはカンマ区切りの文字列を渡せます：

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// または
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## 高度な使用方法

### クエリログと APM

クエリのパフォーマンスを追跡したい場合、登録時に APM 追跡を有効にします：

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* オプション */], true // 最後のパラメータで APM を有効にします
]);
```

クエリを実行した後、手動でログを記録できますが、APM が有効な場合は自動的にログを記録します：

```php
Flight::db()->logQueries();
```

これはイベント (`flight.db.queries`) をトリガーし、接続とクエリメトリクスを含み、Flight のイベントシステムを使用してリッスンできます。

### 完全な例

```php
Flight::route('/users', function () {
    // すべてのユーザーを取得
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // すべてのユーザーをストリーミング
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // 単一のユーザーを取得
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // 単一の値を入手
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // 特別な IN() 構文
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // 新しいユーザーを挿入
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // ユーザーを更新
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // ユーザーを削除
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // 影響を受けた行数を入手
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## 関連項目

- [Collections](/learn/collections) - Collection クラスを使用してデータを簡単にアクセスする方法を学びます。

## トラブルシューティング

- データベース接続に関するエラーが発生した場合、DSN、ユーザー名、パスワード、オプションを確認してください。
- すべての行は Collection として返されます。プレーンな配列が必要な場合は、`$collection->getData()` を使用してください。
- `IN (?)` クエリの場合、配列またはカンマ区切りの文字列を渡すことを確認してください。

## 変更履歴

- v3.2.0 - 基本的なクエリとフェッチメソッドを含む PdoWrapper の初期リリース。