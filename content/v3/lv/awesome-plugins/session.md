# FlightPHP Sesija - Viegls failu balstīts sesijas pārvaldnieks

Tas ir viegls, failu balstīts sesijas pārvaldnieka spraudnis [Flight PHP Framework](https://docs.flightphp.com/). Tas nodrošina vienkāršu, bet spēcīgu risinājumu sesiju pārvaldībai, ar iespējām kā nesekojošas sesijas lasīšanas, izvēles šifrēšanu, automātisku apstiprināšanu un testēšanas režīmu attīstībai. Sesijas dati tiek glabāti failos, padarot to ideālu lietojumprogrammām, kurām nav nepieciešama datubāze.

Ja vēlaties izmantot datubāzi, pārbaudiet [ghostff/session](/awesome-plugins/ghost-session) spraudni, kas satur daudzas no šīm pašām iespējām, bet ar datubāzes aizmuguri.

Apmeklējiet [Github repozitoriju](https://github.com/flightphp/session) pilnam avota kodam un detaļām.

## Instalēšana

Instalējiet spraudni caur Composer:

```bash
composer require flightphp/session
```

## Pamata lietošana

Lūk, vienkāršs piemērs, kā izmantot `flightphp/session` spraudni jūsu Flight lietojumprogrammā:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Reģistrē sesijas servisu
$app->register('session', Session::class);

// Piemērs maršrutam ar sesijas lietošanu
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Izvada: johndoe
    echo $session->get('preferences', 'default_theme'); // Izvada: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Lietotājs ir pieteicies!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Notīra visas sesijas datus
    Flight::json(['message' => 'Izlogojies veiksmīgi']);
});

Flight::start();
```

### Galvenie punkti
- **Nesekojošas**: Pēc noklusējuma izmanto `read_and_close` sesijas sākšanai, novēršot sesijas bloķēšanas problēmas.
- **Automātiska apstiprināšana**: Ieslēgta pēc noklusējuma, tāpēc izmaiņas tiek saglabātas automātiski izslēgšanās brīdī, ja vien nav atslēgta.
- **Failu glabāšana**: Sesijas tiek glabātas sistēmas pagaidu direktorijā zem `/flight_sessions` pēc noklusējuma.

## Konfigurācija

Jūs varat pielāgot sesijas pārvaldnieku, nododot masīvu ar opcijām reģistrējot:

```php
// Jā, tas ir dubults masīvs :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Direktorija sesijas failiem
	'prefix' => 'myapp_',                              // Priekša sesijas failiem
    'encryption_key' => 'a-secure-32-byte-key-here',   // Ieslēgt šifrēšanu (32 baiti ieteikti AES-256-CBC)
    'auto_commit' => false,                            // Atslēgt automātisko apstiprināšanu manuālai kontrolei
    'start_session' => true,                           // Sākt sesiju automātiski (pēc noklusējuma: true)
    'test_mode' => false,                              // Ieslēgt testēšanas režīmu attīstībai
    'serialization' => 'json',                         // Serializācijas metode: 'json' (pēc noklusējuma) vai 'php' (legacy)
] ]);
```

### Konfigurācijas opcijas
| Opcija            | Apraksts                                      | Noklusētā vērtība                     |
|-------------------|----------------------------------------------|---------------------------------------|
| `save_path`       | Direktorija, kur glabājas sesijas faili         | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Priekša saglabātajam sesijas failam                | `sess_`                           |
| `encryption_key`  | Atslēga AES-256-CBC šifrēšanai (izvēles)        | `null` (bez šifrēšanas)            |
| `auto_commit`     | Automātiski saglabāt sesijas datus izslēgšanās brīdī               | `true`                            |
| `start_session`   | Sākt sesiju automātiski                  | `true`                            |
| `test_mode`       | Darboties testēšanas režīmā bez ietekmēšanas uz PHP sesijām  | `false`                           |
| `test_session_id` | Pielāgota sesijas ID testēšanas režīmam (izvēles)       | Nejauši ģenerēta, ja nav iestatīta     |
| `serialization`   | Serializācijas metode: 'json' (pēc noklusējuma, droša) vai 'php' (legacy, atļauj objektus) | `'json'` |

## Serializācijas režīmi

Pēc noklusējuma šī bibliotēka izmanto **JSON serializāciju** sesijas datiem, kas ir droša un novērš PHP objektu iesūkšanās ievainojamības. Ja jums ir nepieciešams glabāt PHP objektus sesijā (nav ieteicams lielākumam app), jūs varat izvēlēties legacy PHP serializāciju:

- `'serialization' => 'json'` (pēc noklusējuma):
  - Tiek atļautas tikai masīvi un primitīvi dati sesijas datos.
  - Drošāka: imūna pret PHP objektu iesūkšanos.
  - Faili tiek priekšēji ar `J` (vienkāršs JSON) vai `F` (šifrēts JSON).
- `'serialization' => 'php'`:
  - Atļauj glabāt PHP objektus (lietojiet ar uzmanību).
  - Faili tiek priekšēji ar `P` (vienkārša PHP serializācija) vai `E` (šifrēta PHP serializācija).

**Piezīme:** Ja izmantojat JSON serializāciju, mēģinot glabāt objektu, tiks izraisīta izņēmuma kļūda.

## Paplašināta lietošana

### Manuāla apstiprināšana
Ja atslēdzat automātisko apstiprināšanu, jums ir manuāli jāapstiprina izmaiņas:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Skaidri saglabā izmaiņas
});
```

### Sesijas drošība ar šifrēšanu
Ieslēdziet šifrēšanu sensitīviem datiem:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Šifrēts automātiski
    echo $session->get('credit_card'); // Dekodēts pie saņemšanas
});
```

### Sesijas atjaunošana
Atjaunojiet sesijas ID drošībai (piemēram, pēc pieteikšanās):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Jauns ID, saglabā datus
    // VAI
    $session->regenerate(true); // Jauns ID, dzēš vecos datus
});
```

### Middleware piemērs
Aizsargājiet maršrutus ar sesijas balstītu autentifikāciju:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Laipni lūgts admin panelī']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Piekļuve liegta');
    }
});
```

Tas ir tikai vienkāršs piemērs, kā to izmantot middleware. Vairāk detalizētam piemēram, skatiet [middleware](/learn/middleware) dokumentāciju.

## Metodes

`Session` klase nodrošina šādas metodes:

- `set(string $key, $value)`: Glabā vērtību sesijā.
- `get(string $key, $default = null)`: Iegūst vērtību, ar izvēles noklusējumu, ja atslēga neeksistē.
- `delete(string $key)`: Noņem specifisku atslēgu no sesijas.
- `clear()`: Dzēš visus sesijas datus, bet saglabā to pašu faila nosaukumu sesijai.
- `commit()`: Saglabā pašreizējos sesijas datus failu sistēmā.
- `id()`: Atgriež pašreizējo sesijas ID.
- `regenerate(bool $deleteOldFile = false)`: Atjauno sesijas ID, ieskaitot jauna sesijas faila izveidošanu, saglabājot visus vecos datus un vecais fails paliek sistēmā. Ja `$deleteOldFile` ir `true`, vecais sesijas fails tiek dzēsts.
- `destroy(string $id)`: Iznīcina sesiju pēc ID un dzēš sesijas failu no sistēmas. Tas ir daļa no `SessionHandlerInterface` un `$id` ir nepieciešams. Tipiska lietošana būtu `$session->destroy($session->id())`.
- `getAll()` : Atgriež visus datus no pašreizējās sesijas.

Visas metodes izņemot `get()` un `id()` atgriež `Session` instanci ķēdes izveidošanai.

## Kāpēc izmantot šo spraudni?

- **Viegls**: Nav ārēju atkarību — tikai faili.
- **Nesekojošas**: Izvairās no sesijas bloķēšanas ar `read_and_close` pēc noklusējuma.
- **Drošs**: Atbalsta AES-256-CBC šifrēšanu sensitīviem datiem.
- **Fleksibls**: Automātiska apstiprināšana, testēšanas režīms un manuālas kontroles opcijas.
- **Flight-Native**: Izveidots specifiski Flight framework.

## Tehniskās detaļas

- **Glabāšanas formāts**: Sesijas faili tiek priekšēji ar `sess_` un glabāti konfigurētajā `save_path`. Faila satura priekši:
  - `J`: Vienkāršs JSON (pēc noklusējuma, bez šifrēšanas)
  - `F`: Šifrēts JSON (pēc noklusējuma ar šifrēšanu)
  - `P`: Vienkārša PHP serializācija (legacy, bez šifrēšanas)
  - `E`: Šifrēta PHP serializācija (legacy ar šifrēšanu)
- **Šifrēšana**: Izmanto AES-256-CBC ar nejaušu IV katrai sesijas rakstīšanai, kad tiek norādīta `encryption_key`. Šifrēšana darbojas gan JSON, gan PHP serializācijas režīmos.
- **Serializācija**: JSON ir pēc noklusējuma un drošākā metode. PHP serializācija pieejama legacy/uzlabotai lietošanai, bet ir mazāk droša.
- **Atkritumu savākšana**: Ietver PHP `SessionHandlerInterface::gc()` veco sesiju tīrīšanai.

## Dalība

Ieteikumi ir laipni gaidīti! Forkojiet [repozitoriju](https://github.com/flightphp/session), veiciet izmaiņas un iesniedziet pull request. Ziņojiet par kļūdām vai ieteikumiām caur Github issue tracker.

## Licence

Šis spraudnis ir licencēts zem MIT Licence. Skatiet [Github repozitoriju](https://github.com/flightphp/session) detaļām.