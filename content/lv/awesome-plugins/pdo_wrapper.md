# PdoWrapper PDO Palīgklase

Flight tiek komplektēts ar palīgklasi PDO. Tas ļauj jums viegli vaicāt savu datu bāzi ar visām sagatavotajām /execute/fetchAll() nodarībām. Tas ievērojami vienkāršo, kā jūs varat vaicāt savu datu bāzi.

## Reģistrējot PDO Palīgklasi

```php
// Reģistrējiet PDO palīgklasi
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'lietotājs', 'parole', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Lietošana
Šis objekts paplašina PDO, tāpēc visi parastie PDO metodēm ir pieejami. Lai padarītu datu bāzes vaicāšanu vieglāku, pievienoti šādiie metodēm:

### `runQuery(string $sql, array $params = []): PDOStatement`
Izmantojiet to INSERTS, UPDATES vai ja plānojat izmantot SELECT cilpā

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM tabula WHERE kautkas = ?", [ $kautkas ]);
while($row = $statement->fetch()) {
	// ...
}

// Vai rakstīt datu bāzē
$db->runQuery("INSERT INTO tabula (vards) VALUES (?)", [ $vards ]);
$db->runQuery("UPDATE tabula SET vards = ? WHERE id = ?", [ $vards, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Izvelk pirmo lauku no vaicājuma

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM tabula WHERE kautkas = ?", [ $kautkas ]);
```

### `fetchRow(string $sql, array $params = []): array`
Izvelk vienu rindu no vaicājuma

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT * FROM tabula WHERE id = ?", [ $id ]);
```

### `fetchAll(string $sql, array $params = []): array`
Izvelk visas rindas no vaicājuma

```php
$db = Flight::db();
rows = $db->fetchAll("SELECT * FROM tabula WHERE kautkas = ?", [ $kautkas ]);
foreach($rows as $row) {
	// dari kaut ko
}
```

## Piezīme ar `IN()` sintakse
Šim ir noderīgs apvalks arī `IN()` teikumiem. Jūs vienkārši varat padot vienu jautājuma zīmi kā aizvietotājs `IN()` un tad vērtību masīvu. Šeit ir piemērs, kā tas varētu izskatīties:

```php
$db = Flight::db();
vards = 'Bobs';
uznemumu_ids = [1,2,3,4,5];
rows = $db->fetchAll("SELECT * FROM tabula WHERE vards = ? AND uznemuma_id IN (?)", [ $vards, $uznemumu_ids ]);
```

## Pilns piemērs

```php
// Piemēra route un kā jūs izmantotu šo apvalku
Flight::route('/lietotaji', function () {
	// Saņemt visus lietotājus
	$lietotaji = Flight::db()->fetchAll('SELECT * FROM lietotaji');

	// Plūdēt visus lietotājus
	$statement = Flight::db()->runQuery('SELECT * FROM lietotaji');
	while ($lietotajs = $statement->fetch()) {
		echo $lietotajs['vards'];
	}

	// Saņemt vienu lietotāju
	$lietotajs = Flight::db()->fetchRow('SELECT * FROM lietotaji WHERE id = ?', [123]);

	// Saņemt vienu vērtību
	$count = Flight::db()->fetchField('SELECT COUNT(*) NO lietotaji');

	// Īpašs IN() sintaksei palīdzēt (pārliecinieties, ka IN ir lielie burti)
	$lietotaji = Flight::db()->fetchAll('SELECT * FROM lietotaji WHERE id IN (?)', [[1,2,3,4,5]]);
	// jūs varētu arī darīt šo
	$lietotaji = Flight::db()->fetchAll('SELECT * FROM lietotaji WHERE id IN (?)', [ '1,2,3,4,5']);

	// Ievietot jaunu lietotāju
	Flight::db()->runQuery("INSERT INTO lietotaji (vards, e_pasts) VALUES (?, ?)", ['Bobs', 'bobs@piemers.lv']);
	insert_id = Flight::db()->lastInsertId();

	// Atjaunot lietotāju
	Flight::db()->runQuery("UPDATE lietotaji SET vards = ? WHERE id = ?", ['Bobs', 123]);

	// Dzēst lietotāju
	Flight::db()->runQuery("DZĒST NO lietotaji WHERE id = ?", [123]);

	// Saņemiet ietekmēto rindu skaitu
	$statement = Flight::db()->runQuery("UPDATE lietotaji SET vards = ? WHERE vards = ?", ['Bobs', 'Sally']);
	afektētās_rindas = $statement->rowCount();

});
```