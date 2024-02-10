# Methoden der Framework-API

Flight wurde entwickelt, um einfach zu bedienen und zu verstehen zu sein. Hier ist die vollständige
Liste der Methoden für das Framework. Diese besteht aus Kernmethoden, die reguläre
statische Methoden sind, und erweiterbaren Methoden, die zugeordnete Methoden sind, die gefiltert
oder überschrieben werden können.

## Kernmethoden

Diese Methoden sind wesentlich für das Framework und können nicht überschrieben werden.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse bei einer Framework-Methode.
Flight::unregister(string $name) // Entfernt eine Klasse von einer Framework-Methode.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad zum Laden von Klassen hinzu.
Flight::get(string $key) // Ruft eine Variable ab.
Flight::set(string $key, mixed $value) // Legt eine Variable fest.
Flight::has(string $key) // Überprüft, ob eine Variable festgelegt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework auf seine Standardwerte.
Flight::app() // Ruft die Objektinstanz der Anwendung ab
Flight::request() // Ruft die Objektinstanz der Anfrage ab
Flight::response() // Ruft die Objektinstanz der Antwort ab
Flight::router() // Ruft die Objektinstanz des Routers ab
Flight::view() // Ruft die Objektinstanz der Ansicht ab
```

## Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit optionalen Statuscode und Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster einer Rückruffunktion zu.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster für POST-Anfragen einer Rückruffunktion zu.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster für PUT-Anfragen einer Rückruffunktion zu.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster für PATCH-Anfragen einer Rückruffunktion zu.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster für DELETE-Anfragen einer Rückruffunktion zu.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppierungen für URLs, Muster muss ein String sein.
Flight::getUrl(string $name, array $params = []) // Generiert eine URL basierend auf einem Routen-Alias.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL weiter.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Vorlagendatei.
Flight::error(Throwable $error) // Sendet eine HTTP 500 Antwort.
Flight::notFound() // Sendet eine HTTP 404 Antwort.
Flight::etag(string $id, string $type = 'string') // Führt ETag-HTTP-Caching durch.
Flight::lastModified(int $time) // Führt HTTP-Caching für zuletzt geändert durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
```

Alle benutzerdefinierten Methoden, die mit `map` und `register` hinzugefügt wurden, können auch gefiltert werden.