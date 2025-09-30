# PdoWrapper Клас-допоміжник PDO

## Огляд

Клас `PdoWrapper` у Flight є зручним помічником для роботи з базами даних за допомогою PDO. Він спрощує поширені завдання баз даних, додає корисні методи для отримання результатів і повертає результати як [Collections](/learn/collections) для легкого доступу. Він також підтримує журналювання запитів і моніторинг продуктивності додатку (APM) для розширених випадків використання.

## Розуміння

Робота з базами даних у PHP може бути дещо багатослівною, особливо при прямому використанні PDO. `PdoWrapper` розширює PDO і додає методи, які роблять запитування, отримання та обробку результатів набагато простішими. Замість жонглювання підготовленими виразами та режимами отримання ви отримуєте прості методи для поширених завдань, і кожен рядок повертається як Collection, тому ви можете використовувати нотацію масиву або об'єкта.

Ви можете зареєструвати `PdoWrapper` як спільну послугу в Flight, а потім використовувати його будь-де у вашому додатку через `Flight::db()`.

## Основне використання

### Реєстрація помічника PDO

Спочатку зареєструйте клас `PdoWrapper` з Flight:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

Тепер ви можете використовувати `Flight::db()` будь-де, щоб отримати з'єднання з базою даних.

### Виконання запитів

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Використовуйте це для INSERT, UPDATE або коли ви хочете отримати результати вручну:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

Ви також можете використовувати його для записів:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Отримайте одне значення з бази даних:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Отримайте один рядок як Collection (доступ через масив/об'єкт):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Отримайте всі рядки як масив Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

### Використання заповнювачів `IN()`

Ви можете використовувати один `?` у клаузі `IN()` і передати масив або рядок, розділений комами:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// or
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Розширене використання

### Журналювання запитів та APM

Якщо ви хочете відстежувати продуктивність запитів, увімкніть відстеження APM під час реєстрації:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // last param enables APM
]);
```

Після виконання запитів ви можете журналізувати їх вручну, але APM журналізуватиме їх автоматично, якщо увімкнено:

```php
Flight::db()->logQueries();
```

Це викличе подію (`flight.db.queries`) з метриками з'єднання та запитів, яку ви можете прослуховувати за допомогою системи подій Flight.

### Повний приклад

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

    // Special IN() syntax
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Insert a new user
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Update a user
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Delete a user
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Get the number of affected rows
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Див. також

- [Collections](/learn/collections) - Дізнайтеся, як використовувати клас Collection для легкого доступу до даних.

## Вирішення проблем

- Якщо ви отримуєте помилку про з'єднання з базою даних, перевірте ваш DSN, ім'я користувача, пароль та опції.
- Усі рядки повертаються як Collections — якщо вам потрібен звичайний масив, використовуйте `$collection->getData()`.
- Для запитів `IN (?)` переконайтеся, що передаєте масив або рядок, розділений комами.

## Журнал змін

- v3.2.0 - Початковий реліз PdoWrapper з базовими методами запитів та отримання.