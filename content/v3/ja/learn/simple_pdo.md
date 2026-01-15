# SimplePdo PDO ヘルパークラス

## 概要

Flight の `SimplePdo` クラスは、PDO を使用したデータベース操作のためのモダンで機能豊富なヘルパーです。`PdoWrapper` を拡張し、`insert()`、`update()`、`delete()`、およびトランザクションなどの一般的なデータベース操作のための便利なヘルパーメソッドを追加します。データベースタスクを簡素化し、結果を [Collections](/learn/collections) として返して簡単なアクセスを可能にし、高度なユースケースのためのクエリログとアプリケーションのパフォーマンス監視 (APM) をサポートします。

## 理解

`SimplePdo` クラスは、PHP でのデータベース操作をはるかに簡単に設計されています。プリペアドステートメント、フェッチモード、冗長な SQL 操作を扱う代わりに、一般的なタスクのためのクリーンでシンプルなメソッドが得られます。各行は Collection として返されるため、配列表記 (`$row['name']`) とオブジェクト表記 (`$row->name`) の両方を使用できます。

このクラスは `PdoWrapper` のスーパーセットであり、`PdoWrapper` のすべての機能に加えて、コードをよりクリーンで保守しやすくする追加のヘルパーメソッドを含みます。現在 `PdoWrapper` を使用している場合、`SimplePdo` へのアップグレードは `PdoWrapper` を拡張しているため簡単です。

Flight で `SimplePdo` を共有サービスとして登録し、`Flight::db()` を使用してアプリのどこからでも使用できます。

## 基本的な使用方法

### SimplePdo の登録

まず、Flight に `SimplePdo` クラスを登録します：

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **注意**
>
> `PDO::ATTR_DEFAULT_FETCH_MODE` を指定しない場合、`SimplePdo` は自動的に `PDO::FETCH_ASSOC` に設定します。

これで、どこからでも `Flight::db()` を使用してデータベース接続を取得できます。

### クエリの実行

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

INSERT、UPDATE、または結果を手動でフェッチする場合に使用します：

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

`function fetchRow(string $sql, array $params = []): ?Collection`

単一の行を Collection (配列/オブジェクトアクセス) として入手します：

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// または
echo $user->name;
```

> **ヒント**
>
> `SimplePdo` は、`fetchRow()` クエリにすでに存在しない場合に自動的に `LIMIT 1` を追加し、クエリをより効率的にします。

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

すべての行を Collections の配列として入手します：

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // または
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

単一の列を配列としてフェッチします：

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// 戻り値: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

結果をキー-バリューペア (最初の列をキー、2番目の列を値) としてフェッチします：

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// 戻り値: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### `IN()` プレースホルダーの使用

`IN()` 句で単一の `?` を使用し、配列を渡すことができます：

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## ヘルパーメソッド

`SimplePdo` の `PdoWrapper` に対する主な利点の1つは、一般的なデータベース操作のための便利なヘルパーメソッドの追加です。

### `insert()`

`function insert(string $table, array $data): string`

1つまたは複数の行を挿入し、最後の挿入 ID を返します。

**単一の挿入：**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**一括挿入：**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

行を更新し、影響を受けた行の数を返します：

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **注意**
>
> SQLite の `rowCount()` は、データが実際に変更された行の数を返します。行を既存の値と同じ値で更新した場合、`rowCount()` は 0 を返します。これは、`PDO::MYSQL_ATTR_FOUND_ROWS` を使用した MySQL の動作とは異なります。

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

行を削除し、削除された行の数を返します：

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

トランザクション内でコールバックを実行します。トランザクションは成功時に自動的にコミットされ、エラー時にロールバックされます：

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

コールバック内で例外が発生した場合、トランザクションは自動的にロールバックされ、例外が再スローされます。

## 高度な使用方法

### クエリログと APM

クエリのパフォーマンスを追跡したい場合、登録時に APM 追跡を有効にします：

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* PDO options */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

クエリを実行した後、手動でログを記録できますが、有効にされている場合 APM は自動的にログを記録します：

```php
Flight::db()->logQueries();
```

これにより、接続とクエリメトリクスを含むイベント (`flight.db.queries`) がトリガーされ、Flight のイベントシステムを使用してリッスンできます。

### 完全な例

```php
Flight::route('/users', function () {
    // すべてのユーザーを取得
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // すべてのユーザーをストリーム
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // 単一のユーザーを取得
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // 単一の値を入手
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // 単一の列を取得
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // キー-バリューペアを取得
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // 特別な IN() 構文
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // 新しいユーザーを挿入
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // ユーザーを一括挿入
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // ユーザーを更新
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // ユーザーを削除
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // トランザクションを使用
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## PdoWrapper からの移行

現在 `PdoWrapper` を使用している場合、`SimplePdo` への移行は簡単です：

1. **登録を更新：**
   ```php
   // 旧
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // 新
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **すべての既存の `PdoWrapper` メソッドが `SimplePdo` で動作** - 破壊的な変更はありません。既存のコードは引き続き動作します。

3. **新しいヘルパーメソッドをオプションで使用** - `insert()`、`update()`、`delete()`、`transaction()` を使用してコードを簡素化します。

## 関連項目

- [Collections](/learn/collections) - 簡単なデータアクセスに Collection クラスを使用する方法を学びます。
- [PdoWrapper](/learn/pdo-wrapper) - レガシー PDO ヘルパークラス (非推奨)。

## トラブルシューティング

- データベース接続に関するエラーが発生した場合、DSN、ユーザー名、パスワード、オプションを確認してください。
- すべての行は Collections として返されます - プレーン配列が必要な場合、`$collection->getData()` を使用してください。
- `IN (?)` クエリの場合、配列を渡すことを確認してください。
- 長時間実行プロセスでクエリログによるメモリの問題が発生している場合、`maxQueryMetrics` オプションを調整してください。

## 変更履歴

- v3.18.0 - insert、update、delete、およびトランザクションのためのヘルパーメソッド付き SimplePdo の初期リリース。