# Konfigurācija

## Pārskats

Flight piedāvā vienkāršu veidu, kā konfigurēt dažādus ietvara aspektus atbilstoši jūsu lietojumprogrammas vajadzībām. Daži iestatījumi ir norādīti pēc noklusējuma, bet jūs varat tos pārrakstīt pēc nepieciešamības. Varat arī iestatīt savus mainīgos, ko izmantot visā lietojumprogrammā.

Skaidra, slāņota konfigurācija (faila noklusējumi + vides noslēpumi) palīdz arī [AI kodēšanas rīkiem](/learn/ai): aģenti apgūst vienu vietu literāļiem un vienu vietu noslēpumiem, nevis izgudro `$_ENV` lasīšanu kontrolieros.

## Izpratne

Jūs varat pielāgot noteiktu Flight darbību, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

Strukturētā lietotnē (ieskaitot [skeletu](https://github.com/flightphp/skeleton)) jūs parasti ielādējat projekta iestatījumus no `app/config/config.php` un pēc tam attiecināt attiecīgās atslēgas uz Engine (piemēram, `flight.base_url`, `flight.views.path`). Varat arī injicēt nelielu konfigurācijas objektu kontrolieros, nevis lasīt globālos mainīgos visur — tas ir draudzīgāk testiem un aģentiem, kas seko `AGENTS.md`.

## Pamata lietošana

### Flight konfigurācijas opcijas

Tālāk ir saraksts ar visiem pieejamajiem konfigurācijas iestatījumiem:

- **flight.base_url** `?string` - Pārrakstīt pieprasījuma pamata URL, ja Flight darbojas apakšdirektorijā. (noklusējums: null)
- **flight.case_sensitive** `bool` - Reģistrjutīga URL atbilstība. (noklusējums: false)
- **flight.handle_errors** `bool` - Ļaut Flight iekšēji apstrādāt visas kļūdas. (noklusējums: true)
  - Ja vēlaties, lai Flight apstrādā kļūdas, nevis noklusējuma PHP darbība, šim ir jābūt true.
  - Ja jums ir instalēts [Tracy](/awesome-plugins/tracy), iestatiet šo uz false, lai Tracy varētu apstrādāt kļūdas.
  - Ja ir instalēts [APM](/awesome-plugins/apm) spraudnis, iestatiet šo uz true, lai APM varētu reģistrēt kļūdas.
- **flight.log_errors** `bool` - Reģistrēt kļūdas tīmekļa servera kļūdu žurnāla failā. (noklusējums: false)
  - Ja instalēts [Tracy](/awesome-plugins/tracy), Tracy reģistrēs kļūdas saskaņā ar Tracy konfigurācijām, nevis šo konfigurāciju.
- **flight.debug** `bool` - Izvadīt detalizētu kļūdas informāciju (izņēmuma ziņojumu, kodu un steka izsekošanu) pārlūkā, kad rodas kļūda. (noklusējums: false)
  - **Nekad neiespējojiet to ražošanas vidē** — tas atklāj iekšējās lietojumprogrammas detaļas. Izmantojiet to tikai lokālai izstrādei vai starpposma vidē.
  - Kad `false`, tiek parādīta vispārīga `500 Internal Server Error` kļūda. Apvienojiet ar `flight.log_errors`, lai uztvertu kļūdas servera pusē.
- **flight.allow_method_override** `bool` - Ļaut pārrakstīt HTTP metodi, izmantojot `X-HTTP-Method-Override` pieprasījuma galveni vai `_method` lauku POST pamattekstā. (noklusējums: true)
  - **Ieteicams iestatīt šo uz `false`** lietojumprogrammām, kurām nav nepieciešama uz HTML veidlapām balstīta metodes viltošana, jo tas neļauj klientiem izveidot `DELETE` vai `PUT` pieprasījumus, izmantojot standarta POST veidlapu.
  - Skatiet [Drošība](/learn/security#flight-configuration-hardening) sīkākai informācijai.
- **flight.views.path** `string` - Direktorija, kurā atrodas skatu veidņu faili. (noklusējums: ./views)
- **flight.views.extension** `string` - Skata veidņu faila paplašinājums. (noklusējums: `.php`; oficiālais skelets iestata to uz `.twig`, ja tiek izmantots Twig)
- **flight.views.restrict_to_path** `bool` - Kad `true`, Flight iebūvētais `View` pieņem tikai tos veidņu failus, kas atrodas `flight.views.path` ietvaros. (noklusējums: `false`). **Ieslēdziet šo** lietojumprogrammām, kas izmanto nativos skatus. Skatiet [Drošība](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Iestatīt `Content-Length` galveni. (noklusējums: true)
  - Ja izmantojat [Tracy](/awesome-plugins/tracy), šis jāiestata uz false, lai Tracy varētu pareizi renderēt.
- **flight.v2.output_buffering** `bool` - Izmantot mantoto izvades buferizāciju. Skatiet [migrācija uz v3](migrating-to-v3). (noklusējums: false)

### Loader konfigurācija

Papildus ir vēl viens konfigurācijas iestatījums loader. Tas ļaus jums automātiski ielādēt klases ar `_` klases nosaukumā.

```php
// Iespējot klases ielādi ar apakšsvītrām
// Pēc noklusējuma: true
Loader::$v2ClassLoading = false;
```

Atcerieties, ka [automātiskā ielāde](/learn/autoloading) ir atkarīga arī no **mapju reģistra** atbilstības jūsu vārdtelpām — īpaši ar skeleta `App\` + `app/Controller/` izkārtojumu.

### Projekta konfigurācija un `.env` (skeleta paraugs)

Flight kodols neprasa `.env` failus. Daudzas lietotnes izmanto tikai PHP konfigurācijas masīvu. Oficiālais skeletas slāņo konfigurāciju, lai noslēpumi paliktu ārpus git, kamēr Runway joprojām var droši pārrakstīt **literālo** konfigurāciju:

1. **`.env` / reālā vide** — noslēpumi un izvietošanas pārrakstījumi (ignorēti git).
2. **`app/config/config.php`** — literāli PHP masīva noklusējumi (kopēti no `config_sample.php`). Šajā failā **neizmantojiet** `$_ENV[...]` izteiksmes: tādi rīki kā `runway config:set` var to pārrakstīt kā statiskas vērtības un var iekļaut noslēpumus failā.
3. **Apvienošana palaišanas laikā** — env iegūst prioritāti kartētajām atslēgām; lietotnes kods lasa konfigurācijas objektu vai `$app->get()`, nevis `$_ENV` kontrolieros.

Piemēra struktūra `config_sample.php` / `config.php` (vienkāršota):

```php
<?php
// Tikai literāļi — noslēpumi pieder .env skeleta darbplūsmai
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
# .env.example → .env (skelets)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Šis sadalījums ir apzināts [AI draudzīgiem projektiem](/learn/ai): instrukcijas var teikt “noklusējumi `config.php`, noslēpumi `.env`, injicējiet Config / Engine — nekad neizgudrojiet env piekļuvi kontrolierī.” Esošās lietotnes var pilnībā ignorēt `.env` un saglabāt vienu konfigurācijas failu.

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
  // Dari kaut ko
}
```

Varat notīrīt mainīgo, rīkojoties šādi:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

> **Piezīme:** Tas, ka varat iestatīt mainīgo, nenozīmē, ka jums tas būtu jādara. Izmantojiet šo funkciju taupīgi. Iemesls ir tas, ka viss, kas šeit tiek saglabāts, kļūst par globālu mainīgo. Globālie mainīgie ir slikti, jo tos var mainīt no jebkuras lietojumprogrammas vietas, apgrūtinot kļūdu izsekošanu. Turklāt tas var sarežģīt tādas lietas kā [vienību testēšana](/guides/unit-testing). Dodiet priekšroku konstruktora injekcijai (kā skeletā + Dice iestatījumā) pakalpojumiem un konfigurācijai, kas nepieciešami kontrolieriem.

### Kļūdas un izņēmumi

Visas kļūdas un izņēmumi tiek noķerti Flight un nodoti `error` metodei, ja `flight.handle_errors` ir iestatīts uz true.

Noklusējuma darbība ir nosūtīt vispārīgu `HTTP 500 Internal Server Error` atbildi ar daļu kļūdas informācijas.

Varat [pārrakstīt](/learn/extending) šo darbību savām vajadzībām:

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

Ja URL nevar atrast, Flight izsauc `notFound` metodi. Noklusējuma darbība ir nosūtīt `HTTP 404 Not Found` atbildi ar vienkāršu ziņojumu.

Varat [pārrakstīt](/learn/extending) šo darbību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādā "nav atrasts"
});
```

## Skatiet arī

- [Instalācija](/install) - Skeleta konfigurācija, `.env` un palaišanas izkārtojums.
- [Automātiskā ielāde](/learn/autoloading) - Vārdtelpas un mapju reģistrs.
- [Flight paplašināšana](/learn/extending) - Kā paplašināt un pielāgot Flight pamatfunkcionalitāti.
- [Vienību testēšana](/guides/unit-testing) - Kā rakstīt vienībtestus savai Flight lietojumprogrammai.
- [AI un izstrādātāja pieredze](/learn/ai) - `AGENTS.md` un konsekventas projekta instrukcijas.
- [Tracy](/awesome-plugins/tracy) - Spraudnis uzlabotai kļūdu apstrādei un atkļūdošanai.
- [Tracy paplašinājumi](/awesome-plugins/tracy_extensions) - Paplašinājumi Tracy integrēšanai ar Flight.
- [APM](/awesome-plugins/apm) - Spraudnis lietojumprogrammas veiktspējas uzraudzībai un kļūdu izsekošanai.
- [Drošība](/learn/security) - Drošības nostiprināšanas karodziņi un noslēpumu apstrāde.

## Problēmu novēršana

- Ja jums ir problēmas noskaidrot visas savas konfigurācijas vērtības, varat izpildīt `var_dump(Flight::get());`
- Ja Runway vai izvietošanas rīki pārrakstīja `config.php`, pārliecinieties, ka noslēpumi netika iekļauti git — turiet tos `.env` vai reālajā vidē, ja izmantojat skeleta paraugu.

## Izmaiņu žurnāls

- Dokumentācija – norādīts `flight.views.restrict_to_path` blakus skatu ceļa iestatījumiem.
- Dokumentācija – aprakstīts skeleta stila config / `.env` slāņojums un Twig skatu paplašinājuma noklusējums jauniem projektiem.
- v3.18.1 - Pievienotas `flight.debug` un `flight.allow_method_override` konfigurācijas opcijas.
- v3.5.0 - Pievienota konfigurācija `flight.v2.output_buffering`, lai atbalstītu mantoto izvades buferizācijas darbību.
- v2.0 - Pievienotas pamata konfigurācijas.