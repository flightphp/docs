# Methoden des Frameworks

Flight wurde entwickelt, um einfach zu bedienen und zu verstehen zu sein. Im Folgenden finden Sie die vollständige Liste der Methoden für das Framework. Es besteht aus Kernmethoden, die reguläre statische Methoden sind, und erweiterbaren Methoden, die zugeordnete Methoden sind, die gefiltert oder überschrieben werden können.

## Kernmethoden

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse bei einer Framework-Methode.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad zum Laden von Klassen hinzu.
Flight::get(string $key) // Ruft eine Variable ab.
Flight::set(string $key, mixed $value) // Legt eine Variable fest.
Flight::has(string $key) // Überprüft, ob eine Variable festgelegt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework auf die Standardeinstellungen.
Flight::app() // Ruft die Anwendungsobjektinstanz ab.
```

## Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit einem optionalen Statuscode und einer Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Ordnet ein URL-Muster einer Rückruffunktion zu.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppierung für URLs, Muster muss ein String sein.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL um.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Vorlagendatei.
Flight::error(Throwable $error) // Sendet eine HTTP 500-Antwort.
Flight::notFound() // Sendet eine HTTP 404-Antwort.
Flight::etag(string $id, string $type = 'string') // Führt ETag-HTTP-Caching durch.
Flight::lastModified(int $time) // Führt HTTP-Caching für zuletzt geändert durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
```

Alle benutzerdefinierten Methoden, die mit `map` und `register` hinzugefügt wurden, können auch gefiltert werden.