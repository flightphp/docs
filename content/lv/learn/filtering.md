# Filtrēšana

Lidojums ļauj jums filtrēt metodes pirms un pēc to izsaukšanas. Nav iepriekš definētu vadu, kurus jums ir jāiemācās. Jūs varat filtrēt jebkuru noklusējuma struktūras metodi, kā arī jebkuru pielāgoto metodi, ko esat pievienojis.

Filtrēšanas funkcija izskatās šādi:

```php
function (array &$params, string &$output): bool {
  // Filtrēšanas kods
}
```

Izmantojot padotās mainīgās, jūs varat manipulēt ievades parametriem un/vai izvades rezultātu.

Jūs varat palaist filtru pirms metodes, izpildot:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat palaist filtru pēc metodes, izpildot:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat pievienot tik daudz filtrus, cik vēlaties, jebkurai metodē. Viņi tiks izsaukti tā, kā tie ir deklarēti.

Šeit ir piemērs filtrēšanas procesam:

```php
// Pievienot pielāgotu metodi
Flight::map('hello', function (string $name) {
  return "Sveiki, $name!";
});

// Pievienot pirms filtra
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulēt parametru
  $params[0] = 'Fred';
  return true;
});

// Pievienot pēc filtra
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulēt rezultātu
  $output .= " Jauku dienu!";
  return true;
});

// Izsaukt pielāgoto metodi
echo Flight::hello('Bob');
```

Tas jāattēlo:

```
Sveiki, Fred! Jauku dienu!
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

Piezīme, pamata metodes, piemēram, `map` un `register`, nevar tikt filtrētas, jo tās
tiek izsauktas tieši un netiek izsauktas dinamiski.