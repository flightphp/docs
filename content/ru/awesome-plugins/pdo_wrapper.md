# Класс-помощник PdoWrapper PDO

Flight поставляется с классом-помощником для PDO. Он позволяет легко выполнять запросы к вашей базе данных со всеми этими подготовленными / выполненными / fetchAll() штуками. Это значительно упрощает способ запроса к вашей базе данных.

## Регистрация класса-помощника PDO

```php
// Регистрация класса-помощника PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Использование
Этот объект расширяет PDO, поэтому все стандартные методы PDO доступны. Следующие методы добавлены для упрощения запросов к базе данных:

### `runQuery(string $sql, array $params = []): PDOStatement`
Используйте это для INSERTS, UPDATES или если вы планируете использовать SELECT в цикле while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Или запись в базу данных
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Выбирает первое поле из запроса

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Выбирает одну строку из запроса

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Выбирает все строки из запроса

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// сделать что-то
}
```

## Примечание к синтаксису `IN()`
Здесь также есть удобная оболочка для операторов `IN()`. Вы можете просто передать один вопросительный знак в качестве заполнителя для `IN()` и затем массив значений. Вот пример того, как это может выглядеть:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Полный пример

```php
// Пример маршрута и как использовать эту оболочку
Flight::route('/users', function () {
	// Получаем всех пользователей
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Поток всех пользователей
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// Получаем отдельного пользователя
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Получаем одно значение
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Специальный синтаксис IN() для помощи (убедитесь, что IN написан заглавными буквами)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// также можно сделать так
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Вставляем нового пользователя
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Обновляем пользователя
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Удаляем пользователя
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Получаем количество затронутых строк
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```