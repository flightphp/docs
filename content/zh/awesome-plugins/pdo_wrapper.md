
# PdoWrapper PDO Helper 类

Flight带有一个用于PDO的辅助类。它允许您轻松查询数据库并使用所有的prepared/execute/fetchAll()功能。它极大地简化了您查询数据库的方式。

## 注册PDO辅助类

```php
// 注册PDO辅助类
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## 用法
该对象扩展了PDO，因此所有常规的PDO方法都可以使用。以下方法是为了使查询数据库更加容易而添加的：

### `runQuery(string $sql, array $params = []): PDOStatement`
用于INSERTS、UPDATES或者如果您计划在while循环中使用SELECT时使用

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
从查询中提取第一个字段

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
从查询中提取一行

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
从查询中提取所有行

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// 执行某些操作
}
```

## `IN()` 语法注意事项
这还有一个有用的`IN()`语句的包装程序。您可以简单地传递一个问号作为`IN()`的占位符，然后是一个值数组。以下是一个可能的示例：

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 完整示例

```php
// 示例路由和如何使用此包装程序
Flight::route('/users', function () {
	// 获取所有用户
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// 流式传输所有用户
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// 获取单个用户
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 获取单个值
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// 特殊的IN()语法帮助 (确保IN大写)
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