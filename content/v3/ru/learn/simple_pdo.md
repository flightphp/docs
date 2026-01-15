# Класс помощника SimplePdo PDO

## Обзор

Класс `SimplePdo` в Flight — это современный, функциональный помощник для работы с базами данных с использованием PDO. Он расширяет `PdoWrapper` и добавляет удобные методы-помощники для распространённых операций с базой данных, таких как `insert()`, `update()`, `delete()` и транзакции. Он упрощает задачи работы с базой данных, возвращает результаты в виде [Collections](/learn/collections) для лёгкого доступа и поддерживает логирование запросов и мониторинг производительности приложений (APM) для продвинутых сценариев использования.

## Понимание

Класс `SimplePdo` предназначен для упрощения работы с базами данных в PHP. Вместо манипуляций с подготовленными выражениями, режимами выборки и многословными SQL-операциями вы получаете чистые, простые методы для распространённых задач. Каждая строка возвращается как Collection, так что вы можете использовать нотацию массива (`$row['name']`) и нотацию объекта (`$row->name`).

Этот класс является суперкомплектом `PdoWrapper`, то есть включает всю функциональность `PdoWrapper` плюс дополнительные методы-помощники, которые делают ваш код чище и удобнее в поддержке. Если вы сейчас используете `PdoWrapper`, переход на `SimplePdo` будет простым, поскольку он расширяет `PdoWrapper`.

Вы можете зарегистрировать `SimplePdo` как общую службу в Flight, а затем использовать её в любом месте вашего приложения через `Flight::db()`.

## Основное использование

### Регистрация SimplePdo

Сначала зарегистрируйте класс `SimplePdo` в Flight:

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

> **ПРИМЕЧАНИЕ**
>
> Если вы не укажете `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` автоматически установит его в `PDO::FETCH_ASSOC` за вас.

Теперь вы можете использовать `Flight::db()` в любом месте для получения соединения с базой данных.

### Выполнение запросов

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Используйте это для INSERT, UPDATE или когда вы хотите вручную получить результаты:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

Вы также можете использовать его для операций записи:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Получите одно значение из базы данных:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

Получите одну строку как Collection (доступ как к массиву/объекту):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

> **СОВЕТ**
>
> `SimplePdo` автоматически добавляет `LIMIT 1` к запросам `fetchRow()`, если он ещё не присутствует, что делает ваши запросы более эффективными.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Получите все строки как массив Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Выберите одну колонку как массив:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Returns: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Выберите результаты как пары ключ-значение (первая колонка как ключ, вторая как значение):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Returns: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Использование заполнителей `IN()`

Вы можете использовать один `?` в предложении `IN()` и передать массив:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Методы-помощники

Одно из главных преимуществ `SimplePdo` по сравнению с `PdoWrapper` — добавление удобных методов-помощников для распространённых операций с базой данных.

### `insert()`

`function insert(string $table, array $data): string`

Вставьте одну или несколько строк и верните ID последней вставки.

**Одиночная вставка:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Пакетная вставка:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Обновите строки и верните количество затронутых строк:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **ПРИМЕЧАНИЕ**
>
> В SQLite `rowCount()` возвращает количество строк, где данные действительно изменились. Если вы обновляете строку с теми же значениями, которые у неё уже есть, `rowCount()` вернёт 0. Это отличается от поведения MySQL при использовании `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Удалите строки и верните количество удалённых строк:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Выполните обратный вызов в рамках транзакции. Транзакция автоматически фиксируется при успехе или откатывается при ошибке:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Если в обратном вызове возникает любое исключение, транзакция автоматически откатывается, и исключение повторно выбрасывается.

## Продвинутое использование

### Логирование запросов и APM

Если вы хотите отслеживать производительность запросов, включите отслеживание APM при регистрации:

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

После выполнения запросов вы можете логировать их вручную, но APM будет логировать их автоматически, если включено:

```php
Flight::db()->logQueries();
```

Это вызовет событие (`flight.db.queries`) с метриками соединения и запросов, на которое вы можете подписаться с помощью системы событий Flight.

### Полный пример

```php
Flight::route('/users', function () {
    // Get all users
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Stream all users
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Get a single user
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Get a single value
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Get a single column
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Get key-value pairs
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Special IN() syntax
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Insert a new user
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Bulk insert users
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Update a user
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Delete a user
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Use a transaction
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Миграция с PdoWrapper

Если вы сейчас используете `PdoWrapper`, миграция на `SimplePdo` будет простой:

1. **Обновите вашу регистрацию:**
   ```php
   // Old
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // New
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Все существующие методы `PdoWrapper` работают в `SimplePdo`** — Нет разрушительных изменений. Ваш существующий код продолжит работать.

3. **Опционально используйте новые методы-помощники** — Начните использовать `insert()`, `update()`, `delete()` и `transaction()` для упрощения вашего кода.

## См. также

- [Collections](/learn/collections) — Узнайте, как использовать класс Collection для лёгкого доступа к данным.
- [PdoWrapper](/learn/pdo-wrapper) — Устаревший класс помощника PDO (устарел).

## Устранение неисправностей

- Если вы получаете ошибку о соединении с базой данных, проверьте ваш DSN, имя пользователя, пароль и опции.
- Все строки возвращаются как Collections — если вам нужен простой массив, используйте `$collection->getData()`.
- Для запросов `IN (?)` убедитесь, что вы передаёте массив.
- Если вы испытываете проблемы с памятью при логировании запросов в долгоживущих процессах, настройте опцию `maxQueryMetrics`.

## Журнал изменений

- v3.18.0 — Первое выпуски SimplePdo с методами-помощниками для insert, update, delete и транзакций.