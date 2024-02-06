# Pāradresācijas

Jūs varat pāradresēt pašreizējo pieprasījumu, izmantojot `redirect` metodi un padodot jaunu URL:
```php
Flight::redirect('/jauna/vietas');
```

Pēc noklusējuma Flight nosūta HTTP 303 statusa kodu. Jūs varat izvēlēties iestatīt pielāgotu kodu:
```php
Flight::redirect('/jauna/vietas', 401);
```