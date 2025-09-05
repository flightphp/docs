# FlightPHP APM Dokumentācija

Laipni lūgti FlightPHP APM—jūsu aplikācijas personīgais veiktspējas treneris! Šis ceļvedis ir jūsu rokasgrāmata, lai iestatītu, izmantotu un apgūtu Application Performance Monitoring (APM) ar FlightPHP. Vai jūs meklējat lēnus pieprasumus vai vienkārši vēlaties izklaidēties ar latentuma diagrammām, mēs esam to pārklājuši. Padarīsim jūsu aplikāciju ātrāku, lietotājus laimīgākus un atkļūdošanas sesijas vieglākas!

![FlightPHP APM](/images/apm.png)

## Kāpēc APM ir svarīgs

Iedomājieties: jūsu aplikācija ir aizņemts restorāns. Bez veida izsekot, cik ilgi ņem pasūtījumi vai kur virtuvē ir aizkavēšanās, jūs minat, kāpēc klienti aiziet neapmierināti. APM ir jūsu pavārs palīgs—tas vēro katru soli, no ienākošiem pieprasījumiem līdz datubāzes vaicājumiem, un atzīmē visu, kas palēnina jūs. Lēnas lapas zaudē lietotājus (pētījumi saka, ka 53% izlec, ja vietne ielādējas vairāk par 3 sekundēm!), un APM palīdz noķert šīs problēmas *pirms* tās sāp. Tas ir proaktīvs miers—mazāk “kāpēc tas ir salauzts?” momentu, vairāk “skatieties, cik gludi tas darbojas!” uzvaru.

## Instalācija

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Saglabā saderību ar LTS Linux izdali, vienlaikus atbalstot mūsdienīgu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglais ietvars, ko mēs uzlabojam.

## Atbalstītās datubāzes

FlightPHP APM pašlaik atbalsta šādas datubāzes metrikas glabāšanai:

- **SQLite3**: Vienkārša, failā balstīta, un lieliska lokālai izstrādei vai mazām aplikācijām. Noklusētā opcija lielākajā daļā iestatījumu.
- **MySQL/MariaDB**: Ideāla lielākiem projektiem vai produkcijas vidēm, kur nepieciešama izturīga, mērogojama glabāšana.

Jūs varat izvēlēties savu datubāzes tipu konfigurācijas solī (skatiet zemāk). Pārliecinieties, ka jūsu PHP vide ir instalējusi nepieciešamās paplašinājumus (piemēram, `pdo_sqlite` vai `pdo_mysql`).

## Sākšana

Šeit ir jūsu solis-pa-solim ceļš uz APM lieliskumu:

### 1. Reģistrējiet APM

Ievietojiet to savā `index.php` vai `services.php` failā, lai sāktu izsekošanu:

```php
use flight\apm\logger\LoggerFactory; // Importē konfigurācijas izveides rīku
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json'); // Izveido loggeri no konfigurācijas
$Apm = new Apm($ApmLogger); // Izveido APM objektu
$Apm->bindEventsToFlightInstance($app); // Saistiet ar Flight instanci

// Ja pievienojat datubāzes savienojumu
// Tas jābūt PdoWrapper vai PdoQueryCapture no Tracy Extensions
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True nepieciešams, lai iespējotu izsekošanu APM.
$Apm->addPdoConnection($pdo);
```

**Kas notiek šeit?**
- `LoggerFactory::create()` paņem jūsu konfigurāciju (vairāk par to drīz) un iestata loggeri—pēc noklusējuma SQLite.
- `Apm` ir zvaigzne—tas klausās Flight notikumus (pieprasījumi, maršruti, kļūdas utt.) un savāc metrikas.
- `bindEventsToFlightInstance($app)` saista to visu ar jūsu Flight aplikāciju.

**Profesionāls padoms: Paraugu ņemšana**
Ja jūsu aplikācija ir aizņemta, žurnālošana *katram* pieprasījumam var pārslogot lietas. Izmantojiet paraugu ņemšanas līmeni (0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Žurnālo 10% pieprasījumu
```

Tas uztur veiktspēju asu, vienlaikus sniedzot stabilus datus.

### 2. Konfigurējiet to

Izpildiet to, lai izveidotu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Kas tas dara?**
- Palaid maģistru, kas jautā, no kurienes nāk izejmateriāla metrikas (avots) un kur iet apstrādātie dati (mērķis).
- Noklusētā ir SQLite—piemēram, `sqlite:/tmp/apm_metrics.sqlite` avotam, cits mērķim.
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
Neapstrādātās metrikas sakrājas ātri (domājiet nefiltrētus žurnālus). Darbinieks tās apstrādā strukturētā mērķī priekš paneļa. Tas uztur lietas kārtīgas!

### 3. Apstrādājiet metrikas ar darbinieku

Darbinieks pārvērš neapstrādātās metrikas par paneļa gataviem datiem. Izpildiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Kas tas dara?**
- Lasīt no jūsu avota (piemēram, `apm_metrics.sqlite`).
- Apstrādā līdz 100 metriku (noklusētā partijas lielums) jūsu mērķī.
- Pārtrauc, kad pabeigts vai ja nav metrikas.

**Uzturiet to darbināšanā**
Produkcijas aplikācijām jūs gribēsiet nepārtrauktu apstrādi. Šeit ir jūsu opcijas:

- **Dēmona režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas mūžīgi, apstrādājot metrikas, kad tās nāk. Lieliski attīstībai vai maziem iestatījumiem.

- **Crontab**:
  Pievienojiet to savam crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Palaid katru minūti—ideāli produkcijai.

- **Tmux/Screen**:
  Sāciet atdalāmu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, tad D, lai atdalītos; `tmux attach -t apm-worker`, lai pievienotos atpakaļ
  ```
  Uztur to darbināšanā pat, ja jūs izlogojaties.

- **Pielāgotas izmaiņas**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādāj 50 metrikas vienlaikus.
  - `--max_messages 1000`: Pārtrauc pēc 1000 metriku.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc to darīt?**
Bez darbinieka jūsu panelis ir tukšs. Tas ir tilts starp neapstrādātiem žurnāliem un izmantojamiem ieskatiem.

### 4. Palaidiet paneli

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

Atveriet URL pārlūkā un izpētiet!

#### Produkcijas režīms

Produkcijā jums var nākties izmēģināt dažas tehnikas, lai paneli palaistu, jo, iespējams, ir ugunsmūri un citas drošības mērvienības. Šeit ir dažas opcijas:

- **Izmantojiet apgriezto starpniekserveri**: Iestatiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz paneli.
- **SSH Tunelis**: Ja jūs varat SSH uz serveri, izmantojiet `ssh -L 8080:localhost:8001 youruser@yourserver`, lai tunelētu paneli uz jūsu lokālo mašīnu.
- **VPN**: Ja jūsu serveris ir aiz VPN, pievienojieties tam un piekļūstiet panelim tieši.
- **Konfigurējiet Ugunsmūri**: Atveriet portu 8001 jūsu IP vai servera tīklam. (vai kādu portu, ko esat iestatījis).
- **Konfigurējiet Apache/Nginx**: Ja jums ir tīmekļa serveris priekš aplikācijas, jūs varat konfigurēt to uz domēna vai apakšdomēna. Ja jūs to darāt, jūs iestatīsiet dokumenta sakni uz `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Vēlaties citu paneli?

Jūs varat izveidot savu paneli, ja vēlaties! Skatiet `vendor/flightphp/apm/src/apm/presenter` direktoriju idejām, kā prezentēt datus jūsu panelim!

## Paneļa funkcijas

Panelis ir jūsu APM štābs—šeit ir, ko jūs redzēsiet:

- **Pieprasījumu žurnāls**: Katrs pieprasījums ar laika zīmogu, URL, atbildes kodu un kopējo laiku. Nospiediet “Detaļas”, lai redzētu vidutēji, vaicājumus un kļūdas.
- **Lēnākie pieprasījumi**: Top 5 pieprasījumi, kas tērē laiku (piemēram, “/api/heavy” ar 2.5s).
- **Lēnākie maršruti**: Top 5 maršruti pēc vidējā laika—lieliski, lai pamanītu modeļus.
- **Kļūdu līmenis**: Procentuāli no neveiksmīgiem pieprasījumiem (piemēram, 2.3% 500s).
- **Latentuma procentiļi**: 95. (p95) un 99. (p99) atbildes laiki—ziniet jūsu sliktākos scenārijus.
- **Atbildes kodu diagramma**: Visualizēj 200s, 404s, 500s laika gaitā.
- **Ilgi vaicājumi/Vidutēji**: Top 5 lēni datubāzes zvani un vidutēji slāņi.
- **Kešs Hit/Miss**: Cik bieži jūsu kešs glābj dienu.

**Papildu**:
- Filtrēj pēc “Pēdējā stunda,” “Pēdējā diena,” vai “Pēdējā nedēļa.”
- Pārslēdz tumšo režīmu priekš vēlu nakts sesijām.

**Piemērs**:
Pieprasījums uz `/users` var parādīt:
- Kopējais laiks: 150ms
- Vidutēji: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešs: Hit uz `user_list` (5ms)

## Pievienošana pielāgotu notikumu

Izsekot jebko—piemēram, API zvanu vai maksājuma procesu:

```php
use flight\apm\CustomEvent; // Importē pielāgotu notikumu klasi

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Kur tas parādās?**
Paneļa pieprasījumu detaļās zem “Pielāgotu notikumu”—paplašināms ar skaistu JSON formatēšanu.

**Lietošanas gadījums**:
```php
$start = microtime(true); // Sāk laika mērīšanu
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start, // Izrēķina laiku
    'success' => $apiResponse !== false // Pārbauda, vai veiksmīgs
]));
```
Tagad jūs redzēsiet, ja šis API velk jūsu aplikāciju lejā!

## Datubāzes uzraudzība

Izsekot PDO vaicājumus šādi:

```php
use flight\database\PdoWrapper; // Importē PDO iesvētītāju

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True nepieciešams, lai iespējotu izsekošanu APM.
$Apm->addPdoConnection($pdo);
```

**Ko jūs saņemat**:
- Vaicājuma tekstu (piemēram, `SELECT * FROM users WHERE id = ?`)
- Izpildes laiku (piemēram, 0.015s)
- Rindu skaitu (piemēram, 42)

**Brīdinājums**:
- **Neobligāti**: Izlaidiet to, ja jums nav nepieciešama DB izsekošana.
- **Tikai PdoWrapper**: Kodola PDO vēl nav pieslēgts—gaidiet!
- **Veiktspējas brīdinājums**: Žurnālošana katra vaicājuma DB-smagā vietnē var palēnināt lietas. Izmantojiet paraugu ņemšanu (`$Apm = new Apm($ApmLogger, 0.1)`), lai samazinātu slodzi.

**Piemērs izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindas: 15

## Darbinieka opcijas

Pielāgojiet darbinieku pēc savas gaumes:

- `--timeout 300`: Pārtrauc pēc 5 minūtēm—labi testēšanai.
- `--max_messages 500`: Ierobežo pie 500 metriku—padara to galīgu.
- `--batch_size 200`: Apstrādā 200 vienlaikus—balansē ātrumu un atmiņu.
- `--daemon`: Darbojas nepārtraukti—ideāli dzīvei uzraudzībai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikas vienlaikus.

## Pieprasījuma ID aplikācijā

Katram pieprasījumam ir unikāls pieprasījuma ID izsekošanai. Jūs varat izmantot šo ID savā aplikācijā, lai saistītu žurnālus un metrikas. Piemēram, jūs varat pievienot pieprasījuma ID kļūdas lapai:

```php
Flight::map('error', function($message) {
	// Iegūstiet pieprasījuma ID no atbildes galvenes X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Turklāt jūs varētu to iegūt no Flight mainīgā
	// Šī metode nedarbosies labi swoole vai citās async platformās.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)"; // Izdrukā kļūdu ar ID
});
```

## Atjaunināšana

Ja jūs atjauninat uz jaunāku APM versiju, var būt, ka ir datubāzes migrācijas, kas jāpalaiž. Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:migrate
```
Šī palaist visas nepieciešamās migrācijas, lai atjauninātu datubāzes shēmu uz jaunāko versiju.

**Piezīme:** Ja jūsu APM datubāze ir liela izmērā, šīs migrācijas var prasīt laiku. Jūs varētu vēlēties palaist šo komandu ārpus maksimālās slodzes laikiem.

## Veco datu iztīrīšana

Lai uzturētu datubāzi kārtīgu, jūs varat iztīrīt vecos datus. Tas ir īpaši noderīgi, ja jūs darbojat aizņemtu aplikāciju un vēlaties uzturēt datubāzes izmēru pārvaldāmu.
Jūs varat to izdarīt, izpildot šo komandu:

```bash
php vendor/bin/runway apm:purge
```
Šī noņems visus datus vecākus par 30 dienām no datubāzes. Jūs varat pielāgot dienu skaitu, nododot citu vērtību `--days` opcijai:

```bash
php vendor/bin/runway apm:purge --days 7
```
Šī noņems visus datus vecākus par 7 dienām no datubāzes.

## Problēmu novēršana

Iestrēdzis? Mēģiniet šos:

- **Nav datu panelī?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Konfigurācijas ceļi atbilst? Pārbaudiet `.runway-config.json` DSN, kas norāda uz reāliem failiem.
  - Izpildiet `php vendor/bin/runway apm:worker` manuāli, lai apstrādātu gaidāmās metrikas.

- **Darbinieka kļūdas?**
  - Apskatiet jūsu SQLite failus (piemēram, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus uz steka izsekojumiem.

- **Panelis nekurasies?**
  - Ports 8001 ir aizņemts? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk lēns?**
  - Samaziniet paraugu līmeni: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet partijas lielumu: `--batch_size 20`.

- **Netiek izsekotas izņēmumi/kļūdas?**
  - Ja jums ir [Tracy](https://tracy.nette.org/) iespējots jūsu projektā, tas pārrakstīs Flight kļūdu apstrādi. Jums būs jādeaktivizē Tracy un jāpārliecinās, ka `Flight::set('flight.handle_errors', true);` ir iestatīts.

- **Netiek izsekoti datubāzes vaicājumi?**
  - Pārliecinieties, ka jūs izmantojat `PdoWrapper` priekš datubāzes savienojumiem.
  - Pārliecinieties, ka pēdējais arguments konstruktorā ir `true`.