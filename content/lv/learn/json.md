# JSON

`Flight` nodrošina atbalstu JSON un JSONP atbildēm. Lai nosūtītu JSON atbildi, jums ir jāpadod kādi dati, kas jāpārveido par JSON:

```php
Flight::json(['id' => 123]);
```

JSONP pieprasījumiem jūs pēc izvēles varat padot vaicājuma parametra nosaukumu, ko izmantojat, lai definētu savu atsauces funkciju:

```php
Flight::jsonp(['id' => 123], 'q');
```

Tātad, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

```javascript
my_func({"id":123});
```

Ja jūs nepieliekiet vaicājuma parametra nosaukumu, tas pēc noklusējuma būs `jsonp`.