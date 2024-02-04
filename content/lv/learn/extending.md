# Paplašināšana / Konteineri

Flight ir izstrādāts, lai būtu paplašināms ietvars. Ietvars nāk ar kopu
noklusējuma metodēm un komponentēm, bet ļauj jums atkartot savas metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

## Kartēšanas Metodes

Savas pielāgotās metodes atkārtot kartējot, izmantojiet funkciju `map`:

```php
// Kartējiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

## Reģistrēšanas Klases / Konteinerizācija

Lai reģistrētu savu klasi, izmantojiet funkciju `register`:

```php
// Reģistrējiet savu klasi
Flight::register('lietotājs', Lietotājs::klase);

// Iegūstiet sava objekta piemēru
$user = Flight::user();
```

Reģistrēšanas metode arī ļauj nodot parametrus jūsu klases konstruktoram.
Tāpēc, ielādējot savu pielāgoto klasi, tā būs iepriekš inicializēta.
Jūs varat definēt konstruktora parametrus, nododot papildu masīvu.
Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrējiet klasi ar konstruktora parametriem
Flight::register('db', PDO::klase, ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole']);

// Iegūstiet sava objekta piemēru
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Ja nododat papildu atsauces parametru, tas tiks izpildīts nekavējoties
pēc klases izveides. Tas ļauj jums veikt jebkādas iestatīšanas procedūras savam
jaunajam objektam. Atsauces funkcija ņem vienu parametru, jauno objekta piemēru.

```php
// Atsauces tiks nodots objekts, kas tika izveidots
Flight::register(
  'db',
  PDO::klase,
  ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, katru reizi, kad ielādējat savu klasi, jūs saņemsiet koplietojuma piemēru.
Lai iegūtu jaunu klases piemēru, vienkārši nododiet `false` kā parametru:

```php
// Klases koplietojuma piemērs
$shared = Flight::db();

// Klases jauns piemērs
$jauns = Flight::db(false);
```

Ņemiet vērā, ka kartētām metodēm ir priekšrocība pār reģistrētām klasēm. Ja jūs
deklarējat abas, izmantojot vienādu nosaukumu, tiks izsaukta tikai atkartotā metode.