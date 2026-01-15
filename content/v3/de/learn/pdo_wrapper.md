# PdoWrapper PDO-Hilfsklasse

> **WARNUNG**
>
> **Veraltet:** `PdoWrapper` ist veraltet ab Flight v3.18.0. Es wird in einer zukünftigen Version nicht entfernt, aber nur für Abwärtskompatibilität gewartet. Bitte verwenden Sie stattdessen [SimplePdo](/learn/simple-pdo), das die gleiche Funktionalität plus zusätzliche Hilfsmethoden für gängige Datenbankoperationen bietet.

## Überblick

Die `PdoWrapper`-Klasse in Flight ist ein freundlicher Helfer für die Arbeit mit Datenbanken unter Verwendung von PDO. Sie vereinfacht gängige Datenbankaufgaben, fügt einige nützliche Methoden zum Abrufen von Ergebnissen hinzu und gibt Ergebnisse als [Collections](/learn/collections) zurück, um einen einfachen Zugriff zu ermöglichen. Sie unterstützt auch Query-Logging und Application Performance Monitoring (APM) für fortgeschrittene Anwendungsfälle.

## Verständnis

Die Arbeit mit Datenbanken in PHP kann etwas umständlich sein, insbesondere bei der direkten Verwendung von PDO. `PdoWrapper` erweitert PDO und fügt Methoden hinzu, die Abfragen, Abrufen und Behandeln von Ergebnissen viel einfacher machen. Statt mit Prepared Statements und Fetch-Modi zu jonglieren, erhalten Sie einfache Methoden für gängige Aufgaben, und jede Zeile wird als Collection zurückgegeben, sodass Sie Array- oder Objekt-Notation verwenden können.

Sie können `PdoWrapper` als geteilten Service in Flight registrieren und es dann überall in Ihrer App über `Flight::db()` verwenden.

## Grundlegende Verwendung

### Registrieren des PDO-Helpers

Zuerst registrieren Sie die `PdoWrapper`-Klasse bei Flight:

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

Holen Sie einen einzelnen Wert aus der Datenbank:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Holen Sie eine einzelne Zeile als Collection (Array/Objekt-Zugriff):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// or
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Holen Sie alle Zeilen als Array von Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // or
    echo $user->name;
}
```

### Verwendung von `IN()`-Platzhaltern

Sie können ein einzelnes `?` in einer `IN()`-Klausel verwenden und ein Array oder einen komma-separierten String übergeben:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// or
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Fortgeschrittene Verwendung

### Query-Logging & APM

Wenn Sie die Query-Leistung verfolgen möchten, aktivieren Sie das APM-Tracking bei der Registrierung:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // letzter Parameter aktiviert APM
]);
```

Nach dem Ausführen von Abfragen können Sie sie manuell loggen, aber das APM loggt sie automatisch, wenn aktiviert:

```php
Flight::db()->logQueries();
```

Dies löst ein Event (`flight.db.queries`) mit Verbindungs- und Query-Metriken aus, das Sie mit Flights Event-System abhören können.

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

## Siehe auch

- [Collections](/learn/collections) - Lernen Sie, wie Sie die Collection-Klasse für einfachen Datenzugriff verwenden.

## Fehlerbehebung

- Wenn Sie einen Fehler bezüglich der Datenbankverbindung erhalten, überprüfen Sie Ihre DSN, Benutzername, Passwort und Optionen.
- Alle Zeilen werden als Collections zurückgegeben – wenn Sie ein einfaches Array benötigen, verwenden Sie `$collection->getData()`.
- Für `IN (?)`-Abfragen stellen Sie sicher, dass Sie ein Array oder einen komma-separierten String übergeben.

## Änderungsprotokoll

- v3.2.0 - Erste Veröffentlichung von PdoWrapper mit grundlegenden Query- und Fetch-Methoden.