```lv
# Mainīgie

Flight ļauj saglabāt mainīgos, lai tos varētu izmantot jebkur aplikācijā.

```php
// Saglabā savu mainīgo
Flight::set('id', 123);

// Citur aplikācijā
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, varat izdarīt:

```php
if (Flight::has('id')) {
  // Izdarīt kaut ko
}
```

Mainīgo var notīrīt:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Flight izmanto mainīgos arī konfigurācijas nolūkos.

```php
Flight::set('flight.log_errors', true);
```