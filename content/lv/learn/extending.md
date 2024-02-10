# Paplašināšana / Konteineri

Flight ir izstrādāts, lai būtu paplašināms ietvars. Ietvars tiek piegādāts ar kopa
no noklusējuma metodēm un komponentēm, bet tas ļauj jums noīmēt savas paša metodes,
reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

## Kartošanas Metodes

Lai iemērotu savu vienkāršo pielāgoto metodi, izmantojiet `map` funkciju:

```php
// Iemērojiet savu metodi
Flight::map('hello', function (string $name) {
  echo "sveiki $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

Šo biežāk izmantojat, kad ir nepieciešams padot mainīgos savai metodai, lai iegūtu gaidīto
vērtību. Izmantojot `register()` metodi tālāk, ir vairāk paredzēts konfigurācijas padot
un tad izsaukt savu iepriekš konfigurēto klasi. 

## Klasu Reģistrēšana / Konteinervirzīšana

Lai reģistrētu savu paša klasi un to konfigurētu, izmantojiet `register` funkciju:

```php
// Reģistrējiet savu klasu
Flight::register('lietotājs', Lietotājs::class);

// Iegūstiet savas klases instanci
lietotājs = Flight::user();
```

Reģistrēšanas metode arī ļauj jums padot parametrus savas klases konstruktoram.
Tātad, ielādējot savu pielāgoto klasi, tā tiks iepriekš inicializēta.
Jūs varat definēt konstruktoram parametrus, padodot papildu masīvu.
Šeit ir piemērs, kā ielādēt datu bāzes savienojumu:

```php
// Reģistrējiet klasi ar konstruktoram parametriem
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole']);

// Iegūstiet savas klases instanci
// Tas izveidos objektu ar definētajiem parametriem
//
// new PDO('mysql:host=localhost;dbname=test','lietotājs','parole');
//
db = Flight::db();

// un ja jums vajadzētu to vēlāk savā kodā, vienkārši atkal izsaukt to pašu metodi
klase SomeController {
  publika funkcija __construct() {
	Šis->db = Flight::db();
  }
}
```

Ja jūs padodat papildu atrodi masīva parametru, tas tiks izpildīts nekavējoties
pēc klašu konstrukcijas. Tas ļauj jums veikt visus iestatīšanas procedūras jūsu
jaunajam objektam. Atrodlaukuma funkcija pieņem vienu parametru, jaunu objekta piemēru.

```php
// Atrodlaukumam tiks padots konstruētais objekts
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'lietotājs', 'parole'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Pēc noklusējuma, katru reizi, kad ielādējat savu klasi, jūs saņemsiet koplietojamo instanci.
Lai iegūtu jaunu klases instanci, vienkārši padodiet `false` kā parametru:

```php
// Klases koplietotā instances
koplietotais = Flight::db();

// Jauna klases instances
jauns = Flight::db(false);
```

Ņemiet vērā, ka iemāpota metodei ir priekšroka pār reģistrētajām klasēm. Ja jūs
deklarējat abas, izmantojot to pašu nosaukumu, tiks izsaukta tikai iemāpota metode.

## Pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai piemērotu savas vajadzības,
neiestrādājot nekādu kodu.

Piemēram, kad Flight nevar saskaņot URL ar maršrutu, tā izsauc `notFound`
metodi, kas nosūta ģenerisku `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādiet pielāgoto 404 lapu
  include 'kļūdas/404.html';
});
```

Flight arī ļauj jums aizstāt ietvaru pamata komponentes.
Piemēram, jūs varat aizstāt noklusējuma Maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('maršrutētājs', ManaMaršrutētājs::class);

// Kad Flight ielādē Maršrutētāja instanci, tas ielādēs jūsu klasi
manamaršrutētājs = Flight::maršrutētājs();
```

Ietvara metodes, piemēram, `map` un `register`, tomēr nevar būt pārrakstītas. Jūs saņemsiet
kļūdu, ja mēģināsit to izdarīt.