# EasyQuery

[knifelemon/easy-query](https://github.com/knifelemon/EasyQueryBuilder) — це легкий, fluent SQL-конструктор запитів, який генерує SQL та параметри для prepared statements. Працює з [SimplePdo](/learn/simple-pdo).

## Можливості

- 🔗 **Fluent API** - Ланцюгові методи для читабельної побудови запитів
- 🛡️ **Захист від SQL Injection** - Автоматичне зв'язування параметрів з prepared statements
- 🔧 **Підтримка Raw SQL** - Вставка SQL-виразів напряму через `raw()`
- 📝 **Різні типи запитів** - SELECT, INSERT, UPDATE, DELETE, COUNT
- 🔀 **Підтримка JOIN** - INNER, LEFT, RIGHT joins з псевдонімами
- 🎯 **Розширені умови** - LIKE, IN, NOT IN, BETWEEN, оператори порівняння
- 🌐 **Незалежність від БД** - Повертає SQL + params, використовуйте з будь-яким з'єднанням
- 🪶 **Легкий** - Мінімальний розмір без залежностей

## Встановлення

```bash
composer require knifelemon/easy-query
```

## Швидкий старт

```php
use KnifeLemon\EasyQuery\Builder;

$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC')
    ->limit(10)
    ->build();

// Використання з SimplePdo Flight
$users = Flight::db()->fetchAll($q['sql'], $q['params']);
```

## Розуміння build()

Метод `build()` повертає масив з `sql` та `params`. Це розділення захищає вашу базу даних через використання prepared statements.

```php
$q = Builder::table('users')
    ->where(['email' => 'user@example.com'])
    ->build();

// Повертає:
// [
//     'sql' => 'SELECT * FROM users WHERE email = ?',
//     'params' => ['user@example.com']
// ]
```

---

## Типи запитів

### SELECT

```php
// Вибрати всі колонки
$q = Builder::table('users')->build();
// SELECT * FROM users

// Вибрати конкретні колонки
$q = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->build();
// SELECT id, name, email FROM users

// З псевдонімом таблиці
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

## Умови WHERE

### Проста рівність

```php
$q = Builder::table('users')
    ->where(['id' => 123, 'status' => 'active'])
    ->build();
// WHERE id = ? AND status = ?
```

### Оператори порівняння

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

### Умови OR

Використовуйте `orWhere()` для додавання згрупованих умов з OR:

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

### Множинні JOIN

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

## Сортування, групування та ліміти

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

### LIMIT та OFFSET

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

## Raw SQL вирази

Використовуйте `raw()` коли потрібні SQL-функції або вирази, які не повинні оброблятися як зв'язані параметри.

### Базовий Raw

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

### Raw зі зв'язаними параметрами

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

### Raw у WHERE (підзапит)

```php
$q = Builder::table('products')
    ->where([
        'price' => ['>', Builder::raw('(SELECT AVG(price) FROM products)')]
    ])
    ->build();
// WHERE price > (SELECT AVG(price) FROM products)
```

### Безпечні ідентифікатори для користувацького вводу

Коли імена колонок надходять від користувача, використовуйте `safeIdentifier()` для запобігання SQL injection:

```php
$sortColumn = $_GET['sort'];  // наприклад: 'created_at'
$safeColumn = Builder::safeIdentifier($sortColumn);

$q = Builder::table('users')
    ->orderBy($safeColumn . ' DESC')
    ->build();

// Якщо користувач спробує: "name; DROP TABLE users--"
// Викидає InvalidArgumentException
```

### rawSafe для користувацьких імен колонок

```php
$userColumn = $_GET['aggregate_column'];

$q = Builder::table('orders')
    ->select([
        Builder::rawSafe('SUM({col})', ['col' => $userColumn])->value . ' AS total'
    ])
    ->build();
// Валідує ім'я колонки, викидає виняток якщо невалідне
```

> **Увага:** Ніколи не конкатенуйте користувацький ввід напряму в `raw()`. Завжди використовуйте зв'язані параметри або `safeIdentifier()`.

---

## Повторне використання Query Builder

### Методи Clear

Очистіть конкретні частини для повторного використання builder:

```php
$query = Builder::table('users')
    ->select(['id', 'name'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Перший запит
$q1 = $query->limit(10)->build();

// Очистити та використати повторно
$query->clearWhere()->clearLimit();

// Другий запит з іншими умовами
$q2 = $query
    ->where(['status' => 'pending'])
    ->limit(5)
    ->build();
```

### Доступні методи Clear

| Метод | Опис |
|-------|------|
| `clearWhere()` | Очистити умови WHERE та параметри |
| `clearSelect()` | Скинути колонки SELECT до '*' за замовчуванням |
| `clearJoin()` | Очистити всі JOIN клаузи |
| `clearGroupBy()` | Очистити GROUP BY клаузу |
| `clearOrderBy()` | Очистити ORDER BY клаузу |
| `clearLimit()` | Очистити LIMIT та OFFSET |
| `clearAll()` | Скинути builder до початкового стану |

### Приклад пагінації

```php
$baseQuery = Builder::table('users')
    ->select(['id', 'name', 'email'])
    ->where(['status' => 'active'])
    ->orderBy('created_at DESC');

// Отримати загальну кількість
$countQuery = clone $baseQuery;
$countResult = $countQuery->clearSelect()->count()->build();
$total = Flight::db()->fetchField($countResult['sql'], $countResult['params']);

// Отримати результати з пагінацією
$page = 1;
$perPage = 20;
$listResult = $baseQuery->limit($perPage, ($page - 1) * $perPage)->build();
$users = Flight::db()->fetchAll($listResult['sql'], $listResult['params']);
```

---

## Динамічна побудова запитів

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

## Повний приклад FlightPHP

```php
use KnifeLemon\EasyQuery\Builder;

// Список користувачів з пагінацією
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

// Створити користувача
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

// Оновити користувача
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

// Видалити користувача
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

## API довідник

### Статичні методи

| Метод | Опис |
|-------|------|
| `Builder::table(string $table)` | Створити новий екземпляр builder для таблиці |
| `Builder::raw(string $sql, array $bindings = [])` | Створити raw SQL вираз |
| `Builder::rawSafe(string $expr, array $identifiers, array $bindings = [])` | Raw вираз з безпечною заміною ідентифікаторів |
| `Builder::safeIdentifier(string $identifier)` | Валідувати та повернути безпечне ім'я колонки/таблиці |

### Методи екземпляра

| Метод | Опис |
|-------|------|
| `alias(string $alias)` | Встановити псевдонім таблиці |
| `select(string\|array $columns)` | Встановити колонки для вибору (за замовчуванням: '*') |
| `where(array $conditions)` | Додати умови WHERE (AND) |
| `orWhere(array $conditions)` | Додати умови OR WHERE |
| `join(string $table, string $condition, string $alias, string $type)` | Додати JOIN клаузу |
| `innerJoin(string $table, string $condition, string $alias)` | Додати INNER JOIN |
| `leftJoin(string $table, string $condition, string $alias)` | Додати LEFT JOIN |
| `groupBy(string $groupBy)` | Додати GROUP BY клаузу |
| `orderBy(string $orderBy)` | Додати ORDER BY клаузу |
| `limit(int $limit, int $offset = 0)` | Додати LIMIT та OFFSET |
| `count(string $column = '*')` | Встановити запит на COUNT |
| `insert(array $data)` | Встановити запит на INSERT |
| `update(array $data)` | Встановити запит на UPDATE |
| `delete()` | Встановити запит на DELETE |
| `build()` | Побудувати та повернути `['sql' => ..., 'params' => ...]` |
| `get()` | Псевдонім для `build()` |

---

## Інтеграція Tracy Debugger

EasyQuery автоматично інтегрується з Tracy Debugger, якщо він встановлений. Налаштування не потрібне!

```bash
composer require tracy/tracy
```

```php
use Tracy\Debugger;

Debugger::enable();

// Всі запити автоматично логуються в панель Tracy
$q = Builder::table('users')->where(['status' => 'active'])->build();
```

Панель Tracy показує:
- Загальну кількість запитів та розбивку за типом
- Згенерований SQL (підсвітка синтаксису)
- Масив параметрів
- Деталі запиту (таблиця, where, join тощо)

Для повної документації відвідайте [GitHub репозиторій](https://github.com/knifelemon/EasyQueryBuilder).
