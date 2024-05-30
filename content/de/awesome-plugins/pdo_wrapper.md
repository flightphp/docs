# PdoWrapper PDO Helper Klasse

Flight wird mit einer Hilfsklasse für PDO geliefert. Es ermöglicht Ihnen, Ihre Datenbank leicht abzufragen
mit all dem vorbereiteten / ausführen / fetchAll () Wahnsinn. Es vereinfacht erheblich, wie Sie können
Ihre Datenbank abfragen. Jedes Zeilenergebnis wird als Flight Collection-Klasse zurückgegeben, die
ermöglicht es Ihnen, auf Ihre Daten über Array-Syntax oder Objektsyntax zuzugreifen.

## Registrieren der PDO Helper Klasse

```php
// Registrieren Sie die PDO-Hilfsklasse
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Verwendung
Dieses Objekt erweitert PDO, so dass alle normalen PDO-Methoden verfügbar sind. Die folgenden Methoden werden hinzugefügt, um das Abfragen der Datenbank zu erleichtern:

### `runQuery(string $sql, array $params = []): PDOStatement`
Verwenden Sie dies für INSERTS, UPDATES oder wenn Sie planen, ein SELECT in einer Schleife zu verwenden

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Oder Schreiben in die Datenbank
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Holt das erste Feld aus der Abfrage

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Holt eine Zeile aus der Abfrage

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// oder
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Holt alle Zeilen aus der Abfrage

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// oder
	echo $row->name;
}
```

## Hinweis zur `IN()` Syntax
Hierfür gibt es auch ein hilfreiches Wrapper für `IN()`-Anweisungen. Sie können einfach ein einzelnes Fragezeichen als Platzhalter für `IN()` übergeben und dann ein Array von Werten. Hier ist ein Beispiel dafür, wie das aussehen könnte:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Vollständiges Beispiel

```php
// Beispielroute und wie Sie diesen Wrapper verwenden würden
Flight::route('/benutzer', function () {
	// Holen Sie sich alle Benutzer
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Streamen Sie alle Benutzer
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// oder echo $user->name;
	}

	// Holen Sie sich einen einzelnen Benutzer
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Holen Sie sich einen einzelnen Wert
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Besondere IN()-Syntax zur Unterstützung (stellen Sie sicher, dass IN großgeschrieben ist)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// Sie könnten auch dies tun
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Fügen Sie einen neuen Benutzer ein
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Aktualisieren Sie einen Benutzer
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Löschen eines Benutzers
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Abrufen der Anzahl der betroffenen Zeilen
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```