# Pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai pielāgotu to savām vajadzībām,
neiesaistoties tajā nekādā veidā.

Piemēram, kad Flight nevar sakrist ar URL adresi maršrutam, tas izsauc `notFound`
metodi, kas nosūta vispārēju `HTTP 404` atbildi. Jūs varat pārrakstīt šo darbību
izmantojot `map` metodi:

```php
Flight::map('notFound', function() {
  // Parādīt pielāgoto 404. lapu
  include 'errors/404.html';
});
```

Flight arī ļauj jums aizstāt pamata framework komponentes.
Piemēram, jūs varat aizstāt noklusējuma Router klasi ar savu pielāgoto klasi:

```php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', MyRouter::class);

// Kad Flight ielādē Router instanci, tā ielādēs jūsu klasi
$myrouter = Flight::router();
```

Framework metodēm, piemēram, `map` un `register`, tomēr nevar pārrakstīt. Jums
iegūsiet kļūdu, ja mēģināsiet to izdarīt.