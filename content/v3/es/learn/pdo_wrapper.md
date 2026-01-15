# Clase Ayudante PDO PdoWrapper

> **ADVERTENCIA**
>
> **Obsoleto:** `PdoWrapper` está obsoleto desde Flight v3.18.0. No se eliminará en una versión futura, pero se mantendrá para compatibilidad hacia atrás. Por favor, use [SimplePdo](/learn/simple-pdo) en su lugar, que ofrece la misma funcionalidad más métodos ayudantes adicionales para operaciones comunes de base de datos.

## Resumen

La clase `PdoWrapper` en Flight es un ayudante amigable para trabajar con bases de datos usando PDO. Simplifica tareas comunes de base de datos, agrega algunos métodos útiles para obtener resultados y devuelve los resultados como [Collections](/learn/collections) para un acceso fácil. También soporta registro de consultas y monitoreo de rendimiento de la aplicación (APM) para casos de uso avanzados.

## Comprensión

Trabajar con bases de datos en PHP puede ser un poco verboso, especialmente cuando se usa PDO directamente. `PdoWrapper` extiende PDO y agrega métodos que hacen que consultar, obtener y manejar resultados sea mucho más fácil. En lugar de manejar declaraciones preparadas y modos de obtención, obtienes métodos simples para tareas comunes, y cada fila se devuelve como una Collection, por lo que puedes usar notación de array u objeto.

Puedes registrar `PdoWrapper` como un servicio compartido en Flight, y luego usarlo en cualquier lugar de tu app a través de `Flight::db()`.

## Uso Básico

### Registrando el Ayudante PDO

Primero, registra la clase `PdoWrapper` con Flight:

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

Ahora puedes usar `Flight::db()` en cualquier lugar para obtener tu conexión a la base de datos.

### Ejecutando Consultas

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Usa esto para INSERTs, UPDATEs, o cuando quieras obtener resultados manualmente:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

También puedes usarlo para escrituras:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Obtén un solo valor de la base de datos:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Obtén una sola fila como una Collection (acceso array/objeto):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Obtén todas las filas como un array de Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

### Usando Marcadores de Posición `IN()`

Puedes usar un solo `?` en una cláusula `IN()` y pasar un array o una cadena separada por comas:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// or
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Uso Avanzado

### Registro de Consultas & APM

Si quieres rastrear el rendimiento de las consultas, habilita el seguimiento APM al registrar:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // last param enables APM
]);
```

Después de ejecutar consultas, puedes registrarlas manualmente pero el APM las registrará automáticamente si está habilitado:

```php
Flight::db()->logQueries();
```

Esto activará un evento (`flight.db.queries`) con métricas de conexión y consulta, que puedes escuchar usando el sistema de eventos de Flight.

### Ejemplo Completo

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

## Ver También

- [Collections](/learn/collections) - Aprende cómo usar la clase Collection para un acceso fácil a los datos.

## Solución de Problemas

- Si obtienes un error sobre la conexión a la base de datos, verifica tu DSN, nombre de usuario, contraseña y opciones.
- Todas las filas se devuelven como Collections—si necesitas un array plano, usa `$collection->getData()`.
- Para consultas `IN (?)`, asegúrate de pasar un array o una cadena separada por comas.

## Registro de Cambios

- v3.2.0 - Lanzamiento inicial de PdoWrapper con métodos básicos de consulta y obtención.