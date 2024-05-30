# Paplašināšana

Flight ir izstrādāts, lai būtu paplašināmais ietvars. Ietvars ir iekļauts ar noteiktu
kopu noklusējuma metodēm un komponentēm, bet tas ļauj jums atkartot jūsu pašu metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

Ja jums ir nepieciešams DIC (Atkarību ievades konteiners), pāriet uz
[Dependency Injection Container](dependency-injection-container) lapu.

## Metožu atskaitīšana

Lai atkārtotu savu vienkāršo pielāgoto metodi, jums ir jāizmanto `map` funkcija:

```php
// Atkartot savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

Šis tiek izmantots vairāk, kad jums ir nepieciešams padot mainīgos savā metodē, lai iegūtu paredzēto
vērtību. Lai iegūtu konfigurāciju un tad izsauktu jūsu iepriekš konfigurēto klasi, ir labāk izmantot
`register()` metodi tālāk.

## Reģistrēšanas klases

Lai reģistrētu savu klasi un konfigurētu to, jums ir jāizmanto `register` funkcija:

```php
// Reģistrējiet savu klasi
Flight::register('user', User::class);

// Iegūstiet savas klases instanci
$user = Flight::user();
```

Reģistrēšanas metode ļauj jums arī padot parametrus savai klases
konstruktoram. Tāpēc, kad jūs ielādējat savu pielāgoto klasi, tā būs iepriekš inicializēta.
Jūs varat noteikt konstruktoru parametrus, padodot papildu masīvu.
Šeit ir piemērs dabas savienojuma ielādei:

```php
// Reģistrējiet klasi ar konstruktora parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Iegūstiet savas klases instanci
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// un ja jums to vēlāk vajadzētu savā kodā, jūs vienkārši atkārtoti izsaucat to pašu metodi
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja jūs padodat papildu atsauces parametru, tas tiks īstenots tūlīt
pēc klases konstruēšanas. Tas ļauj jums veikt jebkādus iestatīšanas procedūras savam
jaunajam objektam. Atsauces funkcija pieņem vienu parametru, jaunu objekta piemēru.

```php
// Atsauces funkcijai tiks padots konstruētais objekts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, ikreiz, kad ielādējat savu klasi, jūs saņemsiet kopīgu instanci.
Lai iegūtu jaunu klases instanci, vienkārši padodiet `false` kā parametru:

```php
// Kopīga klases instance
$shared = Flight::db();

// Jauna klases instance
$new = Flight::db(false);
```

Jāņem vērā, ka atkārtotajām metodēm ir priekšrocība pāri reģistrētajām klasēm. Ja jūs
deklarējat abus, izmantojot to pašu nosaukumu, tiks izsaukta tikai atkārtotā metode.

## Pārrakstīt ietvara metodes

Flight ļauj jums pārrakstīt tā noklusējuma funkcionalitāti, lai atbilstu jūsu vajadzībām,
neizmainot nevienu kodu.

Piemēram, kad Flight nevar sakrist URL ar maršrutu, tas izsauc `notFound`
metodi, kas nosūta vispārīgu `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgoto 404 lapu
  include 'errors/404.html';
});
```

Flight ļauj arī aizstāt ietvara galvenās komponentes.
Piemēram, jūs varat aizstāt noklusējuma Maršruta klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē Maršruta instanci, tas ielādēs jūsu klasi
$myrouter = Flight::router();
```

Tomēr ietvara metodes, piemēram, `map` un `register`, nevar būt pārrakstītas. Jūs
iegūsit kļūdu, ja mēģināsiet to darīt.