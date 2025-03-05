# Rāmis API Metodes

Flight ir izstrādāta, lai būtu viegli lietojama un saprotama. Tālāk ir sniegts pilnīgs
metožu kopums rāmim. Tas sastāv no kodola metodēm, kuras ir parastas
statiskas metodes, un paplašināmām metodēm, kuras ir kartētas metodes, ko var filtrēt
vai pārrakstīt.

## Kodola Metodes

Šīs metodes ir centrālās rāmim un tās nevar tikt pārrakstītas.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Izveido pielāgotu rāmja metodi.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Reģistrē klasi rāmja metodei.
Flight::unregister(string $name) // Atceļ klases reģistrāciju rāmja metodei.
Flight::before(string $name, callable $callback) // Pievieno filtru pirms rāmja metodes.
Flight::after(string $name, callable $callback) // Pievieno filtru pēc rāmja metodes.
Flight::path(string $path) // Pievieno ceļu klases automātiskai ielādēšanai.
Flight::get(string $key) // Iegūst mainīgo, ko iestata Flight::set().
Flight::set(string $key, mixed $value) // Iestata mainīgo Flight dzinī.
Flight::has(string $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(array|string $key = []) // Notīra mainīgo.
Flight::init() // Inicializē rāmi uz noklusējuma iestatījumiem.
Flight::app() // Iegūst lietotnes objekta instanci.
Flight::request() // Iegūst pieprasījuma objekta instanci.
Flight::response() // Iegūst atbildes objekta instanci.
Flight::router() // Iegūst maršrutētāja objekta instanci.
Flight::view() // Iegūst skata objekta instanci.
```

## Paplašināmās Metodes

```php
Flight::start() // Sāk rāmja darbību.
Flight::stop() // Apstājas rāmja darbība un nosūta atbildi.
Flight::halt(int $code = 200, string $message = '') // Apstājas rāmja darbība ar opciju statusa kodu un ziņojumu.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē URL paraugu uz atbildes funkciju.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē POST pieprasījuma URL paraugu uz atbildes funkciju.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē PUT pieprasījuma URL paraugu uz atbildes funkciju.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē PATCH pieprasījuma URL paraugu uz atbildes funkciju.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Kartē DELETE pieprasījuma URL paraugu uz atbildes funkciju.
Flight::group(string $pattern, callable $callback) // Izveido grupēšanu URL, paraugam jābūt virknē.
Flight::getUrl(string $name, array $params = []) // Ģenerē URL, pamatojoties uz maršruta aliasu.
Flight::redirect(string $url, int $code) // Pāradresē uz citu URL.
Flight::download(string $filePath) // Lejupielādē failu.
Flight::render(string $file, array $data, ?string $key = null) // Attēlo veidnes failu.
Flight::error(Throwable $error) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(string $id, string $type = 'string') // Veic ETag HTTP kešatmiņu.
Flight::lastModified(int $time) // Veic pēdējās izmaiņas HTTP kešatmiņu.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSON atbildi.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSONP atbildi.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSON atbildi un apstājas rāmja darbība.
Flight::onEvent(string $event, callable $callback) // Reģistrē notikumu klausītāju.
Flight::triggerEvent(string $event, ...$args) // Izsauc notikumu.
```

Jebkuras pielāgotas metodes, kas pievienotas ar `map` un `register`, var arī tikt filtrētas. Lai iegūtu piemērus, kā kartēt šīs metodes, skatiet [Paplašinot Flight](/learn/extending) ceļvedi.