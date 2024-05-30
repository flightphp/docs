# Clase de ayuda PdoWrapper PDO

Flight viene con una clase de ayuda para PDO. Le permite consultar fácilmente su base de datos
con toda la locura de preparar/ejecutar/obtenerTodo(). Simplifica en gran medida cómo puede
consultar su base de datos. Cada resultado de fila se devuelve como una clase Flight Collection
que le permite acceder a sus datos mediante la sintaxis de matriz o la sintaxis de objeto.

## Registrando la Clase de Ayuda PDO

```php
// Registrar la clase de ayuda PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'usuario', 'contraseña', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Uso
Este objeto extiende PDO, por lo que todos los métodos normales de PDO están disponibles. Los siguientes métodos se agregan para facilitar la consulta a la base de datos:

### `runQuery(string $sql, array $params = []): PDOStatement`
Úselo para INSERTS, UPDATES o si planea usar un SELECT en un bucle while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $algo ]);
while($row = $statement->fetch()) {
	// ...
}

// O escribir en la base de datos
$db->runQuery("INSERT INTO table (nombre) VALUES (?)", [ $nombre ]);
$db->runQuery("UPDATE table SET nombre = ? WHERE id = ?", [ $nombre, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Extrae el primer campo de la consulta

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $algo ]);
```

### `fetchRow(string $sql, array $params = []): array`
Extrae una fila de la consulta

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, nombre FROM table WHERE id = ?", [ $id ]);
echo $row['nombre'];
// o
echo $row->nombre;
```

### `fetchAll(string $sql, array $params = []): array`
Extrae todas las filas de la consulta

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, nombre FROM table WHERE something = ?", [ $algo ]);
foreach($rows as $row) {
	echo $row['nombre'];
	// o
	echo $row->nombre;
}
```

## Nota para la sintaxis de `IN()`
Esto también tiene un envoltorio útil para las declaraciones `IN()`. Simplemente puede pasar un signo de interrogación como marcador de posición para `IN()` y luego un array de valores. Aquí hay un ejemplo de cómo podría verse eso:

```php
$db = Flight::db();
$nombre = 'Bob';
$ids_compañía = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, nombre FROM table WHERE nombre = ? AND company_id IN (?)", [ $nombre, $ids_compañía ]);
```

## Ejemplo Completo

```php
// Ruta de ejemplo y cómo usar este envoltorio
Flight::route('/usuarios', function () {
	// Obtener todos los usuarios
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users');

	// Transmitir todos los usuarios
	$declaración = Flight::db()->runQuery('SELECT * FROM users');
	while ($usuario = $declaración->fetch()) {
		echo $usuario['nombre'];
		// o echo $usuario->nombre;
	}

	// Obtener un usuario único
	$usuario = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Obtener un valor único
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Sintaxis especial de IN() para ayudar (asegúrese de que IN esté en mayúsculas)
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// también se podría hacer esto
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insertar un nuevo usuario
	Flight::db()->runQuery("INSERT INTO users (nombre, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$id_insertado = Flight::db()->lastInsertId();

	// Actualizar un usuario
	Flight::db()->runQuery("UPDATE users SET nombre = ? WHERE id = ?", ['Bob', 123]);

	// Eliminar un usuario
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Obtener el número de filas afectadas
	$declaración = Flight::db()->runQuery("UPDATE users SET nombre = ? WHERE nombre = ?", ['Bob', 'Sally']);
	$filas_afectadas = $declaración->rowCount();

});
```  