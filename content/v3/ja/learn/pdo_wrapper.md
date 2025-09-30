# PdoWrapper PDO ヘルパー クラス

## 概要

Flight の `PdoWrapper` クラスは、PDO を使用してデータベースを扱うための便利なヘルパーです。共通のデータベースタスクを簡素化し、結果を取得するための便利なメソッドを追加し、結果を [Collections](/learn/collections) として返して簡単にアクセスできるようにします。また、クエリログとアプリケーション パフォーマンス監視 (APM) をサポートし、高度なユースケースに対応します。

## 理解

PHP でデータベースを扱う場合、特に PDO を直接使用すると冗長になりがちです。`PdoWrapper` は PDO を拡張し、クエリの実行、結果の取得、結果の処理をはるかに簡単にします。プリペアドステートメントやフェッチモードを扱う必要がなくなり、共通タスクのためのシンプルなメソッドが利用でき、すべての行が Collection として返されるため、配列やオブジェクト記法を使用できます。

`PdoWrapper` を Flight で共有サービスとして登録し、アプリケーションのどこからでも `Flight::db()` を使用して利用できます。

## 基本的な使用方法

### PDO ヘルパーの登録

まず、Flight に `PdoWrapper` クラスを登録します：

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

これで、どこからでも `Flight::db()` を使用してデータベース接続を取得できます。

### クエリの実行

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

INSERT や UPDATE、または結果を手動でフェッチしたい場合に使用します：

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

データベースから単一の値を取得します：

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

単一の行を Collection (配列/オブジェクトアクセス) として取得します：

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// または
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

すべての行を Collections の配列として取得します：

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

### クエリログ & APM

クエリのパフォーマンスを追跡したい場合、登録時に APM 追跡を有効にします：

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* オプション */], true // 最後のパラメータで APM を有効にします
]);
```

クエリを実行した後、手動でログを記録できますが、APM が有効な場合は自動的にログが記録されます：

```php
Flight::db()->logQueries();
```

これにより、接続とクエリメトリクスを含むイベント (`flight.db.queries`) がトリガーされ、Flight のイベントシステムを使用してリッスンできます。

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

    // 単一の値を取得
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

    // 影響を受けた行数を表示
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## 関連項目

- [Collections](/learn/collections) - Collection クラスを使用してデータを簡単にアクセスする方法を学びます。

## トラブルシューティング

- データベース接続エラーが発生した場合、DSN、ユーザー名、パスワード、オプションを確認してください。
- すべての行は Collections として返されます。プレーンな配列が必要な場合は、`$collection->getData()` を使用してください。
- `IN (?)` クエリの場合、配列またはカンマ区切りの文字列を渡すことを確認してください。

## 変更履歴

- v3.2.0 - 基本的なクエリとフェッチメソッドを備えた PdoWrapper の初回リリース。