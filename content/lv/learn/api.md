# Struktūras API Metodes

Flight ir izstrādāts, lai būtu viegli lietojams un saprotams. Zemāk ir pilns
struktūras metožu kopums. Tas sastāv no pamatmetodēm, kas ir parastas
statiskas metodes, un paplašināmajām metodēm, kas ir pievienotas metodes, kuras var filtrēt
vai pārrakstīt.

## Pamatmetodes

Šīs metodes ir būtiskas struktūrai un tās nevar pārrakstīt.

```php
Flight::map(virkne $name, izsaukums $callback, booleāns $pass_route = false) // Izveido pielāgotu struktūras metodi.
Flight::register(virkne $name, virkne $class, masīvs $params = [], ?izsaukums $callback = null) // Reģistrē klasi struktūras metodē.
Flight::unregister(virkne $name) // Atceļ klasi no struktūras metodes.
Flight::before(virkne $name, izsaukums $callback) // Pievieno filtru pirms struktūras metodes.
Flight::after(virkne $name, izsaukums $callback) // Pievieno filtru pēc struktūras metodes.
Flight::path(virkne $path) // Pievieno ceļu, lai automātiski ielādētu klases.
Flight::get(virkne $key) // Iegūst mainīgo vērtību.
Flight::set(virkne $key, mixed $value) // Iestata mainīgo vērtību.
Flight::has(virkne $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(masīvs|virkne $key = []) // Notīra mainīgo.
Flight::init() // Inicializē struktūru pēc noklusējuma iestatījumiem.
Flight::app() // Iegūst lietojumprogrammas objekta instanci.
Flight::request() // Iegūst pieprasījuma objekta instanci.
Flight::response() // Iegūst atbildes objekta instanci.
Flight::router() // Iegūst maršrutētāja objekta instanci.
Flight::view() // Iegūst skata objekta instanci.
```

## Paplašināmās Metodes

```php
Flight::start() // Sāk struktūru.
Flight::stop() // Aptur struktūru un nosūta atbildi.
Flight::halt(int $code = 200, virkne $message = '') // Aptur struktūru ar iespējamo statusa kodu un ziņojumu.
Flight::route(virkne $pattern, izsaukums $callback, booleāns $pass_route = false, virkne $alias = '') // Kartē URL paraugu pieprasījumam.
Flight::post(virkne $pattern, izsaukums $callback, booleāns $pass_route = false, virkne $alias = '') // Kartē POST pieprasījuma URL paraugu pieprasījumam.
Flight::put(virkne $pattern, izsaukums $callback, booleāns $pass_route = false, virkne $alias = '') // Kartē PUT pieprasījuma URL paraugu pieprasījumam.
Flight::patch(virkne $pattern, izsaukums $callback, booleāns $pass_route = false, virkne $alias = '') // Kartē PATCH pieprasījuma URL paraugu pieprasījumam.
Flight::delete(virkne $pattern, izsaukums $callback, booleāns $pass_route = false, virkne $alias = '') // Kartē DELETE pieprasījuma URL paraugu pieprasījumam.
Flight::group(virkne $pattern, izsaukums $callback) // Izveido grupēšanu URL adresēm, paraugam jābūt virknei.
Flight::getUrl(virkne $name, masīvs $params = []) // Ģenerē URL, balstoties uz maršruta aliasu.
Flight::redirect(virkne $url, int $code) // Novirza uz citu URL adresi.
Flight::render(virkne $file, masīvs $data, ?virkne $key = null) // Atveido veidni failā.
Flight::error(Throwable $error) // Sūta HTTP 500 atbildi.
Flight::notFound() // Sūta HTTP 404 atbildi.
Flight::etag(virkne $id, virkne $type = 'string') // Veic ETag HTTP kešošanu.
Flight::lastModified(int $time) // Veic pēdējo modificēto HTTP kešošanu.
Flight::json(mixed $data, int $code = 200, booleāns $encode = true, virkne $charset = 'utf8', int $option) // Sūta JSON atbildi.
Flight::jsonp(mixed $data, virkne $param = 'jsonp', int $code = 200, booleāns $encode = true, virkne $charset = 'utf8', int $option) // Sūta JSONP atbildi.
```

Jebkuras pielāgotas metodes, kas pievienotas ar `map` un `register`, var tikt filtrētas.