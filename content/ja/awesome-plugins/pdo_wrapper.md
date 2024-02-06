
# PdoWrapper PDO ヘルパークラス

Flight には PDO のためのヘルパークラスが付属しています。これにより、すべての prepared/execute/fetchAll() の難しさを簡単にデータベースにクエリすることができます。データベースのクエリ方法を大幅に簡略化します。

## PDO ヘルパークラスの登録

```php
// PDO ヘルパークラスを登録
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## 使用法
このオブジェクトは PDO を拡張しているため、通常の PDO メソッドがすべて利用可能です。次のメソッドはデータベースのクエリを容易にするために追加されています:

### `runQuery(string $sql, array $params = []): PDOStatement`
INSERT、UPDATE、または while ループで SELECT を使用する場合に使用します

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// またはデータベースに書き込み
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
クエリから最初のフィールドを取得します

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
クエリから1行を取得します

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
クエリからすべての行を取得します

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// 何かをします
}
```

## `IN()` 構文について
これには、`IN()` 文のための便利なラッパーもあります。`IN()` のプレースホルダーとして単純に 1 つの疑問符を渡し、その後に値の配列を渡すだけです。以下はその例です:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 完全な例

```php
// 例とこのラッパーの使用方法
Flight::route('/users', function () {
	// すべてのユーザーを取得
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// すべてのユーザーに対して処理
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// 単一のユーザーを取得
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 単一の値を取得
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// IN() 構文を使用して特定 (IN が大文字であることを確認してください)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// これもできます
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// 新しいユーザーを挿入
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// ユーザーを更新
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// ユーザーを削除
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// 影響を受けた行の数を取得
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```