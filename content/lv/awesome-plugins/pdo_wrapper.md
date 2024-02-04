# PdoWrapper PDO Helper Klase

Flight nāk ar palīgklasi PDO. Tas ļauj jums viegli vaicāt savu datu bāzi ar visu sagatavoto/izpildīt/fetchAll() dīvainību. Tas ievērojami vienkāršo, kā jūs varat veikt datu bāzes vaicājumus.

## Reģistrējot PDO Helper Klasi

```php
// Reģistrēt PDO helper klasi
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Izmantošana
Šis objekts paplašina PDO, tāpēc visi parasti PDO metodēs ir pieejami. Lai atvieglotu datu bāzes vaicājumu veikšanu, tiek pievienotas šādas metodes:

### `runQuery (string $sql, array $params = []): PDOStatement`
Izmantojiet šo insertiem, atjauninājumiem vai ja plānojat izmantot SELECT ciklā

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Vai ierakstīt datu bāzē
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField (string $sql, array $params = []): mixed`
Izvelk pirmo lauku no vaicājuma

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow (string $sql, array $params = []): array`
Izvelk vienu rindu no vaicājuma

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM table WHERE id = ?", [ $id ]);
```

### `fetchAll (string $sql, array $params = []): array`
Izvelk visas rindas no vaicājuma

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT * FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	// izdarīt kaut ko
}
```

## Piebilde par `IN()` sintaksi
Tas arī ir noderīgs aploksnes `IN()` teikumu apvalks. Jūs vienkārši varat padot vienu jautājuma zīmi kā aizstājvārdu `IN()` un tad masīvu ar vērtībām. Šeit ir piemērs, kā tas varētu izskatīties:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT * FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Pilns piemērs

```php
// Piemēra maršruts un kā jūs izmantotu šo apvalku
Flight::route('/users', function () {
	// Iegūt visus lietotājus
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Plūdināt visus lietotājus
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
	}

	// Iegūt vienu lietotāju
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Iegūt vienu vērtību
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Speciālā IN() sintakse, lai palīdzētu (pārliecinieties, ka IN ir ar lielajiem burtiem)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// jūs varētu arī izdarīt šo
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