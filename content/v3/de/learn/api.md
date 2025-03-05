# Framework API Methoden

Flight wurde entwickelt, um einfach zu verwenden und zu verstehen zu sein. Die folgende Liste enthält die vollständige
Menge an Methoden für das Framework. Es besteht aus Kernmethoden, die reguläre
statische Methoden sind, und erweiterbaren Methoden, die gemappte Methoden sind, die gefiltert
oder überschrieben werden können.

## Kernmethoden

Diese Methoden sind grundlegend für das Framework und können nicht überschrieben werden.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse für eine Framework-Methode.
Flight::unregister(string $name) // Löscht die Registrierung einer Klasse für eine Framework-Methode.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad für das automatische Laden von Klassen hinzu.
Flight::get(string $key) // Holt eine Variable, die von Flight::set() gesetzt wurde.
Flight::set(string $key, mixed $value) // Setzt eine Variable innerhalb der Flight-Engine.
Flight::has(string $key) // Überprüft, ob eine Variable gesetzt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework mit den Standard Einstellungen.
Flight::app() // Holt die Instanz des Anwendungsobjekts.
Flight::request() // Holt die Instanz des Anfrageobjekts.
Flight::response() // Holt die Instanz des Antwortobjekts.
Flight::router() // Holt die Instanz des Router-Objekts.
Flight::view() // Holt die Instanz des View-Objekts.
```

## Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit einem optionalen Statuscode und einer Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapped ein URL-Muster zu einem Callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapped ein POST-Anfrage-URL-Muster zu einem Callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapped ein PUT-Anfrage-URL-Muster zu einem Callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapped ein PATCH-Anfrage-URL-Muster zu einem Callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapped ein DELETE-Anfrage-URL-Muster zu einem Callback.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppierungen für URLs, das Muster muss ein String sein.
Flight::getUrl(string $name, array $params = []) // Generiert eine URL basierend auf einem Routenalias.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL um.
Flight::download(string $filePath) // Lädt eine Datei herunter.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Template-Datei.
Flight::error(Throwable $error) // Sendet eine HTTP 500-Antwort.
Flight::notFound() // Sendet eine HTTP 404-Antwort.
Flight::etag(string $id, string $type = 'string') // Führt ETag-HTTP-Caching durch.
Flight::lastModified(int $time) // Führt das letzte modifizierte HTTP-Caching durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort und stoppt das Framework.
Flight::onEvent(string $event, callable $callback) // Registriert einen Ereignis-Listener.
Flight::triggerEvent(string $event, ...$args) // Löst ein Ereignis aus.
```

Alle benutzerdefinierten Methoden, die mit `map` und `register` hinzugefügt werden, können ebenfalls gefiltert werden. Für Beispiele, wie man diese Methoden mapped, siehe den [Extending Flight](/learn/extending) Leitfaden.