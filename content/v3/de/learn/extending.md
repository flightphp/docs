# Erweitern

## Überblick

Flight ist so konzipiert, dass es ein erweiterbares Framework ist. Das Framework kommt mit einer Reihe von Standardmethoden und -komponenten, erlaubt es Ihnen jedoch, Ihre eigenen Methoden zuzuordnen, Ihre eigenen Klassen zu registrieren oder sogar bestehende Klassen und Methoden zu überschreiben.

## Verständnis

Es gibt 2 Wege, wie Sie die Funktionalität von Flight erweitern können:

1. Methoden zuordnen - Dies wird verwendet, um einfache benutzerdefinierte Methoden zu erstellen, die Sie von überall in Ihrer Anwendung aufrufen können. Diese werden typischerweise für Hilfsfunktionen verwendet, die Sie von überall in Ihrem Code aufrufen möchten. 
2. Klassen registrieren - Dies wird verwendet, um Ihre eigenen Klassen bei Flight zu registrieren. Dies wird typischerweise für Klassen verwendet, die Abhängigkeiten haben oder Konfiguration erfordern.

Sie können auch bestehende Framework-Methoden überschreiben, um ihr Standardverhalten zu ändern, um den Bedürfnissen Ihres Projekts besser zu entsprechen. 

> Wenn Sie nach einem DIC (Dependency Injection Container) suchen, schauen Sie auf der [Dependency Injection Container](/learn/dependency-injection-container)-Seite vorbei.

## Grundlegende Verwendung

### Framework-Methoden überschreiben

Flight erlaubt es Ihnen, seine Standardfunktionalität zu überschreiben, um Ihren eigenen Bedürfnissen zu entsprechen, ohne Code zu modifizieren. Sie können alle überschreibbaren Methoden [unten](#mappable-framework-methods) ansehen.

Zum Beispiel ruft Flight, wenn es eine URL nicht einer Route zuordnen kann, die `notFound`-Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map`-Methode verwenden:

```php
Flight::map('notFound', function() {
  // Anzeigen einer benutzerdefinierten 404-Seite
  include 'errors/404.html';
});
```

Flight erlaubt es Ihnen auch, Kernkomponenten des Frameworks zu ersetzen.
Zum Beispiel können Sie die Standard-Router-Klasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Erstellen Sie Ihre benutzerdefinierte Router-Klasse
class MyRouter extends \flight\net\Router {
	// Methoden hier überschreiben
	// Zum Beispiel eine Abkürzung für GET-Anfragen, um die
	// Pass-Route-Funktion zu entfernen
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird Ihre Klasse geladen
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie es versuchen (sehen Sie wieder [unten](#mappable-framework-methods) für eine Liste der Methoden).

### Zuordbare Framework-Methoden

Das Folgende ist die vollständige Menge der Methoden für das Framework. Es besteht aus Kernmethoden, die reguläre statische Methoden sind, und erweiterbaren Methoden, die zugeordnete Methoden sind, die gefiltert oder überschrieben werden können.

#### Kernmethoden

Diese Methoden sind zentral für das Framework und können nicht überschrieben werden.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse für eine Framework-Methode.
Flight::unregister(string $name) // Entregistriert eine Klasse für eine Framework-Methode.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad für das Autoloading von Klassen hinzu.
Flight::get(string $key) // Holt eine Variable, die von Flight::set() gesetzt wurde.
Flight::set(string $key, mixed $value) // Setzt eine Variable im Flight-Engine.
Flight::has(string $key) // Überprüft, ob eine Variable gesetzt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework mit seinen Standardeinstellungen.
Flight::app() // Holt die Anwendungsobjekt-Instanz
Flight::request() // Holt die Request-Objekt-Instanz
Flight::response() // Holt die Response-Objekt-Instanz
Flight::router() // Holt die Router-Objekt-Instanz
Flight::view() // Holt die View-Objekt-Instanz
```

#### Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit einem optionalen Statuscode und einer Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein URL-Muster einem Callback zu.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein POST-Request-URL-Muster einem Callback zu.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein PUT-Request-URL-Muster einem Callback zu.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein PATCH-Request-URL-Muster einem Callback zu.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Ordnet ein DELETE-Request-URL-Muster einem Callback zu.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppierungen für URLs, das Muster muss ein String sein.
Flight::getUrl(string $name, array $params = []) // Generiert eine URL basierend auf einem Route-Alias.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL um.
Flight::download(string $filePath) // Lädt eine Datei herunter.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Template-Datei.
Flight::error(Throwable $error) // Sendet eine HTTP-500-Antwort.
Flight::notFound() // Sendet eine HTTP-404-Antwort.
Flight::etag(string $id, string $type = 'string') // Führt ETag-HTTP-Caching durch.
Flight::lastModified(int $time) // Führt letztes-Änderungs-HTTP-Caching durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort und stoppt das Framework.
Flight::onEvent(string $event, callable $callback) // Registriert einen Event-Listener.
Flight::triggerEvent(string $event, ...$args) // Löst ein Event aus.
```

Jede benutzerdefinierte Methode, die mit `map` und `register` hinzugefügt wurde, kann auch gefiltert werden. Für Beispiele, wie man diese Methoden filtert, siehe die [Filtering Methods](/learn/filtering)-Anleitung.

#### Erweiterbare Framework-Klassen

Es gibt mehrere Klassen, deren Funktionalität Sie durch Erweiterung und Registrierung Ihrer eigenen Klasse überschreiben können. Diese Klassen sind:

```php
Flight::app() // Anwendungsklasse - erweitern Sie die flight\Engine-Klasse
Flight::request() // Request-Klasse - erweitern Sie die flight\net\Request-Klasse
Flight::response() // Response-Klasse - erweitern Sie die flight\net\Response-Klasse
Flight::router() // Router-Klasse - erweitern Sie die flight\net\Router-Klasse
Flight::view() // View-Klasse - erweitern Sie die flight\template\View-Klasse
Flight::eventDispatcher() // Event-Dispatcher-Klasse - erweitern Sie die flight\core\Dispatcher-Klasse
```

### Benutzerdefinierte Methoden zuordnen

Um Ihre eigene einfache benutzerdefinierte Methode zuzuordnen, verwenden Sie die `map`-Funktion:

```php
// Ordnen Sie Ihre Methode zu
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

Während es möglich ist, einfache benutzerdefinierte Methoden zu erstellen, wird empfohlen, einfach Standardfunktionen in PHP zu erstellen. Dies hat Autovervollständigung in IDEs und ist einfacher zu lesen.
Das Äquivalent des obigen Codes wäre:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Dies wird mehr verwendet, wenn Sie Variablen in Ihre Methode übergeben müssen, um einen erwarteten Wert zu erhalten. Die Verwendung der `register()`-Methode wie unten ist mehr für das Übergeben von Konfiguration und dann das Aufrufen Ihrer vorkonfigurierten Klasse.

### Benutzerdefinierte Klassen registrieren

Um Ihre eigene Klasse zu registrieren und sie zu konfigurieren, verwenden Sie die `register`-Funktion. Der Vorteil, den dies gegenüber map() hat, ist, dass Sie dieselbe Klasse wiederverwenden können, wenn Sie diese Funktion aufrufen (wäre hilfreich mit `Flight::db()`, um dieselbe Instanz zu teilen).

```php
// Registrieren Sie Ihre Klasse
Flight::register('user', User::class);

// Holen Sie eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die register-Methode erlaubt es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse zu übergeben.
Wenn Sie also Ihre benutzerdefinierte Klasse laden, wird sie voreingestellt initialisiert.
Sie können die Konstruktor-Parameter definieren, indem Sie ein zusätzliches Array übergeben.
Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

```php
// Klasse mit Konstruktor-Parametern registrieren
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Holen Sie eine Instanz Ihrer Klasse
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// und wenn Sie es später in Ihrem Code benötigen, rufen Sie einfach dieselbe Methode erneut auf
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Wenn Sie einen zusätzlichen Callback-Parameter übergeben, wird er unmittelbar nach der Klassenkonstruktion ausgeführt. Dies erlaubt es Ihnen, alle Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Callback-Funktion nimmt einen Parameter: eine Instanz des neuen Objekts.

```php
// Der Callback wird das konstruierte Objekt übergeben
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie bei jedem Laden Ihrer Klasse eine geteilte Instanz.
Um eine neue Instanz einer Klasse zu erhalten, übergeben Sie einfach `false` als Parameter:

```php
// Geteilte Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

> **Hinweis:** Beachten Sie, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

### Beispiele

Hier sind einige Beispiele, wie Sie Flight mit Funktionalität erweitern können, die nicht im Kern integriert ist.

#### Logging

Flight hat kein integriertes Logging-System, es ist jedoch wirklich einfach, eine Logging-Bibliothek mit Flight zu verwenden. Hier ist ein Beispiel mit der Monolog-Bibliothek:

```php
// services.php

// Registrieren Sie den Logger bei Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Nun, da es registriert ist, können Sie es in Ihrer Anwendung verwenden:

```php
// In Ihrem Controller oder Route
Flight::log()->warning('This is a warning message');
```

Dies wird eine Nachricht in die von Ihnen angegebene Log-Datei schreiben. Was, wenn Sie etwas protokollieren möchten, wenn ein Fehler auftritt? Sie können die `error`-Methode verwenden:

```php
// In Ihrem Controller oder Route
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Zeigen Sie Ihre benutzerdefinierte Fehlerseite an
	include 'errors/500.html';
});
```

Sie könnten auch ein einfaches APM (Application Performance Monitoring)-System mit den `before`- und `after`-Methoden erstellen:

```php
// In Ihrer services.php-Datei

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Sie könnten auch Ihre Request- oder Response-Header hinzufügen
	// um sie zu protokollieren (seien Sie vorsichtig, da dies eine 
	// Menge Daten sein würde, wenn Sie viele Anfragen haben)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Caching

Flight hat kein integriertes Caching-System, es ist jedoch wirklich einfach, eine Caching-Bibliothek mit Flight zu verwenden. Hier ist ein Beispiel mit der [PHP File Cache](/awesome-plugins/php_file_cache)-Bibliothek:

```php
// services.php

// Registrieren Sie den Cache bei Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Nun, da es registriert ist, können Sie es in Ihrer Anwendung verwenden:

```php
// In Ihrem Controller oder Route
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Führen Sie einige Verarbeitung durch, um die Daten zu erhalten
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // Cache für 1 Stunde
}
```

#### Einfache DIC-Objekt-Instantiierung

Wenn Sie einen DIC (Dependency Injection Container) in Ihrer Anwendung verwenden, können Sie Flight verwenden, um Ihnen bei der Instantiierung Ihrer Objekte zu helfen. Hier ist ein Beispiel mit der [Dice](https://github.com/level-2/Dice)-Bibliothek:

```php
// services.php

// Erstellen Sie einen neuen Container
$container = new \Dice\Dice;
// Vergessen Sie nicht, ihn sich selbst zuzuweisen wie unten!
$container = $container->addRule('PDO', [
	// shared bedeutet, dass dasselbe Objekt jedes Mal zurückgegeben wird
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Nun können wir eine zuordbare Methode erstellen, um jedes Objekt zu erstellen. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Dies registriert den Container-Handler, damit Flight weiß, dass er ihn für Controller/Middleware verwendet
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// Sagen wir, wir haben die folgende Beispielklasse, die ein PDO-Objekt im Konstruktor nimmt
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// Code, der eine E-Mail sendet
	}
}

// Und schließlich können Sie Objekte mit Dependency Injection erstellen
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Cool, oder?

## Siehe auch
- [Dependency Injection Container](/learn/dependency-injection-container) - Wie man einen DIC mit Flight verwendet.
- [File Cache](/awesome-plugins/php_file_cache) - Beispiel für die Verwendung einer Caching-Bibliothek mit Flight.

## Fehlerbehebung
- Denken Sie daran, dass zugeordnete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die zugeordnete Methode aufgerufen.

## Änderungsprotokoll
- v2.0 - Erste Veröffentlichung.