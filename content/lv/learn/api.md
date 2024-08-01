# Ietvara API Metodes

Flight ir radīts, lai būtu viegli lietojams un saprotams. Zemāk ir pilnīgs
metožu kopa ietvarā. Tā sastāv no pamatmetodēm, kas ir regulāras
statiskās metodes, un paplašināmām metodēm, kas ir atkarinātas metodes, kas var tikt filtrētas
vai pārrakstītas.

## Pamatmetodes

Šīs metodes ir pamata ietvarā un tās nevar tikt pārrakstītas.

```php
Flight::map(virkne $name, callable $callback, bool $pass_route = false) // Izveido pielāgotu ietvara metodi.
Flight::register(virkne $name, virkne $class, masīvs $params = [], ?callable $callback = null) // Reģistrē klasi ietvara metodē.
Flight::unregister(virkne $name) // Atceļ klasi no ietvara metodes.
Flight::before(virkne $name, callable $callback) // Pievieno filtru pirms ietvara metodes.
Flight::after(virkne $name, callable $callback) // Pievieno filtru pēc ietvara metodes.
Flight::path(virkne $path) // Pievieno ceļu klasēm automātiskai ielādei.
Flight::get(virkne $key) // Iegūst mainīgo, ko iestatījis Flight::set().
Flight::set(virkne $key, mixed $value) // Iestata mainīgo Flight dzinējā.
Flight::has(virkne $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(masīvs|virkne $key = []) // Notīra mainīgo.
Flight::init() // Inicializē ietvaru uz tās noklusējuma iestatījumiem.
Flight::app() // Iegūst pieteikumu objekta instanci
Flight::request() // Iegūst pieprasījuma objekta instanci
Flight::response() // Iegūst atbildes objekta instanci
Flight::router() // Iegūst maršrutētāja objekta instanci
Flight::view() // Iegūst skata objekta instanci
```

## Paplašināmas metodes

```php
Flight::start() // Sāk ietvaru.
Flight::stop() // Aptur ietvaru un nosūta atbildi.
Flight::halt(int $code = 200, virkne $message = '') // Aptur ietvaru ar neobligātu statusa kodu un ziņojumu.
Flight::route(virkne $pattern, callable $callback, bool $pass_route = false, virkne $alias = '') // Atkarina URL modeli uz atgriezšanu.
Flight::post(virkne $pattern, callable $callback, bool $pass_route = false, virkne $alias = '') // Atkarina POST pieprasījuma URL modeli uz atgriezšanu.
Flight::put(virkne $pattern, callable $callback, bool $pass_route = false, virkne $alias = '') // Atkarina PUT pieprasījuma URL modeli uz atgriezšanu.
Flight::patch(virkne $pattern, callable $callback, bool $pass_route = false, virkne $alias = '') // Atkarina PATCH pieprasījuma URL modeli uz atgriezšanu.
Flight::delete(virkne $pattern, callable $callback, bool $pass_route = false, virkne $alias = '') // Atkarina DELETE pieprasījuma URL modeli uz atgriezšanu.
Flight::group(virkne $pattern, callable $callback) // Izveido grupu URL, modelim jābūt teksta virknei.
Flight::getUrl(virkne $name, masīvs $params = []) // Ģenerē URL, pamatojoties uz maršruta aliasu.
Flight::redirect(virkne $url, int $code) // Novirza uz citu URL.
Flight::download(virkne $filePath) // Lejupielādē failu.
Flight::render(virkne $file, masīvs $data, ?string $key = null) // Renderē veidni failam.
Flight::error(Throwable $error) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(virkne $id, virkne $type = 'string') // Veic ETag HTTP kešatmiņu.
Flight::lastModified(int $time) // Veic pēdējo modificēto HTTP kešošanu.
Flight::json(mixed $data, int $code = 200, bool $encode = true, virkne $charset = 'utf8', int $option) // Nosūta JSON atbildi.
Flight::jsonp(mixed $data, virkne $param = 'jsonp', int $code = 200, bool $encode = true, virkne $charset = 'utf8', int $option) // Nosūta JSONP atbildi.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, virkne $charset = 'utf8', int $option) // Nosūta JSON atbildi un aptur ietvaru.
```

Jebkuras pielāgotas metodes, kas pievienotas ar `map` un `register`, var tikt filtrētas. Piemēru par to, kā atkarināt šīs metodes, skatīt [Paplašinot Flight](/learn/extending) rokasgrāmatā.