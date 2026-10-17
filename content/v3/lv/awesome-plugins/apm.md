# FlightPHP APM dokumentācija

Laipni lūdzam FlightPHP APM — jūsu lietotnes personīgais veiktspējas treneris! Šī rokasgrāmata ir jūsu ceļvedis, lai iestatītu, izmantotu un apgūtu lietojumprogrammu veiktspējas monitoringu (APM) ar FlightPHP. Neatkarīgi no tā, vai meklējat lēnos pieprasījumus vai vienkārši vēlaties aizrautīgi pētīt latentuma diagrammas, mēs jums palīdzēsim. Padarīsim jūsu lietotni ātrāku, lietotājus laimīgākus un atkļūdošanas sesijas vieglākas!

Skatiet Flight Docs vietnes informācijas paneļa [demonstrāciju](https://flightphp-docs-apm.sky-9.com/apm/dashboard).

![FlightPHP APM](/images/apm.png)

## Kāpēc APM ir svarīgs

Iedomājieties: jūsu lietotne ir noslogots restorāns. Bez iespējas izsekot, cik ilgi aizņem pasūtījumi vai kur virtuve iesprūst, jūs minat, kāpēc klienti aiziet neapmierināti. APM ir jūsu palīgšefs — tas vēro katru soli, sākot no ienākošiem pieprasījumiem līdz datubāzes vaicājumiem, un atzīmē visu, kas jūs bremzē. Lēnas lapas zaudē lietotājus (pētījumi rāda, ka 53% aiziet, ja vietnes ielāde pārsniedz 3 sekundes!), un APM palīdz jums pamanīt šīs problēmas *pirms* tās nodara ļaunumu. Tā ir proaktīva sirdsmiera sajūta — mazāk “kāpēc tas ir salūzis?” mirkļu, vairāk “paskat, cik gludi tas darbojas!” uzvaru.

## Instalēšana

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Saglabā mūs saderīgus ar LTS Linux distribūcijām, vienlaikus atbalstot mūsdienīgu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglaist ietvars, kuru uzlabojam.

## Atbalstītās datubāzes

FlightPHP APM pašlaik atbalsta šādas datubāzes metrikas glabāšanai:

- **SQLite3**: Vienkārša, uz failiem balstīta, lieliski piemērota vietējai izstrādei vai mazām lietotnēm. Noklusējuma opcija lielākajā daļā iestatījumu.
- **MySQL/MariaDB**: Ideāla lielākiem projektiem vai ražošanas vidēm, kur nepieciešama robusta, mērogojama glabāšana.

Datubāzes veidu varat izvēlēties konfigurācijas solī (skatiet tālāk). Pārliecinieties, ka jūsu PHP vidē ir instalēti nepieciešamie paplašinājumi (piem., `pdo_sqlite` vai `pdo_mysql`).

## Darba sākšana

Šeit ir soli pa solim ceļš uz APM meistarību:

### 1. Reģistrējiet APM

Ievietojiet šo savā `index.php` vai `services.php` failā, lai sāktu izsekošanu:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Ja pievienojat datubāzes savienojumu
// Dodiet priekšroku SimplePdo (vai PdoQueryCapture no Tracy paplašinājumiem izstrādes režīmā).
// Iespējojiet APM vaicājumu izsekošanu, izmantojot opciju masīvu (5. arguments).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // nepieciešams, lai uzņemtu vaicājumus APM
]);
$Apm->addPdoConnection($pdo);
```

**Kas šeit notiek?**
- `LoggerFactory::create()` paņem jūsu konfigurāciju (vairāk par to drīzumā) un iestata žurnālētāju — pēc noklusējuma SQLite.
- `Apm` ir galvenais varonis — tas klausās Flight notikumos (pieprasījumi, maršruti, kļūdas utt.) un vāc metrikas.
- `bindEventsToFlightInstance($app)` to visu piesaista jūsu Flight lietotnei.

**Profesionāļa padoms: izlases ņemšana**
Ja jūsu lietotne ir noslogota, *katra* pieprasījuma reģistrēšana var pārslogot sistēmu. Izmantojiet izlases koeficientu (no 0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Reģistrē 10% pieprasījumu
```

Tas uztur veiktspēju ātru, vienlaikus sniedzot stabilus datus.

### 2. Konfigurējiet to

Palaidiet šo, lai izveidotu savu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Ko tas dara?**
- Palaiž vedni, kas jautā, no kurienes nāk neapstrādātās metrikas (avots) un kur nonāk apstrādātie dati (galamērķis).
- Noklusējums ir SQLite — piem., `sqlite:/tmp/apm_metrics.sqlite` avotam, cits galamērķim.
- Jūs iegūsiet konfigurāciju, kas līdzinās šai:
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

> Šis process arī jautās, vai vēlaties palaist migrācijas šim iestatījumam. Ja iestatāt to pirmo reizi, atbilde ir jā.

**Kāpēc divas atrašanās vietas?**
Neapstrādātās metrikas ātri uzkrājas (iedomājieties nefiltrētus žurnālus). Darbinieks tās apstrādā strukturētā galamērķī informācijas panelim. Tas uztur lietas kārtīgas!

### 3. Apstrādājiet metrikas ar darbinieku

Darbinieks pārvērš neapstrādātās metrikas informācijas panelim gatavos datos. Palaidiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Ko tas dara?**
- Nolasa no jūsu avota (piem., `apm_metrics.sqlite`).
- Apstrādā līdz 100 metrikām (noklusējuma paketes lielums) un ievieto galamērķī.
- Apstājas, kad pabeigts, vai kad vairs nav palicis metrikas.

**Turiet to darbojamies**
Produktīvām lietotnēm vēlēsities nepārtrauktu apstrādi. Šeit ir jūsu iespējas:

- **Dēmona režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas bezgalīgi, apstrādājot metrikas, tiklīdz tās parādās. Lieliski izstrādei vai maziem iestatījumiem.

- **Crontab**:
  Pievienojiet šo savam crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Iedarbojas katru minūti — ideāli ražošanai.

- **Tmux/Screen**:
  Sāciet atvienojamu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, pēc tam D, lai atvienotos; `tmux attach -t apm-worker`, lai atkal pievienotos
  ```
  Uztur to darbībā pat tad, ja jūs izrakstāties.

- **Pielāgotas korekcijas**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādā 50 metrikas vienlaikus.
  - `--max_messages 1000`: Apstājas pēc 1000 metrikām.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc tas jādara?**
Bez darbinieka jūsu informācijas panelis būs tukšs. Tas ir tilts starp neapstrādātiem žurnāliem un praktiskiem ieskatiem.

### 4. Palaidiet informācijas paneli

Skatiet savas lietotnes vitālos rādītājus:

```bash
php vendor/bin/runway apm:dashboard
```

**Kas tas ir?**
- Palaiž PHP serveri uz `http://localhost:8001/apm/dashboard`.
- Rāda pieprasījumu žurnālus, lēnos maršrutus, kļūdu līmeņus un vēl vairāk.

**Pielāgojiet to**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Pieejams no jebkuras IP (noder attālinātai apskatei).
- `--port 8080`: Izmantojiet citu portu, ja 8001 ir aizņemts.
- `--php-path`: Norāda uz PHP, ja tas nav jūsu PATH.

Atveriet URL savā pārlūkā un pētiet!

#### Ražošanas režīms

Ražošanas vidē, iespējams, būs jāizmēģina dažas metodes, lai informācijas panelis darbotos, jo, visticamāk, ir ugunsmūri un citi drošības pasākumi. Šeit ir dažas iespējas:

- **Izmantojiet reverso starpniekserveri**: Iestatiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz informācijas paneli.
- **SSH tunelis**: Ja varat SSH pievienoties serverim, izmantojiet `ssh -L 8080:localhost:8001
youruser@yourserver`, lai tunelētu informācijas paneli uz savu lokālo mašīnu.
- **VPN**: Ja jūsu serveris atrodas aiz VPN, pievienojieties tam un piekļūstiet informācijas panelim tieši.
- **Konfigurējiet ugunsmūri**: Atveriet portu 8001 savai IP vai servera tīklam (vai jebkuru citu portu, kuru esat iestatījis).
- **Konfigurējiet Apache/Nginx**: Ja jūsu lietotnes priekšā ir tīmekļa serveris, varat to konfigurēt domēnam vai apakšdomēnam. Norādiet šo resursdatoru uz `dashboard/index.php` (iebūvētais serveris to izmanto kā maršrutētāju). Statiskie faili atrodas blakus mapēs `dashboard/css` un `dashboard/js`.
- **Pamata autentifikācija**: Iestatiet `apm.dashboard_auth.user` un `apm.dashboard_auth.password` runway konfigurācijā. Ja kāda no vērtībām ir tukša, informācijas panelis paliek atvērts. Šī ir viena koplietota parole, nevis lietotnes lietotāju konti.

#### Vai vēlaties citu informācijas paneli?

Ja vēlaties, varat izveidot savu informācijas paneli! Apskatiet direktoriju `vendor/flightphp/apm/src/apm/presenter`, lai gūtu idejas, kā attēlot datus savam informācijas panelim!

## Informācijas paneļa funkcijas

Informācijas panelis atveras ar skaitļiem, pēc tam ar diagrammām un tad ar pieprasījumu žurnālu.

- **Statistikas rinda**: pieprasījumu skaits, kļūdu līmenis, p95, p99, vidējais latentums, caurlaidība minūtē un kešatmiņas trāpījumu līmenis.
- **Darbinieka neapstrādāto uzdevumu rinda**: cik neapstrādātu metriku joprojām atrodas `apm_metrics_log`. Liels skaitlis nozīmē, ka darbinieks atpaliek. Tukšs informācijas panelis ar rindu 0 nozīmē, ka nekas nav ievākts.
- **Latentuma diagramma**: vidējais latentums un pieprasījumu skaits tiem pašiem intervāliem. Pēdējā stunda izmanto 5 minūšu intervālus, pēdējā diena izmanto 15 minūšu intervālus, bet pēdējā nedēļa izmanto 6 stundu intervālus. Pielāgots diapazons izvēlas intervālu, kas saglabā apmēram 40 punktus. Laiki tiek glabāti UTC un attēloti jūsu izvēlētajā laika joslā.
- **Statusa diagramma**: sakrautie `2xx`, `3xx`, `4xx` un `5xx` skaitījumi. Noklikšķiniet uz sērijas, lai filtrētu žurnālu pēc šīs klases.
- **Lēnie maršruti, starpprogrammatūra, vaicājumi un pieprasījumi**: pieci katrā. Vaicājumi tiek grupēti, tāpēc `WHERE id = 1` un `WHERE id = 2` tiek skaitīti kā viens paziņojums. Noklikšķiniet uz maršruta, lēna pieprasījuma vai kļūdu grupas, lai filtrētu žurnālu.
- **Pielāgotie notikumi**: katrs jūsu lietotnes izraisītais pielāgotā notikuma veids ar tā skaitu, kad tas pēdējo reizi redzēts, un nelielu tendences līniju izvēlētajam logam. Kārtots pēc skaita. Tas ņem vērā laika diapazonu, opciju Paslēpt botus un citus filtrus. Noklikšķiniet uz veida, lai filtrētu žurnālu pēc tā.
- **Pieprasījumu žurnāls**: metode, URL, statuss, laiks, atmiņa, resursdators un bota karodziņš. Detaļas ielādējas, kad atverat rindu, un ietver laika joslu, skatus, vaicājuma parametrus, kļūdas izsekošanu un pielāgotos notikumus.
- **Paslēpt botus**: izslēdz botu satiksmi no apkopotajiem datiem un žurnāla. Meklēšanas pārlūkprogrammu un LLM pārlūkprogrammu boti (GPTBot, ClaudeBot, PerplexityBot, SearxNG un citi) tiek atzīmēti kā boti.
- **Pielāgots diapazons**: izvēlieties no/līdz logu papildus pēdējai stundai, dienai vai nedēļai.
- **Filtri**: URL, statuss, pieprasījuma ID, maršruts, IP, resursdators, sesija, lietotāja aģents, pielāgotā notikuma veids, notikuma dati un kļūdas ziņojums. Vaicājuma virkne tos saglabā arī pēc atsvaidzināšanas.

**Piemērs**:
Pieprasījums uz `/users` var parādīt:
- Kopējais laiks: 150ms
- Starpprogrammatūra: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Skata renderēšanas laiks
- Kešatmiņa: trāpījums uz `user_list` (5ms)

**Piemērs**:
Pieprasījums uz `/users` var parādīt:
- Kopējais laiks: 150ms
- Starpprogrammatūra: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešatmiņa: trāpījums uz `user_list` (5ms)

## Pievienojot pielāgotus notikumus

Izsekojiet jebko — piemēram, API izsaukumu vai maksājuma procesu:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Kur tas parādās?**
Informācijas paneļa pieprasījuma detaļās sadaļā “Custom Events” — paplašināms ar skaisti formatētu JSON. Panelis **Custom events** arī uzskaita katru veidu, tāpēc ātrs `docs.markdown_served` notikums ir viss, kas nepieciešams, lai atbildētu uz jautājumu “cik bieži tas notiek?”

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
Tagad redzēsiet, vai šis API bremzē jūsu lietotni!

## Datubāzes monitorings

Izsekojiet PDO vaicājumus šādi:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // nepieciešams, lai uzņemtu vaicājumus APM
]);
$Apm->addPdoConnection($pdo);
```

**Ko jūs iegūstat**:
- Vaicājuma teksts (piem., `SELECT * FROM users WHERE id = ?`)
- Izpildes laiks (piem., 0.015s)
- Rindu skaits (piem., 42)

**Ņemiet vērā**:
- **Neobligāti**: Izlaidiet šo, ja jums nav nepieciešama DB izsekošana.
- **SimplePdo (ieteicamais)**: Izmantojiet `SimplePdo` ar `trackApmQueries => true`. Novecojusī `PdoWrapper` joprojām darbojas (5. konstruktora arguments `true`). Neapstrādātais kodola PDO vēl nav piesaistīts — sekojiet līdzi!
- **Veiktspējas brīdinājums**: Katra vaicājuma reģistrēšana vietnē ar lielu DB slodzi var palēnināt sistēmu. Izmantojiet izlasi (`$Apm = new Apm($ApmLogger, 0.1)`), lai samazinātu slodzi.

**Piemēra izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindas: 15

## Darbinieka opcijas

Pielāgojiet darbinieku pēc saviem vēlmēm:

- `--timeout 300`: Apstājas pēc 5 minūtēm — labi testēšanai.
- `--max_messages 500`: Ierobežo līdz 500 metrikām — uztur to ierobežotu.
- `--batch_size 200`: Apstrādā 200 vienlaikus — līdzsvaro ātrumu un atmiņu.
- `--daemon`: Darbojas bez apstājas — ideāli dzīvai uzraudzībai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikas vienlaikus.

## Pieprasījuma ID lietotnē

Katram pieprasījumam ir unikāls pieprasījuma ID izsekošanai. Varat izmantot šo ID savā lietotnē, lai korelētu žurnālus un metrikas. Piemēram, varat pievienot pieprasījuma ID kļūdas lapai:

```php
Flight::map('error', function($message) {
	// Iegūstiet pieprasījuma ID no atbildes galvenes X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Papildus varat to iegūt no Flight mainīgā
	// Šī metode labi nedarbosies swoole vai citās asinhronās platformās.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Jaunināšana

Ja jaunināt uz jaunāku APM versiju, pastāv iespēja, ka ir jāpalaiž datubāzes migrācijas. To var izdarīt, palaižot šādu komandu:

```bash
php vendor/bin/runway apm:migrate
```
Tas palaidīs visas nepieciešamās migrācijas, lai atjauninātu datubāzes shēmu uz jaunāko versiju.

**Piezīme:** Ja jūsu APM datubāze ir liela, šo migrāciju palaišana var aizņemt kādu laiku. Palaidiet tās mierīgā periodā. Darbinieks var turpināt darboties. Indeksu migrācijas nepārraksta ievāktās rindas.

### Jaunināšana no versijas, kas vecāka par 0.5.2 (informācijas panelis un indeksi)

0.5.2 tikai paplašināja botu noteikšanu. Tālāk minētie indeksu faili tiek piegādāti laidienā pēc 0.5.2.

Vispirms izveidojiet galamērķa datubāzes dublējumu. Kopējiet SQLite failu vai izveidojiet `mysqldump` MySQL/MariaDB shēmai.

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

Tas piemēro:

- SQLite: `0006-dest-indexes.sql` (`request_dt` indeksi tika nomesti, kad `0005` pārbūvēja `apm_requests`)
- MySQL/MariaDB: `0002-dest-indexes.sql` (`request_dt` un citi informācijas paneļa filtri)

`apm:migrate` ieraksta faila nosaukumu un nākamreiz to izlaiž. Droši palaist to divreiz. `apm:purge` salīdzina `request_dt` ar UTC robežvērtību, kas atbilst tam, kā rindas tiek glabātas. Pirmā tīrīšana pēc šī jauninājuma var saglabāt atšķirīgu logu, ja servera laika josla iepriekš nebija UTC.

`X-Flight-Request-Id` atbilst šim pieprasījumam glabātajam `request_token`.

### Jaunināšana no 0.4.3 -> 0.5.0

Ja jaunināt no 0.4.3 uz 0.5.0, jums būs jāpalaiž šāda komanda:

```bash
php vendor/bin/runway apm:config-migrate
```

Tas migrēs jūsu konfigurāciju no vecā formāta, izmantojot `.runway-config.json` failu, uz jauno formātu, kas glabā atslēgas/vērtības `config.php` failā.

## Veco datu tīrīšana

Lai uzturētu datubāzi kārtīgu, varat notīrīt vecos datus. Tas ir īpaši noderīgi, ja uzturat noslogotu lietotni un vēlaties saglabāt datubāzes izmēru pārvaldāmu.
To var izdarīt, palaižot šādu komandu:

```bash
php vendor/bin/runway apm:purge
```
Tas no datubāzes noņems visus datus, kas vecāki par 30 dienām. Dienu skaitu varat pielāgot, nododot citu vērtību opcijai `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Tas no datubāzes noņems visus datus, kas vecāki par 7 dienām.

## Problēmu novēršana

Iestrēdzis? Izmēģiniet šos:

- **Nav informācijas paneļa datu?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Vai konfigurācijas ceļi atbilst? Pārliecinieties, ka `.runway-config.json` DSN norāda uz reāliem failiem.
  - Palaidiet `php vendor/bin/runway apm:worker` manuāli, lai apstrādātu gaidošās metrikas.

- **Darbinieka kļūdas?**
  - Ieskatieties savos SQLite failos (piem., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus, vai tajos nav steka izsekojuma.

- **Informācijas panelis nepalaižas?**
  - Ports 8001 ir aizņemts? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk lēni?**
  - Samaziniet izlases koeficientu: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet paketes lielumu: `--batch_size 20`.

- **Neizseko izņēmumus/kļūdas?**
  - Ja jūsu projektā ir iespējota [Tracy](https://tracy.nette.org/), tā pārņems Flight kļūdu apstrādi. Jums būs jāatspējo Tracy un pēc tam jāpārliecinās, ka ir iestatīts `Flight::set('flight.handle_errors', true);`.

- **Neizseko datubāzes vaicājumus?**
  - Dodiet priekšroku `SimplePdo` ar `['trackApmQueries' => true]` kā 5. konstruktora argumentu (opciju masīvu).
  - Ja joprojām izmantojat novecojušo `PdoWrapper`, nododiet `true` kā 5. argumentu.
  - Pēc savienojuma izveides izsauciet `$Apm->addPdoConnection($pdo)`.