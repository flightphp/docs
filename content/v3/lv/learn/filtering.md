# Filtrēšana

## Pārskats

Flight ļauj jums filtrēt [kartētos metodes](/learn/extending) pirms un pēc to izsaukšanas.

## Saprašana
Nav iepriekš definētu āķu, kurus jums vajadzētu iegaumēt. Jūs varat filtrēt jebkuru no noklusējuma ietvara metodēm, kā arī jebkuru pielāgotu metožu, kuras esat kartējuši.

Filtra funkcija izskatās šādi:

```php
/**
 * @param array $params Metodei, kas tiek filtrēta, nodotie parametri.
 * @param string $output (tikai v2 izvades buferizēšana) Metodes, kas tiek filtrēta, izvade.
 * @return bool Atgrieziet true/void vai neatgrieziet, lai turpinātu ķēdi, false, lai pārtrauktu ķēdi.
 */
function (array &$params, string &$output): bool {
  // Filtra kods
}
```

Izmantojot nodotās mainīgās, jūs varat manipulēt ar ievades parametriem un/vai izvadi.

Jūs varat likt filtram darboties pirms metodes, izdarot:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Dariet kaut ko
});
```

Jūs varat likt filtram darboties pēc metodes, izdarot:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Dariet kaut ko
});
```

Jūs varat pievienot tik daudz filtru, cik vēlaties, jebkurai metodei. Tie tiks izsaukti secībā, kādā tie ir deklarēti.

Šeit ir filtrēšanas procesa piemērs:

```php
// Kartējiet pielāgotu metodi
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Pievienojiet pirms filtra
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulējiet parametru
  $params[0] = 'Fred';
  return true;
});

// Pievienojiet pēc filtra
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulējiet izvadi
  $output .= " Have a nice day!";
  return true;
});

// Izsauciet pielāgoto metodi
echo Flight::hello('Bob');
```

Šim vajadzētu parādīt:

```
Hello Fred! Have a nice day!
```

Ja esat definējuši vairākus filtrus, jūs varat pārtraukt ķēdi, atgriežot `false`
jebkurā no jūsu filtra funkcijām:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Tas beigs ķēdi
  return false;
});

// Tas netiks izsaukts
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Piezīme:** Kodola metodes, piemēram, `map` un `register`, nevar tikt filtrētas, jo tās tiek izsauktas tieši un ne dinamiski. Skatiet [Extending Flight](/learn/extending), lai iegūtu vairāk informācijas.

## Skatīt arī
- [Extending Flight](/learn/extending)

## Traucējummeklēšana
- Pārliecinieties, ka atgriežat `false` no savām filtra funkcijām, ja vēlaties, lai ķēde apstātos. Ja neatgriežat neko, ķēde turpināsies.

## Izmaiņu žurnāls
- v2.0 - Sākotnējais izdevums.