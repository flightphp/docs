# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) es un constructor de consultas SQL ligero y fluido que genera SQL y parámetros para sentencias preparadas. Funciona con [SimplePdo](/learn/simple-pdo).

## Características

- 🔗 **API Fluida** - Métodos encadenados para construcción de consultas legibles
- 🛡️ **Protección contra Inyección SQL** - Vinculación automática de parámetros con sentencias preparadas
- 🔧 **Soporte Raw SQL** - Insertar expresiones SQL directamente con `raw()`
- 📝 **Múltiples Tipos de Consulta** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Soporte JOIN** - INNER, LEFT, RIGHT joins con alias
- 🎯 **Condiciones Avanzadas** - LIKE, IN, NOT IN, BETWEEN, operadores de comparación
- 🌐 **Agnóstico de Base de Datos** - Devuelve SQL + params, úsalo con cualquier conexión DB
- 🪶 **Ligero** - Huella mínima sin dependencias

## Instalación

```bash
composer require knifelemon/easy-query
```

## Inicio Rápido

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Usar con SimplePdo de Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Entendiendo build()

El método `build()` devuelve un array con `sql` y `params`. Esta separación mantiene tu base de datos segura usando sentencias preparadas.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Devuelve:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Tipos de Consulta

### SELECT

```php
// Seleccionar todas las columnas
$q = Builder::table('users')->build();
// SELECT * FROM users

// Seleccionar columnas específicas
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// Con alias de tabla
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

## Condiciones WHERE

### Igualdad Simple

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Operadores de Comparación

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

### Condiciones OR

Usa `orWhere()` para añadir condiciones agrupadas con OR:

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

### Múltiples JOINs

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

## Ordenamiento, Agrupación y Límites

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

### LIMIT y OFFSET

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

## Expresiones Raw SQL

Usa `raw()` cuando necesites funciones SQL o expresiones que no deben tratarse como parámetros vinculados.

### Raw Básico

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

### Raw con Parámetros Vinculados

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

### Raw en WHERE (Subconsulta)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Identificadores Seguros para Entrada de Usuario

Cuando los nombres de columna vienen de entrada de usuario, usa `safeIdentifier()` para prevenir inyección SQL:

```php
$sortColumn = $_GET['sort'];  // ej: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Si el usuario intenta: "name; DROP TABLE users--"
// Lanza InvalidArgumentException
```

### rawSafe para Nombres de Columna del Usuario

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Valida el nombre de columna, lanza excepción si es inválido
```

> **Advertencia:** Nunca concatenes entrada de usuario directamente en `raw()`. Siempre usa parámetros vinculados o `safeIdentifier()`.

---

## Reutilización del Query Builder

### Métodos Clear

Limpia partes específicas para reutilizar el builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Primera consulta
$q1 = $query->limit(10)->build();

// Limpiar y reutilizar
$query->clearWhere()->clearLimit();

// Segunda consulta con diferentes condiciones
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Métodos Clear Disponibles

| Método | Descripción |
|--------|-------------|
| `clearWhere()` | Limpiar condiciones WHERE y parámetros |
| `clearSelect()` | Resetear columnas SELECT al predeterminado '*' |
| `clearJoin()` | Limpiar todas las cláusulas JOIN |
| `clearGroupBy()` | Limpiar cláusula GROUP BY |
| `clearOrderBy()` | Limpiar cláusula ORDER BY |
| `clearLimit()` | Limpiar LIMIT y OFFSET |
| `clearAll()` | Resetear builder al estado inicial |

### Ejemplo de Paginación

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Obtener conteo total
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Obtener resultados paginados
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Construcción Dinámica de Consultas

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

## Ejemplo Completo FlightPHP

```php
use KnifeLemon\EasyQuery\Builder;

// Listar usuarios con paginación
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

// Crear usuario
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

// Actualizar usuario
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

// Eliminar usuario
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

## Referencia API

### Métodos Estáticos

| Método | Descripción |
|--------|-------------|
| `Builder::table(string $table)` | Crear nueva instancia del builder para la tabla |
| `Builder::raw(string $sql, array $bindings = [])` | Crear expresión SQL raw |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Expresión raw con sustitución segura de identificadores |
| `Builder::safeIdentifier(string $identifier)` | Validar y devolver nombre seguro de columna/tabla |

### Métodos de Instancia

| Método | Descripción |
|--------|-------------|
| `alias(string $alias)` | Establecer alias de tabla |
| `select(string\|array $columns)` | Establecer columnas a seleccionar (predeterminado: '*') |
| `where(array $conditions)` | Añadir condiciones WHERE (AND) |
| `orWhere(array $conditions)` | Añadir condiciones OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Añadir cláusula JOIN |
| `innerJoin(string $table, string $condition, string $alias)` | Añadir INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Añadir LEFT JOIN |
| `groupBy(string $groupBy)` | Añadir cláusula GROUP BY |
| `orderBy(string $orderBy)` | Añadir cláusula ORDER BY |
| `limit(int $limit, int $offset = 0)` | Añadir LIMIT y OFFSET |
| `count(string $column = '*')` | Establecer consulta a COUNT |
| `insert(array $data)` | Establecer consulta a INSERT |
| `update(array $data)` | Establecer consulta a UPDATE |
| `delete()` | Establecer consulta a DELETE |
| `build()` | Construir y devolver `['sql' => ..., 'params' => ...]` |
| `get()` | Alias de `build()` |

---

## Integración con Tracy Debugger

EasyQuery se integra automáticamente con Tracy Debugger si está instalado. ¡Sin configuración necesaria!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Todas las consultas se registran automáticamente en el panel Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

El panel Tracy muestra:
- Total de consultas y desglose por tipo
- SQL generado (resaltado de sintaxis)
- Array de parámetros
- Detalles de consulta (tabla, where, joins, etc.)

Para documentación completa, visita el [repositorio GitHub](https://github.com/knifelemon/EasyQueryBuilder).
