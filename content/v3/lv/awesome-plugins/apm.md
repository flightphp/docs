# FlightPHP APM Dokumentācija

Laipni lūdzam FlightPHP APM — jūsu lietojumprogrammas personīgais veiktspējas treneris! Šis ceļvedis ir jūsu ceļakarte uz APM (Application Performance Monitoring) uzstādīšanu, izmantošanu un apgūšanu ar FlightPHP. Neatkarīgi no tā, vai jūs medījāt lēnus pieprasījumus vai vienkārši vēlaties iedziļināties latentuma diagrammās, mēs jums palīdzēsim. Padarīsim jūsu lietojumprogrammu ātrāku, jūsu lietotājus laimīgākus un jūsu atkļūdošanas sesijas vieglas!

Apskatiet [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) Flight Docs vietnes panelim.

![FlightPHP APM](/images/apm.png)

## Kāpēc APM ir svarīgi

Iedomājieties: jūsu lietojumprogramma ir aizņemts restorāns. Bez veida, kā izsekot, cik ilgi tiek apstrādāti pasūtījumi vai kur virtuvē rodas aizkavēšanās, jūs minaties, kāpēc klienti aiziet neapmierināti. APM ir jūsu pavāra palīgs — tas novēro katru soli, no ienākošajiem pieprasījumiem līdz datubāzes vaicājumiem, un atzīmē visu, kas jūs palēnina. Lēnas lapas zaudē lietotājus (pētījumi saka, ka 53% aiziet, ja vietne ielādējas ilgāk par 3 sekundēm!), un APM palīdz jums uztvert šīs problēmas *pirms* tās kaitē. Tas ir proaktīvs mierinājums — mazāk “kāpēc tas ir salūzis?” brīžu, vairāk “paskatieties, cik gludi tas darbojas!” uzvaru.

## Instalācija

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Nodrošina saderību ar LTS Linux izplatījumiem, vienlaikus atbalstot modernu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglā ietvara versija, ko mēs uzlabojam.

## Atbalstītās datubāzes

FlightPHP APM pašlaik atbalsta šādas datubāzes metrikas uzglabāšanai:

- **SQLite3**: Vienkārša, failu balstīta, lieliska lokālai izstrādei vai mazām lietojumprogrammām. Noklusējuma opcija lielākajā daļā uzstādījumu.
- **MySQL/MariaDB**: Ideāla lielākiem projektiem vai ražošanas vidēm, kur nepieciešama robusta, mērogojama uzglabāšana.

Jūs varat izvēlēties datubāzes tipu konfigurācijas solī (skat. zemāk). Pārliecinieties, ka jūsu PHP videi ir instalētas nepieciešamās paplašinājumi (piem., `pdo_sqlite` vai `pdo_mysql`).

## Sākšana

Šeit ir jūsu soli pa solim ceļš uz APM lieliskumu:

### 1. Reģistrējiet APM

Ievietojiet to savā `index.php` vai `services.php` failā, lai sāktu izsekošanu:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Ja pievienojat datubāzes savienojumu
// Jābūt PdoWrapper vai PdoQueryCapture no Tracy Extensions
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True nepieciešams, lai iespējotu izsekošanu APM.
$Apm->addPdoConnection($pdo);
```

**Kas šeit notiek?**
- `LoggerFactory::create()` paņem jūsu konfigurāciju (vairāk par to drīz) un uzstāda žurnālu — pēc noklusējuma SQLite.
- `Apm` ir zvaigzne — tas klausās Flight notikumus (pieprasījumus, maršrutus, kļūdas utt.) un savāc metrikas.
- `bindEventsToFlightInstance($app)` saista visu ar jūsu Flight lietojumprogrammu.

**Pro Padoms: Paraugšana**
Ja jūsu lietojumprogramma ir aizņemta, žurnālošana *katram* pieprasījumam var pārslodzes. Izmantojiet paraugu (0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Žurnālo 10% pieprasījumu
```

Tas saglabā veiktspēju ātru, vienlaikus dodot jums stingrus datus.

### 2. Konfigurējiet to

Izpildiet to, lai izveidotu jūsu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Ko tas dara?**
- Palaista vedni, kas jautā, no kurienes nāk izejmateriālu metrikas (avots) un kur iet apstrādātie dati (mērķis).
- Noklusējums ir SQLite — piem., `sqlite:/tmp/apm_metrics.sqlite` avotam, cits mērķim.
- Jūs iegūsiet konfigurāciju, piemēram:
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

> Šis process arī jautās, vai vēlaties palaist migrācijas šim uzstādījumam. Ja to uzstādāt pirmo reizi, atbilde ir jā.

**Kāpēc divas vietas?**
Izejmateriālu metrikas uzkrājas ātri (domājiet nefiltrētus žurnālus). Darbinieks tos apstrādā strukturētā mērķī paneļa vajadzībām. Tas uztur kārtību!

### 3. Apstrādājiet metrikas ar darbinieku

Darbinieks pārvērš izejmateriālu metrikas par paneļa gataviem datiem. Palaidiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Ko tas dara?**
- Lasīt no jūsu avota (piem., `apm_metrics.sqlite`).
- Apstrādā līdz 100 metrikiem (noklusējuma partijas lielums) jūsu mērķī.
- Apstājas, kad pabeigts vai ja nav atlikušo metriku.

**Uzturiet to darbojošos**
Dzīvām lietojumprogrammām jūs vēlēsities nepārtrauktu apstrādi. Šeit ir jūsu opcijas:

- **Dēmona režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas mūžīgi, apstrādājot metrikas, kad tās nāk. Lieliski izstrādei vai maziem uzstādījumiem.

- **Crontab**:
  Pievienojiet to jūsu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Palaid katru minūti — ideāli ražošanai.

- **Tmux/Screen**:
  Sāciet atvienojamu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, tad D, lai atvienotos; `tmux attach -t apm-worker` lai savienotos atkārtoti
  ```
  Uztur to darbojošos pat ja izvadaties.

- **Pielāgoti uzstādījumi**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādā 50 metrikas uzreiz.
  - `--max_messages 1000`: Apstājas pēc 1000 metrikiem.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc censties?**
Bez darbinieka jūsu panelis ir tukšs. Tas ir tilts starp izejmateriālu žurnāliem un izmantojamiem ieskatiem.

### 4. Palaidiet paneli

Redziet jūsu lietojumprogrammas vitālos rādītājus:

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
- `--host 0.0.0.0`: Piekļaujams no jebkura IP (noderīgs attālam skatīšanai).
- `--port 8080`: Izmanto citu portu, ja 8001 ir aizņemts.
- `--php-path`: Norāda uz PHP, ja tas nav jūsu PATH.

Atveriet URL savā pārlūkprogrammā un izpētiet!

#### Ražošanas režīms

Ražošanai jums var nākties izmēģināt vairākas tehnikas, lai palaistu paneli, jo, iespējams, ir ugunsmūri un citas drošības pasākumi. Šeit ir dažas opcijas:

- **Izmantojiet reverso proxy**: Uzstādiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz paneli.
- **SSH tunelis**: Ja varat SSH uz serveri, izmantojiet `ssh -L 8080:localhost:8001 youruser@yourserver`, lai tunelētu paneli uz jūsu lokālo mašīnu.
- **VPN**: Ja jūsu serveris ir aiz VPN, savienojieties ar to un piekļūstiet panelim tieši.
- **Konfigurējiet ugunsmūri**: Atveriet portu 8001 jūsu IP vai servera tīklam. (vai kādu portu jūs uzstādījāt).
- **Konfigurējiet Apache/Nginx**: Ja jums ir tīmekļa serveris jūsu lietojumprogrammas priekšā, jūs varat to konfigurēt uz domēna vai apakšdomēna. Ja to darāt, iestatiet dokumenta sakni uz `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Vai vēlaties citu paneli?

Jūs varat izveidot savu paneli, ja vēlaties! Apskatiet vendor/flightphp/apm/src/apm/presenter direktoriju idejām, kā prezentēt datus jūsu paša panelim!

## Paneļa funkcijas

Panels ir jūsu APM galvenā mītne — šeit ir tas, ko redzēsiet:

- **Pieprasījumu žurnāls**: Katrs pieprasījums ar laika zīmi, URL, atbildes kodu un kopējo laiku. Noklikšķiniet “Detalizēti”, lai redzētu starprogrammatūru, vaicājumus un kļūdas.
- **Lēnākie pieprasījumi**: Top 5 pieprasījumi, kas aizņem laiku (piem., “/api/heavy” 2.5s).
- **Lēnākie maršruti**: Top 5 maršruti pēc vidējā laika — lieliski, lai pamanītu modeļus.
- **Kļūdu līmenis**: Procentuālā daļa no neveiksmīgiem pieprasījumiem (piem., 2.3% 500).
- **Latentuma percentīļi**: 95. (p95) un 99. (p99) atbilžu laiki — ziniet savus sliktākos scenārijus.
- **Atbildes koda diagramma**: Vizualizējiet 200, 404, 500 laika gaitā.
- **Gari vaicājumi/Starpprogrammatūra**: Top 5 lēni datubāzes zvani un starprogrammatūras slāņi.
- **Kešs trāpījums/Prašu**: Cik bieži jūsu kešs glābj dienu.

**Ekstras**:
- Filtrējiet pēc “Pēdējā stunda”, “Pēdējā diena” vai “Pēdējā nedēļa”.
- Pārslēdziet tumšo režīmu tiem vēlošiem nakts seansiem.

**Piemērs**:
Pieprasījums uz `/users` var parādīt:
- Kopējais laiks: 150ms
- Starpprogrammatūra: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešs: Trāpījums uz `user_list` (5ms)

## Pievienošana pielāgotu notikumu

Izsekojiet jebko — piemēram, API zvanu vai maksājuma procesu:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Kur tas parādās?**
Paneļa pieprasījuma detaļās zem “Pielāgoti notikumi” — paplašināms ar skaistu JSON formatējumu.

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
Tagad jūs redzēsiet, vai tas API velk jūsu lietojumprogrammu uz leju!

## Datubāzes uzraudzība

Izsekojiet PDO vaicājumus šādi:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True nepieciešams, lai iespējotu izsekošanu APM.
$Apm->addPdoConnection($pdo);
```

**Ko jūs iegūsiet**:
- Vaicājuma teksts (piem., `SELECT * FROM users WHERE id = ?`)
- Izpildes laiks (piem., 0.015s)
- Rindu skaits (piem., 42)

**Brīdinājums**:
- **Neobligāti**: Izlaidiet to, ja nevajag DB izsekošanu.
- **Tikai PdoWrapper**: Kodols PDO vēl nav savienots — sekojiet līdzi!
- **Veiktspējas brīdinājums**: Žurnālošana katram vaicājumam uz DB smagas vietnes var palēnināt. Izmantojiet paraugu (`$Apm = new Apm($ApmLogger, 0.1)`), lai samazinātu slodzi.

**Piemēra izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindu: 15

## Darbinieka opcijas

Pielāgojiet darbinieku savai gaumei:

- `--timeout 300`: Apstājas pēc 5 minūtēm — labs testēšanai.
- `--max_messages 500`: Ierobežo līdz 500 metrikiem — uztur to ierobežotu.
- `--batch_size 200`: Apstrādā 200 uzreiz — līdzsvaro ātrumu un atmiņu.
- `--daemon`: Darbojas nepārtraukti — ideāli tiešai uzraudzībai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikas uzreiz.

## Pieprasījuma ID lietojumprogrammā

Katram pieprasījumam ir unikāls pieprasījuma ID izsekošanai. Jūs varat izmantot šo ID savā lietojumprogrammā, lai korelētu žurnālus un metrikas. Piemēram, jūs varat pievienot pieprasījuma ID kļūdas lapai:

```php
Flight::map('error', function($message) {
	// Iegūstiet pieprasījuma ID no atbildes galvenes X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Papildus jūs varētu to iegūt no Flight mainīgā
	// Šī metode labi nedarbosies swoole vai citās async platformās.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Kļūda: $message (Pieprasījuma ID: $requestId)";
});
```

## Atjaunināšana

Ja jūs atjaunināt uz jaunāku APM versiju, ir iespējams, ka ir datubāzes migrācijas, kas jāpalaid. Jūs varat to izdarīt, izpildot šādu komandu:

```bash
php vendor/bin/runway apm:migrate
```
Tas palaids visas nepieciešamās migrācijas, lai atjauninātu datubāzes shēmu uz jaunāko versiju.

**Piezīme:** Ja jūsu APM datubāze ir liela izmērā, šīs migrācijas var prasīt laiku. Jūs varat vēlēties palaist šo komandu ārpus maksimālās slodzes stundām.

### Atjaunināšana no 0.4.3 -> 0.5.0

Ja jūs atjaunināt no 0.4.3 uz 0.5.0, jums jāpalaid šāda komanda:

```bash
php vendor/bin/runway apm:config-migrate
```

Tas migrēs jūsu konfigurāciju no vecā formāta, izmantojot `.runway-config.json` failu, uz jauno formātu, kas glabā atslēgas/vērtības `config.php` failā.

## Veco datu dzēšana

Lai uzturētu jūsu datubāzi kārtīgu, jūs varat dzēst vecus datus. Tas ir īpaši noderīgi, ja darbojaties aizņemtu lietojumprogrammu un vēlaties uzturēt datubāzes izmēru pārvaldāmu.
Jūs varat to izdarīt, izpildot šādu komandu:

```bash
php vendor/bin/runway apm:purge
```
Tas noņem visus datus, kas vecāki par 30 dienām no datubāzes. Jūs varat pielāgot dienu skaitu, nododot citu vērtību `--days` opcijai:

```bash
php vendor/bin/runway apm:purge --days 7
```
Tas noņem visus datus, kas vecāki par 7 dienām no datubāzes.

## Traucējummeklēšana

Iestrēdzis? Izmēģiniet šos:

- **Nav paneļa datu?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Konfigurācijas ceļi sakrīt? Pārbaudiet `.runway-config.json` DSN norāda uz reāliem failiem.
  - Palaidiet `php vendor/bin/runway apm:worker` manuāli, lai apstrādātu gaidošās metrikas.

- **Darbinieka kļūdas?**
  - Apskatiet jūsu SQLite failus (piem., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus uz steka pēdnēm.

- **Panels nesākas?**
  - Ports 8001 aizņemts? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk lēns?**
  - Samaziniet parauga līmeni: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet partijas lielumu: `--batch_size 20`.

- **Netiek izsekotas izņēmumi/kļūdas?**
  - Ja jums ir [Tracy](https://tracy.nette.org/) iespējots projektam, tas pārņems Flight kļūdu apstrādi. Jums jāatspējo Tracy un pēc tam jāpārliecinās, ka `Flight::set('flight.handle_errors', true);` ir iestatīts.

- **Netiek izsekoti datubāzes vaicājumi?**
  - Pārliecinieties, ka izmantojat `PdoWrapper` saviem datubāzes savienojumiem.
  - Pārliecinieties, ka konstruktorā pēdējais arguments ir `true`.