# PdoWrapper PDO Palīgklase

Flight ir palīgklase PDO. Tas ļauj viegli vaicāt savu datu bāzi ar visu sagatavoto/izpildīto/fetchAll() šaubību. Tas ievērojami vienkāršo, kā jūs varat vaicāt savu datu bāzi. Katra rindas rezultāts tiek atgriezts kā "Flight Collection" klase, kas ļauj piekļūt datiem, izmantojot masīva sintaksi vai objekta sintaksi.

## Reģistrējot PDO Palīgklasi

```php
// Reģistrējiet PDO palīgklasi
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Lietošana
Šis objekts paplašina PDO, tāpēc visi normālie PDO metodēs ir pieejami. Tika pievienotas šādas metodes, lai atvieglotu datu bāzes vaicājumu veikšanu:

### `runQuery(string $sql, array $params = []): PDOStatement`
Izmantojiet šo INSERTS, UPDATE vai ja plānojat izmantot SELECT cilpas iekšā

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Vai ierakstiet datubāzē
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Izvelk pirmo lauku no vaicājuma

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Izvelk vienu rindu no vaicājuma

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// vai
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Izvelk visus rindas no vaicājuma

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// vai
	echo $row->name;
}
```

## Piezīme par `IN()` sintaksi
Tai ir noderīga aploksne `IN()` apgalvojumiem. Vienu jautājuma zīmi vienkārši varat padot kā vietotni `IN()` un pēc tam masīvu ar vērtībām. Šeit ir piemērs, kā tas varētu izskatīties:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Pilns piemērs

```php
// Piemēra maršruts un kā jūs izmantotu šo apvalku
Flight::route('/users', function () {
	// Iegūt visus lietotājus
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Plūsmas visi lietotāji
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// vai echo $user->name;
	}

	// Iegūt vienu lietotāju
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Iegūt vienu vērtību
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Speciālā IN() sintakse, lai palīdzētu (pārliecinieties, ka IN ir lieli burti)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// jūs varētu arī darīt šo
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Ievietot jaunu lietotāju
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Atjaunināt lietotāju
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Dzēst lietotāju
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Iegūt ietekmēto rindu skaitu
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```