# PdoWrapper PDO Helper Klase

Flight nāk ar palīgklasi PDO. Tas ļauj viegli vaicāt savu datu bāzi ar visu sagatavoto/izpildīt/izgūtVisus() sātanību. Tas ievērojami vienkāršo, kā jūs varat vaicāt savu datu bāzi.

## Reģistrējot PDO Helper Klasi

```php
// Reģistrēt PDO palīgklasi
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'lietotājs', 'parole', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Lietošana
Šis objekts paplašina PDO, tāpēc visi normālie PDO metodēs ir pieejami. Lai veiktu datu bāzes vaicājumus vieglāk, tiek pievienotas šādas metodes:

### `runQuery(string $sql, array $params = []): PDOStatement`
Izmantojiet to INSERT, UPDATE vai ja plānojat izmantot SELECT cilpā

```php
$db = Flight::db();
$izpildpareizraksts = $db->runQuery("SELECT * FROM tabula WHERE kautkas = ?", [ $kautkas ]);
while($rinda = $izpildpareizraksts->fetch()) {
	// ...
}

// Vai rakstīšana datu bāzē
$db->runQuery("INSERT INTO tabula (nosaukums) VALUES (?)", [ $nosaukums ]);
$db->runQuery("UPDATE tabula SET nosaukums = ? WHERE id = ?", [ $nosaukums, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Izvelk pirmo lauku no vaicājuma

```php
$db = Flight::db();
(skaitīt = $db->fetchField("SELECT COUNT(*) FROM tabula WHERE kautkas = ?", [ $kautkas ]);
```

### `fetchRow(string $sql, array $params = []): array`
Izvelk vienu rindu no vaicājuma

```php
$db = Flight::db();
$rinda = $db->fetchRow("SELECT * FROM tabula WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Izvelk visus rindas no vaicājuma

```php
$db = Flight::db();
$rindas = $db->fetchAll("SELECT * FROM tabula WHERE kautkas = ?", [ $kautkas ]);
foreach($rindas as $rinda) {
	// kaut ko dariet
}
```

## Piezīme ar `IN()` sintakses
Tam ir ari noderīgs ietinājums `IN()` apgalvojumiem. Vienkārši padodiet vienu jautājumu zīmi kā aizvietotāj simbolu `IN()` un tad masīvu ar vērtībām. Šeit ir piemērs, kā tas varētu izskatīties:

```php
$db = Flight::db();
$vārds = 'Bobs';
$uzņēmuma_id = [1,2,3,4,5];
$rindas = $db->fetchAll("SELECT * FROM tabula WHERE vārds = ? AND uzņēmuma_id IN (?)", [ $vārds, $uzņēmuma_id ]);
```

## Pilns piemērs

```php
// Piemēra maršruts un kā izmantot šo ietinājumu
Flight::route('/lietotāji', function () {
	// iegūt visus lietotājus
	$lietotāji = Flight::db()->fetchAll('SELECT * FROM lietotāji');

	// Plūst visi lietotāji
	$izpildpareizraksts = Flight::db()->runQuery('SELECT * FROM lietotāji');
	while ($lietotājs = $izpildpareizraksts->fetch()) {
		echo $lietotājs['vārds'];
	}

	// Iegūt vienu lietotāju
	$lietotājs = Flight::db()->fetchRow('SELECT * FROM lietotāji WHERE id = ?', [123]);

	// Iegūt vienu vērtību
	(skaitīt = Flight::db()->fetchField('SELECT COUNT(*) FROM lietotāji');

	// Īpašais IN() sintakse lai palīdzētu (pārliecinieties, ka IN ir lielie)
	$lietotāji = Flight::db()->fetchAll('SELECT * FROM lietotāji WHERE id IN (?)', [[1,2,3,4,5]]);
	// jūs varētu arī to izdarīt
	$lietotāji = Flight::db()->fetchAll('SELECT * FROM lietotāji WHERE id IN (?)', [ '1,2,3,4,5']);

	// Ievietot jaunu lietotāju
	Flight::db()->runQuery("INSERT INTO lietotāji (vārds, epasts) VēRTĪBAS (?, ?)", ['Bobs', 'bobs@example.com']);
	insert_id = Flight::db()->lastInsertId();

	// Atjaunināt lietotāju
	Flight::db()->runQuery("UPDATE lietotāji SET vārds = ? WHERE id = ?", ['Bobs', 123]);

	// Dzēst lietotāju
	Flight::db()->runQuery("DELETE FROM lietotāji WHERE id = ?", [123]);

	// Iegūt ietekmēto rindu skaitu
	$izpildpareizraksts = Flight::db()->runQuery("update lietotāji SET vārds = ? WHERE vārds = ?", ['Bobs', 'Sally']);
	affected_rows = $izpildpareizraksta->rowCount();

});
```