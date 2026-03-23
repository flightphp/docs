# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) — это легковесный, fluent SQL-запросостроитель, который генерирует SQL и параметры для подготовленных запросов. Работает с [SimplePdo](/learn/simple-pdo).

## Особенности

- 🔗 **Fluent API** — Цепочка методов для читаемого построения запросов
- 🛡️ **Защита от SQL-инъекций** — Автоматическая привязка параметров с подготовленными запросами
- 🔧 **Поддержка сырого SQL** — Вставка сырых SQL-выражений с помощью `raw()`
- 📝 **Множество типов запросов** — SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Поддержка JOIN** — INNER, LEFT, RIGHT соединения с алиасами
- 🎯 **Расширенные условия** — LIKE, IN, NOT IN, BETWEEN, операторы сравнения
- 🌐 **Независимость от базы данных** — Возвращает SQL + параметры, используйте с любой DB-связью
- 🪶 **Легковесность** — Минимальный след с нулевыми зависимостями

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

Метод `build()` возвращает массив с `sql` и `params`. Это разделение обеспечивает безопасность вашей базы данных с использованием подготовленных запросов.

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
// Выбор всех колонок
$q = Builder::table('users')->build();
// SELECT * FROM users

// Выбор конкретных колонок
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// С алиасом таблицы
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

Используйте `orWhere()` для добавления сгруппированных условий OR:

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

## Сырые SQL-выражения

Используйте `raw()` когда нужны SQL-функции или выражения, которые не должны обрабатываться как привязанные параметры.

### Базовый raw

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

### Raw с привязанными параметрами

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

Когда имена колонок приходят от пользователя, используйте `safeIdentifier()` для предотвращения SQL-инъекций:

```php
$sortColumn = $_GET['sort'];  // например, 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Если пользователь пытается: "name; DROP TABLE users--"
// Выбрасывается InvalidArgumentException
```

### rawSafe для имен колонок от пользователя

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Проверяет имя колонки, выбрасывает исключение если недействительно
```

> **Предупреждение:** Никогда не конкатенируйте пользовательский ввод напрямую в `raw()`. Всегда используйте привязанные параметры или `safeIdentifier()`.

---

## Повторное использование Query Builder

### Методы очистки

Очистите конкретные части для повторного использования билдера:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Первый запрос
$q1 = $query->limit(10)->build();

// Очистка и повторное использование
$query->clearWhere()->clearLimit();

// Второй запрос с другими условиями
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Доступные методы очистки

| Метод | Описание |
|--------|-------------|
| `clearWhere()` | Очистить условия WHERE и параметры |
| `clearSelect()` | Сбросить колонки SELECT на значение по умолчанию '*' |
| `clearJoin()` | Очистить все JOIN-клаузы |
| `clearGroupBy()` | Очистить клаузу GROUP BY |
| `clearOrderBy()` | Очистить клаузу ORDER BY |
| `clearLimit()` | Очистить LIMIT и OFFSET |
| `clearAll()` | Сбросить билдер в начальное состояние |

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

// Получить пагинированные результаты
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

// Создание пользователя
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

// Обновление пользователя
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

// Удаление пользователя
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

## Справочник API

### Статические методы

| Метод | Описание |
|--------|-------------|
| `Builder::table(string $table)` | Создать новый экземпляр билдера для таблицы |
| `Builder::raw(string $sql, array $bindings = [])` | Создать сырое SQL-выражение |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Сырое выражение с безопасной заменой идентификаторов |
| `Builder::safeIdentifier(string $identifier)` | Проверить и вернуть безопасное имя колонки/таблицы |

### Методы экземпляра

| Метод | Описание |
|--------|-------------|
| `alias(string $alias)` | Установить алиас таблицы |
| `select(string\|array $columns)` | Установить колонки для выбора (по умолчанию: '*') |
| `where(array $conditions)` | Добавить условия WHERE (AND) |
| `orWhere(array $conditions)` | Добавить условия OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Добавить клаузу JOIN |
| `innerJoin(string $table, string $condition, string $alias)` | Добавить INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Добавить LEFT JOIN |
| `groupBy(string $groupBy)` | Добавить клаузу GROUP BY |
| `orderBy(string $orderBy)` | Добавить клаузу ORDER BY |
| `limit(int $limit, int $offset = 0)` | Добавить LIMIT и OFFSET |
| `count(string $column = '*')` | Установить запрос на COUNT |
| `insert(array $data)` | Установить запрос на INSERT |
| `update(array $data)` | Установить запрос на UPDATE |
| `delete()` | Установить запрос на DELETE |
| `build()` | Построить и вернуть `['sql' => ..., 'params' => ...]` |
| `get()` | Псевдоним для `build()` |

---

## Интеграция с Tracy Debugger

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
- Общее количество запросов и разбивку по типам
- Сгенерированный SQL (с подсветкой синтаксиса)
- Массив параметров
- Детали запроса (таблица, where, joins и т.д.)

Для полной документации посетите [GitHub-репозиторий](https://github.com/knifelemon/EasyQueryBuilder).