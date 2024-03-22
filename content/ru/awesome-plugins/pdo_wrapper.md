# Класс-помощник PdoWrapper PDO

Flight поставляется с вспомогательным классом для PDO. Он позволяет вам легко выполнять запросы к вашей базе данных с помощью всех штучек для подготовки/выполнения/fetchAll(). Он значительно упрощает способ запроса к вашей базе данных. Каждый строковый результат возвращается в виде класса Flight Collection, который позволяет вам получить доступ к вашим данным с помощью синтаксиса массива или синтаксиса объекта.

## Регистрация класса-помощника PDO

```php
// Регистрация класса-помощника PDO
Flight::зарегистрировать('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Использование
Этот объект расширяет PDO, поэтому все стандартные методы PDO доступны. Следующие методы добавлены для упрощения запроса к базе данных:

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
Извлекает первое поле из запроса

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Извлекает одну строку из запроса

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// или
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Извлекает все строки из запроса

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// или
	echo $row->name;
}
```

## Примечание с синтаксисом `IN()`
Также есть полезная обертка для выражений `IN()`. Вы можете просто передать одиночный вопросительный знак в качестве заполнителя для `IN()` и затем массив значений. Вот пример, как это может выглядеть:

```php
$db = Flight::db();
$name = 'Боб';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Полный пример

```php
// Пример маршрута и как использовать эту обертку
Flight::route('/users', function () {
	// Получить всех пользователей
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Поток всех пользователей
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// или echo $user->name;
	}

	// Получить одного пользователя
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Получить одно значение
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Специальный синтаксис IN() для помощи (убедитесь, что IN написано заглавными буквами)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// также можно сделать так
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Вставить нового пользователя
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Боб', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Обновить пользователя
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Боб', 123]);

	// Удалить пользователя
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Получить количество затронутых строк
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Боб', 'Салли']);
	$affected_rows = $statement->rowCount();

});
```