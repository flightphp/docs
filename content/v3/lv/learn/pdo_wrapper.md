# PdoWrapper PDO Palīgdarbības klase

## Pārskats

`PdoWrapper` klase Flight ir draudzīgs palīgs darbam ar datubāzēm, izmantojot PDO. Tā vienkāršo izplatītās datubāzes uzdevumus, pievieno dažas ērtas metodes rezultātu iegūšanai un atgriež rezultātus kā [Collections](/learn/collections) vieglai piekļuvei. Tā arī atbalsta vaicājumu reģistrēšanu un lietotnes veiktspējas uzraudzību (APM) sarežģītām lietošanas situācijām.

## Saprašana

Darbs ar datubāzēm PHP var būt nedaudz verbāls, īpaši, izmantojot PDO tieši. `PdoWrapper` paplašina PDO un pievieno metodes, kas padara vaicājumu veikšanu, rezultātu iegūšanu un apstrādi daudz vieglāku. Tā vietā, lai žonglētu ar sagatavotiem paziņojumiem un iegūšanas režīmiem, jūs iegūstat vienkāršas metodes izplatītiem uzdevumiem, un katra rindiņa tiek atgriezta kā Collection, tāpēc jūs varat izmantot masīva vai objekta notāciju.

Jūs varat reģistrēt `PdoWrapper` kā koplietojamu servisu Flight un pēc tam izmantot to jebkur savā lietotnē caur `Flight::db()`.

## Pamatlietošana

### PDO palīgdarbības reģistrēšana

Vispirms reģistrējiet `PdoWrapper` klasi ar Flight:

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

Tagad jūs varat izmantot `Flight::db()` jebkur, lai iegūtu savienojumu ar datubāzi.

### Vaicājumu izpildīšana

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Izmantojiet to INSERT, UPDATE vai kad vēlaties manuāli iegūt rezultātus:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row ir masīvs
}
```

Jūs varat izmantot to arī rakstīšanai:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Iegūstiet vienu vērtību no datubāzes:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Iegūstiet vienu rindiņu kā Collection (masīva/objekta piekļuve):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// vai
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Iegūstiet visas rindiņas kā Collection masīvu:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // vai
    echo $user->name;
}
```

### Izmantojot `IN()` aizstājējus

Jūs varat izmantot vienu `?` IN() klauzulā un nodot masīvu vai komatiem atdalītu virkni:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// vai
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Uzlabota lietošana

### Vaicājumu reģistrēšana & APM

Ja vēlaties izsekot vaicājumu veiktspēju, iespējiet APM izsekošanu reģistrēšanas laikā:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // pēdējais parametrs iespējina APM
]);
```

Pēc vaicājumu izpildes jūs varat manuāli reģistrēt tos, bet APM tos reģistrēs automātiski, ja iespējots:

```php
Flight::db()->logQueries();
```

Tas izraisīs notikumu (`flight.db.queries`) ar savienojuma un vaicājumu metrikiem, ko jūs varat klausīties, izmantojot Flight notikumu sistēmu.

### Pilns piemērs

```php
Flight::route('/users', function () {
    // Iegūstiet visus lietotājus
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Plūsma visiem lietotājiem
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Iegūstiet vienu lietotāju
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Iegūstiet vienu vērtību
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Īpašā IN() sintakse
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Ievietojiet jaunu lietotāju
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Atjauniniet lietotāju
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Dzēsiet lietotāju
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Iegūstiet skarto rindiņu skaitu
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Skatīt arī

- [Collections](/learn/collections) - Uzziniet, kā izmantot Collection klasi vieglai datu piekļuvei.

## Traucējummeklēšana

- Ja saņemat kļūdu par datubāzes savienojumu, pārbaudiet savu DSN, lietotājvārdu, paroli un opcijas.
- Visas rindiņas tiek atgrieztas kā Collections—ja vajadzīgs vienkāršs masīvs, izmantojiet `$collection->getData()`.
- IN (?) vaicājumiem pārliecinieties, ka nododiet masīvu vai komatiem atdalītu virkni.

## Izmaiņu žurnāls

- v3.2.0 - Sākotnējā PdoWrapper izlaišana ar pamata vaicājumu un iegūšanas metodēm.