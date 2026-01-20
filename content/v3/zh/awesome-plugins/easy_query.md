# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) 是一个轻量级的流畅式 SQL 查询构建器，用于生成 SQL 和参数。可与 [SimplePdo](/learn/simple-pdo) 配合使用。

## 特性

- 🔗 **流畅 API** - 链式方法构建可读性高的查询
- 🛡️ **SQL 注入防护** - 通过预处理语句自动参数绑定
- 🔧 **原生 SQL 支持** - 使用 `raw()` 插入 SQL 表达式
- 📝 **多种查询类型** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN 支持** - INNER, LEFT, RIGHT 连接和别名
- 🎯 **高级条件** - LIKE, IN, NOT IN, BETWEEN, 比较运算符
- 🌐 **数据库无关** - 返回 SQL + params，可用于任何数据库连接
- 🪶 **轻量级** - 零依赖，最小体积

## 安装

```bash
composer require knifelemon/easy-query
```

## 快速开始

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// 与 Flight 的 SimplePdo 配合使用
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## 理解 build()

`build()` 方法返回包含 `sql` 和 `params` 的数组。这种分离通过使用预处理语句来保护数据库安全。

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// 返回:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## 查询类型

### SELECT

```php
// 选择所有列
$q = Builder::table('users')->build();
// SELECT * FROM users

// 选择特定列
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// 使用表别名
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.id', 'u.name'])
    ->build();
// SELECT u.id, u.name FROM users AS u
```

### INSERT

```php
$q = Builder::table('users')
    ->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active'
    ])
    ->build();
// INSERT INTO users SET name = ?, email = ?, status = ?

Flight::db()->runQuery($q['sql'], $q['params']);
$userId = Flight::db()->lastInsertId();
```

### UPDATE

```php
$q = Builder::table('users')
    ->update(['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')])
    ->where(['id' => 123])
    ->build();
// UPDATE users SET status = ?, updated_at = ? WHERE id = ?

Flight::db()->runQuery($q['sql'], $q['params']);
```

### DELETE

```php
$q = Builder::table('users')
    ->delete()
    ->where(['id' => 123])
    ->build();
// DELETE FROM users WHERE id = ?

Flight::db()->runQuery($q['sql'], $q['params']);
```

### COUNT

```php
$q = Builder::table('users')
    ->count()
    ->where(['status' => 'active'])
    ->build();
// SELECT COUNT(*) AS cnt FROM users WHERE status = ?

$count = Flight::db()->fetchField($q['sql'], $q['params']);
```

---

## WHERE 条件

### 简单相等

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### 比较运算符

```php
$q = Builder::table('users')
    ->where([
        'age' => ['>=', 18],
        'score' => ['<', 100],
        'name' => ['!=', 'admin']
    ])
    ->build();
// WHERE age >= ? AND score < ? AND name != ?
```

### LIKE

```php
$q = Builder::table('users')
    ->where(['name' => ['LIKE', '%john%']])
    ->build();
// WHERE name LIKE ?
```

### IN / NOT IN

```php
// IN
$q = Builder::table('users')
    ->where(['id' => ['IN', [1, 2, 3, 4, 5]]])
    ->build();
// WHERE id IN (?, ?, ?, ?, ?)

// NOT IN
$q = Builder::table('users')
    ->where(['status' => ['NOT IN', ['banned', 'deleted']]])
    ->build();
// WHERE status NOT IN (?, ?)
```

### BETWEEN

```php
$q = Builder::table('products')
    ->where(['price' => ['BETWEEN', [100, 500]]])
    ->build();
// WHERE price BETWEEN ? AND ?
```

### OR 条件

使用 `orWhere()` 添加 OR 分组条件:

```php
$q = Builder::table('users')
    ->where(['status' => 'active'])
    ->orWhere([
        'role' => 'admin',
        'permissions' => ['LIKE', '%manage%']
    ])
    ->build();
// WHERE status = ? AND (role = ? OR permissions LIKE ?)
```

---

## JOIN

### INNER JOIN

```php
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.id', 'u.name', 'p.title'])
    ->innerJoin('posts', 'u.id = p.user_id', 'p')
    ->build();
// SELECT u.id, u.name, p.title FROM users AS u INNER JOIN posts AS p ON u.id = p.user_id
```

### LEFT JOIN

```php
$q = Builder::table('users')
    ->alias('u')
    ->select(['u.name', 'o.total'])
    ->leftJoin('orders', 'u.id = o.user_id', 'o')
    ->build();
// ... LEFT JOIN orders AS o ON u.id = o.user_id
```

### 多个 JOIN

```php
$q = Builder::table('orders')
    ->alias('o')
    ->select(['o.id', 'u.name AS customer', 'p.title AS product'])
    ->innerJoin('users', 'o.user_id = u.id', 'u')
    ->leftJoin('order_items', 'o.id = oi.order_id', 'oi')
    ->leftJoin('products', 'oi.product_id = p.id', 'p')
    ->where(['o.status' => 'completed'])
    ->build();
```

---

## 排序、分组和限制

### ORDER BY

```php
$q = Builder::table('users')
    ->orderBy('created_at DESC')
    ->build();
// ORDER BY created_at DESC
```

### GROUP BY

```php
$q = Builder::table('orders')
    ->select(['user_id', 'COUNT(*) as order_count'])
    ->groupBy('user_id')
    ->build();
// SELECT user_id, COUNT(*) as order_count FROM orders GROUP BY user_id
```

### LIMIT 和 OFFSET

```php
$q = Builder::table('users')
    ->limit(10)
    ->build();
// LIMIT 10

$q = Builder::table('users')
    ->limit(10, 20)  // limit, offset
    ->build();
// LIMIT 10 OFFSET 20
```

---

## 原生 SQL 表达式

当需要不应作为绑定参数处理的 SQL 函数或表达式时，使用 `raw()`。

### 基本 Raw

```php
$q = Builder::table('users')
    ->update([
        'login_count' => Builder::raw('login_count + 1'),
        'updated_at' => Builder::raw('NOW()')
    ])
    ->where(['id' => 123])
    ->build();
// SET login_count = login_count + 1, updated_at = NOW()
```

### 带绑定参数的 Raw

```php
$q = Builder::table('orders')
    ->update([
        'total' => Builder::raw('COALESCE(subtotal, ?) + ?', [0, 10])
    ])
    ->where(['id' => 1])
    ->build();
// SET total = COALESCE(subtotal, ?) + ?
// params: [0, 10, 1]
```

### WHERE 中的 Raw（子查询）

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### 用户输入的安全标识符

当列名来自用户输入时，使用 `safeIdentifier()` 防止 SQL 注入:

```php
$sortColumn = $_GET['sort'];  // 例如: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// 如果用户尝试: "name; DROP TABLE users--"
// 抛出 InvalidArgumentException
```

### 用户提供列名的 rawSafe

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// 验证列名，无效时抛出异常
```

> **警告:** 永远不要直接将用户输入连接到 `raw()`。始终使用绑定参数或 `safeIdentifier()`。

---

## 查询构建器重用

### Clear 方法

清除特定部分以重用构建器:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 第一个查询
$q1 = $query->limit(10)->build();

// 清除并重用
$query->clearWhere()->clearLimit();

// 使用不同条件的第二个查询
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### 可用的 Clear 方法

| 方法 | 描述 |
|------|------|
| `clearWhere()` | 清除 WHERE 条件和参数 |
| `clearSelect()` | 将 SELECT 列重置为默认 '*' |
| `clearJoin()` | 清除所有 JOIN 子句 |
| `clearGroupBy()` | 清除 GROUP BY 子句 |
| `clearOrderBy()` | 清除 ORDER BY 子句 |
| `clearLimit()` | 清除 LIMIT 和 OFFSET |
| `clearAll()` | 将构建器重置为初始状态 |

### 分页示例

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// 获取总数
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// 获取分页结果
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## 动态查询构建

```php
$query = Builder::table('products')->alias('p');

if (!empty($categoryId)) {
    $query->where(['p.category_id' => $categoryId]);
}

if (!empty($minPrice)) {
    $query->where(['p.price' => ['>=', $minPrice]]);
}

if (!empty($maxPrice)) {
    $query->where(['p.price' => ['<=', $maxPrice]]);
}

if (!empty($searchTerm)) {
    $query->where(['p.name' => ['LIKE', "%{$searchTerm}%"]]);
}

$result = $query->orderBy('p.created_at DESC')->limit(20)->build();
$products = Flight::db()->fetchAll($result['sql'], $result['params']);
```

---

## FlightPHP 完整示例

```php
use KnifeLemon\EasyQuery\Builder;

// 带分页的用户列表
Flight::route('GET /users', function() {
    $page = (int) (Flight::request()->query['page'] ?? 1);
    $perPage = 20;

    $q = Builder::table('users')
        ->select(['id', 'name', 'email', 'created_at'])
        ->where(['status' => 'active'])
        ->orderBy('created_at DESC')
        ->limit($perPage, ($page - 1) * $perPage)
        ->build();
    
    $users = Flight::db()->fetchAll($q['sql'], $q['params']);
    Flight::json(['users' => $users, 'page' => $page]);
});

// 创建用户
Flight::route('POST /users', function() {
    $data = Flight::request()->data;
    
    $q = Builder::table('users')
        ->insert([
            'name' => $data->name,
            'email' => $data->email,
            'created_at' => Builder::raw('NOW()')
        ])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['id' => Flight::db()->lastInsertId()]);
});

// 更新用户
Flight::route('PUT /users/@id', function($id) {
    $data = Flight::request()->data;
    
    $q = Builder::table('users')
        ->update([
            'name' => $data->name,
            'email' => $data->email,
            'updated_at' => Builder::raw('NOW()')
        ])
        ->where(['id' => $id])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['success' => true]);
});

// 删除用户
Flight::route('DELETE /users/@id', function($id) {
    $q = Builder::table('users')
        ->delete()
        ->where(['id' => $id])
        ->build();
    
    Flight::db()->runQuery($q['sql'], $q['params']);
    Flight::json(['success' => true]);
});
```

---

## API 参考

### 静态方法

| 方法 | 描述 |
|------|------|
| `Builder::table(string $table)` | 为表创建新的构建器实例 |
| `Builder::raw(string $sql, array $bindings = [])` | 创建原生 SQL 表达式 |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | 带安全标识符替换的原生表达式 |
| `Builder::safeIdentifier(string $identifier)` | 验证并返回安全的列/表名 |

### 实例方法

| 方法 | 描述 |
|------|------|
| `alias(string $alias)` | 设置表别名 |
| `select(string\|array $columns)` | 设置要选择的列（默认: '*'） |
| `where(array $conditions)` | 添加 WHERE 条件（AND） |
| `orWhere(array $conditions)` | 添加 OR WHERE 条件 |
| `join(string $table, string $condition, string $alias, string $type)` | 添加 JOIN 子句 |
| `innerJoin(string $table, string $condition, string $alias)` | 添加 INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | 添加 LEFT JOIN |
| `groupBy(string $groupBy)` | 添加 GROUP BY 子句 |
| `orderBy(string $orderBy)` | 添加 ORDER BY 子句 |
| `limit(int $limit, int $offset = 0)` | 添加 LIMIT 和 OFFSET |
| `count(string $column = '*')` | 设置为 COUNT 查询 |
| `insert(array $data)` | 设置为 INSERT 查询 |
| `update(array $data)` | 设置为 UPDATE 查询 |
| `delete()` | 设置为 DELETE 查询 |
| `build()` | 构建并返回 `['sql' => ..., 'params' => ...]` |
| `get()` | `build()` 的别名 |

---

## Tracy 调试器集成

如果安装了 Tracy Debugger，EasyQuery 会自动集成。无需配置！

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// 所有查询自动记录到 Tracy 面板
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Tracy 面板显示:
- 总查询数和类型分类
- 生成的 SQL（语法高亮）
- 参数数组
- 查询详情（表、where、join 等）

完整文档请访问 [GitHub 仓库](https://github.com/knifelemon/EasyQueryBuilder)。
