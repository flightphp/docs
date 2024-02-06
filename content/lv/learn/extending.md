# Paplašināšana / Konteineri

Flight ir izstrādāts kā paplašināmais ietvars. Ietvars nāk ar iebūvētu metožu un komponentu kopumu, bet tas ļauj jums norādīt savas metodes, reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

## Metožu norādīšana

Lai norādītu savu pielāgoto metodi, jūs izmantojat `map` funkciju:

```php
// Norādīt savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bobs');
```

## Klases reģistrēšana / Konteinerizācija

Lai reģistrētu savu klasi, jūs izmantojat `register` funkciju:

```php
// Reģistrēt savu klasi
Flight::register('lietotājs', User::class);

// Iegūstiet savas klases instanci
$user = Flight::lietotājs();
```

Reģistrēšanas metode arī ļauj jums nodot parametrus savas klases konstruktoram. Tāpēc, ielādējot savu pielāgoto klasi, tā tiks iepriekš inicializēta. Konstruktoram paredzētos parametrus var definēt, nododot papildu masīvu. Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrēt klasi ar konstruktoram paredzētajiem parametriem
Flight::register('db', PDO::class, ['mysql:host=lokālais_dators;dbname=testa', 'lietotājs', 'parole']);

// Iegūstiet savas klases instanci
// Tiks izveidots objekts ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Ja nododat papildu atsauces parametru, tas tiks izpildīts nekavējoties pēc klases konstrukcijas. Tas ļauj jums veikt jebkādas iestatīšanas procedūras jaunajam objektam. Atsauces funkcija ņem vienu parametru - jaunā objekta instanci.

```php
// Atsaucei tiks padots izveidoto objektu
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, katru reizi, kad ielādējat savu klasi, jūs saņemsiet koplietojamo instanci. Ja vēlaties jaunu klases instanci, vienkārši nododiet `false` kā parametru:

```php
// Koplietojama klases instance
$shared = Flight::db();

// Jauna klases instance
$new = Flight::db(false);
```

Ņemiet vērā, ka norādītajām metodēm ir prioritāte salīdzinājumā ar reģistrētajām klasēm. Ja jūs deklarējat abas ar vienādu nosaukumu, tiks izsaukta tikai norādītā metode.