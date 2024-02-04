## Frameworka Metodes

Lidojums ir izstrādāts tā, lai to būtu viegli izmantot un saprast. Zemāk ir pilns ietvars
metožu kopums. Tas sastāv no pamatmetodēm, kas ir parastās
statiskās metodes, un paplašināmās metodes, kas ir kartētas metodes, kuras var filtrēt
vai pārdefinēt.

## Pamatmetodes

```php
Flight::map(virkne $name, atsauksme $callback, boolea $pass_route = false) // Izveido pielāgotu ietvaru metodi.
Flight::register(virkne $name, virkne $class, masīvs $params = [], ?atsauksme $callback = null) // Reģistrē klasi ietvara metodē.
Flight::before(virkne $name, atsauksme $callback) // Pievieno filtru pirms ietvara metodes.
Flight::after(virkne $name, atsauksme $callback) // Pievieno filtru pēc ietvara metodes.
Flight::path(virkne $path) // Pievieno ceļu klases automātiskai ielādei.
Flight::get(virkne $key) // Saņem mainīgo.
Flight::set(virkne $key, sajaukts $value) // Iestata mainīgo.
Flight::has(virkne $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(masīvs|virkne $key = []) // Notīra mainīgo.
Flight::init() // Inicializē ietvaru sākotnēji.
Flight::app() // Saņem pieteikuma objekta instanci
```

## Paplašināmās Metodes

```php
Flight::start() // Uzsāk ietvaru.
Flight::stop() // Aptur ietvaru un nosūta atbildi.
Flight::halt(int $code = 200, virkne $message = '') // Aptur ietvaru ar iespējamo statusa kodu un ziņojumu.
Flight::route(virkne $pattern, atsauksme $callback, boolea $pass_route = false) // Kartē URL paraugu pie atsauksmes.
Flight::group(virkne $pattern, atsauksme $callback) // Izveido grupušanu vietnēm, paraugam jābūt virknei.
Flight::redirect(virkne $url, int $code) // Novirza uz citu URL.
Flight::render(virkne $fails, masīvs $data, ?virkne $key = null) // Atveido veidnes failu.
Flight::error(Throwable $error) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(virkne $id, virkne $type = 'string') // Veic ETag HTTP kešatmiņu.
Flight::lastModified(int $time) // Veic pēdējo modificēto HTTP kešošanu.
Flight::json(sajaukts $data, int $code = 200, boolea $encode = true, virkne $charset = 'utf8', int $option) // Nosūta JSON atbildi.
Flight::jsonp(sajaukts $data, virkne $param = 'jsonp', int $code = 200, boolea $encode = true, virkne $charset = 'utf8', int $option) // Nosūta JSONP atbildi.
```

Ar `map` un `register` pievienotās pielāgotās metodes var arī filtrēt.