# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) ist ein leichtgewichtiger, fließender SQL-Query-Builder, der SQL und Parameter für vorbereitete Anweisungen generiert. Funktioniert mit [SimplePdo](/learn/simple-pdo).

## Features

- 🔗 **Fließende API** - Methoden verkettet für lesbare Abfragekonstruktion
- 🛡️ **SQL-Injection-Schutz** - Automatische Parameterbindung mit vorbereiteten Anweisungen
- 🔧 **Raw-SQL-Unterstützung** - Rohe SQL-Ausdrücke mit `raw()` einfügen
- 📝 **Mehrere Abfragetypen** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **JOIN-Unterstützung** - INNER, LEFT, RIGHT Joins mit Aliasen
- 🎯 **Erweiterte Bedingungen** - LIKE, IN, NOT IN, BETWEEN, Vergleichsoperatoren
- 🌐 **Datenbankunabhängig** - Gibt SQL + Parameter zurück, verwendbar mit jeder DB-Verbindung
- 🪶 **Leichtgewichtig** - Minimaler Footprint ohne Abhängigkeiten

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

// Verwenden mit Flights SimplePdo
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Understanding build()

Die Methode `build()` gibt ein Array mit `sql` und `params` zurück. Diese Trennung hält Ihre Datenbank sicher durch die Verwendung vorbereiteter Anweisungen.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Gibt zurück:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Query Types

### SELECT

```php
// Alle Spalten auswählen
$q = Builder::table('users')->build();
// SELECT * FROM users

// Spezifische Spalten auswählen
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Mit Tabellenalias
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

Verwenden Sie `orWhere()`, um OR-gruppierte Bedingungen hinzuzufügen:

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

Verwenden Sie `raw()`, wenn Sie SQL-Funktionen oder Ausdrücke benötigen, die nicht als gebundene Parameter behandelt werden sollen.

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

Wenn Spaltennamen aus Benutzereingaben stammen, verwenden Sie `safeIdentifier()`, um SQL-Injection zu verhindern:

```php
$sortColumn = $_GET['sort'];  // z.B. 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Wenn Benutzer versucht: "name; DROP TABLE users--"
// Wirft InvalidArgumentException
```

### rawSafe for User-Provided Column Names

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Validiert Spaltennamen, wirft Ausnahme bei ungültig
```

> **Warnung:** Konkatenieren Sie Benutzereingaben nie direkt in `raw()`. Verwenden Sie immer gebundene Parameter oder `safeIdentifier()`.

---

## Query Builder Reuse

### Clear Methods

Löschen Sie spezifische Teile, um den Builder wiederzuverwenden:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Erste Abfrage
$q1 = $query->limit(10)->build();

// Löschen und wiederverwenden
$query->clearWhere()->clearLimit();

// Zweite Abfrage mit anderen Bedingungen
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Available Clear Methods

| Method | Description |
|--------|-------------|
| `clearWhere()` | WHERE-Bedingungen und Parameter löschen |
| `clearSelect()` | SELECT-Spalten auf Standard '*' zurücksetzen |
| `clearJoin()` | Alle JOIN-Klauseln löschen |
| `clearGroupBy()` | GROUP BY-Klausel löschen |
| `clearOrderBy()` | ORDER BY-Klausel löschen |
| `clearLimit()` | LIMIT und OFFSET löschen |
| `clearAll()` | Builder auf Anfangszustand zurücksetzen |

### Pagination Example

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Gesamtzahl abrufen
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Paginierte Ergebnisse abrufen
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

// Benutzer mit Pagination auflisten
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

// Benutzer erstellen
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

// Benutzer aktualisieren
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

// Benutzer löschen
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
| `Builder::table(string $table)` | Neue Builder-Instanz für die Tabelle erstellen |
| `Builder::raw(string $sql, array $bindings = [])` | Roher SQL-Ausdruck erstellen |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Roher Ausdruck mit sicherer Identifier-Substitution |
| `Builder::safeIdentifier(string $identifier)` | Identifier validieren und sicheren Spalten-/Tabellennamen zurückgeben |

### Instance Methods

| Method | Description |
|--------|-------------|
| `alias(string $alias)` | Tabellenalias setzen |
| `select(string\|array $columns)` | Spalten zum Auswählen setzen (Standard: '*') |
| `where(array $conditions)` | WHERE-Bedingungen hinzufügen (AND) |
| `orWhere(array $conditions)` | OR-WHERE-Bedingungen hinzufügen |
| `join(string $table, string $condition, string $alias, string $type)` | JOIN-Klausel hinzufügen |
| `innerJoin(string $table, string $condition, string $alias)` | INNER JOIN hinzufügen |
| `leftJoin(string $table, string $condition, string $alias)` | LEFT JOIN hinzufügen |
| `groupBy(string $groupBy)` | GROUP BY-Klausel hinzufügen |
| `orderBy(string $orderBy)` | ORDER BY-Klausel hinzufügen |
| `limit(int $limit, int $offset = 0)` | LIMIT und OFFSET hinzufügen |
| `count(string $column = '*')` | Abfrage auf COUNT setzen |
| `insert(array $data)` | Abfrage auf INSERT setzen |
| `update(array $data)` | Abfrage auf UPDATE setzen |
| `delete()` | Abfrage auf DELETE setzen |
| `build()` | Bauen und `['sql' => ..., 'params' => ...]` zurückgeben |
| `get()` | Alias für `build()` |

---

## Tracy Debugger Integration

EasyQuery integriert sich automatisch mit Tracy Debugger, falls installiert. Keine Einrichtung erforderlich!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Alle Abfragen werden automatisch im Tracy-Panel protokolliert
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Das Tracy-Panel zeigt:
- Gesamtabfragen und Aufschlüsselung nach Typ
- Generiertes SQL (syntaxhervorgehoben)
- Parameter-Array
- Abfragedetails (Tabelle, where, joins usw.)

Für die vollständige Dokumentation besuchen Sie das [GitHub-Repository](https://github.com/knifelemon/EasyQueryBuilder).