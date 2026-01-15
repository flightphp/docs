# PdoWrapper Клас-помічник PDO

> **ПОПЕРЕДЖЕННЯ**
>
> **Застарілий:** `PdoWrapper` є застарілим з версії Flight v3.18.0. Він не буде видалений у майбутніх версіях, але буде підтримуватися для зворотної сумісності. Будь ласка, використовуйте [SimplePdo](/learn/simple-pdo) замість нього, який пропонує ту ж функціональність плюс додаткові допоміжні методи для поширених операцій з базою даних.

## Огляд

Клас `PdoWrapper` у Flight є дружнім помічником для роботи з базами даних за допомогою PDO. Він спрощує поширені завдання з базами даних, додає деякі зручні методи для отримання результатів і повертає результати як [Collections](/learn/collections) для легкого доступу. Він також підтримує логування запитів і моніторинг продуктивності додатків (APM) для просунутих випадків використання.

## Розуміння

Робота з базами даних у PHP може бути дещо багатослівною, особливо при прямому використанні PDO. `PdoWrapper` розширює PDO і додає методи, які роблять запитування, отримання та обробку результатів набагато простішими. Замість жонглювання підготовленими виразами та режимами отримання, ви отримуєте прості методи для поширених завдань, і кожен рядок повертається як Collection, тому ви можете використовувати нотацію масиву або об'єкта.

Ви можете зареєструвати `PdoWrapper` як спільну послугу в Flight, а потім використовувати його будь-де у вашому додатку за допомогою `Flight::db()`.

## Основне використання

### Реєстрація помічника PDO

Спочатку зареєструйте клас `PdoWrapper` у Flight:

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

Отримайте одне значення з бази даних:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Отримайте один рядок як Collection (доступ як до масиву/об'єкта):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// або
echo $user->name;
```

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

### Використання заповнювачів `IN()`

Ви можете використовувати єдиний `?` у клаузі `IN()` і передати масив або рядок, розділений комами:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// або
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Просунуте використання

### Логування запитів та APM

Якщо ви хочете відстежувати продуктивність запитів, увімкніть відстеження APM під час реєстрації:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // останній параметр увімкнює APM
]);
```

Після виконання запитів ви можете логувати їх вручну, але APM логуватиме їх автоматично, якщо увімкнено:

```php
Flight::db()->logQueries();
```

Це викличе подію (`flight.db.queries`) з метриками з'єднання та запитів, яку ви можете прослуховувати за допомогою системи подій Flight.

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

    // Отримайте одного користувача
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Отримайте одне значення
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Спеціальний синтаксис IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Вставте нового користувача
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Оновіть користувача
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Видаліть користувача
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Отримайте кількість уражених рядків
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Дивіться також

- [Collections](/learn/collections) - Дізнайтеся, як використовувати клас Collection для легкого доступу до даних.

## Вирішення проблем

- Якщо ви отримуєте помилку про з'єднання з базою даних, перевірте ваш DSN, ім'я користувача, пароль та опції.
- Усі рядки повертаються як Collections — якщо вам потрібен звичайний масив, використовуйте `$collection->getData()`.
- Для запитів `IN (?)` переконайтеся, що ви передаєте масив або рядок, розділений комами.

## Журнал змін

- v3.2.0 - Початковий реліз PdoWrapper з базовими методами запитів та отримання.