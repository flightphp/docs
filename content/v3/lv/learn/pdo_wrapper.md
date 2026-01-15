# PdoWrapper PDO Palīdze ģenerators

> **BRĪDINĀJUMS**
>
> **Novecojis:** `PdoWrapper` ir novecojis kopš Flight v3.18.0. Tas netiks noņemts nākamajā versijā, bet tiks uzturēts atpakaļsaderībai. Lūdzu, izmantojiet [SimplePdo](/learn/simple-pdo) tā vietā, kas piedāvā tādu pašu funkcionalitāti plus papildu palīgmēģus biežām datubāzes operācijām.

## Pārskats

`PdoWrapper` klase Flight ir draudzīgs palīgs darbam ar datubāzēm, izmantojot PDO. Tā vienkāršo izplatītās datubāzes uzdevumus, pievieno dažas ērtas metodes rezultātu iegūšanai un atgriež rezultātus kā [Collections](/learn/collections) vieglai piekļuvei. Tā arī atbalsta vaicājumu reģistrēšanu un lietojumprogrammas veiktspējas uzraudzību (APM) sarežģītiem gadījumiem.

## Saprašana

Darbs ar datubāzēm PHP var būt nedaudz verbāls, īpaši, izmantojot PDO tieši. `PdoWrapper` paplašina PDO un pievieno metodes, kas padara vaicājumu veikšanu, rezultātu iegūšanu un apstrādi daudz vieglāku. Tā vietā, lai žonglētu ar sagatavotiem paziņojumiem un iegūšanas režīmiem, jūs saņemat vienkāršas metodes izplatītiem uzdevumiem, un katra rindiņa tiek atgriezta kā Collection, tāpēc jūs varat izmantot masīva vai objekta notāciju.

Jūs varat reģistrēt `PdoWrapper` kā kopīgu servisu Flight, un tad izmantot to jebkur savā lietojumprogrammā caur `Flight::db()`.

## Pamata Izmantošana

### Reģistrēšana PDO Palīgam

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

### Vaicājumu Izpilde

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Izmantojiet to INSERT, UPDATE vai kad vēlaties iegūt rezultātus manuāli:

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

Iegūstiet visas rindiņas kā Collections masīvu:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // vai
    echo $user->name;
}
```

### Izmantojot `IN()` Vietu Turētājus

Jūs varat izmantot vienu `?` IN() klauzulā un nodot masīvu vai komatiem atdalītu virkni:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// vai
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Uzlabota Izmantošana

### Vaicājumu Reģistrēšana & APM

Ja vēlaties izsekot vaicājuma veiktspēju, iespējiet APM izsekošanu reģistrējot:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // pēdējais params iespējina APM
]);
```

Pēc vaicājumu izpildes jūs varat reģistrēt tos manuāli, bet APM tos reģistrēs automātiski, ja iespējots:

```php
Flight::db()->logQueries();
```

Tas izraisīs notikumu (`flight.db.queries`) ar savienojuma un vaicājuma metrikiem, ko jūs varat klausīties, izmantojot Flight notikumu sistēmu.

### Pilns Piemērs

```php
Flight::route('/users', function () {
    // Iegūt visus lietotājus
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Straumēt visus lietotājus
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Iegūt vienu lietotāju
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Iegūt vienu vērtību
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Īpaša IN() sintakse
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Ievietot jaunu lietotāju
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Atjaunināt lietotāju
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Dzēst lietotāju
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Iegūt skarto rindu skaitu
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Skatīt Arī

- [Collections](/learn/collections) - Uzziniet, kā izmantot Collection klasi vieglai datu piekļuvei.

## Traucējummeklēšana

- Ja saņemat kļūdu par datubāzes savienojumu, pārbaudiet savu DSN, lietotājvārdu, paroli un opcijas.
- Visas rindiņas tiek atgrieztas kā Collections—ja vajadzīgs vienkāršs masīvs, izmantojiet `$collection->getData()`.
- IN (?) vaicājumiem pārliecinieties, ka nododiet masīvu vai komatiem atdalītu virkni.

## Izmaiņu Žurnāls

- v3.2.0 - Sākotnējā PdoWrapper izlaišana ar pamata vaicājuma un iegūšanas metodēm.