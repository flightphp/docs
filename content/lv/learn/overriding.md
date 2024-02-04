# Pārrakstīšana

Lidojums ļauj jums pārrakstīt noklusējuma funkcionalitāti, lai pielāgotu to saviem vajadzībām,
neizmainot nevienu kodu.

Piemēram, kad Lidojums nevar atrast atbilstību URL pievienojumam, tas izsauc `notFound`
metodi, kas nosūta vispārīgu `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgotu 404 lapu
  include 'errors/404.html';
});
```

Lidojums arī ļauj jums aizstāt pamata sastāvdaļas no ietvariem.
Piemēram, jūs varat aizstāt noklusējuma Maršruta klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Lidojums ielādē Maršruta instanci, tas ielādēs jūsu klasi
$myrouter = Flight::router();
```

Ietvara metodes, piemēram, `map` un `register`, tomēr nevar tikt pārrakstītas. Jums
radīsies kļūda, ja mēģināsiet to darīt.