# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) is a lightweight, fluent SQL query builder that generates SQL and parameters for prepared statements. Works with [SimplePdo](/learn/simple-pdo).

## Features

- 🔗 **Fluent API** - Chain methods for readable query construction
- 🛡️ **SQL Injection Protection** - Automatic parameter binding with prepared statements
- 🔧 **Raw SQL Support** - Insert raw SQL expressions with `raw()`
- 📝 **Multiple Query Types** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN Support** - INNER, LEFT, RIGHT joins with aliases
- 🎯 **Advanced Conditions** - LIKE, IN, NOT IN, BETWEEN, comparison operators
- 🌐 **Database Agnostic** - Returns SQL + params, use with any DB connection
- 🪶 **Lightweight** - Minimal footprint with zero dependencies

## Installation

```bash
composer require knifelemon/easy-query
```

## Quick Start

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Use with Flight's SimplePdo
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Understanding build()

The `build()` method returns an array with `sql` and `params`. This separation keeps your database safe by using prepared statements.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Returns:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Query Types

### SELECT

```php
// Select all columns
$q = Builder::table('users')->build();
// SELECT * FROM users

// Select specific columns
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// With table alias
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

## WHERE Conditions

### Simple Equality

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Comparison Operators

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

### OR Conditions

Use `orWhere()` to add OR grouped conditions:

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

### Multiple JOINs

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

## Ordering, Grouping, and Limits

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

### LIMIT and OFFSET

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

## Raw SQL Expressions

Use `raw()` when you need SQL functions or expressions that shouldn't be treated as bound parameters.

### Basic Raw

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

### Raw with Bound Parameters

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

### Raw in WHERE (Subquery)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Safe Identifiers for User Input

When column names come from user input, use `safeIdentifier()` to prevent SQL injection:

```php
$sortColumn = $_GET['sort'];  // e.g., 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// If user tries: "name; DROP TABLE users--"
// Throws InvalidArgumentException
```

### rawSafe for User-Provided Column Names

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Validates column name, throws exception if invalid
```

> **Warning:** Never concatenate user input directly into `raw()`. Always use bound parameters or `safeIdentifier()`.

---

## Query Builder Reuse

### Clear Methods

Clear specific parts to reuse the builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// First query
$q1 = $query->limit(10)->build();

// Clear and reuse
$query->clearWhere()->clearLimit();

// Second query with different conditions
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Available Clear Methods

| Method | Description |
|--------|-------------|
| `clearWhere()` | Clear WHERE conditions and parameters |
| `clearSelect()` | Reset SELECT columns to default '*' |
| `clearJoin()` | Clear all JOIN clauses |
| `clearGroupBy()` | Clear GROUP BY clause |
| `clearOrderBy()` | Clear ORDER BY clause |
| `clearLimit()` | Clear LIMIT and OFFSET |
| `clearAll()` | Reset builder to initial state |

### Pagination Example

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Get total count
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Get paginated results
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Dynamic Query Building

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

## Full FlightPHP Example

```php
use KnifeLemon\EasyQuery\Builder;

// List users with pagination
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

// Create user
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

// Update user
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

// Delete user
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

## API Reference

### Static Methods

| Method | Description |
|--------|-------------|
| `Builder::table(string $table)` | Create a new builder instance for the table |
| `Builder::raw(string $sql, array $bindings = [])` | Create a raw SQL expression |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Raw expression with safe identifier substitution |
| `Builder::safeIdentifier(string $identifier)` | Validate and return a safe column/table name |

### Instance Methods

| Method | Description |
|--------|-------------|
| `alias(string $alias)` | Set table alias |
| `select(string\|array $columns)` | Set columns to select (default: '*') |
| `where(array $conditions)` | Add WHERE conditions (AND) |
| `orWhere(array $conditions)` | Add OR WHERE conditions |
| `join(string $table, string $condition, string $alias, string $type)` | Add JOIN clause |
| `innerJoin(string $table, string $condition, string $alias)` | Add INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Add LEFT JOIN |
| `groupBy(string $groupBy)` | Add GROUP BY clause |
| `orderBy(string $orderBy)` | Add ORDER BY clause |
| `limit(int $limit, int $offset = 0)` | Add LIMIT and OFFSET |
| `count(string $column = '*')` | Set query to COUNT |
| `insert(array $data)` | Set query to INSERT |
| `update(array $data)` | Set query to UPDATE |
| `delete()` | Set query to DELETE |
| `build()` | Build and return `['sql' => ..., 'params' => ...]` |
| `get()` | Alias for `build()` |

---

## Tracy Debugger Integration

EasyQuery automatically integrates with Tracy Debugger if installed. No setup required!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// All queries are automatically logged to Tracy panel
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

The Tracy panel shows:
- Total queries and breakdown by type
- Generated SQL (syntax highlighted)
- Parameters array
- Query details (table, where, joins, etc.)

For full documentation, visit the [GitHub repository](https://github.com/knifelemon/EasyQueryBuilder).
