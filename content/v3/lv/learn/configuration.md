# Konfigurācija

## Pārskats

Flight nodrošina vienkāršu veidu, kā konfigurēt dažādus framework aspektus, lai tie atbilstu jūsu lietojumprogrammas vajadzībām. Daži ir iestatīti pēc noklusējuma, bet jūs varat tos pārrakstīt pēc vajadzības. Jūs varat arī iestatīt savas mainīgās vērtības, lai tās izmantotu visā jūsu lietojumprogrammā.

## Saprašana

Jūs varat pielāgot noteiktus Flight uzvedības aspektus, iestatot konfigurācijas vērtības
izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

`app/config/config.php` failā jūs varat redzēt visas noklusējuma konfigurācijas mainīgās vērtības, kas ir pieejamas jums.

## Pamata izmantošana

### Flight konfigurācijas opcijas

Turpmāk ir visu pieejamo konfigurācijas iestatījumu saraksts:

- **flight.base_url** `?string` - Pārrakstīt pieprasījuma bāzes URL, ja Flight darbojas apakšdirektorijā. (noklusējums: null)
- **flight.case_sensitive** `bool` - URL reģistra jutīga atbilstība. (noklusējums: false)
- **flight.handle_errors** `bool` - Ļaut Flight apstrādāt visas kļūdas iekšēji. (noklusējums: true)
  - Ja vēlaties, lai Flight apstrādā kļūdas nevis noklusējuma PHP uzvedību, tas jāiestata uz true.
  - Ja jums ir instalēts [Tracy](/awesome-plugins/tracy), jūs vēlaties iestatīt to uz false, lai Tracy varētu apstrādāt kļūdas.
  - Ja jums ir instalēts [APM](/awesome-plugins/apm) spraudnis, jūs vēlaties iestatīt to uz true, lai APM varētu reģistrēt kļūdas.
- **flight.log_errors** `bool` - Reģistrēt kļūdas tīmekļa servera kļūdu žurnālfailā. (noklusējums: false)
  - Ja jums ir instalēts [Tracy](/awesome-plugins/tracy), Tracy reģistrēs kļūdas balstoties uz Tracy konfigurācijām, nevis šo konfigurāciju.
- **flight.views.path** `string` - Direktorija, kas satur skata veidnes failus. (noklusējums: ./views)
- **flight.views.extension** `string` - Skata veidnes faila paplašinājums. (noklusējums: .php)
- **flight.content_length** `bool` - Iestatīt `Content-Length` galveni. (noklusējums: true)
  - Ja jūs izmantojat [Tracy](/awesome-plugins/tracy), tas jāiestata uz false, lai Tracy varētu pareizi renderēt.
- **flight.v2.output_buffering** `bool` - Izmantot mantojamu izvades buferizāciju. Skatīt [migrēšanu uz v3](migrating-to-v3). (noklusējums: false)

### Loader konfigurācija

Ir papildu konfigurācijas iestatījums loader. Tas ļaus jums 
automatiski ielādēt klases ar `_` klases nosaukumā.

```php
// Iespējot klases ielādi ar apakšsvītrām
// Noklusējums ir true
Loader::$v2ClassLoading = false;
```

### Mainīgās vērtības

Flight ļauj jums saglabāt mainīgās vērtības, lai tās varētu izmantot jebkur jūsu lietojumprogrammā.

```php
// Saglabāt jūsu mainīgo
Flight::set('id', 123);

// Citur jūsu lietojumprogrammā
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, jūs varat izdarīt:

```php
if (Flight::has('id')) {
  // Izpildīt kaut ko
}
```

Jūs varat notīrīt mainīgo šādi:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visas mainīgās vērtības
Flight::clear();
```

> **Piezīme:** Tas, ka jūs varat iestatīt mainīgo, nenozīmē, ka jums tas jādarītu. Izmantojiet šo funkciju saudzīgi. Iemesls ir tas, ka jebkas, kas uzglabāts šeit, kļūst par globālu mainīgo. Globālās mainīgās vērtības ir sliktas, jo tās var mainīt no jebkuras vietas jūsu lietojumprogrammā, padarot grūti izsekot kļūdas. Turklāt tas var sarežģīt lietas, piemēram, [vienības testēšanu](/guides/unit-testing).

### Kļūdas un izņēmumi

Visas kļūdas un izņēmumi tiek uztverti Flight un nodoti `error` metodei.
ja `flight.handle_errors` ir iestatīts uz true.

Noklusējuma uzvedība ir nosūtīt vispārīgu `HTTP 500 Internal Server Error`
atbildi ar dažām kļūdas informācijām.

Jūs varat [pārrakstīt](/learn/extending) šo uzvedību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Pēc noklusējuma kļūdas netiek reģistrētas tīmekļa serverī. Jūs varat to iespējot,
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Nav atrasts

Kad URL nevar atrast, Flight izsauc `notFound` metodi. Noklusējuma
uzvedība ir nosūtīt `HTTP 404 Not Found` atbildi ar vienkāršu ziņojumu.

Jūs varat [pārrakstīt](/learn/extending) šo uzvedību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```

## Skatīt arī
- [Paplašināšana Flight](/learn/extending) - Kā paplašināt un pielāgot Flight kodola funkcionalitāti.
- [Vienības testēšana](/guides/unit-testing) - Kā rakstīt vienības testus jūsu Flight lietojumprogrammai.
- [Tracy](/awesome-plugins/tracy) - Spraudnis uzlabotai kļūdu apstrādei un atkļūdošanai.
- [Tracy paplašinājumi](/awesome-plugins/tracy_extensions) - Paplašinājumi Tracy integrācijai ar Flight.
- [APM](/awesome-plugins/apm) - Spraudnis lietojumprogrammas veiktspējas uzraudzībai un kļūdu izsekošanai.

## Traucējummeklēšana
- Ja jums ir problēmas, lai noskaidrotu visas jūsu konfigurācijas vērtības, jūs varat izdarīt `var_dump(Flight::get());`

## Izmaiņu žurnāls
- v3.5.0 - Pievienota konfigurācija `flight.v2.output_buffering`, lai atbalstītu mantojamu izvades buferizācijas uzvedību.
- v2.0 - Pievienotas kodola konfigurācijas.