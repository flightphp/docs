## Kernmethoden

Diese Methoden sind Kernfunktionen des Frameworks und können nicht überschrieben werden.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse für eine Framework-Methode.
Flight::unregister(string $name) // Hebt die Registrierung einer Klasse für eine Framework-Methode auf.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad für das automatische Laden von Klassen hinzu.
Flight::get(string $key) // Ruft eine variable ab, die durch Flight::set() festgelegt wurde.
Flight::set(string $key, mixed $value) // Legt eine Variable innerhalb des Flight-Motors fest.
Flight::has(string $key) // Überprüft, ob eine Variable festgelegt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework auf seine Standardwerte.
Flight::app() // Ruft die Objektinstanz der Anwendung ab
Flight::request() // Ruft die Objektinstanz des Requests ab
Flight::response() // Ruft die Objektinstanz der Antwort ab
Flight::router() // Ruft die Objektinstanz des Routers ab
Flight::view() // Ruft die Objektinstanz der Ansicht ab
```

## Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit einem optionalen Statuscode und einer Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet einem Callback ein URL-Muster zu.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet einem Callback ein URL-Muster für POST-Anforderungen zu.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet einem Callback ein URL-Muster für PUT-Anforderungen zu.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet einem Callback ein URL-Muster für PATCH-Anforderungen zu.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet einem Callback ein URL-Muster für DELETE-Anforderungen zu.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppierungen für URLs, wobei das Muster ein String sein muss.
Flight::getUrl(string $name, array $params = []) // Generiert eine URL basierend auf einem Routenalias.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL um.
Flight::download(string $filePath) // Lädt eine Datei herunter.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Vorlagendatei.
Flight::error(Throwable $error) // Sendet eine HTTP-500-Antwort.
Flight::notFound() // Sendet eine HTTP-404-Antwort.
Flight::etag(string $id, string $type = 'string') // Führt die ETag-HTTP-Cachebildung durch.
Flight::lastModified(int $time) // Führt die zuletzt geänderte HTTP-Cachebildung durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort und stoppt das Framework.
```

Alle benutzerdefinierten Methoden, die mit `map` und `register` hinzugefügt wurden, können auch gefiltert werden. Beispiele, wie diese Methoden zugeordnet werden können, finden Sie im [Flight erweitern](/learn/extending) Leitfaden.