# PdoWrapper PDO Hilfsklasse

Flight wird mit einer Hilfsklasse für PDO geliefert. Es ermöglicht Ihnen, Ihre Datenbank einfach abzufragen
mit all der vorbereiteten/ausführen/fetchAll() Verrücktheit. Es vereinfacht erheblich, wie Sie
auf Ihre Datenbank zugreifen können. Jedes Zeilenergebnis wird als eine Flug Sammlungsklasse zurückgegeben, die
es Ihnen ermöglicht, auf Ihre Daten über die Array-Syntax oder Objektsyntax zuzugreifen.

## Registrierung der PDO Hilfsklasse

```php
// Registrieren Sie die PDO Hilfsklasse
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Verwendung
Dieses Objekt erweitert PDO, daher sind alle normalen PDO-Methoden verfügbar. Die folgenden Methoden wurden hinzugefügt, um Abfragen an die Datenbank einfacher zu machen:

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

## Hinweis zur Verwendung von `IN()` Syntax
Hier gibt es auch einen hilfreichen Wrapper für `IN()` Anweisungen. Sie können einfach ein einzelnes Fragezeichen als Platzhalter für `IN()` übergeben und dann ein Array von Werten. Hier ist ein Beispiel, wie das aussehen könnte:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
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
		// oder echo $user->name;
	}

	// Einen einzelnen Benutzer abrufen
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Einen einzelnen Wert abrufen
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Besondere IN() Syntax, um zu helfen (stellen Sie sicher, dass IN großgeschrieben ist)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// Sie könnten auch dies tun
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Einen neuen Benutzer einfügen
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Einen Benutzer aktualisieren
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Einen Benutzer löschen
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Die Anzahl der betroffenen Zeilen abrufen
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```