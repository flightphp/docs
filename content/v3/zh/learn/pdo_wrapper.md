# PdoWrapper PDO 辅助类

## 概述

Flight 中的 `PdoWrapper` 类是一个友好的辅助工具，用于使用 PDO 处理数据库。它简化了常见的数据库任务，添加了一些方便的方法来获取结果，并将结果返回为 [Collections](/learn/collections)，便于访问。它还支持查询日志记录和应用程序性能监控 (APM)，适用于高级用例。

## 理解

在 PHP 中处理数据库可能有点冗长，尤其是直接使用 PDO 时。`PdoWrapper` 扩展了 PDO，并添加了使查询、获取和处理结果更容易的方法。不再需要处理预准备语句和获取模式，您可以获得简单的方法来处理常见任务，并且每行都返回为 Collection，因此您可以使用数组或对象表示法。

您可以将 `PdoWrapper` 注册为 Flight 中的共享服务，然后在您的应用程序的任何地方通过 `Flight::db()` 使用它。

## 基本用法

### 注册 PDO 辅助工具

首先，将 `PdoWrapper` 类与 Flight 注册：

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

现在，您可以在任何地方使用 `Flight::db()` 来获取数据库连接。

### 运行查询

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

用于 INSERT、UPDATE，或者当您想要手动获取结果时：

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row 是数组
}
```

您也可以用于写入：

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

从数据库获取单个值：

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

获取单行作为 Collection（数组/对象访问）：

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// 或
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

获取所有行作为 Collection 数组：

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // 或
    echo $user->name;
}
```

### 使用 `IN()` 占位符

您可以在 `IN()` 子句中使用单个 `?`，并传递数组或逗号分隔的字符串：

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// 或
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## 高级用法

### 查询日志记录 & APM

如果您想要跟踪查询性能，请在注册时启用 APM 跟踪：

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // 最后一个参数启用 APM
]);
```

运行查询后，您可以手动记录它们，但如果启用，APM 会自动记录它们：

```php
Flight::db()->logQueries();
```

这将触发一个事件（`flight.db.queries`），包含连接和查询指标，您可以使用 Flight 的事件系统监听它。

### 完整示例

```php
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

    // 特殊的 IN() 语法
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

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

## 另请参阅

- [Collections](/learn/collections) - 了解如何使用 Collection 类进行简单的数据访问。

## 故障排除

- 如果您收到关于数据库连接的错误，请检查您的 DSN、用户名、密码和选项。
- 所有行都返回为 Collections——如果您需要普通数组，请使用 `$collection->getData()`。
- 对于 `IN (?)` 查询，请确保传递数组或逗号分隔的字符串。

## 更新日志

- v3.2.0 - PdoWrapper 的初始发布，带有基本查询和获取方法。