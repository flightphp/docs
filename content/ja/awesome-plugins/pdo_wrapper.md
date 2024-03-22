# PdoWrapper PDO ヘルパークラス

Flight には PDO のヘルパークラスが付属しています。これにより、データベースへのクエリを簡単に実行することができます
全ての prepared/execute/fetchAll() の混乱を解消します。データベースへのクエリがどのように簡素化されるかが大幅に向上します。各行の結果は Flight Collection クラスとして返され、配列構文またはオブジェクト構文を使用してデータにアクセスできます。

## PDO ヘルパークラスの登録

```php
// PDO ヘルパークラスの登録
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## 使用方法
このオブジェクトは PDO を拡張しているため、通常の PDO メソッドがすべて使用できます。データベースのクエリをより簡単に行うために、以下のメソッドが追加されています:

### `runQuery(string $sql, array $params = []): PDOStatement`
INSERT、UPDATE、または while ループ内で SELECT を使用する場合に使用します

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
クエリから最初のフィールドを取得します

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
クエリから1行を取得します

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// または
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
クエリからすべての行を取得します

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// または
	echo $row->name;
}
```

## `IN()` 構文との注意
`IN()` ステートメントのための便利なラッパーもあります。 `IN()` のプレースホルダーとして単一のクエスチョンマークを渡し、その後に値の配列を簡単に渡すことができます。以下はその例です:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 完全な例

```php
// 例として、ルートとこのラッパーの使用方法
Flight::route('/users', function () {
	// すべてのユーザーを取得
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// すべてのユーザーをストリーム化
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// または echo $user->name;
	}

	// 個々のユーザーを取得
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 単一の値を取得
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// ヘルプ用の特別な IN() 構文 (IN が大文字であることを確認してください)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// これでも可能です
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// 新しいユーザーを挿入
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// ユーザーを更新
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// ユーザーを削除
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// 影響を受ける行の数を取得
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```