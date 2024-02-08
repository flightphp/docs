# Konfigurācija

Jūs varat pielāgot noteiktas Flight darbības, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

```php
Flight::set('flight.log_errors', true);
```

## Pieejamie konfigurācijas iestatījumi

Zemāk ir saraksts ar visiem pieejamajiem konfigurācijas iestatījumiem:

- **flight.base_url** - Pārrakstīt pieprasījuma pamata URL. (noklusējuma vērtība: null)
- **flight.case_sensitive** - Reģistru atšķirīgs atbilstība URL. (noklusējuma vērtība: false)
- **flight.handle_errors** - Atļaut Flight apstrādāt visus kļūdas iekšēji. (noklusējuma vērtība: true)
- **flight.log_errors** - Reģistrēt kļūdas tīmekļa servera kļūdu žurnālā. (noklusējuma vērtība: false)
- **flight.views.path** - Katalogs, kas satur skata veidnes failus. (noklusējuma vērtība: ./views)
- **flight.views.extension** - Skata veidnes faila paplašinājums. (noklusējuma vērtība: .php)

## Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkur jūsu lietotnē.

```php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Cits kur lietotnē
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, varat izdarīt:

```php
if (Flight::has('id')) {
  // Darīt kaut ko
}
```

Varat notīrīt mainīgo ar:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Flight lieto arī mainīgos konfigurācijas nolūkos.

```php
Flight::set('flight.log_errors', true);
```

## Kļūdu apstrāde

### Kļūdas un Izņēmumi

Visas kļūdas un izņēmumi tiek atķertas ar Flight un nodoti `error` metodē.
Noklusējuma uzvedība ir nosūtīt vispārēju `HTTP 500 Iekšēja servera kļūda`
atbildi ar kādu kļūdas informāciju.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('error', function (Throwable $error) {
  // Apstrādāt kļūdu
  echo $error->getTraceAsString();
});
```

Noklusējuma kārtībā kļūdas netiek reģistrētas tīmekļa serverī. To var iespējot,
mainot konfigurāciju:

```php
Flight::set('flight.log_errors', true);
```

### Nav Atrasts

Kad nevar atrast URL, Flight izsauc `notFound` metodi. Noklusējuma
uzvedība ir nosūtīt `HTTP 404 Atvērt neizdevās` atbildi ar vienkāršu ziņojumu.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

```php
Flight::map('notFound', function () {
  // Apstrādāt nav atrasts
});
```