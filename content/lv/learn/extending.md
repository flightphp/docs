# Paplašināšana

Flight ir izstrādāts, lai būtu paplašināms ietvars. Ietvars tiek piegādāts ar kopu
pēc noklusējuma metodēm un komponentiem, bet tas ļauj jums atspoguļot savas metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

Ja meklējat DIC (Atkarību ievades konteineris), apmeklējiet
[Dependency Injection Container](dependency-injection-container) lapu.

## Metodes kartēšana

Lai atspoguļotu savu vienkāršo pielāgoto metodi, izmantojiet `map` funkciju:

```php
// Atspoguļojiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

Tas tiek izmantots vairāk, kad jums ir jāpadod mainīgie savā metodē, lai iegūtu paredzēto
vērtību. Lai nodotu konfigurāciju un pēc tam izsauktu jūsu iepriekš konfigurēto klasi, ir vairāk ieteicams izmantot `register()` metodi.

## Klases reģistrēšana

Lai reģistrētu savu klasi un konfigurētu to, izmantojiet `register` funkciju:

```php
// Reģistrējiet savu klasi
Flight::register('user', User::class);

// Iegūstiet savas klases eksemplāru
$user = Flight::user();
```

Reģistrēšanas metode arī ļauj jums nodot parametrus savai klases
konstruktoram. Tāpēc, ielādējot savu pielāgoto klasi, tā tiks iepriekš inicializēta.
Jūs varat definēt konstruktoru parametrus, padodot papildu masīvu.
Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrējiet klasi ar konstruktoru parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole']);

// Iegūstiet savas klases eksemplāru
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','lietotājs','parole');
//
$db = Flight::db();

// un ja vēlāk vajadzētu to savā kodā, vienkārši atkārtoti izsauciet to pašu metodi
class DažasKontrolieris {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja jūs nododat papildu atsauces parametru, tas tiks izpildīts nekavējoties
pēc klases izveides. Tas ļauj jums veikt jebkādas iestatīšanas procedūras jūsu
jaunajam objektam. Atsauces funkcija ņem vienu parametru, jaunu objekta piemēru.

```php
// Atsauces parametram tiks nodots izveidots objekts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, katru reizi, kad ielādējat savu klasi, jūs saņemsit koplietojumu.
Lai iegūtu jaunu klases eksemplāru, vienkārši nododiet `false` kā parametru:

```php
// Koplietots šīs klases eksemplārs
$shared = Flight::db();

// Jauns šīs klases eksemplārs
$new = Flight::db(false);
```

Ņemiet vērā, ka atspoguļotām metodēm ir prioritāte pār reģistrētajām klasēm. Ja jūs
deklarējat abas, izmantojot to pašu nosaukumu, tiks izsaukta tikai atspoguļotā metode.

## Esošo ietvara metožu pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai piemērotu jūsu pašu vajadzībām,
neizmainot nevienu kodu.

Piemēram, ja Flight nevar atbilst URL ar maršrutu, tā izsauc `notFound`
metodi, kas nosūta vispārēju `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgoto 404 lapu
  include 'kļūdas/404.html';
});
```

Flight arī ļauj jums aizstāt ietvāra pamata komponentes.
Piemēram, jūs varat aizstāt noklusējuma Router klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', ManaisRouteris::class);

// Kad Flight ielādē Router eksemplāru, tas ielādēs jūsu klasi
$manaisrouteris = Flight::router();
```

Tomēr ietvara metodes, piemēram `map` un `register`, nevar būt pārrakstītas. Jums
radīsies kļūda, ja mēģināsit to izdarīt.