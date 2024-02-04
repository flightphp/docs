# Filtrēšana

Lidojums ļauj jums filtrēt metodēs pirms un pēc to izsaukšanas. Nav iepriekš definētu āķu, ko jums vajadzētu iemācīties atmiņā. Jūs varat filtrēt jebkuru noklusēto struktūras metodi, kā arī jebkuru pielāgoto metodi, ko esat kartējis.

Filtra funkcija izskatās šādi:

```php
function (array &$params, string &$output): bool {
  // Filtra kods
}
```

Izmantojot padotās mainīgās, jūs varat manipulēt ieejas parametriem un/vai izvadi.

Jūs varat izpildīt filtru pirms metodes, izmantojot:

```php
Lidojums::before('sākt', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat izpildīt filtru pēc metodes, izmantojot:

```php
Lidojums::after('sākt', function (array &$params, string &$output): bool {
  // Darīt kaut ko
});
```

Jūs varat pievienot tik daudz filtru, cik vēlaties, jebkurai metodai. Viņi tiks izsaukti secībā, kādā tie ir norādīti.

Šeit ir filtrēšanas procesa piemērs:

```php
// Nozīmēt pielāgotu metodi
Lidojums::kartēt('sveiki', function (string $vārds) {
  atgriezt "Sveiki, $vārds!";
});

// Pievienot pirms filtra
Lidojums::before('sveiki', function (array &$params, string &$output): bool {
  // Manipulēt parametru
  $params[0] = 'Freds';
  atgriezt true;
});

// Pievienot pēc filtra
Lidojums::after('sveiki', function (array &$params, string &$output): bool {
  // Manipulēt izvadi
  $output .= " Jauku dienu!";
  atgriezt true;
});

// Izsaukt pielāgoto metodi
echo Lidojums::sveiki('Bobs');
```

Tas parādīs:

```
Sveiki Freds! Jauku dienu!
```

Ja esat definējis vairākus filtrus, jūs varat pārtraukt ķēdi, atgriežot `false`
jebkurā no saviem filtra funkcijām:

```php
Lidojums::before('sākt', function (array &$params, string &$output): bool {
  echo 'viens';
  atgriezt true;
});

Lidojums::before('sākt', function (array &$params, string &$output): bool {
  echo 'divi';

  // Tas pārtrauks ķēdi
  atgriezt false;
});

// Tas netiks izsaukts
Lidojums::before('sākt', function (array &$params, string &$output): bool {
  echo 'trīs';
  atgriezt true;
});
```

Piezīme, pamata metodes, piemēram, `kartēt` un `reģistrēt`, nevar tikt filtrētas, jo tās
tiek izsauktas tieši un netiek izsauktas dinamiski.