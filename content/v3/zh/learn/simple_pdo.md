# SimplePdo PDO 辅助类

## 概述

Flight 中的 `SimplePdo` 类是一个现代的、功能丰富的 PDO 数据库操作辅助类。它扩展了 `PdoWrapper`，并添加了用于常见数据库操作的便捷辅助方法，如 `insert()`、`update()`、`delete()` 和事务。它简化了数据库任务，将结果返回为 [Collections](/learn/collections) 以便于访问，并支持查询日志记录和应用程序性能监控 (APM) 以用于高级用例。

## 理解

`SimplePdo` 类旨在使 PHP 中的数据库操作变得更加容易。与处理预处理语句、获取模式和冗长的 SQL 操作相比，您可以获得用于常见任务的干净、简单的方方法。每行都返回为 Collection，因此您可以使用数组表示法 (`$row['name']`) 和对象表示法 (`$row->name`)。

这个类是 `PdoWrapper` 的超集，这意味着它包含了 `PdoWrapper` 的所有功能加上额外的辅助方法，使您的代码更干净、更易维护。如果您当前使用 `PdoWrapper`，升级到 `SimplePdo` 非常简单，因为它扩展了 `PdoWrapper`。

您可以将 `SimplePdo` 注册为 Flight 中的共享服务，然后通过 `Flight::db()` 在您的应用中的任何地方使用它。

## 基本用法

### 注册 SimplePdo

首先，使用 Flight 注册 `SimplePdo` 类：

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
> 如果您没有指定 `PDO::ATTR_DEFAULT_FETCH_MODE`，`SimplePdo` 将自动为您设置为 `PDO::FETCH_ASSOC`。

现在，您可以在任何地方使用 `Flight::db()` 来获取数据库连接。

### 执行查询

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

用于 INSERT、UPDATE 或手动获取结果：

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row 是数组
}
```

您也可以用于写入操作：

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

`function fetchRow(string $sql, array $params = []): ?Collection`

获取单行作为 Collection（数组/对象访问）：

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// 或
echo $user->name;
```

> **提示**
>
> `SimplePdo` 会自动在 `fetchRow()` 查询中添加 `LIMIT 1`，如果尚未存在的话，这会使您的查询更高效。

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

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

获取单列作为数组：

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// 返回: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

获取结果作为键值对（第一列作为键，第二列作为值）：

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// 返回: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### 使用 `IN()` 占位符

您可以在 `IN()` 子句中使用单个 `?` 并传递数组：

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## 辅助方法

`SimplePdo` 相对于 `PdoWrapper` 的主要优势之一是添加了用于常见数据库操作的便捷辅助方法。

### `insert()`

`function insert(string $table, array $data): string`

插入一行或多行并返回最后插入的 ID。

**单个插入：**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**批量插入：**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

更新行并返回受影响的行数：

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
> SQLite 的 `rowCount()` 返回实际更改数据的行数。如果您用相同的现有值更新一行，`rowCount()` 将返回 0。这与使用 `PDO::MYSQL_ATTR_FOUND_ROWS` 时的 MySQL 行为不同。

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

删除行并返回删除的行数：

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

在事务中执行回调。事务在成功时自动提交，在错误时回滚：

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

如果在回调中抛出任何异常，事务将自动回滚并重新抛出异常。

## 高级用法

### 查询日志记录 & APM

如果您想跟踪查询性能，请在注册时启用 APM 跟踪：

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

在执行查询后，您可以手动记录它们，但如果启用，APM 将自动记录它们：

```php
Flight::db()->logQueries();
```

这将触发一个事件 (`flight.db.queries`)，包含连接和查询指标，您可以使用 Flight 的事件系统监听它。

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

    // 获取单列
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // 获取键值对
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // 特殊的 IN() 语法
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // 插入新用户
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // 批量插入用户
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // 更新用户
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // 删除用户
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // 使用事务
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## 从 PdoWrapper 迁移

如果您当前使用 `PdoWrapper`，迁移到 `SimplePdo` 非常简单：

1. **更新您的注册：**
   ```php
   // 旧版
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // 新版
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **所有现有的 `PdoWrapper` 方法在 `SimplePdo` 中都有效** - 没有破坏性更改。您现有的代码将继续工作。

3. **可选使用新辅助方法** - 开始使用 `insert()`、`update()`、`delete()` 和 `transaction()` 来简化您的代码。

## 另请参阅

- [Collections](/learn/collections) - 了解如何使用 Collection 类进行轻松数据访问。
- [PdoWrapper](/learn/pdo-wrapper) - 遗留的 PDO 辅助类（已弃用）。

## 故障排除

- 如果您收到数据库连接错误，请检查您的 DSN、用户名、密码和选项。
- 所有行都返回为 Collections——如果您需要普通数组，请使用 `$collection->getData()`。
- 对于 `IN (?)` 查询，请确保传递数组。
- 如果在长时间运行的进程中查询日志记录导致内存问题，请调整 `maxQueryMetrics` 选项。

## 更新日志

- v3.18.0 - SimplePdo 的初始发布，带有用于插入、更新、删除和事务的辅助方法。