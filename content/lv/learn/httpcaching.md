# HTTP Caching

Flight piedāvā iebūvētu atbalstu HTTP līmeņa kešatmiņai. Ja tiek izpildīta kešatmiņas nosacījums, Flight atgriezīs HTTP `304 Nav modificēts` atbildi. Nākamajā reizē, kad klients pieprasa to pašu resursu, viņiem tiks lūgts izmantot vietējo kešatmiņas versiju.

## Pēdējais modificēts

Jūs varat izmantot `lastModified` metodi un padot UNIX laika zīmi, lai iestatītu datumu un laiku, kad lapa tika pēdējo reizi modificēta. Klients turpinās izmantot savu kešatmiņu, līdz pēdējais modificētais vērtība tiek mainīta.

```php
Flight::route('/jaunumi', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešatmiņā.';
});
```

## ETag

`ETag` kešatmiņa ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkuru identifikatoru, ko vēlaties resursam:

```php
Flight::route('/jaunumi', function () {
  Flight::etag('mans-unikālais-id');
  echo 'Šis saturs tiks kešatmiņā.';
});
```

Ņemiet vērā, ka izsaucot vai nu `lastModified`, vai `etag`, Flight iestatīs un pārbaudīs kešatmiņas vērtību. Ja kešatmiņas vērtība ir vienāda starp pieprasījumiem, Flight nekavējoties nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.