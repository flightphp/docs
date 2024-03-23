# Clase de Ayuda PdoWrapper para PDO

Flight viene con una clase de ayuda para PDO. Te permite consultar fácilmente tu base de datos con toda la locura de preparar/ejecutar/fetchAll(). Simplifica en gran medida cómo puedes consultar tu base de datos. Cada resultado de fila se devuelve como una clase de colección de Flight que te permite acceder a tus datos mediante sintaxis de array o sintaxis de objeto.

## Registro de la Clase de Ayuda PDO

```php
// Registrar la clase de ayuda PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => falso,
		PDO::ATTR_STRINGIFY_FETCHES => falso,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Uso
Este objeto extiende PDO, por lo que todos los métodos normales de PDO están disponibles. Se agregan los siguientes métodos para facilitar la consulta de la base de datos:

### `runQuery(string $sql, array $params = []): PDOStatement`
Úsalo para INSERTS, UPDATES, o si planeas usar un SELECT en un bucle while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// O escribiendo en la base de datos
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Extrae el primer campo de la consulta

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Extrae una fila de la consulta

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// o
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Extrae todas las filas de la consulta

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// o
	echo $row->name;
}
```

## Nota sobre la sintaxis de `IN()`
Esto también tiene un envoltorio útil para las declaraciones `IN()`. Simplemente puedes pasar un signo de interrogación como marcador de posición para `IN()` y luego un array de valores. Aquí tienes un ejemplo de cómo podría ser eso:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Ejemplo Completo

```php
// Ruta de ejemplo y cómo usar este envoltorio
Flight::route('/usuarios', function () {
	// Obtener todos los usuarios
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users');

	// Transmitir todos los usuarios
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($usuario = $statement->fetch()) {
		echo $usuario['name'];
		// o echo $usuario->name;
	}

	// Obtener un solo usuario
	$usuario = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Obtener un solo valor
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Sintaxis especial de IN() para ayudar (asegúrate de que IN esté en mayúsculas)
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// también podrías hacer esto
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

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