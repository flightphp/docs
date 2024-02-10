# Konfigurācija

Jūs varat pielāgot noteiktas Flight darbības, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

## Pieejamie konfigurācijas iestatījumi

Zemāk ir uzskaitīti visi pieejamie konfigurācijas iestatījumi:

- **flight.base_url** - Pārrakstiet pieprasījuma bāzes URL. (noklusējums: null)
- **flight.case_sensitive** - Lielo burtu atbilstība URL. (noklusējums: false)
- **flight.handle_errors** - Atļaujiet Flight apstrādāt visas kļūdas iekšpusē. (noklusējums: true)
- **flight.log_errors** - Reģistrējiet kļūdas tīmekļa servera kļūdu žurnālā. (noklusējums: false)
- **flight.views.path** - Katalogs, kurā ir skata veidnes. (noklusējums: ./views)
- **flight.views.extension** - Skata veidnes faila paplašinājums. (noklusējums: .php)

## Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkurā vietā jūsu lietotnē.

```php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Citur jūsu lietotnē
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, varat izdarīt šādi:

```php
if (Flight::has('id')) {
  // Darīt kaut ko
}
```

Jūs varat notīrīt mainīgo darbībā:

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

Visas kļūdas un izņēmumi tiek noķertas ar Flight un padotas `error` metodē.
Noklusējuma rīcība ir nosūtīt vispārēju `HTTP 500 Iekšēju servera kļūdu`
atbildi ar dažiem kļūdas informāciju.

Jūs varat pārrakstīt šo rīcību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Risināt kļūdu
  echo $error->getTraceAsString();
});
```

Noklusējuma veidā kļūdas netiek reģistrētas tīmekļa serverī. To var iespējot,
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

### Nav Atrasts

Kad nevar atrast URL, Flight izsauc `notFound` metodi. Noklusējuma
rīcība ir nosūtīt `HTTP 404 Nav Atrasts` atbildi ar vienkāršu ziņu.

Jūs varat pārrakstīt šo rīcību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Risināt nav atrasts
});
```