# PdoWrapper PDO Helper Klasse

Flight kommt mit einer Hilfsklasse für PDO. Es ermöglicht Ihnen, Ihre Datenbank einfach abzufragen
mit all dem vorbereiteten/ausgeführten/fetchAll() Durcheinander. Es vereinfacht stark, wie Sie
Ihre Datenbank abfragen können.

## Registrierung der PDO Helper Klasse

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
Dieses Objekt erweitert PDO, so dass alle normalen PDO-Methoden verfügbar sind. Die folgenden Methoden wurden hinzugefügt, um Abfragen an die Datenbank zu erleichtern:

### `runQuery(string $sql, array $params = []): PDOStatement`
Verwenden Sie dies für INSERTS, UPDATES oder wenn Sie vorhaben, ein SELECT in einer while-Schleife zu verwenden

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM tabelle WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Oder zum Schreiben in die Datenbank
$db->runQuery("INSERT INTO tabelle (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE tabelle SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Holt das erste Feld aus der Abfrage

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM tabelle WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Holt eine Zeile aus der Abfrage

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM tabelle WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Holt alle Zeilen aus der Abfrage

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM tabelle WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// etwas tun
}
```

## Hinweis zur `IN()` Syntax
Es gibt auch einen nützlichen Wrapper für `IN()` Anweisungen. Sie können einfach ein einzelnes Fragezeichen als Platzhalter für `IN()` übergeben und dann ein Array von Werten. Hier ist ein Beispiel, wie das aussehen könnte:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM tabelle WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Vollständiges Beispiel

```php
// Beispielroute und wie Sie diesen Wrapper verwenden würden
Flight::route('/benutzer', function () {
	// Alle Benutzer abrufen
	$benutzer = Flight::db()->fetchAll('SELECT * FROM benutzer');

	// Alle Benutzer anzeigen
	$statement = Flight::db()->runQuery('SELECT * FROM benutzer');
	while ($benutzer = $statement->fetch()) {
		echo $benutzer['name'];
	}

	// Einen einzelnen Benutzer abrufen
	$benutzer = Flight::db()->fetchRow('SELECT * FROM benutzer WHERE id = ?', [123]);

	// Einen einzigen Wert abrufen
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM benutzer');

	// Besondere IN() Syntax zur Unterstützung (stellen Sie sicher, dass IN großgeschrieben ist)
	$benutzer = Flight::db()->fetchAll('SELECT * FROM benutzer WHERE id IN (?)', [[1,2,3,4,5]]);
	// Sie könnten auch dies tun
	$benutzer = Flight::db()->fetchAll('SELECT * FROM benutzer WHERE id IN (?)', [ '1,2,3,4,5']);

	// Einen neuen Benutzer einfügen
	Flight::db()->runQuery("INSERT INTO benutzer (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$einfuege_id = Flight::db()->lastInsertId();

	// Einen Benutzer aktualisieren
	Flight::db()->runQuery("UPDATE benutzer SET name = ? WHERE id = ?", ['Bob', 123]);

	// Einen Benutzer löschen
	Flight::db()->runQuery("DELETE FROM benutzer WHERE id = ?", [123]);

	// Anzahl der betroffenen Zeilen abrufen
	$statement = Flight::db()->runQuery("UPDATE benutzer SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$betroffene_zeilen = $statement->rowCount();

});
```