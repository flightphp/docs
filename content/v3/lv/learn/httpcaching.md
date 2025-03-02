# HTTP kešošana

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešošanai. Ja kešošanas nosacījums
ir izpildīts, Flight atgriezīs HTTP `304 Nav modificēts` atbildi. Nākamajā reizē,
kad klienta pieprasīs to pašu resursu, viņiem tiks ierosināts izmantot vietējo
kešotu versiju.

## Pēdējais modificēšanas laiks

Jūs varat izmantot `lastModified` metodi un padot UNIX laikrādi, lai iestatītu datumu
un laiku, kad lapas tika pēdējo reizi modificētas. Klients turpinās izmantot savu kešu līdz
pēdējais modificēšanas vērtība tiks mainīta.

```php
Flight::route('/jaunumi', function () {
  Flight::lastModified(1234567890);
  echo 'Šis saturs tiks kešots.';
});
```

## ETag

`ETag` kešošana ir līdzīga `Last-Modified`, izņemot to, ka jūs varat norādīt jebkādu id,
ko vēlaties izmantot resursam:

```php
Flight::route('/jaunumi', function () {
  Flight::etag('mans-unikālais-id');
  echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka saucot gan `lastModified`, gan `etag`, tiks gan iestatīta, gan pārbaudīta
kešu vērtība. Ja kešu vērtība ir vienāda starp pieprasījumiem, Flight nekavējoties
nosūtīs `HTTP 304` atbildi un pārtrauks apstrādi.