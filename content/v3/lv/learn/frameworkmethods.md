# Framework Metodes

Flight ir izstrādāts, lai būtu viegli lietojams un saprotams. Zemāk ir pilns
metožu kopums ietvariem. Tas sastāv no pamatmetodēm, kas ir parastie
statiskie metodes, un paplašināmās metodes, kas ir kartētas metodes, kurām var piemērot filtrus
vai pārrakstīt.

## Pamatmetodes

```php
Flight::map(virkne $nosaukums, callable $atgriezamā_virziena_funkcija, bool $ietekme_uz_marsrutu = false) // Izveido pielāgotu ietvaru metodi.
Flight::register(virkne $nosaukums, string $klase, masīvs $parametri = [], ?callable $atgriezamā_virziena_funkcija = null) // Reģistrē klasi ietvaru metodē.
Flight::before(virkne $nosaukums, callable $atgriezamā_virziena_funkcija) // Pievieno filtru pirms ietvaru metodes.
Flight::after(virkne $nosaukums, callable $atgriezamā_virziena_funkcija) // Pievieno filtru pēc ietvaru metodes.
Flight::path(virkne $ceļš) // Pievieno ceļu automātiskai klasielu ielādei.
Flight::get(virkne $atslēga) // Iegūst mainīgo.
Flight::set(virkne $atslēga, mixed $vertība) // Iestata mainīgo.
Flight::has(virkne $atslēga) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(masīvs|virkne $atslēga = []) // Nodzēš mainīgo.
Flight::init() // Inicializē ietvaru tās noklusētajos iestatījumos.
Flight::app() // Iegūst aplikācijas objekta instanci
```

## Paplašināmās metodes

```php
Flight::start() // Sāk ietvaru.
Flight::stop() // Aptur ietvaru un nosūta atbildi.
Flight::halt(int $kods = 200, virkne $ziņojums = '') // Aptur ietvaru ar neobligātu statusa kodu un ziņojumu.
Flight::route(virkne $parauga, callable $atgriezamā_virziena_funkcija, bool $ietekme_uz_marsrutu = false) // Kartē URL paraugu atpakaļsaukumam.
Flight::group(virkne $parauga, callable $atgriezamā_virziena_funkcija) // Izveido grupēšanu URL, paraugs ir jābūt virknei.
Flight::redirect(virkne $url, int $kods) // Novirza uz citu URL.
Flight::render(virkne $fails, masīvs $datus, ?string $atslēga = null) // Atveido veidnes failu.
Flight::error(Throwable $kļūda) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(virkne $id, virkne $tips = 'string') // Veic ETag HTTP kešošanu.
Flight::lastModified(int $laiks) // Veic pēdējo modificēto HTTP kešošanu.
Flight::json(mixed $datus, int $kods = 200, bool $kodēt = true, virkne $kodēšanas_kopa = 'utf8', int $opcija) // Nosūta JSON atbildi.
Flight::jsonp(mixed $datus, virkne $param = 'jsonp', int $kods = 200, bool $kodēt = true, virkne $kodēšanas_kopa = 'utf8', int $opcija) // Nosūta JSONP atbildi.
```

Jebkuras pielāgotas metodes, kas pievienotas ar `map` un `register`, var arī tikt filtrētas.