# PdoWrapper PDO Helper Class

Flight постачається з допоміжним класом для PDO. Це дозволяє вам легко запитувати вашу базу даних
з усією підготовкою/виконанням/fetchAll() заморочкою. Це значно спрощує, як ви можете 
запитувати вашу базу даних. Кожен рядок результату повертається як клас Colletion Flight, який
дозволяє вам отримувати доступ до ваших даних через синтаксис масиву або синтаксис об'єкта.

## Реєстрація класу допомоги PDO

```php
// Зареєструйте клас допомоги PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Використання
Цей об'єкт розширює PDO, тому всі звичайні методи PDO доступні. Наступні методи додані, щоб спростити запити до бази даних:

### `runQuery(string $sql, array $params = []): PDOStatement`
Використовуйте це для ВСТАВКИ, ОНОВЛЕННЯ або якщо ви плануєте використовувати SELECT в циклі while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Або запис до бази даних
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Отримує перше поле з запиту

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Отримує один рядок з запиту

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// або
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Отримує всі рядки з запиту

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// або
	echo $row->name;
}
```

## Примітка зі синтаксисом `IN()`
Це також має корисну обгортку для операторів `IN()`. Ви можете просто передати один знак питання як заповнювач для `IN()` а потім масив значень. Ось приклад того, як це може виглядати:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Повний приклад

```php
// Приклад маршруту і як ви б використовували цю обгортку
Flight::route('/users', function () {
	// Отримати всіх користувачів
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Потік всіх користувачів
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// або echo $user->name;
	}

	// Отримати одного користувача
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Отримати одне значення
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Спеціальний синтаксис IN() для допомоги (обов'язково, щоб IN було великими літерами)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// ви також можете зробити так
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Вставити нового користувача
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Оновити користувача
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Видалити користувача
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Отримати кількість змінених рядків
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```