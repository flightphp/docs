# Clase de ayuda PdoWrapper PDO

Flight viene con una clase de ayuda para PDO. Te permite consultar fácilmente tu base de datos
con todas las rarezas de preparado/ejecutar/fetchAll(). Simplifica en gran medida cómo puedes
consultar tu base de datos.

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
Este objeto extiende PDO, por lo que todos los métodos normales de PDO están disponibles. Se agregan los siguientes métodos para facilitar la consulta de la base de datos:

### `runQuery(string $sql, array $params = []): PDOStatement`
Úsalo para INSERTAR, ACTUALIZAR, o si planeas usar un SELECT en un bucle while

```php
$db = Flight::db();
$declaración = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($fila = $statement->fetch()) {
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
$fila = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Obtiene todas las filas de la consulta

```php
$db = Flight::db();
$filas = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// hacer algo
}
```

## Nota con sintaxis de `IN()`
Esto también tiene una envoltura útil para las declaraciones `IN()`. Simplemente puedes pasar un signo de interrogación como marcador de posición para `IN()` y luego un array de valores. Aquí tienes un ejemplo de cómo podría verse eso:

```php
$db = Flight::db();
$nombre = 'Bob';
$ids_compañia = [1,2,3,4,5];
$filas = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Ejemplo Completo

```php
// Ruta de ejemplo y cómo usar esta envoltura
Flight::route('/usuarios', function () {
	// Obtener todos los usuarios
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users');

	// Transmitir todos los usuarios
	$declaración = Flight::db()->runQuery('SELECT * FROM users');
	while ($usuario = $statement->fetch()) {
		echo $usuario['name'];
	}

	// Obtener un solo usuario
	$usuario = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Obtener un único valor
	$cuenta = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Sintaxis especial IN() para ayudar (asegúrate de que IN esté en mayúsculas)
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// también podrías hacer esto
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insertar un nuevo usuario
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$id_inserción = Flight::db()->lastInsertId();

	// Actualizar un usuario
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Eliminar un usuario
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Obtener el número de filas afectadas
	$declaración = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$filas_afectadas = $statement->rowCount();

});
```