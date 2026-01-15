# SimplePdo PDO Palīdze Ģimene

## Pārskats

`SimplePdo` klase Flight ir moderna, funkcijām bagāta palīdze darbam ar datubāzēm, izmantojot PDO. Tā paplašina `PdoWrapper` un pievieno ērtas palīgmēģenēm izplatītām datubāzes operācijām, piemēram, `insert()`, `update()`, `delete()` un transakcijām. Tā vienkāršo datubāzes uzdevumus, atgriež rezultātus kā [Collections](/learn/collections) vieglai piekļuvei un atbalsta vaicājumu žurnālveidošanu un lietojumprogrammas veiktspējas uzraudzību (APM) sarežģītām lietošanas situācijām.

## Saprašana

`SimplePdo` klase ir izstrādāta, lai padarītu darbu ar datubāzēm PHP daudz vieglāku. Tā vietā, lai žonglētu ar sagatavotiem paziņojumiem, iegūšanas režīmiem un verbāliem SQL operācijām, jūs saņemat tīras, vienkāršas metodes izplatītiem uzdevumiem. Katra rindiņa tiek atgriezta kā Collection, tāpēc jūs varat izmantot gan masīva notāciju (`$row['name']`), gan objekta notāciju (`$row->name`).

Šī klase ir `PdoWrapper` pārklājums, kas nozīmē, ka tā ietver visu `PdoWrapper` funkcionalitāti plus papildu palīgmēģenes, kas padara jūsu kodu tīrāku un vieglāk uzturamu. Ja jūs pašlaik izmantojat `PdoWrapper`, pāreja uz `SimplePdo` ir vienkārša, jo tā paplašina `PdoWrapper`.

Jūs varat reģistrēt `SimplePdo` kā kopīgu servisu Flight, un tad izmantot to jebkur savā lietojumprogrammā caur `Flight::db()`.

## Pamata Lietošana

### Reģistrēšana SimplePdo

Vispirms reģistrējiet `SimplePdo` klasi Flight:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **PIEZĪME**
>
> Ja jūs ne norādāt `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` automātiski iestatīs to uz `PDO::FETCH_ASSOC` jūsu vietā.

Tagad jūs varat izmantot `Flight::db()` jebkur, lai iegūtu savienojumu ar datubāzi.

### Vaicājumu Izpilde

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Izmantojiet to INSERT, UPDATE vai kad vēlaties pašrocīgi iegūt rezultātus:

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

`function fetchRow(string $sql, array $params = []): ?Collection`

Iegūstiet vienu rindiņu kā Collection (masīva/objekta piekļuve):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// vai
echo $user->name;
```

> **PADOMS**
>
> `SimplePdo` automātiski pievieno `LIMIT 1` vaicājumiem `fetchRow()`, ja tas vēl nav klāt, padarot jūsu vaicājumus efektīvākus.

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

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Iegūstiet vienu kolonnu kā masīvu:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Atgriež: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Iegūstiet rezultātus kā atslēgu-vērtību pārus (pirmā kolonna kā atslēga, otrā kā vērtība):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Atgriež: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Izmantojot `IN()` Vietas Turētājus

Jūs varat izmantot vienu `?` IN() klauzulā un nodot masīvu:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Palīgmēģenes

Viens no galvenajiem `SimplePdo` priekšrocībām salīdzinājumā ar `PdoWrapper` ir ērtu palīgmēģeņu pievienošana izplatītām datubāzes operācijām.

### `insert()`

`function insert(string $table, array $data): string`

Ievietojiet vienu vai vairākas rindiņas un atgrieziet pēdējo ievietošanas ID.

**Viena ievietošana:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Masveida ievietošana:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Atjauniniet rindiņas un atgrieziet skarto rindiņu skaitu:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **PIEZĪME**
>
> SQLite `rowCount()` atgriež skaitu rindiņu, kur dati patiešām mainījās. Ja jūs atjauninat rindiņu ar tām pašām vērtībām, kas tai jau ir, `rowCount()` atgriezīs 0. Tas atšķiras no MySQL uzvedības, izmantojot `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Dzēsiet rindiņas un atgrieziet dzēsto rindiņu skaitu:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Izpildiet atsaukumu transakcijas ietvaros. Transakcija automātiski apstiprina veiksmīgos gadījumos vai atgriežas kļūdas gadījumā:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Ja jebkura izņēmuma tiek mestas atsaukuma ietvaros, transakcija automātiski atgriežas un izņēmums tiek atkārtoti mestas.

## Padziļinātā Lietošana

### Vaicājumu Žurnālveidošana & APM

Ja vēlaties izsekot vaicājuma veiktspēju, iespējiet APM izsekošanu reģistrēšanas laikā:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* PDO opcijas */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Pēc vaicājumu izpildes jūs varat žurnālveidot tos manuāli, bet APM tos žurnālveidos automātiski, ja iespējots:

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

    // Iegūt vienu kolonnu
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Iegūt atslēgu-vērtību pārus
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Īpaša IN() sintakse
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Ievietot jaunu lietotāju
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Masveida ievietošana lietotājiem
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Atjaunināt lietotāju
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Dzēst lietotāju
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Izmantot transakciju
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migrēšana no PdoWrapper

Ja jūs pašlaik izmantojat `PdoWrapper`, migrēšana uz `SimplePdo` ir vienkārša:

1. **Atjauniniet savu reģistrāciju:**
   ```php
   // Vecais
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Jaunais
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Visas esošās `PdoWrapper` metodes darbojas `SimplePdo`** - Nav laušanas izmaiņu. Jūsu esošais kods turpinās darboties.

3. **Pēc izvēles izmantojiet jaunas palīgmēģenes** - Sāciet izmantot `insert()`, `update()`, `delete()` un `transaction()`, lai vienkāršotu savu kodu.

## Skatīt Arī

- [Collections](/learn/collections) - Uzziniet, kā izmantot Collection klasi vieglai datu piekļuvei.
- [PdoWrapper](/learn/pdo-wrapper) - Mantotā PDO palīdze klase (novecojusi).

## Traucējummeklēšana

- Ja saņemat kļūdu par datubāzes savienojumu, pārbaudiet savu DSN, lietotājvārdu, paroli un opcijas.
- Visas rindiņas tiek atgrieztas kā Collections—ja vajag vienkāršu masīvu, izmantojiet `$collection->getData()`.
- IN (?) vaicājumiem pārliecinieties, ka nodod masīvu.
- Ja rodas atmiņas problēmas ar vaicājumu žurnālveidošanu garos procesos, pielāgojiet `maxQueryMetrics` opciju.

## Izmaiņu Žurnāls

- v3.18.0 - Sākotnējā SimplePdo izlaišana ar palīgmēģenēm insert, update, delete un transakcijām.