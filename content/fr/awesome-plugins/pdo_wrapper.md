# PdoWrapper Clase de Ayuda PDO

Flight viene con una clase de ayuda para PDO. Te permite consultar fácilmente tu base de datos
con toda la locura de preparar/ejecutar/fetchAll(). Simplifica en gran medida cómo puedes
consultar tu base de datos.

## Registrando la Clase de Ayuda PDO

```php
// Registrar la clase de ayuda de PDO
Flight::registrar('db', \flight\base de datos\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'usuario', 'contraseña', [
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
Úsalo para INSERTS, UPDATES, o si planeas usar un SELECT en un bucle while

```php
$db = Flight::db();
$declaración = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $algo ]);
while($fila = $declaración->fetch()) {
	// ...
}

// O escribiendo en la base de datos
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
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Extrae todas las filas de la consulta

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $algo ]);
foreach($rows as $fila) {
	// hacer algo
}
```

## Nota con la sintaxis `IN()`
Esto también tiene un envoltorio útil para las declaraciones `IN()`. Simplemente puedes pasar un signo de interrogación como marcador de posición para `IN()` y luego un array de valores. Aquí tienes un ejemplo de cómo podría ser eso:

```php
$db = Flight::db();
$nombre = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE nombre = ? AND company_id IN (?)", [ $nombre, $company_ids ]);
```

## Ejemplo Completo

```php
// Ruta de ejemplo y cómo usar este envoltorio
Flight::ruta('/usuarios', function () {
	// Obtener todos los usuarios
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios');

	// Transmitir todos los usuarios
	$declaración = Flight::db()->runQuery('SELECT * FROM usuarios');
	while ($usuario = $declaración->fetch()) {
		echo $usuario['nombre'];
	}

	// Obtener un solo usuario
	$usuario = Flight::db()->fetchRow('SELECT * FROM usuarios WHERE id = ?', [123]);

	// Obtener un solo valor
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM usuarios');

	// Sintaxis especial IN() para ayudar (asegúrate de que IN esté en mayúsculas)
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE id IN (?)', [[1,2,3,4,5]]);
	// también podrías hacer esto
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE id IN (?)', [ '1,2,3,4,5']);

	// Insertar un nuevo usuario
	Flight::db()->runQuery("INSERT INTO usuarios (nombre, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Actualizar un usuario
	Flight::db()->runQuery("UPDATE usuarios SET nombre = ? WHERE id = ?", ['Bob', 123]);

	// Borrar un usuario
	Flight::db()->runQuery("DELETE FROM usuarios WHERE id = ?", [123]);

	// Obtener el número de filas afectadas
	$declaración = Flight::db()->runQuery("UPDATE usuarios SET nombre = ? WHERE nombre = ?", ['Bob', 'Sally']);
	$filas_afectadas = $declaración->rowCount();

});
```