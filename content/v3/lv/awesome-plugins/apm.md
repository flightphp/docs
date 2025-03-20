# FlightPHP APM Dokumentācija

Laipni lūdzam FlightPHP APM — jūsu lietotnes personīgais veiktspējas treneris! Šis ceļvedis ir jūsu maršruts, lai iestatītu, lietotu un apgūtu Lietojumprogrammu veiktspējas uzraudzību (APM) ar FlightPHP. Neatkarīgi no tā, vai jūs meklējat lēnas pieprasījuma vai vienkārši vēlaties izpētīt latentuma grafikus, mēs jums palīdzēsim. Padarīsim jūsu lietotni ātrāku, jūsu lietotājus laimīgākus un jūsu atkļūdošanas sesijas vieglākas!

## Kāpēc APM ir svarīgs

Iedomājieties sekojošo: jūsu lietotne ir aizņemts restorāns. Bez veida, kā izsekot, cik ilgi pasūtījumi tiek veikti vai kur virtuvē ir kavēšanās, jūs minat, kādēļ klienti aiziet neapmierināti. APM ir jūsu sous-chef — tas uzrauga katru soli, sākot no ienākošiem pieprasījumiem līdz datu bāzes vaicājumiem, un noraksta visu, kas palēnina jūsu gaitu. Lēnas lapas zaudē lietotājus (pētījumi apgalvo, ka 53% pamet, ja vietne ielādējas ilgāk par 3 sekundēm!), un APM palīdz jums pamanīt šīs problēmas *pirms* tās jums sāp. Tas ir proaktīvs prāta miers — mazāk "kāpēc tas ir saplēsts?" mirkļu, vairāk "skaties, cik gludi tas darbojas!" uzvaras.

## Instalācija

Sāciet ar Composer:

```bash
composer require flightphp/apm
```

Jums būs nepieciešams:
- **PHP 7.4+**: Nodrošina saderību ar LTS Linux distribūcijām, vienlaikus atbalstot mūsdienu PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: Vieglākais ietvars, ko mēs uzlabojam.

## Sākums

Šeit ir jūsu soli pa solim uz APM lielisko pasauli:

### 1. Reģistrējiet APM

Ievietojiet šo kodu savā `index.php` vai `services.php` failā, lai sāktu uzraudzību:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**Kas notiek šeit?**
- `LoggerFactory::create()` ņem jūsu konfigurāciju (vairāk par to drīz) un iestata žurnālu — SQLite pēc noklusējuma.
- `Apm` ir zvaigzne — tā klausās Flight notikumus (pieprasījumus, maršrutus, kļūdas utt.) un apkopo metriku.
- `bindEventsToFlightInstance($app)` sasaista to visu ar jūsu Flight lietotni.

**Pro padoms: Paraugu ņemšana**
Ja jūsu lietotne ir aizņemta, katra pieprasījuma žurnāļu saglabāšana var palielināt slodzi. Izmantojiet paraugu likmi (no 0.0 līdz 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Žurnāli 10% pieprasījumu
```

Tas uztur veiktspēju ātru, vienlaikus sniedzot jums kvalitatīvus datus.

### 2. Konfigurējiet to

Izpildiet šo, lai izveidotu savu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**Kas tas dara?**
- Palaiž vedni, kas jautā, no kurienes nāk neapstrādātās metrikas (avots) un kur tiek nosūtīti apstrādātie dati (galamērķis).
- Noklusējums ir SQLite — piemēram, `sqlite:/tmp/apm_metrics.sqlite` avotam, cits galamērķim.
- Jums beidzot būs konfigurācija, kas izskatās tā:
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

**Kāpēc divas vietas?**
Neapstrādātās metrikas ātri sakrājas (domājiet par filtrētām žurnālu datnēm). Darbinieks tās apstrādā struktūrētā galamērķī priekš paneļa. Uztur lietas kārtībā!

### 3. Apstrādājiet metrikas ar darbinieku

Darbinieks pārveido neapstrādātās metrikas par paneļa gataviem datiem. Izpildiet to vienreiz:

```bash
php vendor/bin/runway apm:worker
```

**Kas tas dara?**
- Lasīt no jūsu avota (piemēram, `apm_metrics.sqlite`).
- Apstrādā līdz 100 metrikām (noklusējuma partijas lielums) uz jūsu galamērķi.
- Apstājas, kad ir pabeigta vai ja metrikas vairs nav.

**Turpiniet darbināt to**
Dzīvām lietotnēm jūs vēlēsieties nepārtrauktu apstrādi. Šeit ir jūsu iespējas:

- **Daimon režīms**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Darbojas bezgalīgi, apstrādājot metrikas, kad tās ierodas. Lieliska priekš izstrādes vai maziem iestatījumiem.

- **Crontab**:
  Pievienojiet to savam crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Izsist katru minūti — ideāli piemērota ražošanai.

- **Tmux/Screen**:
  Sāciet noņemamu sesiju:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, tad D, lai noņemtu; `tmux attach -t apm-worker`, lai atjaunotu savienojumu
  ```
  Uztur to darbināmu pat tad, ja izrakstāties.

- **Pielāgotas izmaiņas**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Apstrādā 50 metrikas reizē.
  - `--max_messages 1000`: Apstājas pēc 1000 metrikām.
  - `--timeout 300`: Iziet pēc 5 minūtēm.

**Kāpēc uztraukties?**
Bez darbinieka jūsu panelis ir tukšs. Tas ir tilts starp neapstrādātajiem žurnāliem un rīcībai nepieciešamajiem ieskatiem.

### 4. Palaižiet paneli

Redziet jūsu lietotnes vitālos datus:

```bash
php vendor/bin/runway apm:dashboard
```

**Kas tas?**
- Palaiž PHP serveri adresē `http://localhost:8001/apm/dashboard`.
- Rāda pieprasījuma žurnālus, lēnus maršrutus, kļūdu likmes un daudz ko citu.

**Pielāgojiet to**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Pieejami no jebkura IP (noderīgi attālinātai skatīšanai).
- `--port 8080`: Izmantojiet citu portu, ja 8001 ir aizņemts.
- `--php-path`: Norādiet uz PHP, ja tas nav jūsu PATH.

Piekļūstiet URL savā pārlūkprogrammā un izpētiet!

#### Ražošanas režīms

Ražošanā jums, iespējams, nāksies izmēģināt dažādas tehnikas, lai panāktu paneļa darbību, jo ir iespējams, ka tur ir ugunsmūri un citi drošības pasākumi. Šeit ir dažas iespējas:

- **Izmantojiet apgrieztos proksī**: Iestatiet Nginx vai Apache, lai pārsūtītu pieprasījumus uz paneli.
- **SSH tunelis**: Ja varat SSH savienoties ar serveri, izmantojiet `ssh -L 8080:localhost:8001 youruser@yourserver`, lai tunelētu paneli jūsu lokālajā datorā.
- **VPN**: Ja jūsu serveris ir aiz VPN, pieslēdzieties tam un piekļūstiet panelim tieši.
- **Konfigurējiet ugunsmūri**: Atveriet 8001 portu savai IP vai servera tīklam (vai kādu citu portu, ko jūs esat iestatījis).
- **Konfigurējiet Apache/Nginx**: Ja jums ir tīmekļa serveris priekš jūsu lietotnes, varat to konfigurēt uz domēna vai apakšdomēna. Ja to darāt, iestatiet dokumentu sakni uz `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Vēlaties citu paneli?

Ja vēlaties, varat izveidot savu paneli! Pārlūkojiet `vendor/flightphp/apm/src/apm/presenter` direktoriju, lai iegūtu idejas par to, kā prezentēt datus savai panelei!

## Paneļa funkcijas

Panelis ir jūsu APM HQ — šeit ir tas, ko jūs redzēsiet:

- **Pieprasījuma žurnāls**: Katrs pieprasījums ar laika zīmogu, URL, atbilžu kodu un kopējo laiku. Noklikšķiniet uz "Detālēs", lai redzētu starpprogrammas, vaicājumus un kļūdas.
- **Lēnākie pieprasījumi**: Top 5 pieprasījumi, kas tērē laiku (piemēram, “/api/heavy” 2.5s).
- **Lēnākie maršruti**: Top 5 maršruti pēc vidējā laika — lieliski, lai atklātu modeļus.
- **Kļūdu likme**: Procentuālais pieprasījumu skaits, kas neizdodas (piemēram, 2.3% 500s).
- **Latentuma percentīļi**: 95. (p95) un 99. (p99) atbilžu laiki — ziniet savus sliktākos gadījumus.
- **Atbilžu koda diagramma**: Vizualizējiet 200s, 404s, 500s laika gaitā.
- **Garie vaicājumi/Starpprogrammas**: Top 5 lēnākie datu bāzes zvani un starpprogrammas slāņi.
- **Katrā hit/zaudējums**: Cik bieži jūsu kešatmiņa glābj dienu.

**Papildinājumi**:
- Filtrējiet pēc “pēdējās stundas”, “pēdējās dienas” vai “pēdējās nedēļas”.
- Pārslēdziet tumšo režīmu nogurušām sesijām.

**Piemērs**:
Pieprasījums uz `/users` varētu parādīt:
- Kopējais laiks: 150ms
- Starpprogramma: `AuthMiddleware->handle` (50ms)
- Vaicājums: `SELECT * FROM users` (80ms)
- Kešatmiņa: Hit uz `user_list` (5ms)

## Pievienojot pielāgotus notikumus

Izsekojiet jebko — kā API zvanu vai maksājumu procesu:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Kur tas tiks parādīts?**
Paneļa pieprasījumu detalizētās sadaļas zem "Pielāgoti notikumi" — izplatināms ar jauku JSON formātu.

**Izmantošanas gadījums**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Tagad jūs redzēsiet, vai tas API palēnina jūsu lietotni!

## Datu bāzes uzraudzība

Izsekojiet PDO vaicājumus šādi:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**Ko jūs iegūstat**:
- Vaicājuma teksts (piemēram, `SELECT * FROM users WHERE id = ?`)
- Izpildes laiks (piemēram, 0.015s)
- Rindu skaits (piemēram, 42)

**Uzziniet**:
- **Pēc izvēles**: Izlaižiet to, ja nekas datu bāzes uzraudzība nav nepieciešama.
- **Tikai PdoWrapper**: Galvenais PDO vēl nav piesaistīts — palieciet pievienoti!
- **Veiktspējas brīdinājums**: Neapstrādāt katru vaicājumu datu bāzē ar lielu slodzi, var palēnināt lietas. Izmantojiet paraugu ņemšanu (`$Apm = new Apm($ApmLogger, 0.1)`) lai samazinātu slodzi.

**Piemēra izvade**:
- Vaicājums: `SELECT name FROM products WHERE price > 100`
- Laiks: 0.023s
- Rindas: 15

## Darbinieka opcijas

Regulējiet darbinieku pēc saviem vēlmēm:

- `--timeout 300`: Apstājas pēc 5 minūtēm — labi testēšanai.
- `--max_messages 500`: Ierobežo līdz 500 metrikām — notur to finansiālajā limitā.
- `--batch_size 200`: Apstrādā 200 reizē — līdzsvaro ātrumu un atmiņu.
- `--daemon`: Nepārtraukti darbojas — ideāli piemērota tiešai uzraudzībai.

**Piemērs**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Darbojas stundu, apstrādājot 100 metrikas vienlaikus.

## Problēmu risināšana

Iestiprinat? Izmēģiniet šos:

- **Nē paneļa datu?**
  - Vai darbinieks darbojas? Pārbaudiet `ps aux | grep apm:worker`.
  - Vai konfigurācijas ceļi sakrīt? Pārliecinieties, ka `.runway-config.json` DSN norāda uz reālām datnēm.
  - Rūpīgi palaižat `php vendor/bin/runway apm:worker`, lai apstrādātu gaidošās metrikas.

- **Darbinieka kļūdas?**
  - Ieskats jūsu SQLite datnēs (piemēram, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Pārbaudiet PHP žurnālus par steka pēdām.

- **Paneļa nesāksies?**
  - 8001 ports tiek izmantots? Izmantojiet `--port 8080`.
  - PHP nav atrasts? Izmantojiet `--php-path /usr/bin/php`.
  - Ugunsmūris bloķē? Atveriet portu vai izmantojiet `--host localhost`.

- **Pārāk lēni?**
  - Samaziniet paraugu likmi: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Samaziniet partijas lielumu: `--batch_size 20`.