# PdoWrapper PDO Hilfsklasse

Flight kommt mit einer Hilfsklasse für PDO. Es ermöglicht Ihnen, Ihre Datenbank einfach abzufragen
mit all dem Verrücktheit von vorbereiten/ausführen/fetchAll(). Es vereinfacht erheblich, wie Sie
Ihre Datenbank abfragen können.

## Registrieren der PDO Hilfsklasse

```php
// Registriere die PDO Hilfsklasse
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Verwendung
Dieses Objekt erweitert PDO, so dass alle normalen PDO-Methoden verfügbar sind. Die folgenden Methoden wurden hinzugefügt, um die Abfrage der Datenbank zu erleichtern:

### `runQuery(string $sql, array $params = []): PDOStatement`
Verwenden Sie dies für INSERTS, UPDATES oder wenn Sie planen, ein SELECT in einer While-Schleife zu verwenden

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Oder zum Schreiben in die Datenbank
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Ruft das erste Feld der Abfrage ab

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Ruft eine Zeile aus der Abfrage ab

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Ruft alle Zeilen aus der Abfrage ab

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// etwas tun
}
```

## Hinweis zur `IN()` Syntax
Hier gibt es auch einen nützlichen Wrapper für `IN()`-Anweisungen. Sie können einfach ein einzelnes Fragezeichen als Platzhalter für `IN()` übergeben und dann ein Array von Werten. Hier ist ein Beispiel, wie das aussehen könnte:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Vollständiges Beispiel

```php
// Beispielroute und wie Sie diesen Wrapper verwenden würden
Flight::route('/users', function () {
	// Alle Benutzer abrufen
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Alle Benutzer streamen
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// Einen einzelnen Benutzer abrufen
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Einen einzelnen Wert abrufen
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Spezielle IN() Syntax unterstützen (Stellen Sie sicher, dass IN in Großbuchstaben ist)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// Sie könnten auch dies tun
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Einen neuen Benutzer einfügen
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Ein Benutzer aktualisieren
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Einen Benutzer löschen
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Die Anzahl der betroffenen Zeilen abrufen
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```