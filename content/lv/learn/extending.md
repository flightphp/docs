# Paplašināšana / Konteineri

Flight ir izstrādāts, lai būtu paplašināms ietvars. Ietvars nāk ar komplektu
noklusējuma metožu un komponentiem, bet tas ļauj jums norādīt savas metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

## Kartēšanas Metodes

Lai norādītu savu vienkāršo pielāgoto metodi, izmantojiet `map` funkciju:

```php
// Norādiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Jānis');
```

Šis tiek izmantots vairāk, ja jums ir nepieciešams nodot mainīgos savai metodai, lai iegūtu gaidīto
vērtību. Lai nodotu iestatīšanu, izmantojiet `register()` metodi tālāk, kas ir vairāk paredzēta konfigurācijas
nodrošināšanai un pēc tam izsaukt jūsu iepriekš konfigurēto klasi.

## Reģistrēšana Klašu / Konteinerizācija

Lai reģistrētu savu klasi un konfigurētu to, izmantojiet `register` funkciju:

```php
// Reģistrējiet savu klasi
Flight::register('lietotājs', Lietotājs::class);

// Iegūstiet savas klases piemēru
$user = Flight::lietotājs();
```

Reģistrēšanas metode arī ļauj nodot parametrus jūsu klases
konstruktoram. Tāpēc, kad ielādējat savu pielāgoto klasi, tā tiks iepriekš inicializēta.
Konstruktoram varat definēt parametrus, padodot papildu masīvu.
Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrē klasi ar konstruktoram parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole']);

// Iegūstiet savas klases piemēru
// Tiks izveidots objekts ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','lietotājs','parole');
//
$db = Flight::db();

// un ja vēlāk jūsu kodā tas būtu nepieciešams, vienkārši izsauciet to pašu metodi vēlreiz
class KādsKontrolieris {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja nododat papildu atsauces parametru, tas tiks nekavējoties izpildīts
pēc klases konstruēšanas. Tas ļauj veikt jebkādas iestatīšanas procedūras priekš
jaunā objekta. Atsauces funkcija paņem vienu parametru, jaunu objekta piemēru.

```php
// Atsauces parametram tiks šis izveidots objekts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, katru reizi, kad ielādējat savu klasi, jūs saņemsiet koplietojamo piemēru.
Lai iegūtu jaunu klases piemēru, vienkārši nododiet `false` kā parametru:

```php
// Koplietots klases piemērs
$shared = Flight::db();

// Jauns klases piemērs
$new = Flight::db(false);
```

Ņemiet vērā, ka norādītajām metodēm ir prioritāte pār reģistrētajām klasēm. Ja
deklarējat abas, izmantojot vienādu nosaukumu, tikai kartētā metode tiks izsaukta.

## Pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai atbilstu jūsu pašu vajadzībām,
neizmainot nevienu kodu.

Piemēram, kad Flight nevar atrast URL, lai saistītu ar maršrutu, tas izsauks `notFound`
metodi, kas nosūta vispārīgu `HTTP 404` atbildi. Varat pārrakstīt šo darbību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgoto 404 lapu
  iekļaut 'kļūdas/404.html';
});
```

Flight arī ļauj jums aizstāt ietvarā esošās galvenās komponentes.
Piemēram, jūs varat aizstāt noklusējuma Maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MansMaršrutētājs::class);

// Kad Flight ielādē Maršrutētāja instanci, tas ielādēs jūsu klasi
$manisMaršrutētājs = Flight::router();
```

Ietvara metodes, piemēram, `map` un `register`, tomēr nevar būt pārrakstītas. Jums
tiks kļūda, ja mēģināsiet to darīt.