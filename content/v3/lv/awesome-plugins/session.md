# FlightPHP Sesija - Viegls Failu Bāzes Sesiju Apstrādātājs

Šis ir viegls, failu bāzes sesiju apstrādātāja paplašinājums [Flight PHP Framework](https://docs.flightphp.com/). Tas nodrošina vienkāršu, taču jaudīgu risinājumu sesiju pārvaldīšanai, ar tādām funkcijām kā neblokējoša sesiju lasīšana, opcionalā šifrēšana, automātiskā apstiprināšana un testa režīms izstrādei. Sesiju dati tiek glabāti failos, padarot to ideāli piemērotu lietojumprogrammām, kurām nav nepieciešama datu bāze.

Ja vēlaties izmantot datu bāzi, apskatiet [ghostff/session](/awesome-plugins/ghost-session) paplašinājumu ar daudzām no šīm pašām funkcijām, bet ar datu bāzes atbalstu.

Apmeklējiet [Github repozitoriju](https://github.com/flightphp/session) pilnīgai avota kodu un detaļu apskatei.

## Instalācija

Uzstādiet paplašinājumu, izmantojot Composer:

```bash
composer require flightphp/session
```

## Pamata Lietošana

Šeit ir vienkāršs piemērs, kā izmantot `flightphp/session` paplašinājumu savā Flight lietojumprogrammā:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Reģistrējiet sesiju pakalpojumu
$app->register('session', Session::class);

// Piemēra maršruta izmantošana ar sesiju
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Izvade: johndoe
    echo $session->get('preferences', 'default_theme'); // Izvade: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Lietotājs ir pieteicies!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Notīra visus sesiju datus
    Flight::json(['message' => 'Veiksmīgi izrakstījās']);
});

Flight::start();
```

### Galvenie Punkti
- **Neblokējoša**: Izmanto `read_and_close` sesijas sākšanai pēc noklusējuma, novēršot sesijas bloķēšanas problēmas.
- **Automātiska Apstiprināšana**: Iespējota pēc noklusējuma, tāpēc izmaiņas tiek saglabātas automātiski izbeigšanās brīdī, ja nav atspējota.
- **Failu Uzglabāšana**: Sesijas tiek glabātas sistēmas temp direktorijā zem `/flight_sessions` pēc noklusējuma.

## Konfigurācija

Jūs varat pielāgot sesiju apstrādātāju, pārsūtot opciju masīvu, reģistrējot:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Direktorija sesiju failiem
    'encryption_key' => 'a-secure-32-byte-key-here',   // Iespējot šifrēšanu (32 baiti ieteicami AES-256-CBC)
    'auto_commit' => false,                            // Atspējot automātisko apstiprināšanu manuālai kontrolei
    'start_session' => true,                           // Automātiski uzsākt sesiju (noklusējums: true)
    'test_mode' => false                               // Iespējot testa režīmu izstrādei
]);
```

### Konfigurācijas Opcijas
| Opcija            | Apraksts                                      | Noklusējuma Vērtība                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Direktorija, kurā glabājas sesiju faili         | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Atslēga AES-256-CBC šifrēšanai (nopietna)        | `null` (nav šifrēšanas)            |
| `auto_commit`     | Automātiski saglabāt sesiju datus izbeigšanās brīdī               | `true`                            |
| `start_session`   | Automātiski uzsākt sesiju                  | `true`                            |
| `test_mode`       | Darbība testa režīmā bez PHP sesiju ietekmes   | `false`                           |
| `test_session_id` | Pielāgota sesijas ID testa režīmā (opcijas)       | Nejauši ģenerēts, ja nav iestatīts     |

## Uzlabota Lietošana

### Manuāla Apstiprināšana
Ja atspējojat automātisko apstiprināšanu, jums manuāli jāsaglabā izmaiņas:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Skaidri saglabāt izmaiņas
});
```

### Sesijas Drošība ar Šifrēšanu
Iespējot šifrēšanu sensitīviem datiem:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Automātiski šifrēts
    echo $session->get('credit_card'); // Atšifrēts pie atgūšanas
});
```

### Sesijas Atjaunošana
Atjaunojiet sesijas ID drošībai (piemēram, pēc pieteikšanās):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Jauns ID, saglabā datus
    // VAI
    $session->regenerate(true); // Jauns ID, izdzēš vecos datus
});
```

### Middleware Piemērs
Aizsargājiet maršrutus ar sesiju balstītu autentifikāciju:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Laipni lūdzam administratora panelī']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Piekļuve liegta');
    }
});
```

Tas ir tikai vienkāršs piemērs, kā izmantot šo vidusdaļā. Detalizētākai piemēram skatiet [middleware](/learn/middleware) dokumentāciju.

## Metodes

`Session` klase nodrošina šīs metodes:

- `set(string $key, $value)`: Glabā vērtību sesijā.
- `get(string $key, $default = null)`: Atgūst vērtību, ar opciju noklusējuma vērtību, ja atslēga nepastāv.
- `delete(string $key)`: Noņem specifisku atslēgu no sesijas.
- `clear()`: Izdzēš visus sesijas datus.
- `commit()`: Saglabā pašreizējos sesijas datus failu sistēmā.
- `id()`: Atgriež pašreizējo sesijas ID.
- `regenerate(bool $deleteOld = false)`: Atjauno sesijas ID, opcionalitātes gadījumā dzēšot vecos datus.

Visas metodes, izņemot `get()` un `id()`, atgriež `Session` instance, lai saistītu izsaukumus.

## Kāpēc Izmantot Šo Paplašinājumu?

- **Viegls**: Nav ārēju atkarību – tikai faili.
- **Neblokējošs**: Novērš sesijas bloķēšanas problēmas ar `read_and_close` pēc noklusējuma.
- **Drošs**: Atbalsta AES-256-CBC šifrēšanu sensitīviem datiem.
- **Elastīgs**: Automātiskā apstiprināšana, testa režīms un manuālās kontroles iespējas.
- **Flight-Natīvs**: Izstrādāts īpaši Flight framework.

## Tehniskās Detaļas

- **Uzglabāšanas Formāts**: Sesiju faili tiek prefiksēti ar `sess_` un uzglabāti konfigurētajā `save_path`. Šifrētie dati izmanto `E` prefiksu, teksta dati izmanto `P`.
- **Šifrēšana**: Izmanto AES-256-CBC ar nejaušu IV katrai sesijas rakstīšanai, kad ir norādīta `encryption_key`.
- **Atkritumu Vākšana**: īsteno PHP `SessionHandlerInterface::gc()` funkciju, lai notīrītu beigušās sesijas.

## Ieguldījumi

Ieguldījumi ir laipni gaidīti! Forkojiet [rep(res-zitoriju](https://github.com/flightphp/session), veiciet izmaiņas un iesniedziet pull pieprasījumu. Ziņojiet par kļūdām vai ieteiciet funkcijas, izmantojot Github problēmu izsekošanu.

## Licences

Šis paplašinājums ir licencēts saskaņā ar MIT licenci. Lai iegūtu detaļas, skatiet [Github repozitoriju](https://github.com/flightphp/session).