# Konfigurācija

Jūs varat pielāgot noteiktas Flight uzvedības, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

## Pieejamie konfigurācijas iestatījumi

Zemāk ir uzskaitīts visi pieejamie konfigurācijas iestatījumi:

- **flight.base_url** `?string` - Pārrakstīt pieprasījuma pamata URL. (pēc noklusējuma: null)
- **flight.case_sensitive** `bool` - Reģistrjūtīga atbilstība URL. (pēc noklusējuma: false)
- **flight.handle_errors** `bool` - Ļauj Flight apstrādāt visas kļūdas iekšēji. (pēc noklusējuma: true)
- **flight.log_errors** `bool` - Reģistrēt kļūdas tīmekļa servera kļūdu žurnālā. (pēc noklusējuma: false)
- **flight.views.path** `string` - Katalogs, kurā atrodas skata veidnes faili. (pēc noklusējuma: ./views)
- **flight.views.extension** `string` - Skata veidnes faila paplašinājums. (pēc noklusējuma: .php)
- **flight.content_length** `bool` - Iestatīt `Content-Length` galveni. (pēc noklusējuma: true)
- **flight.v2.output_buffering** `bool` - Izmantot vecāku izvades buferēšanu. Skatiet [migrēšanu uz v3](migrating-to-v3). (pēc noklusējuma: false)

## Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkur jūsu lietojumprogrammā.

```php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Cits jūsu lietojumprogrammas vietā
$id = Flight::get('id');
```

Lai pārbaudītu, vai mainīgais ir iestatīts, varat:

```php
if (Flight::has('id')) {
  // Darīt kaut ko
}
```

Jūs varat notīrīt mainīgo, darot:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Flight izmanto mainīgos arī konfigurācijas nolūkiem.

```php
Flight::set('flight.log_errors', true);
```

## Kļūdu apstrāde

### Kļūdas un Izņēmumi

Visas kļūdas un izņēmumi tiek noķerti ar Flight un nodoti `error` metodē.
Noklusējuma uzvedība ir nosūtīt vienkāršu `HTTP 500 Internal Server Error`
atbildi ar dažādu kļūdu informāciju.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Noklusējuma uzvedībā kļūdas netiek reģistrētas tīmekļa serverī. Jūs varat iespējot to,
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

### Nav Atrasts

Kad URL nav atrasts, Flight izsauc `notFound` metodi. Noklusējuma
uzvedība ir nosūtīt `HTTP 404 Not Found` atbildi ar vienkāršu ziņojumu.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```