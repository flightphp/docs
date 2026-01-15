# PdoWrapper Класс-помощник PDO

> **ПРЕДУПРЕЖДЕНИЕ**
>
> **Устарело:** `PdoWrapper` устарел начиная с Flight v3.18.0. Он не будет удален в будущих версиях, но будет поддерживаться для обратной совместимости. Пожалуйста, используйте [SimplePdo](/learn/simple-pdo) вместо него, который предлагает те же функции плюс дополнительные вспомогательные методы для распространенных операций с базой данных.

## Обзор

Класс `PdoWrapper` в Flight — это удобный помощник для работы с базами данных с использованием PDO. Он упрощает распространенные задачи с базами данных, добавляет полезные методы для получения результатов и возвращает результаты в виде [Collections](/learn/collections) для легкого доступа. Он также поддерживает логирование запросов и мониторинг производительности приложений (APM) для продвинутых случаев использования.

## Понимание

Работа с базами данных в PHP может быть немного многословной, особенно при прямом использовании PDO. `PdoWrapper` расширяет PDO и добавляет методы, которые делают запросы, получение и обработку результатов гораздо проще. Вместо жонглирования подготовленными выражениями и режимами получения вы получаете простые методы для распространенных задач, и каждая строка возвращается как Collection, так что вы можете использовать нотацию массива или объекта.

Вы можете зарегистрировать `PdoWrapper` как общую службу в Flight, а затем использовать его в любом месте вашего приложения через `Flight::db()`.

## Основное использование

### Регистрация помощника PDO

Сначала зарегистрируйте класс `PdoWrapper` в Flight:

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

Теперь вы можете использовать `Flight::db()` в любом месте для получения соединения с базой данных.

### Выполнение запросов

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Используйте это для INSERT, UPDATE или когда вы хотите получить результаты вручную:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

Вы также можете использовать это для записей:

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

`function fetchRow(string $sql, array $params = []): Collection`

Получите одну строку как Collection (доступ по массиву/объекту):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

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

### Использование заполнителей `IN()`

Вы можете использовать один `?` в предложении `IN()` и передать массив или строку, разделенную запятыми:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// or
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Продвинутое использование

### Логирование запросов и APM

Если вы хотите отслеживать производительность запросов, включите отслеживание APM при регистрации:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // последний параметр включает APM
]);
```

После выполнения запросов вы можете логировать их вручную, но APM будет логировать их автоматически, если включено:

```php
Flight::db()->logQueries();
```

Это вызовет событие (`flight.db.queries`) с метриками соединения и запросов, которые вы можете прослушивать с помощью системы событий Flight.

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

## См. также

- [Collections](/learn/collections) - Узнайте, как использовать класс Collection для легкого доступа к данным.

## Устранение неисправностей

- Если вы получаете ошибку о соединении с базой данных, проверьте ваш DSN, имя пользователя, пароль и опции.
- Все строки возвращаются как Collections — если вам нужен простой массив, используйте `$collection->getData()`.
- Для запросов `IN (?)` убедитесь, что вы передаете массив или строку, разделенную запятыми.

## Журнал изменений

- v3.2.0 - Первоначальный выпуск PdoWrapper с базовыми методами запросов и получения.