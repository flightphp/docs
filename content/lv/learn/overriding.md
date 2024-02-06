# Pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai pielāgotu to saviem vajadzībām,
nevieramies mainīt nekādu kodu.

Piemēram, kad Flight nevar atrast URL, kas atbilst maršrutam, tas izsauc metodi `notFound`,
kura nosūta vispārēju `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību,
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgotu 404 lapu
  include 'errors/404.html';
});
```

Flight arī ļauj jums aizstāt pamatkomponentes ietvaru.
Piemēram, jūs varat aizstāt noklusējuma maršrutētāja klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē Maršrutētāja instanci, tas ielādēs jūsu klasi
$myrouter = Flight::router();
```

Ietvara metodes, piemēram, `map` un `register`, tomēr nevar tikt pārrakstītas. Jums
būs kļūda, ja mēģināsit to darīt.