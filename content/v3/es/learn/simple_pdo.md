# Clase Auxiliar SimplePdo para PDO

## Resumen

La clase `SimplePdo` en Flight es un auxiliar moderno y rico en funciones para trabajar con bases de datos usando PDO. Extiende `PdoWrapper` y agrega métodos auxiliares convenientes para operaciones comunes de base de datos como `insert()`, `update()`, `delete()` y transacciones. Simplifica las tareas de base de datos, devuelve resultados como [Collections](/learn/collections) para un acceso fácil, y soporta registro de consultas y monitoreo de rendimiento de aplicaciones (APM) para casos de uso avanzados.

## Comprensión

La clase `SimplePdo` está diseñada para hacer que trabajar con bases de datos en PHP sea mucho más fácil. En lugar de manejar declaraciones preparadas, modos de obtención y operaciones SQL verbosas, obtienes métodos limpios y simples para tareas comunes. Cada fila se devuelve como una Collection, por lo que puedes usar tanto notación de arreglo (`$row['name']`) como notación de objeto (`$row->name`).

Esta clase es un superconjunto de `PdoWrapper`, lo que significa que incluye toda la funcionalidad de `PdoWrapper` más métodos auxiliares adicionales que hacen que tu código sea más limpio y mantenible. Si estás usando actualmente `PdoWrapper`, actualizar a `SimplePdo` es directo ya que extiende `PdoWrapper`.

Puedes registrar `SimplePdo` como un servicio compartido en Flight, y luego usarlo en cualquier lugar de tu aplicación a través de `Flight::db()`.

## Uso Básico

### Registro de SimplePdo

Primero, registra la clase `SimplePdo` con Flight:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **NOTA**
>
> Si no especificas `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` lo establecerá automáticamente en `PDO::FETCH_ASSOC` por ti.

Ahora puedes usar `Flight::db()` en cualquier lugar para obtener tu conexión a la base de datos.

### Ejecución de Consultas

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Usa esto para INSERT, UPDATE, o cuando quieras obtener resultados manualmente:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row es un arreglo
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

`function fetchRow(string $sql, array $params = []): ?Collection`

Obtén una sola fila como una Collection (acceso a arreglo/objeto):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// o
echo $user->name;
```

> **CONSEJO**
>
> `SimplePdo` agrega automáticamente `LIMIT 1` a las consultas de `fetchRow()` si no está presente, haciendo que tus consultas sean más eficientes.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Obtén todas las filas como un arreglo de Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // o
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Obtén una sola columna como un arreglo:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Devuelve: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Obtén resultados como pares clave-valor (primera columna como clave, segunda como valor):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Devuelve: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Usando Marcadores de Posición `IN()`

Puedes usar un solo `?` en una cláusula `IN()` y pasar un arreglo:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Métodos Auxiliares

Una de las principales ventajas de `SimplePdo` sobre `PdoWrapper` es la adición de métodos auxiliares convenientes para operaciones comunes de base de datos.

### `insert()`

`function insert(string $table, array $data): string`

Inserta una o más filas y devuelve el último ID de inserción.

**Inserción única:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Inserción masiva:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Actualiza filas y devuelve el número de filas afectadas:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **NOTA**
>
> El `rowCount()` de SQLite devuelve el número de filas donde los datos realmente cambiaron. Si actualizas una fila con los mismos valores que ya tiene, `rowCount()` devolverá 0. Esto difiere del comportamiento de MySQL cuando se usa `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Elimina filas y devuelve el número de filas eliminadas:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Ejecuta un callback dentro de una transacción. La transacción se confirma automáticamente en caso de éxito o se revierte en caso de error:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Si se lanza alguna excepción dentro del callback, la transacción se revierte automáticamente y la excepción se relanza.

## Uso Avanzado

### Registro de Consultas y APM

Si quieres rastrear el rendimiento de las consultas, habilita el seguimiento de APM al registrar:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* opciones de PDO */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Después de ejecutar consultas, puedes registrarlas manualmente, pero el APM las registrará automáticamente si está habilitado:

```php
Flight::db()->logQueries();
```

Esto activará un evento (`flight.db.queries`) con métricas de conexión y consultas, que puedes escuchar usando el sistema de eventos de Flight.

### Ejemplo Completo

```php
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

    // Obtener una sola columna
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Obtener pares clave-valor
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Sintaxis especial IN()
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Insertar un nuevo usuario
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Inserción masiva de usuarios
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Actualizar un usuario
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Eliminar un usuario
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Usar una transacción
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migración desde PdoWrapper

Si estás usando actualmente `PdoWrapper`, migrar a `SimplePdo` es directo:

1. **Actualiza tu registro:**
   ```php
   // Antiguo
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Nuevo
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Todos los métodos existentes de `PdoWrapper` funcionan en `SimplePdo`** - No hay cambios que rompan la compatibilidad. Tu código existente continuará funcionando.

3. **Opcionalmente usa los nuevos métodos auxiliares** - Comienza a usar `insert()`, `update()`, `delete()` y `transaction()` para simplificar tu código.

## Ver También

- [Collections](/learn/collections) - Aprende cómo usar la clase Collection para un acceso fácil a los datos.
- [PdoWrapper](/learn/pdo-wrapper) - La clase auxiliar PDO legacy (deprecada).

## Solución de Problemas

- Si obtienes un error sobre la conexión a la base de datos, verifica tu DSN, nombre de usuario, contraseña y opciones.
- Todas las filas se devuelven como Collections—si necesitas un arreglo plano, usa `$collection->getData()`.
- Para consultas `IN (?)`, asegúrate de pasar un arreglo.
- Si estás experimentando problemas de memoria con el registro de consultas en procesos de larga duración, ajusta la opción `maxQueryMetrics`.

## Registro de Cambios

- v3.18.0 - Lanzamiento inicial de SimplePdo con métodos auxiliares para insert, update, delete y transacciones.