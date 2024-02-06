# Clase de Ayuda PdoWrapper PDO

Flight viene con una clase de ayuda para PDO. Te permite consultar fácilmente tu base de datos
con toda la locura de preparar/ejecutar/fetchAll(). Simplifica enormemente cómo puedes
consultar tu base de datos.

## Registrando la Clase de Ayuda PDO

```php
// Registrar la clase de ayuda PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Uso
Este objeto extiende PDO, por lo que todos los métodos normales de PDO están disponibles. Se añaden los siguientes métodos para facilitar la consulta de la base de datos:

### `runQuery(string $sql, array $params = []): PDOStatement`
Úsalo para INSERTS, UPDATES, o si planeas usar un SELECT en un bucle while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// O escribir en la base de datos
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Obtiene el primer campo de la consulta

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Obtiene una fila de la consulta

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Obtiene todas las filas de la consulta

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// hacer algo
}
```

## Nota con la sintaxis `IN()`
Esto también tiene un envoltorio útil para las declaraciones `IN()`. Simplemente puedes pasar un signo de interrogación como marcador de posición para `IN()` y luego un array de valores. Aquí tienes un ejemplo de cómo podría verse eso:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Ejemplo Completo

```php
// Ruta de ejemplo y cómo usar este envoltorio
Flight::route('/users', function () {
	// Obtener todos los usuarios
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Transmitir todos los usuarios
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// Obtener un solo usuario
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Obtener un solo valor
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Sintaxis especial IN() para ayudar (asegúrate de que IN esté en mayúsculas)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// también podrías hacer esto
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insertar un nuevo usuario
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Actualizar un usuario
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Eliminar un usuario
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Obtener el número de filas afectadas
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```