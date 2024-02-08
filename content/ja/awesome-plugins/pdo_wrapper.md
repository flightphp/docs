# PdoWrapper PDO ヘルパークラス

Flight には PDO 用のヘルパークラスが付属しています。これにより、準備/実行/fetchAll() の一連の手順を簡単にデータベースにクエリできます。データベースのクエリ方法を大幅に簡略化します。

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
このオブジェクトは PDO を拡張しているため、通常の PDO メソッドすべてが利用可能です。以下のメソッドが追加され、データベースのクエリを簡単にします:

### `runQuery(string $sql, array $params = []): PDOStatement`
これを使用して、INSERT、UPDATE、または while ループでの SELECT を使用する場合に使用します。

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// またはデータベースへの書き込み
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
クエリから最初のフィールドを取得します。

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
クエリから1行取得します。

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
クエリからすべての行を取得します。

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// 何かを行います
}
```

## `IN()` 構文の注意
これには `IN()` ステートメントのための便利なラッパーもあります。`IN()` のプレースホルダーとして単に1つのクエスチョンマークを渡し、その後に値の配列を渡すことができます。以下はその例です:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 完全な例

```php
// 例: ルートとこのラッパーの使用方法
Flight::route('/users', function () {
	// すべてのユーザーを取得
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// すべてのユーザーをストリームで取得
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// 1人のユーザーを取得
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 1つの値を取得
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// お手伝いするための特別な IN() 構文
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