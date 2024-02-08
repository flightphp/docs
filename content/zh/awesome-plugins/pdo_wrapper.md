# PdoWrapper PDO 助手类

Flight 自带一个 PDO 助手类。它允许您轻松地使用全部预准备/执行/fetchAll() 操作来查询您的数据库。极大地简化了您查询数据库的方式。

## 注册 PDO 助手类

```php
// 注册 PDO 助手类
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## 用法
此对象扩展了 PDO，因此所有常规的 PDO 方法都可用。以下方法是为了更轻松地查询数据库而添加的：

### `runQuery(string $sql, array $params = []): PDOStatement`
用于 INSERTS、UPDATES，或者如果您打算在 while 循环中使用 SELECT

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// 或者写入数据库
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
从查询结果中提取第一个字段

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
从查询结果中获取一行

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
从查询结果中获取所有行

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// 做一些事情
}
```

## 关于 `IN()` 语法的注意事项
这还有一个有用的 `IN()` 语句包装器。您可以简单地传递一个问号作为 `IN()` 的占位符，然后是一个值数组。以下是可能的例子：

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 完整示例

```php
// 示例路由以及如何使用此包装器
Flight::route('/users', function () {
	// 获取全部用户
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// 流式传输全部用户
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// 获取单个用户
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 获取单个值
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// 特殊的 IN() 语法以帮助您（确保 IN 大写）
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// 您也可以这样做
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// 插入新用户
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// 更新用户
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// 删除用户
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// 获取受影响的行数
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```