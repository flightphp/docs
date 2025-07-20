# FlightPHP APM Dokumentācija

Laipni lūgti FlightPHP APM — jūsu app personīgais veiktspējas treneris! Šis ceļvedis ir jūsu ceļvedis, lai iestatītu, izmantotu un apgūtu Application Performance Monitoring (APM) ar FlightPHP. Vai jūs meklējat lēnus pieprasījumus vai vienkārši vēlaties iedziļināties latentuma diagrammās, mēs esam to seguši. Padarīsim jūsu app ātrāku, jūsu lietotājus priecīgākus un atkļūdošanas sesijas vieglākas!

![FlightPHP APM](/images/apm.png)

## Kāpēc APM ir svarīgs

Iedomājieties: jūsu app ir aizņemts restorāns. Bez veida izsekot, cik ilgi ņem pasūtījumi vai kur virtuvē rodas aizkavēšanās, jūs minat, kāpēc klienti atstāj neapmierināti. APM ir jūsu pavārs — tas vēro katru soli, no ienākošiem pieprasījumiem līdz datu bāzes vaicājumiem, un atzīmē visu, kas palēnina jūs. Lēnas lapas zaudē lietotājus (pētījumi saka, ka 53% izlec, ja vietne ielādējas vairāk nekā 3 sekundes!), un APM palīdz noķert šīs problēmas *pirms* tās sāp. Tas ir proaktīvs miers — mazāk “kāpēc tas ir salauzts?” momentu, vairāk “skatieties, cik gludi tas darbojas!” uzvaru.

## Instalēšana

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Saglabā saderību ar LTS Linux izdalījumiem, vienlaikus atbalstot mūsdienīgu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglais ietvars, ko mēs uzlabojam.

## Sākšana

Šeit ir jūsu soļu pa soļiem līdz APM lieliskumam:

### 1. Reģistrējiet APM

Ievietojiet to savā `index.php` vai `services.php` failā, lai sāktu izsekošanu:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Izveido konfigurāciju (vairāk par to drīz) un iestata reģistrētāju — pēc noklusējuma SQLite.
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Kas notiek šeit?**
- `LoggerFactory::create()` paņem jūsu konfigurāciju (vairāk par to drīz) un iestata reģistrētāju — pēc noklusējuma SQLite.
- `Apm` ir zvaigzne — tas klausās Flight notikumus (pieprasījumus, maršrutus, kļūdas utt.) un savāc metriku.
- `bindEventsToFlightInstance($app)` saista to ar jūsu Flight app.

**Profesionāls padoms: Paraugu ņemšana**
Ja jūsu app ir aizņemts, reģistrēšana *katram* pieprasījumam var pārkraut sistēmu. Izmantojiet paraugu ņemšanas ātrumu (no 0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Reģistrē 10% pieprasījumu
```

Tas saglabā veiktspēju asu, vienlaikus sniedzot stabilus datus.

### 2. Konfigurējiet to

Izpildiet šo, lai izveidotu savu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Kas tas dara?**
- Palaid maģistru, kas jautā, no kurienes nāk neapstrādāti metriki (avots) un kur iet apstrādāti dati (mērķis).
- Pēc noklusējuma ir SQLite — piem., `sqlite:/tmp/apm_metrics.sqlite` avotam, cits mērķim.
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

**Kāpēc divas vietas?**
Neapstrādāti metriki uzkrājas ātri (domājiet par nefiltrētiem žurnāliem). Darbinieks apstrādā tos strukturētā mērķī, lai izmantotu paneli. Tas uztur visu kārtīgu!

### 3. Apstrādājiet metrikus ar darbinieku

Darbinieks pārvērš neapstrādātus metrikus par panelim gataviem datiem. Izpildiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Kas tas dara?**
- Lasīt no jūsu avota (piem., `apm_metrics.sqlite`).
- Apstrādā līdz 100 metrikiem (pēc noklusējuma partijas lielums) jūsu mērķī.
- Pārtrauc, kad pabeigts vai ja metriki nav palikuši.

**Uzturiet to darbojošos**
Reālām app, jūs vēlaties nepārtrauktu apstrādi. Šeit ir jūsu iespējas:

- **Dēmona režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas mūžīgi, apstrādājot metrikus, kad tie ienāk. Lieliski piemērots izstrādei vai maziem iestatījumiem.

- **Crontab**:
  Pievienojiet to savam crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Palaid katru minūti — perfekti piemērots producēšanai.

- **Tmux/Screen**:
  Sāciet atdalāmu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, tad D, lai atdalītu; `tmux attach -t apm-worker`, lai atkārtoti pievienotos
  ```
  Uztur to darbojošos pat, ja jūs izlogojaties.

- **Pielāgotas izmaiņas**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādā 50 metrikus vienlaikus.
  - `--max_messages 1000`: Pārtrauc pēc 1000 metrikiem.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc tas ir nepieciešams?**
Bez darbinieka jūsu panelis ir tukšs. Tas ir tilts starp neapstrādātiem žurnāliem un izmantojamiem ieskatiem.

### 4. Palaidiet paneli

Skatiet jūsu app vitālos rādītājus:

```bash
php vendor/bin/runway apm:dashboard
```

**Kas tas ir?**
- Palaid PHP serveri uz `http://localhost:8001/apm/dashboard`.
- Rāda pieprasījumu žurnālus, lēnos maršrutus, kļūdu līmeni un vairāk.

**Pielāgojiet to**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Pieejams no jebkura IP (noderīgs attālinātai skatīšanai).
- `--port 8080`: Izmanto citu portu, ja 8001 ir aizņemts.
- `--php-path`: Norāda uz PHP, ja tas nav jūsu PATH.

Atveriet URL pārlūkā un izpētiet!

#### Producēšanas režīms

Producēšanā jums varētu būt jāizmēģina daži paņēmieni, lai paneli palaistu, jo, iespējams, ir ugunsmūri un citas drošības pasākumi. Šeit ir dažas iespējas:

- **Izmantojiet reverso proxy**: Iestatiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz paneli.
- **SSH tunelis**: Ja jūs varat SSH uz serveri, izmantojiet `ssh -L 8080:localhost:8001 youruser@yourserver`, lai tunelētu paneli uz savu lokālo mašīnu.
- **VPN**: Ja jūsu serveris ir aiz VPN, pievienojieties tam un piekļūstiet panelim tieši.
- **Konfigurējiet ugunsmūri**: Atveriet portu 8001 jūsu IP vai servera tīklam. (vai kādu citu portu, ko esat iestatījis).
- **Konfigurējiet Apache/Nginx**: Ja jums ir tīmekļa serveris priekš jūsu app, jūs varat konfigurēt to uz domēna vai apakšdomēna. Ja jūs to darāt, iestatiet dokumenta sakni uz `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Vai vēlaties citu paneli?

Jūs varat izveidot savu paneli, ja vēlaties! Skatiet vendor/flightphp/apm/src/apm/presenter direktoriju idejām, kā prezentēt datus savam panelim!

## Paneļa iespējas

Panelis ir jūsu APM galvenā mītne — šeit ir, ko jūs redzēsiet:

- **Pieprasījumu žurnāls**: Katrs pieprasījums ar laika zīmi, URL, atbildes kodu un kopējo laiku. Noklikšķiniet uz “Detaļas”, lai redzētu vidutni, vaicājumus un kļūdas.
- **Lēnākie pieprasījumi**: Top 5 pieprasījumi, kas patērē laiku (piem., “/api/heavy” ar 2.5s).
- **Lēnākie maršruti**: Top 5 maršruti pēc vidējā laika — lieliski, lai pamanītu modeļus.
- **Kļūdu līmenis**: Procentuāli no neveiksmīgiem pieprasījumiem (piem., 2.3% 500s).
- **Latentuma procentili**: 95. (p95) un 99. (p99) atbildes laiki — ziniet savus sliktākos scenārijus.
- **Atbildes kodu diagramma**: Vizualizējiet 200s, 404s, 500s laika gaitā.
- **Ilgi vaicājumi/Vidutne**: Top 5 lēni datu bāzes zvani un vidutnes slāņi.
- **Kešu trāpījumi/Nepieciešams**: Cik bieži jūsu kešs glābj dienu.

**Papildu**:
- Filtrējiet pēc “Pēdējās stundas”, “Pēdējās dienas” vai “Pēdējās nedēļas”.
- Pārslēdziet tumšo režīmu nakts sesijām.

**Piemērs**:
Pieprasījums uz `/users` varētu rādīt:
- Kopējais laiks: 150ms
- Vidutne: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešs: Trāpījums uz `user_list` (5ms)

## Pievienošana pielāgotu notikumu

Izsekot jebko — piemēram, API zvanu vai maksājuma procesu:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Kur tas parādās?**
Paneļa pieprasījuma detaļās zem “Custom Events” — izplejams ar glītu JSON formatējumu.

**Lietošanas gadījums**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Tagad jūs redzēsiet, ja šis API velk jūsu app uz leju!

## Datu bāzes monitorings

Izsekot PDO vaicājumus šādi:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Ko jūs saņemat**:
- Vaicājuma tekstu (piem., `SELECT * FROM users WHERE id = ?`)
- Izpildes laiku (piem., 0.015s)
- Rindu skaitu (piem., 42)

**Brīdinājums**:
- **Neobligāti**: Izlaidiet to, ja jums nav nepieciešams datu bāzes izsekošana.
- **Tikai PdoWrapper**: Kodola PDO vēl nav pievienots — sekojiet līdzi!
- **Veiktspējas brīdinājums**: Reģistrēšana katram vaicājumam datu bāzes smagā vietnē var palēnināt lietas. Izmantojiet paraugu ņemšanu (`$Apm = new Apm($ApmLogger, 0.1)`), lai samazinātu slodzi.

**Piemēra izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindas: 15

## Darbinieka iespējas

Pielāgojiet darbinieku pēc savas gaumes:

- `--timeout 300`: Pārtrauc pēc 5 minūtēm — labs testēšanai.
- `--max_messages 500`: Ierobežo līdz 500 metrikiem — uztur to galīgu.
- `--batch_size 200`: Apstrādā 200 vienlaikus — līdzsvaro ātrumu un atmiņu.
- `--daemon`: Darbojas nepārtraukti — ideāli piemērots tiešai monitorēšanai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikus vienlaikus.

## Pieprasījuma ID app

Katram pieprasījumam ir unikāls pieprasījuma ID izsekošanai. Jūs varat izmantot šo ID app, lai saistītu žurnālus un metrikus. Piemēram, jūs varat pievienot pieprasījuma ID kļūdas lapai:

```php
Flight::map('error', function($message) {
	// Iegūstiet pieprasījuma ID no atbildes galvenes X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Turklāt jūs varētu to iegūt no Flight mainīgā
	// Šī metode nedarbosies labi swoole vai citās asinhronās platformās.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Atjaunināšana

Ja jūs atjauninat uz jaunāku APM versiju, iespējams, ka ir nepieciešamas datu bāzes migrācijas. Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:migrate
```
Šī izpildīs visas nepieciešamās migrācijas, lai atjauninātu datu bāzes shēmu uz jaunāko versiju.

**Piezīme:** Ja jūsu APM datu bāze ir liela, šīs migrācijas var prasīt laiku. Jūs varētu vēlēties izpildīt šo komandu ārpus maksimālā slodzes laika.

## Tīrīšana veco datu

Lai uzturētu datu bāzi kārtīgu, jūs varat tīrīt vecos datus. Tas ir īpaši noderīgi, ja jūs vadāt aizņemtu app un vēlaties uzturēt datu bāzes izmēru pārvaldāmu.
Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:purge
```
Šī noņems visus datus, kas vecāki par 30 dienām no datu bāzes. Jūs varat pielāgot dienu skaitu, nododot citu vērtību uz `--days` opciju:

```bash
php vendor/bin/runway apm:purge --days 7
```
Šī noņems visus datus, kas vecāki par 7 dienām no datu bāzes.

## Traucējummeklēšana

Iestrēdzis? Mēģiniet šos:

- **Nav paneļa datu?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Konfigurācijas ceļi atbilst? Pārbaudiet `.runway-config.json` DSN, vai tie norāda uz īstiem failiem.
  - Izpildiet `php vendor/bin/runway apm:worker` manuāli, lai apstrādātu gaidāmos metrikus.

- **Darbinieka kļūdas?**
  - Apskatiet jūsu SQLite failus (piem., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus uz steka pēdzēm.

- **Panelis nekustas?**
  - Ports 8001 ir aizņemts? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk lēns?**
  - Samaziniet paraugu ņemšanas ātrumu: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet partijas lielumu: `--batch_size 20`.