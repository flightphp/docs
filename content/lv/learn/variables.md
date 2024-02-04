# Mainīgie

Lidojums ļauj jums saglabāt mainīgos, lai tos varētu izmantot jebkur jūsu lietotnē.

```php
// Saglabā savu mainīgo
Flight::set('id', 123);

// citur jūsu lietotnē
$id = Flight::get('id');
```
Lai pārbaudītu, vai mainīgais ir iestatīts, jūs varat izdarīt:

```php
if (Flight::has('id')) {
  // Dari kas
}
```

Jūs varat notīrīt mainīgo šādi:

```php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Lidojums arī izmanto mainīgos konfigurācijas nolūkos.

```php
Flight::set('lidojums.log_errors', true);
```