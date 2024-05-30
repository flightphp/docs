# Konfigurācija

Jūs varat pielāgot noteiktus Flight uzvedības veidus, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

## Pieejamie konfigurācijas iestatījumi

Zemāk ir saraksts ar visiem pieejamajiem konfigurācijas iestatījumiem:

- **flight.base_url** `?string` - Aizstāj pieprasījuma pamata URL. (default: null)
- **flight.case_sensitive** `bool` - Reģistrjūtīgs sakritības meklējums URL adresēs. (default: false)
- **flight.handle_errors** `bool` - Ļauj Flight apstrādāt visas kļūdas iekšēji. (default: true)
- **flight.log_errors** `bool` - Reģistrjūtīgs sakritības meklējums URL adresēs. (default: false)
- **flight.views.path** `string` - Katalogs, kur glabājas skata veidnes faili. (default: ./views)
- **flight.views.extension** `string` - Skata veidnes faila paplašinājums. (default: .php)
- **flight.content_length** `bool` - Iegūt `Saturs-Garums` galveni. (default: true)
- **flight.v2.output_buffering** `bool` - Izmantot veco izvades buferēšanu. Skatiet [migrating to v3](migrating-to-v3). (default: false)

## Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkur aplikācijā.

```php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Citur jūsu aplikācijā
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, varat izdarīt šādi:

```php
if (Flight::has('id')) {
  // Dariet kaut ko
}
```

Jūs varat notīrīt mainīgo, izmantojot šo:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīrīt visus mainīgos
Flight::clear();
```

Flight izmanto mainīgos arī konfigurācijas nolūkiem.

```php
Flight::set('flight.log_errors', true);
```

## Kļūdu apstrāde

### Kļūdas un Izņēmumi

Visas kļūdas un izņēmumi tiek noķerti ar Flight un nodoti `error` metodē.
Noklusējuma rīcība ir nosūtīt vispārēju `HTTP 500 Iekšēja servera kļūda` atbildi ar kādu kļūdas informāciju.

Jūs varat atjaunot šo rīcību saviem mērķiem:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Noklusējuma rīcība ir nelogot kļūdas web serverī. Jūs varat iespējot to, mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

### Nav Atrasts

Kad URL nevar atrast, Flight izsauc `notFound` metodi. Noklusējuma rīcība ir nosūtīt `HTTP 404 Nav Atrasts` atbildi ar vienkāršu ziņojumu.

Jūs varat atjaunot šo rīcību saviem mērķiem:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```