# Filtrēšana

Lidojums ļauj jums filtrēt metodes pirms un pēc to izsaukšanas. Nav iepriekš definētu āķu, ko jums vajadzētu iemācīties atmiņā. Jūs varat filtrēt jebkuru noklusējuma ietvaru metodi, kā arī jebkuras pielāgotas metodes, ko esat atainojis.

Filtrēšanas funkcija izskatās šādi:

```php
function (array &$params, string &$output): bool {
  // Filtrēšanas kods
}
```

Izmantojot padotos mainīgos, jūs varat manipulēt ievades parametrus un/vai izvadi.

Jūs varat ļaut filtram darboties pirms metodes, izmantojot:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat ļaut filtram darboties pēc metodes, izmantojot:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat pievienot tik daudz filtrus, cik vēlaties, jebkurai metodai. Tie tiks izsaukti tādā secībā, kādā tie ir deklarēti.

Šeit ir piemērs par filtrēšanas procesu:

```php
// Atainot pielāgotu metodi
Flight::map('hello', function (string $name) {
  return "Sveiki, $name!";
});

// Pievienot pirms filtru
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulēt parametru
  $params[0] = 'Jānis';
  return true;
});

// Pievienot pēc filtra
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulēt izvadi
  $output .= " Jums laimīgu dienu!";
  return true;
});

// Izsaukt pielāgoto metodi
echo Flight::hello('Roberts');
```

Tas vajadzētu parādīt:

```
Sveiki Jānis! Jums laimīgu dienu!
```

Ja esat definējis vairākus filtrus, jūs varat pārtraukt ķēdi, atgriežot `false`
jebkurā no jūsu filtra funkcijām:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'viens';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'divi';

  // Tas pārtrauks ķēdi
  return false;
});

// Tas netiks izsaukts
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'trīs';
  return true;
});
```

Piezīme, pamata metodes, piemēram, `map` un `register`, nevar būt filtri, jo
tos izsauc tieši, nevis dinamiski.