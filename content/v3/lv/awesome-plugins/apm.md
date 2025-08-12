# FlightPHP APM Dokumentācija

Laipni lūgts FlightPHP APM — jūsu aplikācijas personīgais veiktspējas treneris! Šis ceļvedis ir jūsu ceļš uz Application Performance Monitoring (APM) iestatīšanu, izmantošanu un apgūšanu ar FlightPHP. Vai jūs medījat lēnus pieprasījumus vai vienkārši vēlaties izklaidēties ar latentuma diagrammām, mēs esam parūpējušies. Padarīsim jūsu aplikāciju ātrāku, jūsu lietotājus priecīgākus un atkļūdošanas sesijas vieglākas!

![FlightPHP APM](/images/apm.png)

## Kāpēc APM ir svarīgs

Iedomājieties: jūsu aplikācija ir aizņemts restorāns. Bez veida, kā izsekot, cik ilgi pasūtījumi prasa vai kur virtuvē ir aizkavēšanās, jūs minaties, kāpēc klienti aiziet neapmierināti. APM ir jūsu pavārs — tas vēro katru soli, no ienākošiem pieprasījumiem līdz datubāzes vaicājumiem, un atzīmē visu, kas palēnina. Lēnas lapas zaudē lietotājus (pētījumi rāda, ka 53% aiziet, ja vietne prasa vairāk par 3 sekundēm, lai ielādētos!), un APM palīdz noķert šīs problēmas *pirms* tās sāpin. Tas ir proaktīvs miers — mazāk “kāpēc tas ir salauzts?” momentu, vairāk “skatieties, cik tas darbojas gludi!” uzvaru.

## Instalēšana

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Uztur saderību ar LTS Linux distribūcijām, vienlaikus atbalstot mūsdienīgu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglais ietvars, ko mēs uzlabojam.

## Sākšana

Šeit ir jūsu soli-pa-solim ceļš uz APM lieliskumu:

### 1. Reģistrējiet APM

Ievietojiet to savā `index.php` vai `services.php` failā, lai sāktu izsekošanu:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);  // Kas notiek šeit?
// - `LoggerFactory::create()` paņem jūsu konfigurāciju (vairāk par to drīz) un iestata žurnālu — pēc noklusējuma SQLite.
// - `Apm` ir zvaigzne — tas klausās Flight notikumus (pieprasījumus, maršrutus, kļūdas utt.) un savāc metrikus.
// - `bindEventsToFlightInstance($app)` saista visu ar jūsu Flight aplikāciju.

**Profesionāls Padoms: Paraugu Ņemšana**
Ja jūsu aplikācija ir aizņemta, žurnālošana *katram* pieprasījumam var pārkraut sistēmu. Izmantojiet paraugu ātrumu (no 0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1);  // Žurnālo 10% pieprasījumu
```

Tas uztur veiktspēju asu, vienlaikus sniedzot stabilus datus.

### 2. Konfigurējiet to

Izpildiet šo, lai izveidotu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Kas tas dara?**
- Palaid maģiju, kas jautā, no kurienes nāk izejmateriālu metriki (avots) un kur dodas apstrādātie dati (galamērķis).
- Noklusējums ir SQLite — piem., `sqlite:/tmp/apm_metrics.sqlite` avotam, cits galamērķim.
- Jūs saņemsiet konfigurāciju, piemēram:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Šis process arī jautās, vai vēlaties palaist migrācijas šim iestatījumam. Ja jūs to iestatāt pirmo reizi, atbilde ir jā.

**Kāpēc divas atrašanās vietas?**
Neapstrādātās metrikas uzkrājas ātri (domājiet par nefiltrētiem žurnāliem). Darbinieks apstrādā tās strukturētā galamērķī paneļa darbībai. Tas uztur lietas kārtīgas!

### 3. Apstrādājiet metrikas ar Darbinieku

Darbinieks pārvērš neapstrādātās metrikas par paneļa gataviem datiem. Izpildiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Kas tas dara?**
- Lasīt no jūsu avota (piem., `apm_metrics.sqlite`).
- Apstrādā līdz 100 metriku (noklusējuma partijas izmērs) jūsu galamērķī.
- Apstājas, kad pabeigts vai ja nav metrikas atlikušas.

**Uzturiet to Darbojošos**
Reālām aplikācijām jūs vēlaties nepārtrauktu apstrādi. Šeit ir jūsu iespējas:

- **Dēmona Režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas mūžīgi, apstrādājot metrikas, kad tās ienāk. Lieliski piemērots attīstībai vai maziem iestatījumiem.

- **Crontab**:
  Pievienojiet to savam crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Palaid katru minūti — perfekti piemērots produkcijai.

- **Tmux/Screen**:
  Sāciet atdalāmu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, tad D, lai atdalītos; `tmux attach -t apm-worker`, lai atkal pievienotos
  ```
  Uztur to darbojošos pat, ja jūs izlogojaties.

- **Pielāgotas Izmaiņas**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādā 50 metrikas vienā reizē.
  - `--max_messages 1000`: Apstāties pēc 1000 metriku.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc tas ir nepieciešams?**
Bez darbinieka jūsu panelis ir tukšs. Tas ir tilts starp neapstrādātiem žurnāliem un izmantojamiem ieskatiem.

### 4. Palaidiet Paneli

Skatiet jūsu aplikācijas vitālos rādītājus:

```bash
php vendor/bin/runway apm:dashboard
```

**Kas tas ir?**
- Palaid PHP serveri uz `http://localhost:8001/apm/dashboard`.
- Rāda pieprasījumu žurnālus, lēnus maršrutus, kļūdu līmeņus un vairāk.

**Pielāgojiet to**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Pieejams no jebkura IP (noderīgs attālinātai skatīšanai).
- `--port 8080`: Izmanto citu portu, ja 8001 ir aizņemts.
- `--php-path`: Norāda uz PHP, ja tas nav jūsu PATH.

Iet uz URL pārlūkā un izpētiet!

#### Produkcijas Režīms

Produkcijā jums var nākties izmēģināt dažas tehnikas, lai paneli palaistu, jo iespējams ir ugunsmūri un citas drošības mēra. Šeit ir dažas iespējas:

- **Izmantojiet Reverso Proxy**: Iestatiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz paneli.
- **SSH Tunelis**: Ja jūs varat SSH uz serveri, izmantojiet `ssh -L 8080:localhost:8001 youruser@yourserver`, lai tunelētu paneli uz savu lokālo mašīnu.
- **VPN**: Ja jūsu serveris ir aiz VPN, pievienojieties tam un piekļūstiet panelim tieši.
- **Konfigurējiet Ugunsmūri**: Atveriet portu 8001 jūsu IP vai servera tīkla. (vai jebkuru portu, ko esat iestatījis).
- **Konfigurējiet Apache/Nginx**: Ja jums ir tīmekļa serveris priekš aplikācijas, jūs varat konfigurēt to uz domēna vai apakšdomēna. Ja jūs to darāt, jūs iestatīsiet dokumenta sakni uz `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Vēlaties citu paneli?

Jūs varat izveidot savu paneli, ja vēlaties! Skatiet vendor/flightphp/apm/src/apm/presenter direktoriju idejām, kā prezentēt datus jūsu panelim!

## Paneļa Funkcijas

Panelis ir jūsu APM galvenā mītne — šeit ir, ko jūs redzēsiet:

- **Pieprasījumu Žurnāls**: Katrs pieprasījums ar laika zīmogu, URL, atbildes kodu un kopējo laiku. Klikšķiniet “Detaļas” uz vidēm, vaicājumiem un kļūdām.
- **Lēnākie Pieprasījumi**: Top 5 pieprasījumi, kas patērē laiku (piem., “/api/heavy” uz 2.5s).
- **Lēnākie Maršruti**: Top 5 maršruti pēc vidējā laika — lieliski, lai pamanītu modeļus.
- **Kļūdu Līmenis**: Procentuāli neveiksmīgi pieprasījumi (piem., 2.3% 500s).
- **Latentuma Percentili**: 95. (p95) un 99. (p99) atbildes laiki — ziniet savus sliktākos scenārijus.
- **Atbildes Kodu Diagramma**: Vizualizējiet 200s, 404s, 500s laika gaitā.
- **Ilgi Vaicājumi/Vidēm**: Top 5 lēni datubāzes zvani un vidēm slāņi.
- **Keša Sit/Miss**: Cik bieži jūsu kešs glābj dienu.

**Papildu**:
- Filtrējiet pēc “Pēdējā Stundas,” “Pēdējās Dienas” vai “Pēdējās Nedēļas.”
- Pārslēdziet tumšo režīmu uz vēlo nakšu sesijām.

**Piemērs**:
Pieprasījums uz `/users` var parādīt:
- Kopējais Laiks: 150ms
- Vidēm: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešs: Sit on `user_list` (5ms)

## Pievienošana Pielāgotu Notikumu

Izsekot jebko — piemēram, API zvanu vai maksājumu procesu:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));  // Kur tas parādās?
// In paneļa pieprasījuma detaļās zem “Custom Events” — paplašināms ar glīti JSON formatējumu.

**Lietošanas Piemērs**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));  // Tagad jūs redzēsiet, vai tas API vilcin jūsu aplikāciju!
```

## Datubāzes Monitorings

Izsekot PDO vaicājumus šādi:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);  // Ko jūs saņemat?
// - Vaicājuma tekstu (piem., `SELECT * FROM users WHERE id = ?`)
// - Izpildes laiku (piem., 0.015s)
// - Rindu skaitu (piem., 42)

**Brīdinājums**:
// - **Neobligāti**: Izlaidiet to, ja jums nav vajadzīgs DB izsekošana.
// - **PdoWrapper Only**: Kodola PDO vēl nav pieķerts — gaidiet!
// - **Veiktspējas Brīdinājums**: Žurnālošana katram vaicājumam DB-smagā vietnē var palēnināt lietas. Izmantojiet paraugu ņemšanu (`$Apm = new Apm($ApmLogger, 0.1)`), lai samazinātu slodzi.

**Piemērs Izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindas: 15

## Darbinieka Iespējas

Pielāgojiet darbinieku pēc vēlēšanās:

- `--timeout 300`: Apstājas pēc 5 minūtēm — labs testēšanai.
- `--max_messages 500`: Ierobežo līdz 500 metriku — uztur to galīgu.
- `--batch_size 200`: Apstrādā 200 vienā reizē — līdzsvaro ātrumu un atmiņu.
- `--daemon`: Darbojas nepārtraukti — ideāli piemērots tiešraides monitorēšanai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikas vienā reizē.

## Pieprasījuma ID Aplikācijā

Katram pieprasījumam ir unikāls pieprasījuma ID izsekošanai. Jūs varat izmantot šo ID savā aplikācijā, lai saistītu žurnālus un metrikas. Piemēram, jūs varat pievienot pieprasījuma ID kļūdas lapai:

```php
Flight::map('error', function($message) {
	// Saņemiet pieprasījuma ID no atbildes galvenes X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Turklāt jūs varētu to saņemt no Flight mainīgā
	// Šī metode nedarbosies labi Swoole vai citās asinhronās platformās.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Kļūda: $message (Pieprasījuma ID: $requestId)";
});
```

## Atjaunināšana

Ja jūs atjauninat uz jaunāku APM versiju, iespējams, ka ir datubāzes migrācijas, kas jāpalaid. Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:migrate
```
Šis palaids visas nepieciešamās migrācijas, lai atjauninātu datubāzes shēmu uz jaunāko versiju.

**Piezīme:** Ja jūsu APM datubāze ir liela, šīs migrācijas var prasīt laiku. Jūs varētu vēlēties palaist šo komandu ārpus maksimālās slodzes stundām.

## Tīrīšana Veco Datu

Lai uzturētu datubāzi kārtīgu, jūs varat tīrīt vecos datus. Tas ir īpaši noderīgi, ja jūs vadāt aizņemtu aplikāciju un vēlaties uzturēt datubāzes izmēru pārvaldāmu.
Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:purge
```
Šis izdzēsīs visus datus, kas vecāki par 30 dienām no datubāzes. Jūs varat pielāgot dienu skaitu, pievienojot citu vērtību `--days` opcijai:

```bash
php vendor/bin/runway apm:purge --days 7
```
Šis izdzēsīs visus datus, kas vecāki par 7 dienām no datubāzes.

## Problēmu Novēršana

Iestrēdzis? Mēģiniet šos:

- **Nav Paneļa Datu?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Konfigurācijas ceļi atbilst? Pārbaudiet `.runway-config.json` DSN norādes uz reāliem failiem.
  - Izpildiet `php vendor/bin/runway apm:worker` manuāli, lai apstrādātu gaidošās metrikas.

- **Darbinieka Kļūdas?**
  - Ielūkojieties jūsu SQLite failos (piem., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus uz steka trasēm.

- **Panelis Neuzsākas?**
  - Ports 8001 ir aizņemts? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk Lēns?**
  - Samaziniet paraugu ātrumu: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet partijas izmēru: `--batch_size 20`.