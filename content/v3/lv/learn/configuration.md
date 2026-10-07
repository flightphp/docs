# Konfigurācija

## Pārskats

Flight nodrošina vienkāršu veidu, kā konfigurēt dažādus sistēmas aspektus atbilstoši jūsu lietojumprogrammas vajadzībām. Daži ir iestatīti pēc noklusējuma, bet jūs varat tos pārrakstīt pēc nepieciešamības. Varat arī iestatīt savus mainīgos, ko izmantot visā lietojumprogrammā.

Skaidra, slāņota konfigurācija (faila noklusējumi + vides noslēpumi) arī palīdz [AI kodēšanas rīkiem](/learn/ai): aģenti apgūst vienu vietu literāļiem un vienu vietu noslēpumiem, nevis izgudro `$_ENV` lasījumus kontrolleros.

## Izpratne

Jūs varat pielāgot noteiktus Flight uzvedības veidus, iestatot konfigurācijas vērtības ar `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

Strukturētā lietojumprogrammā (ieskaitot [skeleton](https://github.com/flightphp/skeleton)) jūs parasti ielādējat projekta iestatījumus no `app/config/config.php` un pēc tam attiecināt atbilstošās atslēgas uz Engine (piemēram, `flight.base_url`, `flight.views.path`). Varat arī ievadīt mazu konfigurācijas objektu kontrolleros, nevis lasīt globālos mainīgos visur — tas ir draudzīgāk testiem un aģentiem, kas seko `AGENTS.md`.

## Pamata lietošana

### Flight konfigurācijas opcijas

Šis ir visu pieejamo konfigurācijas iestatījumu saraksts:

- **flight.base_url** `?string` — Pārraksta pieprasījuma bāzes URL, ja Flight darbojas apakšdirektorijā. (noklusējums: null)
- **flight.case_sensitive** `bool` — Reģistrjutīga URL atbilstība. (noklusējums: false)
- **flight.handle_errors** `bool` — Ļauj Flight iekšēji apstrādāt visas kļūdas. (noklusējums: true)
  - Ja vēlaties, lai Flight apstrādā kļūdas, nevis noklusējuma PHP uzvedību, tam jābūt true.
  - Ja jums ir instalēts [Tracy](/awesome-plugins/tracy), vēlaties iestatīt to uz false, lai Tracy varētu apstrādāt kļūdas.
  - Ja jums ir instalēts [APM](/awesome-plugins/apm) spraudnis, iestatiet to uz true, lai APM varētu reģistrēt kļūdas.
- **flight.log_errors** `bool` — Reģistrē kļūdas tīmekļa servera kļūdu žurnālā. (noklusējums: false)
  - Ja jums ir instalēts [Tracy](/awesome-plugins/tracy), Tracy reģistrēs kļūdas, pamatojoties uz Tracy konfigurācijām, nevis šo konfigurāciju.
- **flight.debug** `bool` — Izvada detalizētu kļūdu informāciju (izņēmuma ziņojumu, kodu un steka izsekošanu) pārlūkprogrammā, kad rodas kļūda. (noklusējums: false)
  - **Nekad neiespējojiet to ražošanā** — tas nopludina iekšējās lietojumprogrammas detaļas. Izmantojiet to tikai vietējai izstrādei vai testēšanas videi.
  - Ja `false`, tiek rādīta vispārīga `500 Internal Server Error` atbilde. Savienojiet ar `flight.log_errors`, lai uztvertu kļūdas servera pusē.
- **flight.allow_method_override** `bool` — Ļauj pārrakstīt HTTP metodi, izmantojot `X-HTTP-Method-Override` pieprasījuma galveni vai `_method` lauku POST pamattekstā. (noklusējums: true)
  - **Ieteicams iestatīt `false`** lietojumprogrammām, kurām nav nepieciešama HTML veidlapu metodes viltošana, jo tas neļauj klientiem izveidot `DELETE` vai `PUT` pieprasījumus, izmantojot standarta POST veidlapu.
  - Skatiet [Drošība](/learn/security#flight-configuration-hardening) sīkākai informācijai.
- **flight.views.path** `string` — Direktorija, kurā atrodas skata veidņu faili. (noklusējums: ./views)
- **flight.views.extension** `string` — Skata veidņu faila paplašinājums. (noklusējums: `.php`; oficiālais skeleton iestata to uz `.twig`, ja tiek izmantots Twig)
- **flight.views.restrict_to_path** `bool` — Ja `true`, Flight vietējais `View` pieņem tikai veidņu failus, kas atrodas `flight.views.path` iekšpusē. (noklusējums: `false`). **Ieslēdziet to** lietotnēm, kas izmanto vietējos skatus. Skatiet [Drošība](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` — Iestata `Content-Length` galveni. (noklusējums: true)
  - Ja izmantojat [Tracy](/awesome-plugins/tracy), tas jāiestata uz false, lai Tracy varētu pareizi renderēt.
- **flight.v2.output_buffering** `bool` — Izmanto mantoto izejas buferizāciju. Skatiet [pāreja uz v3](migrating-to-v3). (noklusējums: false)

### Loader konfigurācija

Papildus ir vēl viens konfigurācijas iestatījums loaderim. Tas ļaus jums automātiski ielādēt klases ar `_` klases nosaukumā.

```php
// Iespējo klases ielādi ar apakšsvītrām
// Pēc noklusējuma ir true
Loader::$v2ClassLoading = false;
```

Atcerieties, ka [automātiskā ielāde](/learn/autoloading) ir atkarīga arī no **mapju reģistra** atbilstības jūsu nosaukumvietām — īpaši ar skeleton `App\` + `app/Controller/` izkārtojumu.

### Projekta konfigurācija un `.env` (skeleton modelis)

Flight kodols neprasa `.env` failus. Daudzas lietotnes izmanto tikai PHP konfigurācijas masīvu. Oficiālais skeleton slāņo konfigurāciju, lai noslēpumi paliktu ārpus git, kamēr Runway joprojām var droši pārrakstīt **literālo** konfigurāciju:

1. **`.env` / reālā vide** — noslēpumi un izvietošanas pārrakstīšana (gitignored).
2. **`app/config/config.php`** — literālo PHP masīvu noklusējumi (kopēti no `config_sample.php`). Dodiet priekšroku **bez** `$_ENV[...]` izteiksmēm šajā failā: rīki, piemēram, `runway config:set`, var to pārrakstīt kā statiskas vērtības un var iecept noslēpumus failā.
3. **Apvienošana startējot** — vide uzvar kartētajām atslēgām; lietotnes kods nolasa konfigurācijas objektu vai `$app->get()`, nevis `$_ENV` kontrolleros.

Piemēra struktūra `config_sample.php` / `config.php` (vienkāršota):

```php
<?php
// Tikai literāļi — noslēpumi pieder .env failā skeleton darbplūsmā
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // vai mysql, vai '' lai atspējotu
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Šī sadalīšana ir apzināta [AI draudzīgiem projektiem](/learn/ai): instrukcijas var teikt "noklusējumi `config.php`, noslēpumi `.env`, injicējiet Config / Engine — nekad neizgudrojiet env piekļuvi kontrollerī." Esošās lietotnes var ignorēt `.env` pilnībā un saglabāt vienu konfigurācijas failu.

### Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkurā lietojumprogrammas vietā.

```php
// Saglabā savu mainīgo
Flight::set('id', 123);

// Citur jūsu lietojumprogrammā
$id = Flight::get('id');
```

Lai pārbaudītu, vai mainīgais ir iestatīts, varat rīkoties šādi:

```php
if (Flight::has('id')) {
  // Dariet kaut ko
}
```

Jūs varat notīrīt mainīgo:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

> **Piezīme:** Tas, ka varat iestatīt mainīgo, nenozīmē, ka jums to vajadzētu darīt. Izmantojiet šo funkciju taupīgi. Iemesls ir tāds, ka viss, kas šeit tiek saglabāts, kļūst par globālu mainīgo. Globālie mainīgie ir slikti, jo tos var mainīt no jebkuras lietojumprogrammas vietas, tādējādi apgrūtinot kļūdu izsekošanu. Turklāt tas var sarežģīt tādas lietas kā [vienību testēšana](/guides/unit-testing). Dodiet priekšroku konstruktora injekcijai (kā skeleton + Dice iestatījumā) pakalpojumiem un konfigurācijai, kas nepieciešama kontrolleriem.

### Kļūdas un izņēmumi

Visas kļūdas un izņēmumus uztver Flight un nodod `error` metodei, ja `flight.handle_errors` ir iestatīts uz true.

Noklusējuma uzvedība ir nosūtīt vispārīgu `HTTP 500 Internal Server Error` atbildi ar nelielu kļūdu informāciju.

Jūs varat [pārrakstīt](/learn/extending) šo uzvedību atbilstoši savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādā kļūdu
  echo $error->getTraceAsString();
});
```

Pēc noklusējuma kļūdas netiek reģistrētas tīmekļa serverī. To var iespējot, mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Nav atrasts

Kad URL nevar atrast, Flight izsauc `notFound` metodi. Noklusējuma uzvedība ir nosūtīt `HTTP 404 Not Found` atbildi ar vienkāršu ziņojumu.

Jūs varat [pārrakstīt](/learn/extending) šo uzvedību atbilstoši savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādā "nav atrasts"
});
```

## Skatīt arī
- [Instalēšana](/install) — Skeleton konfigurācija, `.env` un startēšanas izkārtojums.
- [Automātiskā ielāde](/learn/autoloading) — Nosaukumvietas un mapju reģistrs.
- [Flight paplašināšana](/learn/extending) — Kā paplašināt un pielāgot Flight pamatfunkcionalitāti.
- [Vienību testēšana](/guides/unit-testing) — Kā rakstīt vienību testus jūsu Flight lietojumprogrammai.
- [AI un izstrādātāja pieredze](/learn/ai) — `AGENTS.md` un konsekventas projekta instrukcijas.
- [Tracy](/awesome-plugins/tracy) — Spraudnis uzlabotai kļūdu apstrādei un atkļūdošanai.
- [Tracy paplašinājumi](/awesome-plugins/tracy_extensions) — Paplašinājumi Tracy integrēšanai ar Flight.
- [APM](/awesome-plugins/apm) — Spraudnis lietojumprogrammu veiktspējas uzraudzībai un kļūdu izsekošanai.
- [Drošība](/learn/security) — Nostiprināšanas karodziņi un noslēpumu apstrāde.

## Problēmu novēršana
- Ja jums ir problēmas noskaidrot visas konfigurācijas vērtības, varat izmantot `var_dump(Flight::get());`
- Ja Runway vai izvietošanas rīki pārrakstīja `config.php`, pārliecinieties, ka noslēpumi netika iekļauti versiju kontroles sistēmā — turiet tos `.env` vai reālajā vidē, ja izmantojat skeleton modeli.

## Izmaiņu žurnāls
- Dokumentācija — Atzīmēts `flight.views.restrict_to_path` blakus skatu ceļa iestatījumiem.
- Dokumentācija — Dokumentēts skeleton stila konfigurācijas / `.env` slāņojums un Twig skatu paplašinājuma noklusējums jauniem projektiem.
- v3.18.1 — Pievienotas `flight.debug` un `flight.allow_method_override` konfigurācijas opcijas.
- v3.5.0 — Pievienota konfigurācija `flight.v2.output_buffering`, lai atbalstītu mantoto izejas buferizācijas uzvedību.
- v2.0 — Pievienotas pamata konfigurācijas.