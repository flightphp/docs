# Клас помічника SimplePdo PDO

## Огляд

Клас `SimplePdo` у Flight є сучасним, багатим на функції помічником для роботи з базами даних за допомогою PDO. Він розширює `PdoWrapper` та додає зручні методи-помічники для поширених операцій з базами даних, таких як `insert()`, `update()`, `delete()` та транзакції. Він спрощує завдання з базами даних, повертає результати як [Collections](/learn/collections) для легкого доступу та підтримує логування запитів і моніторинг продуктивності додатка (APM) для просунутих випадків використання.

## Розуміння

Клас `SimplePdo` розроблений для того, щоб зробити роботу з базами даних у PHP набагато простішою. Замість жонглювання підготовленими запитами, режимами отримання даних та громіздкими SQL-операціями ви отримуєте чисті, прості методи для поширених завдань. Кожен рядок повертається як Collection, тому ви можете використовувати як нотацію масиву (`$row['name']`), так і нотацію об'єкта (`$row->name`).

Цей клас є надмножиною `PdoWrapper`, тобто він включає всю функціональність `PdoWrapper` плюс додаткові методи-помічники, які роблять ваш код чистішим і легшим у підтримці. Якщо ви зараз використовуєте `PdoWrapper`, оновлення до `SimplePdo` є простим, оскільки він розширює `PdoWrapper`.

Ви можете зареєструвати `SimplePdo` як спільну послугу у Flight, а потім використовувати його будь-де у вашому додатку за допомогою `Flight::db()`.

## Основне використання

### Реєстрація SimplePdo

Спочатку зареєструйте клас `SimplePdo` у Flight:

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

> **ПРИМІТКА**
>
> Якщо ви не вкажете `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` автоматично встановить його на `PDO::FETCH_ASSOC` для вас.

Тепер ви можете використовувати `Flight::db()` будь-де, щоб отримати з'єднання з базою даних.

### Виконання запитів

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Використовуйте це для INSERT, UPDATE або коли ви хочете отримати результати вручну:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row є масивом
}
```

Ви також можете використовувати його для записів:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Отримайте єдине значення з бази даних:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

Отримайте єдиний рядок як Collection (доступ як до масиву/об'єкта):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// або
echo $user->name;
```

> **ПОРАДА**
>
> `SimplePdo` автоматично додає `LIMIT 1` до запитів `fetchRow()`, якщо його ще немає, роблячи ваші запити ефективнішими.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Отримайте всі рядки як масив Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // або
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Отримайте єдиний стовпець як масив:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Повертає: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Отримайте результати як пари ключ-значення (перший стовпець як ключ, другий як значення):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Повертає: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Використання заповнювачів `IN()`

Ви можете використовувати єдиний `?` у клаузі `IN()` та передати масив:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Методи-помічники

Однією з основних переваг `SimplePdo` над `PdoWrapper` є додавання зручних методів-помічників для поширених операцій з базами даних.

### `insert()`

`function insert(string $table, array $data): string`

Вставте один або більше рядків і поверніть останній ID вставки.

**Єдина вставка:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Пакетна вставка:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Оновіть рядки та поверніть кількість уражених рядків:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **ПРИМІТКА**
>
> `rowCount()` у SQLite повертає кількість рядків, де дані дійсно змінилися. Якщо ви оновлюєте рядок тими самими значеннями, які він уже має, `rowCount()` поверне 0. Це відрізняється від поведінки MySQL при використанні `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Видаліть рядки та поверніть кількість видалених рядків:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Виконайте зворотний виклик у межах транзакції. Транзакція автоматично фіксується при успіху або скасовується при помилці:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Якщо будь-яке виняток виникає в межах зворотного виклику, транзакція автоматично скасовується, а виняток повторно викидається.

## Просунуте використання

### Логування запитів та APM

Якщо ви хочете відстежувати продуктивність запитів, увімкніть відстеження APM під час реєстрації:

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

Після виконання запитів ви можете логувати їх вручну, але APM логуватиме їх автоматично, якщо увімкнено:

```php
Flight::db()->logQueries();
```

Це викличе подію (`flight.db.queries`) з метриками з'єднання та запитів, яку ви можете слухати за допомогою системи подій Flight.

### Повний приклад

```php
Flight::route('/users', function () {
    // Отримайте всіх користувачів
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Потоково отримайте всіх користувачів
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Отримайте єдиного користувача
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Отримайте єдине значення
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Отримайте єдиний стовпець
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Отримайте пари ключ-значення
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Спеціальний синтаксис IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Вставте нового користувача
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Пакетна вставка користувачів
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Оновіть користувача
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Видаліть користувача
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Використовуйте транзакцію
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Міграція з PdoWrapper

Якщо ви зараз використовуєте `PdoWrapper`, міграція до `SimplePdo` є простою:

1. **Оновіть вашу реєстрацію:**
   ```php
   // Старий
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Новий
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Усі існуючі методи `PdoWrapper` працюють у `SimplePdo`** - Немає руйнівних змін. Ваш існуючий код продовжить працювати.

3. **Опціонально використовуйте нові методи-помічники** - Почніть використовувати `insert()`, `update()`, `delete()` та `transaction()`, щоб спростити ваш код.

## Дивіться також

- [Collections](/learn/collections) - Дізнайтеся, як використовувати клас Collection для легкого доступу до даних.
- [PdoWrapper](/learn/pdo-wrapper) - Спадковий клас-помічник PDO (застарілий).

## Вирішення проблем

- Якщо ви отримуєте помилку щодо з'єднання з базою даних, перевірте ваш DSN, ім'я користувача, пароль та опції.
- Усі рядки повертаються як Collections — якщо вам потрібен звичайний масив, використовуйте `$collection->getData()`.
- Для запитів `IN (?)` переконайтеся, що ви передаєте масив.
- Якщо ви стикаєтеся з проблемами пам'яті при логуванні запитів у довготривалих процесах, налаштуйте опцію `maxQueryMetrics`.

## Журнал змін

- v3.18.0 - Початковий реліз SimplePdo з методами-помічниками для insert, update, delete та транзакцій.