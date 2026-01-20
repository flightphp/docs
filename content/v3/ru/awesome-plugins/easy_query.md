# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) — это легковесный, fluent SQL-конструктор запросов, который генерирует SQL и параметры для prepared statements. Работает с [SimplePdo](/learn/simple-pdo).

## Возможности

- 🔗 **Fluent API** - Цепочные методы для читаемого построения запросов
- 🛡️ **Защита от SQL Injection** - Автоматическое связывание параметров с prepared statements
- 🔧 **Поддержка Raw SQL** - Вставка SQL-выражений напрямую через `raw()`
- 📝 **Различные типы запросов** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Поддержка JOIN** - INNER, LEFT, RIGHT joins с псевдонимами
- 🎯 **Расширенные условия** - LIKE, IN, NOT IN, BETWEEN, операторы сравнения
- 🌐 **Независимость от БД** - Возвращает SQL + params, используйте с любым соединением
- 🪶 **Легкий** - Минимальный размер без зависимостей

## Установка

```bash
composer require knifelemon/easy-query
```

## Быстрый старт

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Использование с SimplePdo Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Понимание build()

Метод `build()` возвращает массив с `sql` и `params`. Это разделение защищает вашу базу данных через использование prepared statements.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Возвращает:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Типы запросов

### SELECT

```php
// Выбрать все колонки
$q = Builder::table('users')->build();
// SELECT * FROM users

// Выбрать конкретные колонки
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// С псевдонимом таблицы
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

## Условия WHERE

### Простое равенство

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Операторы сравнения

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

### Условия OR

Используйте `orWhere()` для добавления сгруппированных условий с OR:

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

### Множественные JOIN

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

## Сортировка, группировка и лимиты

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

### LIMIT и OFFSET

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

## Raw SQL выражения

Используйте `raw()` когда нужны SQL-функции или выражения, которые не должны обрабатываться как связанные параметры.

### Базовый Raw

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

### Raw со связанными параметрами

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

### Raw в WHERE (подзапрос)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Безопасные идентификаторы для пользовательского ввода

Когда имена колонок приходят от пользователя, используйте `safeIdentifier()` для предотвращения SQL injection:

```php
$sortColumn = $_GET['sort'];  // например: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Если пользователь попробует: "name; DROP TABLE users--"
// Выбрасывает InvalidArgumentException
```

### rawSafe для пользовательских имен колонок

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Валидирует имя колонки, выбрасывает исключение если невалидное
```

> **Предупреждение:** Никогда не конкатенируйте пользовательский ввод напрямую в `raw()`. Всегда используйте связанные параметры или `safeIdentifier()`.

---

## Повторное использование Query Builder

### Методы Clear

Очистите конкретные части для повторного использования builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Первый запрос
$q1 = $query->limit(10)->build();

// Очистить и использовать повторно
$query->clearWhere()->clearLimit();

// Второй запрос с другими условиями
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Доступные методы Clear

| Метод | Описание |
|-------|----------|
| `clearWhere()` | Очистить условия WHERE и параметры |
| `clearSelect()` | Сбросить колонки SELECT к '*' по умолчанию |
| `clearJoin()` | Очистить все JOIN клаузы |
| `clearGroupBy()` | Очистить GROUP BY клаузу |
| `clearOrderBy()` | Очистить ORDER BY клаузу |
| `clearLimit()` | Очистить LIMIT и OFFSET |
| `clearAll()` | Сбросить builder в начальное состояние |

### Пример пагинации

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Получить общее количество
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Получить результаты с пагинацией
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Динамическое построение запросов

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

## Полный пример FlightPHP

```php
use KnifeLemon\EasyQuery\Builder;

// Список пользователей с пагинацией
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

// Создать пользователя
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

// Обновить пользователя
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

// Удалить пользователя
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

## API справочник

### Статические методы

| Метод | Описание |
|-------|----------|
| `Builder::table(string $table)` | Создать новый экземпляр builder для таблицы |
| `Builder::raw(string $sql, array $bindings = [])` | Создать raw SQL выражение |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Raw выражение с безопасной заменой идентификаторов |
| `Builder::safeIdentifier(string $identifier)` | Валидировать и вернуть безопасное имя колонки/таблицы |

### Методы экземпляра

| Метод | Описание |
|-------|----------|
| `alias(string $alias)` | Установить псевдоним таблицы |
| `select(string\|array $columns)` | Установить колонки для выборки (по умолчанию: '*') |
| `where(array $conditions)` | Добавить условия WHERE (AND) |
| `orWhere(array $conditions)` | Добавить условия OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Добавить JOIN клаузу |
| `innerJoin(string $table, string $condition, string $alias)` | Добавить INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Добавить LEFT JOIN |
| `groupBy(string $groupBy)` | Добавить GROUP BY клаузу |
| `orderBy(string $orderBy)` | Добавить ORDER BY клаузу |
| `limit(int $limit, int $offset = 0)` | Добавить LIMIT и OFFSET |
| `count(string $column = '*')` | Установить запрос на COUNT |
| `insert(array $data)` | Установить запрос на INSERT |
| `update(array $data)` | Установить запрос на UPDATE |
| `delete()` | Установить запрос на DELETE |
| `build()` | Построить и вернуть `['sql' => ..., 'params' => ...]` |
| `get()` | Псевдоним для `build()` |

---

## Интеграция Tracy Debugger

EasyQuery автоматически интегрируется с Tracy Debugger, если он установлен. Настройка не требуется!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Все запросы автоматически логируются в панель Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Панель Tracy показывает:
- Общее количество запросов и разбивку по типу
- Сгенерированный SQL (подсветка синтаксиса)
- Массив параметров
- Детали запроса (таблица, where, join и т.д.)

Для полной документации посетите [GitHub репозиторий](https://github.com/knifelemon/EasyQueryBuilder).
