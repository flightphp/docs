# Konfigurācija

Varat pielāgot dažādas Flight darbības, iestatot konfigurācijas vērtības, 
izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

## Pieejamie konfigurācijas iestatījumi

Zemāk ir saraksts ar visiem pieejamajiem konfigurācijas iestatījumiem:

- **flight.base_url** `?string` - Pārrakstīt pieprasījuma pamata URL. (pēc noklusējuma: null)
- **flight.case_sensitive** `bool` - Reģistra jutīga atbilstība URL. (pēc noklusējuma: false)
- **flight.handle_errors** `bool` - Ļauj Flight apstrādāt visas kļūdas iekšpusē. (pēc noklusējuma: true)
- **flight.log_errors** `bool` - Reģistrēt kļūdas tīmekļa servera kļūdu žurnālā. (pēc noklusējuma: false)
- **flight.views.path** `string` - Katalogs, kas satur skata veidnes failus. (pēc noklusējuma: ./views)
- **flight.views.extension** `string` - Skata veidnes faila paplašinājums. (pēc noklusējuma: .php)
- **flight.content_length** `bool` - Iestatīt `Content-Length` galveni. (pēc noklusējuma: true)
- **flight.v2.output_buffering** `bool` - Izmantot veco datu buferēšanas metodi. Skat. [migrating to v3](migrating-to-v3). (pēc noklusējuma: false)

## Ielādes konfigurācija

Turklāt ir vēl viens konfigurācijas iestatījums ielādētājam. Tas ļaus jums
automātiski ielādēt klases ar `_` klases nosaukumā.

```php
// Iespējot klases ielādi ar apakšsvītra zīmi
// Pēc noklusējuma ir ieslēgts
Loader::$v2ClassLoading = false;
```

## Mainīgie

Flight ļauj jums saglabāt mainīgos, lai tos varētu izmantot jebkur lietotnē.

```php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Cits kur lietotnē
$id = Flight::get('id');
```

Lai pārbaudītu, vai mainīgais ir iestatīts, varat izdarīt:

```php
if (Flight::has('id')) {
  // Izdarīt kaut ko
}
```

Mainīgo var notīrīt, darot:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Flight arī izmanto mainīgos konfigurācijas nolūkiem.

```php
Flight::set('flight.log_errors', true);
```

## Kļūdu apstrāde

### Kļūdas un Izņēmumi

Visas kļūdas un izņēmumi tiek uztverti ar Flight un nodoti `error` metodē.
Noklusējuma uzvedība ir nosūtīt vispārēju `HTTP 500 Iekšēja servera kļūda`
atbildi ar dažām kļūdas informācijām.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Pēc noklusējuma kļūdas netiek reģistrētas tīmekļa serverī. To var aktivizēt,
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

### Nav Atrasts

Kad URL nav atrasts, Flight izsauc `notFound` metodi. Noklusējuma
uzvedība ir nosūtīt `HTTP 404 Nav atrasts` atbildi ar vienkāršu ziņu.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```