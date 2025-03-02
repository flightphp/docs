# JSON

Flight nodrošina atbalstu JSON un JSONP atbildēm. Lai nosūtītu JSON atbildi, jums ir
jāpadod dati, kas tiks pārveidoti par JSON formātu:

```php
Flight::json(['id' => 123]);
```

JSONP pieprasījumiem jūs varam papildus norādīt vietrādi parametra nosaukumu, kuru
izmantojat, lai definētu savu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja neesat norādījis vietrādi parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.