# PdoWrapper PDO Palīgklase

Flight tiek nodrošināts ar palīgklasi priekš PDO. Tas ļauj jums viegli piekļūt datubāzei
ar visu sagatavoto/izpildīto/pēcņemto() apjukumu. Tas ļoti vienkāršo to, kā jūs varat
piekļūt datubāzei. Katrs rindiņas rezultāts tiek atgriezts kā Flight kolekcijas klase,
kas ļauj jums piekļūt saviem datiem, izmantojot masīva sintaksi vai objekta sintaksi.

## Reģistrējot PDO Palīgklasi

```php
// Reģistrēt PDO palīgklasi
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Lietošana
Šis objekts paplašina PDO, tāpēc visi parasti pieejamie PDO metodēs ir pieejami. Lai piekļūtu datubāzei, ir pievienotas sekojošas metodes, lai atvieglotu vaicājumu veikšanu:

### `runQuery(string $sql, array $params = []): PDOStatement`
Izmanto šo funkciju IESNIEGUMIEM, ATJAUNINĀJUMIEM, vai ja plāno lietot IZVĒLES teikumu cilpā

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Vai ierakstīt datubāzē
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
Izvelk visas rindas no vaicājuma

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// vai
	echo $row->name;
}
```

## Piezīme ar `IN()` sintaksi
Šim ir noderīgs apvalks `IN()` teikumiem. Viens vienkāršs jautājuma zīme kā aizstājvietu `IN()` un tad masīvs ar vērtībām. Šeit ir piemērs, kā tas varētu izskatīties:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Pilns Piemērs

```php
// Piemēra maršruts un kā jūs varētu izmantot šo apvalku
Flight::route('/users', function () {
	// Saņemt visus lietotājus
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Straumēt visus lietotājus
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// vai echo $user->name;
	}

	// Saņemt vienu lietotāju
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Saņemt vienu vērtību
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Īpašais IN() sintakse palīdzēt (pārliecinieties, ka IN ir lieliem burtiem)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// Jūs varētu arī izdarīt šo
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Ievietot jaunu lietotāju
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Atjaunināt lietotāju
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Dzēst lietotāju
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Saņemt ietekmēto rindu skaitu
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```