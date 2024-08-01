# Paplašināšana

Flight ir izstrādāts kā paplašināms ietvars. Ietvars ir aprīkots ar iebūvētām metodēm un komponentēm, bet tas ļauj jums pievienot savas metodes, reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

Ja jums ir nepieciešams DIC (Dependency Injection Container), apmeklējiet [Dependency Injection Container](dependency-injection-container) lapu.

## Kartēšanas Metodes

Lai kartētu savu vienkāršo pielāgoto metodi, izmantojiet funkciju `map`:

```php
// Norādiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bobs');
```

Lai izveidotu vienkāršas pielāgotas metodes, ir iespējams veidot standarta funkcijas PHP. Tā ir ieteicamā prakse, jo tajā ir automātiskā pabeigšana IDE vidēs un ir vieglāk lasāma.
Tas būtu ekvivalents augstāk redzamajam kodam:

```php
function hello(string $name) {
  echo "sveiki $name!";
}

hello('Bobs');
```

Šis tiek izmantots vairāk, kad jums ir nepieciešams nodot mainīgos savai metodē, lai iegūtu paredzamo vērtību. Izvietojot metodi `register()` kā zemāk, tas ir vairāk paredzēts, lai nodotu konfigurāciju un pēc tam izsauktu jūsu iepriekš konfigurēto klasi.

## Reģistrēšanas Klases

Lai reģistrētu savu klasi un konfigurētu to, izmantojiet funkciju `register`:

```php
// Reģistrējiet savu klasi
Flight::register('lietotājs', Lietotājs::class);

// Iegūstiet sava objekta piemēru
$manslietotājs = Flight::lietotājs();
```

Reģistrēšanas metode ļauj jums arī nodot parametrus jūsu klases konstruktoram. Tādējādi, ielādējot savu pielāgoto klasi, tā tiks iepriekš inicializēta. Konstruktoram varat definēt parametrus, padodot papildu masīvu.
Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrējiet klasi ar konstruktoram paredzētiem parametriem
Flight::register('db', PDO::class, ['mysql:host=localhosts;dbname=test', 'lietotājs', 'parole']);

// Iegūstiet sava objekta piemēru
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhosts;dbname=test','lietotājs','parole');
//
$db = Flight::db();

// un ja vēlēsities to vēlāk izmantot savā kodā, vienkārši atkal izsauciet to pašu metodi
class KādsKontrolieris {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Ja nododat papildu atsauksmes parametru, tas tiks izpildīts nekavējoties pēc klases izveides. Tas ļauj veikt jebkādas iestatīšanas procedūras jaunajam objektam. Atsauksmes funkcija ņem vienu parametru, jauna objekta piemēru.

```php
// Objekts, kas tika izveidots, tiks nodots atsauksmei
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma katru reizi, kad ielādējat savu klasi, jums tiks nodots koplietotā instance.
Lai iegūtu jaunu klases piemēru, vienkārši nododiet `false` kā parametru:

```php
// Klases koplietotais piemērs
$koplietots = Flight::db();

// Jauns klases piemērs
$jauns = Flight::db(false);
```

Netaisni, ka kartētām metodēm ir priekšrocība pār reģistrētajām klasēm. Ja deklarējat abas, izmantojot to pašu nosaukumu, tiks izsaukta tikai kartētā metode.

## Pārrakstīšanas Ietvara Metodes

Flight ļauj jums pārrakstīt tās noklusētās funkcijas, lai piemērotu tās saviem mērķiem, neiemodificējot nekādu kodu. Jūs varat aplūkot visas metodes, ko varat pārrakstīt [šeit](/learn/api).

Piemēram, kad Flight nevar sakrist URL ar maršrutu, tā izsauc metodi `notFound`, kas nosūta vispārīgu `HTTP 404` atbildi. Šo uzvedību var pārrakstīt, izmantojot metodi `map`:

```php
Flight::map('notFound', function() {
  // Parādiet pielāgoto 404 lapu
  iekļaut 'kļūdas/404.html';
});
```

Flight arī ļauj jums aizstāt ietvara pamata sastāvdaļas.
Piemēram, jūs varat aizstāt noklusējuma maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('maršrutētājs', ManaMaršrutētāja::class);

// Kad Flight ielādē Maršrutētāja piemēru, tas ielādēs jūsu klasi
$manamaršrutētājs = Flight::maršrutētājs();
```

Taču ietvara metodes, piemēram, `map` un `register`, nevar tikt pārrakstītas. Ja mēģināsit to izdarīt, saņemsiet kļūdu.