# SimplePdo PDO-Hilfsklasse

## Überblick

Die `SimplePdo`-Klasse in Flight ist eine moderne, funktionsreiche Hilfsklasse für die Arbeit mit Datenbanken unter Verwendung von PDO. Sie erweitert `PdoWrapper` und fügt bequeme Hilfsmethoden für gängige Datenbankoperationen wie `insert()`, `update()`, `delete()` und Transaktionen hinzu. Sie vereinfacht Datenbankaufgaben, gibt Ergebnisse als [Collections](/learn/collections) zurück für einfachen Zugriff und unterstützt Abfrageprotokollierung und Anwendungsleistungsüberwachung (APM) für fortgeschrittene Anwendungsfälle.

## Verständnis

Die `SimplePdo`-Klasse ist so konzipiert, dass die Arbeit mit Datenbanken in PHP viel einfacher wird. Statt mit vorbereiteten Anweisungen, Abrufmodi und ausführlichen SQL-Operationen zu jonglieren, erhalten Sie saubere, einfache Methoden für gängige Aufgaben. Jede Zeile wird als Collection zurückgegeben, sodass Sie sowohl Array-Notation (`$row['name']`) als auch Objekt-Notation (`$row->name`) verwenden können.

Diese Klasse ist eine Übersetzung von `PdoWrapper`, was bedeutet, dass sie alle Funktionen von `PdoWrapper` plus zusätzliche Hilfsmethoden enthält, die Ihren Code sauberer und wartbarer machen. Wenn Sie derzeit `PdoWrapper` verwenden, ist das Upgrade auf `SimplePdo` unkompliziert, da es `PdoWrapper` erweitert.

Sie können `SimplePdo` als geteilten Dienst in Flight registrieren und es dann überall in Ihrer App über `Flight::db()` verwenden.

## Grundlegende Verwendung

### Registrierung von SimplePdo

Registrieren Sie zuerst die `SimplePdo`-Klasse bei Flight:

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

> **HINWEIS**
>
> Wenn Sie `PDO::ATTR_DEFAULT_FETCH_MODE` nicht angeben, setzt `SimplePdo` es automatisch auf `PDO::FETCH_ASSOC` für Sie.

Nun können Sie `Flight::db()` überall verwenden, um Ihre Datenbankverbindung zu erhalten.

### Ausführen von Abfragen

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Verwenden Sie dies für INSERTs, UPDATEs oder wenn Sie Ergebnisse manuell abrufen möchten:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row is an array
}
```

Sie können es auch für Schreibvorgänge verwenden:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Einen einzelnen Wert aus der Datenbank abrufen:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

Eine einzelne Zeile als Collection (Array-/Objektzugriff) abrufen:

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

> **TIP**
>
> `SimplePdo` fügt automatisch `LIMIT 1` zu `fetchRow()`-Abfragen hinzu, falls es noch nicht vorhanden ist, was Ihre Abfragen effizienter macht.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Alle Zeilen als Array von Collections abrufen:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Eine einzelne Spalte als Array abrufen:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Returns: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Ergebnisse als Schlüssel-Wert-Paare abrufen (erste Spalte als Schlüssel, zweite als Wert):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Returns: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Verwendung von `IN()`-Platzhaltern

Sie können einen einzelnen `?` in einer `IN()`-Klausel verwenden und ein Array übergeben:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Hilfsmethoden

Einer der Hauptvorteile von `SimplePdo` gegenüber `PdoWrapper` ist die Hinzufügung bequemer Hilfsmethoden für gängige Datenbankoperationen.

### `insert()`

`function insert(string $table, array $data): string`

Eine oder mehrere Zeilen einfügen und die letzte Einfüge-ID zurückgeben.

**Einzelner Einfügevorgang:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Massen-Einfügevorgang:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Zeilen aktualisieren und die Anzahl der betroffenen Zeilen zurückgeben:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **HINWEIS**
>
> SQLite's `rowCount()` gibt die Anzahl der Zeilen zurück, in denen Daten tatsächlich geändert wurden. Wenn Sie eine Zeile mit denselben Werten aktualisieren, die sie bereits hat, gibt `rowCount()` 0 zurück. Dies unterscheidet sich vom Verhalten von MySQL bei der Verwendung von `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Zeilen löschen und die Anzahl der gelöschten Zeilen zurückgeben:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Einen Callback innerhalb einer Transaktion ausführen. Die Transaktion wird automatisch bei Erfolg committet oder bei Fehler zurückgerollt:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Wenn innerhalb des Callbacks eine Ausnahme geworfen wird, wird die Transaktion automatisch zurückgerollt und die Ausnahme erneut geworfen.

## Fortgeschrittene Verwendung

### Abfrageprotokollierung & APM

Wenn Sie die Abfrageleistung verfolgen möchten, aktivieren Sie die APM-Verfolgung bei der Registrierung:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* PDO options */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Nach dem Ausführen von Abfragen können Sie sie manuell protokollieren, aber das APM protokolliert sie automatisch, wenn aktiviert:

```php
Flight::db()->logQueries();
```

Dies löst ein Ereignis (`flight.db.queries`) mit Verbindungs- und Abfragemetriken aus, das Sie mit Flights Ereignissystem abhören können.

### Vollständiges Beispiel

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

    // Get a single column
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Get key-value pairs
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Special IN() syntax
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Insert a new user
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Bulk insert users
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Update a user
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Delete a user
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Use a transaction
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migration von PdoWrapper

Wenn Sie derzeit `PdoWrapper` verwenden, ist die Migration zu `SimplePdo` unkompliziert:

1. **Aktualisieren Sie Ihre Registrierung:**
   ```php
   // Old
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // New
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Alle bestehenden `PdoWrapper`-Methoden funktionieren in `SimplePdo`** - Es gibt keine Breaking Changes. Ihr bestehender Code wird weiterhin funktionieren.

3. **Optional die neuen Hilfsmethoden verwenden** - Beginnen Sie mit `insert()`, `update()`, `delete()` und `transaction()`, um Ihren Code zu vereinfachen.

## Siehe auch

- [Collections](/learn/collections) - Erfahren Sie, wie Sie die Collection-Klasse für einfachen Datenzugriff verwenden.
- [PdoWrapper](/learn/pdo-wrapper) - Die veraltete PDO-Hilfsklasse (deprecated).

## Fehlerbehebung

- Wenn Sie einen Fehler bezüglich der Datenbankverbindung erhalten, überprüfen Sie Ihre DSN, Benutzername, Passwort und Optionen.
- Alle Zeilen werden als Collections zurückgegeben – wenn Sie ein einfaches Array benötigen, verwenden Sie `$collection->getData()`.
- Für `IN (?)`-Abfragen stellen Sie sicher, dass Sie ein Array übergeben.
- Wenn Sie Speicherprobleme mit der Abfrageprotokollierung in lang laufenden Prozessen haben, passen Sie die `maxQueryMetrics`-Option an.

## Changelog

- v3.18.0 - Erste Veröffentlichung von SimplePdo mit Hilfsmethoden für insert, update, delete und Transaktionen.