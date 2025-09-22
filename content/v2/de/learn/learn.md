# Lernen

Diese Seite ist ein Leitfaden zum Lernen von Flight. Sie behandelt die Grundlagen des Frameworks und wie man es verwendet.

## <a name="routing"></a> Routing

Routing in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Callback-Funktion.

``` php
Flight::route('/', function(){
    echo 'hallo welt!';
});
```

Der Callback kann jedes aufrufbare Objekt sein. Sie können also eine reguläre Funktion verwenden:

``` php
function hello(){
    echo 'hallo welt!';
}

Flight::route('/', 'hello');
```

Oder eine Klassenmethode:

``` php
class Greeting {
    public static function hello() {
        echo 'hallo welt!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Oder eine Objektmethode:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hallo, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste Route, die eine Anfrage übereinstimmt, wird aufgerufen.

### Methoden-Routing

Standardmäßig werden Routenmuster gegen alle Anfrage-Methoden abgeglichen. Sie können auf spezifische Methoden reagieren, indem Sie einen Identifikator vor die URL setzen.

``` php
Flight::route('GET /', function(){
    echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function(){
    echo 'Ich habe eine POST-Anfrage erhalten.';
});
```

Sie können auch mehrere Methoden einem einzigen Callback zuordnen, indem Sie einen `|` Trenner verwenden:

``` php
Flight::route('GET|POST /', function(){
    echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```

### Reguläre Ausdrücke

Sie können reguläre Ausdrücke in Ihren Routen verwenden:

``` php
Flight::route('/user/[0-9]+', function(){
    // Dies wird /user/1234 abgleichen
});
```

### Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Callback-Funktion weitergegeben werden.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einfügen, indem Sie den `:` Trenner verwenden:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Dies wird /bob/123 abgleichen
    // Aber nicht /bob/12345
});
```

### Optionale Parameter

Sie können benannte Parameter angeben, die optional für das Matching sind, indem Sie Segmente in Klammern einfügen.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Dies wird die folgenden URLs abgleichen:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Alle optionalen Parameter, die nicht abgeglichen werden, werden als NULL übergeben.

### Platzhalter

Das Abgleichen erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente abgleichen möchten, können Sie den `*` Platzhalter verwenden.

``` php
Flight::route('/blog/*', function(){
    // Dies wird /blog/2000/02/01 abgleichen
});
```

Um alle Anfragen an einen einzelnen Callback weiterzuleiten, können Sie Folgendes tun:

``` php
Flight::route('*', function(){
    // Etwas tun
});
```

### Weitergeben

Sie können die Ausführung an die nächste übereinstimmende Route weitergeben, indem Sie `true` von Ihrer Callback-Funktion zurückgeben.

``` php
Flight::route('/user/@name', function($name){
    // Überprüfen Sie eine Bedingung
    if ($name != "Bob") {
        // Fortfahren zur nächsten Route
        return true;
    }
});

Flight::route('/user/*', function(){
    // Dies wird aufgerufen
});
```

### Routeninfo

Wenn Sie die übereinstimmenden Routeninformationen inspizieren möchten, können Sie anfordern, dass das Routenobjekt an Ihren Callback übergeben wird, indem Sie `true` als drittes Parameter im Routemethoden übergeben. Das Routenobjekt wird immer das letzte Parameter sein, das an Ihre Callback-Funktion übergeben wird.

``` php
Flight::route('/', function($route){
    // Array der abgeglichenen HTTP-Methoden
    $route->methods;

    // Array der benannten Parameter
    $route->params;

    // Übereinstimmender regulärer Ausdruck
    $route->regex;

    // Enthält die Inhalte von jedem '*' der im URL-Muster verwendet wird
    $route->splat;
}, true);
```
### Routengruppierung

Es gibt möglicherweise Zeiten, in denen Sie verwandte Routen gruppieren möchten (z. B. `/api/v1`). Sie können dies tun, indem Sie die `group` Methode verwenden:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Entspricht /api/v1/users
  });

  Flight::route('/posts', function () {
	// Entspricht /api/v1/posts
  });
});
```

Sie können sogar Gruppen von Gruppen schachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() erhält Variablen, es setzt keine Route! Siehe Kontext unten
	Flight::route('GET /users', function () {
	  // Entspricht GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Entspricht POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Entspricht PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() erhält Variablen, es setzt keine Route! Siehe Kontext unten
	Flight::route('GET /users', function () {
	  // Entspricht GET /api/v2/users
	});
  });
});
```

#### Gruppierung mit Objektkontext

Sie können die Routengruppierung weiterhin mit dem `Engine`-Objekt in der folgenden Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Entspricht GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Entspricht POST /api/v1/posts
  });
});
```

### Routen-Alias

Sie können einer Route einen Alias zuweisen, sodass die URL später in Ihrem Code dynamisch generiert werden kann (wie beispielsweise eine Vorlage).

```php
Flight::route('/users/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');

// später irgendwo im Code
Flight::getUrl('user_view', [ 'id' => 5 ]); // wird '/users/5' zurückgeben
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändert. Im obigen Beispiel nehmen wir an, dass Benutzer zu `/admin/users/@id` verschoben wurde. Mit dem Alias müssen Sie an keiner Stelle, an der Sie den Alias referenzieren, eine Änderung vornehmen, da der Alias nun `/admin/users/5` wie im obigen Beispiel zurückgeben wird.

Routen-Alias funktioniert auch in Gruppen:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');
});


// später irgendwo im Code
Flight::getUrl('user_view', [ 'id' => 5 ]); // wird '/users/5' zurückgeben
```

## <a name="extending"></a> Erweiterung

Flight ist so konzipiert, dass es ein erweiterbares Framework ist. Das Framework wird mit einer Reihe von Standardmethoden und -komponenten geliefert, ermöglicht es Ihnen jedoch, Ihre eigenen Methoden abzubilden, Ihre eigenen Klassen zu registrieren oder sogar vorhandene Klassen und Methoden zu überschreiben.

### Abbildung von Methoden

Um Ihre eigene benutzerdefinierte Methode abzubilden, verwenden Sie die `map` Funktion:

``` php
// Bilden Sie Ihre Methode ab
Flight::map('hello', function($name){
    echo "hallo $name!";
});

// Rufen Sie Ihre benutzerdefinierte Methode auf
Flight::hello('Bob');
```

### Klassenregistrierung

Um Ihre eigene Klasse zu registrieren, verwenden Sie die `register` Funktion:

``` php
// Registrieren Sie Ihre Klasse
Flight::register('user', 'User');

// Erhalten Sie eine Instanz Ihrer Klasse
$user = Flight::user();
```

Die Registrierungsmethode ermöglicht es Ihnen auch, Parameter an den Konstruktor Ihrer Klasse weiterzugeben. Wenn Sie also Ihre benutzerdefinierte Klasse laden, wird sie vorab initialisiert. Sie können die Konstruktorparameter angeben, indem Sie ein zusätzliches Array übergeben. Hier ist ein Beispiel für das Laden einer Datenbankverbindung:

``` php
// Klasse mit Konstruktorparametern registrieren
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Erhalten Sie eine Instanz Ihrer Klasse
// Dies wird ein Objekt mit den definierten Parametern erstellen
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Wenn Sie ein zusätzliches Callback-Parameter übergeben, wird es sofort nach dem Klassenkonstruktor ausgeführt. Dies ermöglicht Ihnen, alle Einrichtungsverfahren für Ihr neues Objekt durchzuführen. Die Callback-Funktion nimmt einen Parameter, eine Instanz des neuen Objekts.

``` php
// Der Callback wird das Objekt erhalten, das erstellt wurde
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Standardmäßig erhalten Sie jedes Mal, wenn Sie Ihre Klasse laden, eine gemeinsame Instanz. Um eine neue Instanz einer Klasse zu erhalten, geben Sie einfach `false` als Parameter an:

``` php
// Gemeinsame Instanz der Klasse
$shared = Flight::db();

// Neue Instanz der Klasse
$new = Flight::db(false);
```

Beachten Sie, dass abgebildete Methoden Vorrang vor registrierten Klassen haben. Wenn Sie beide mit demselben Namen deklarieren, wird nur die abgebildete Methode aufgerufen.

## <a name="overriding"></a> Überschreiben

Flight ermöglicht es Ihnen, die Standardfunktionen an Ihre eigenen Bedürfnisse anzupassen, ohne jeglichen Code ändern zu müssen.

Wenn Flight beispielsweise eine URL nicht mit einer Route abgleichen kann, ruft es die `notFound` Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map` Methode verwenden:

``` php
Flight::map('notFound', function(){
    // Benutzerdefinierte 404-Seite anzeigen
    include 'errors/404.html';
});
```

Flight ermöglicht es Ihnen auch, die Hauptkomponenten des Frameworks zu ersetzen. Beispielsweise können Sie die Standard-Router-Klasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

``` php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', 'MyRouter');

// Wenn Flight die Router-Instanz lädt, wird es Ihre Klasse laden
$myrouter = Flight::router();
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie versuchen, dies zu tun.

## <a name="filtering"></a> Filtern

Flight ermöglicht es Ihnen, Methoden vor und nach ihrem Aufruf zu filtern. Es gibt keine vordefinierten Hooks, die Sie sich merken müssen. Sie können jede der Standard-Framework-Methoden sowie alle benutzerdefinierten Methoden, die Sie abgebildet haben, filtern.

Eine Filterfunktion sieht so aus:

``` php
function(&$params, &$output) {
    // Filtercode
}
```

Mit den übergebenen Variablen können Sie die Eingabeparameter und/oder die Ausgabe manipulieren.

Sie können einen Filter vor einer Methode ausführen, indem Sie Folgendes tun:

``` php
Flight::before('start', function(&$params, &$output){
    // Etwas tun
});
```

Sie können einen Filter nach einer Methode ausführen, indem Sie Folgendes tun:

``` php
Flight::after('start', function(&$params, &$output){
    // Etwas tun
});
```

Sie können so viele Filter hinzufügen, wie Sie möchten, zu jeder Methode. Sie werden in der Reihenfolge aufgerufen, in der sie deklariert sind.

Hier ist ein Beispiel für den Filterprozess:

``` php
// Map eine benutzerdefinierte Methode
Flight::map('hello', function($name){
    return "Hallo, $name!";
});

// Fügen Sie einen Vorfilter hinzu
Flight::before('hello', function(&$params, &$output){
    // Parameter manipulieren
    $params[0] = 'Fred';
});

// Fügen Sie einen Nachfilter hinzu
Flight::after('hello', function(&$params, &$output){
    // Ausgabe manipulieren
    $output .= " Einen schönen Tag!";
});

// Rufen Sie die benutzerdefinierte Methode auf
echo Flight::hello('Bob');
```

Dies sollte anzeigen:

``` html
Hallo Fred! Einen schönen Tag!
```

Wenn Sie mehrere Filter definiert haben, können Sie die Kette brechen, indem Sie in einer Ihrer Filterfunktionen `false` zurückgeben:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'eins';
});

Flight::before('start', function(&$params, &$output){
    echo 'zwei';

    // Dies stoppt die Kette
    return false;
});

// Dies wird nicht aufgerufen
Flight::before('start', function(&$params, &$output){
    echo 'drei';
});
```

Beachten Sie, dass grundlegende Methoden wie `map` und `register` nicht gefiltert werden können, da sie direkt aufgerufen und nicht dynamisch aufgerufen werden.

## <a name="variables"></a> Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, sodass sie überall in Ihrer Anwendung verwendet werden können.

``` php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// An anderer Stelle in Ihrer Anwendung
$id = Flight::get('id');
```
Um zu sehen, ob eine Variable gesetzt wurde, können Sie Folgendes tun:

``` php
if (Flight::has('id')) {
     // Etwas tun
}
```

Sie können eine Variable löschen, indem Sie Folgendes tun:

``` php
// Löscht die id-Variable
Flight::clear('id');

// Löscht alle Variablen
Flight::clear();
```

Flight verwendet auch Variablen zu Konfigurationszwecken.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Ansichten

Flight bietet standardmäßig einige grundlegende Template-Funktionalitäten. Um eine Ansichtsvorlage anzuzeigen, rufen Sie die `render` Methode mit dem Namen der Vorlagendatei und optionalen Vorlagendaten auf:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Die übergebenen Vorlagendaten werden automatisch in die Vorlage injiziert und können wie eine lokale Variable referenziert werden. Vorlagendateien sind einfach PHP-Dateien. Wenn der Inhalt der Datei `hello.php` ist:

``` php
Hallo, '<?php echo $name; ?>'!
```

Würde die Ausgabe so aussehen:

``` html
Hallo, Bob!
```

Sie können auch manuell Ansichtvariablen mit der set-Methode festlegen:

``` php
Flight::view()->set('name', 'Bob');
```

Die Variable `name` ist jetzt in allen Ihren Ansichten verfügbar. Sie können also einfach Folgendes tun:

``` php
Flight::render('hello');
```

Beachten Sie, dass Sie beim Angeben des Namens der Vorlage in der render-Methode die `.php`-Erweiterung weglassen können.

Standardmäßig sucht Flight nach einem `views`-Verzeichnis für Vorlagendateien. Sie können einen alternativen Pfad für Ihre Vorlagen festlegen, indem Sie die folgende Konfiguration festlegen:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

Es ist verbreitet, dass Websites eine einzige Layoutvorlagendatei mit wechselndem Inhalt haben. Um Inhalte zu rendern, die in einem Layout verwendet werden sollen, können Sie optional einen Parameter an die `render`-Methode übergeben.

``` php
Flight::render('header', array('heading' => 'Hallo'), 'header_content');
Flight::render('body', array('body' => 'Welt'), 'body_content');
```

Ihre Ansicht hat dann gespeicherte Variablen mit den Namen `header_content` und `body_content`. Sie können dann Ihr Layout wie folgt rendern:

``` php
Flight::render('layout', array('title' => 'Startseite'));
```

Wenn die Vorlagendateien so aussehen:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

Würde die Ausgabe so aussehen:

``` html
<html>
<head>
<title>Startseite</title>
</head>
<body>
<h1>Hallo</h1>
<div>Welt</div>
</body>
</html>
```

### Benutzerdefinierte Ansichten

Flight ermöglicht es Ihnen, die Standardansicht-Engine einfach durch Registrierung Ihrer eigenen Ansichtsklasse auszutauschen. So würden Sie die [Smarty](http://www.smarty.net/) Template-Engine für Ihre Ansichten verwenden:

``` php
// Laden Sie die Smarty-Bibliothek
require './Smarty/libs/Smarty.class.php';

// Registrieren Sie Smarty als die Ansichtsklasse
// Übergeben Sie auch eine Callback-Funktion zum Konfigurieren von Smarty beim Laden
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Weisen Sie Vorlagendaten zu
Flight::view()->assign('name', 'Bob');

// Zeigen Sie die Vorlage an
Flight::view()->display('hello.tpl');
```

Um die Standard-Render-Methode von Flight zu überschreiben, tun Sie folgendes:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Fehlerbehandlung

### Fehler und Ausnahmen

Alle Fehler und Ausnahmen werden von Flight erfasst und an die `error` Methode übergeben. Das Standardverhalten besteht darin, eine generische `HTTP 500 Internal Server Error`-Antwort mit einigen Fehlerinformationen zu senden.

Sie können dieses Verhalten an Ihre eigenen Bedürfnisse anpassen:

``` php
Flight::map('error', function(Exception $ex){
    // Fehler behandeln
    echo $ex->getTraceAsString();
});
```

Standardmäßig werden Fehler nicht im Webserver protokolliert. Sie können dies aktivieren, indem Sie die Konfiguration ändern:

``` php
Flight::set('flight.log_errors', true);
```

### Nicht gefunden

Wenn eine URL nicht gefunden werden kann, ruft Flight die `notFound`-Methode auf. Das Standardverhalten besteht darin, eine `HTTP 404 Not Found`-Antwort mit einer einfachen Nachricht zu senden.

Sie können dieses Verhalten an Ihre eigenen Bedürfnisse anpassen:

``` php
Flight::map('notFound', function(){
    // Nicht gefunden behandeln
});
```

## <a name="redirects"></a> Weiterleitungen

Sie können die aktuelle Anfrage umleiten, indem Sie die `redirect` Methode verwenden und eine neue URL übergeben:

``` php
Flight::redirect('/new/location');
```

Standardmäßig sendet Flight einen HTTP 303-Statuscode. Sie können optional einen benutzerdefinierten Code festlegen:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Anfragen

Flight kapselt die HTTP-Anfrage in einem einzigen Objekt, das durch Folgendes aufgerufen werden kann:

``` php
$request = Flight::request();
```

Das Anfrageobjekt bietet die folgenden Eigenschaften:

``` html
url - Die angeforderte URL
base - Das übergeordnete Unterverzeichnis der URL
method - Die Anfragemethode (GET, POST, PUT, DELETE)
referrer - Die Referrer-URL
ip - IP-Adresse des Clients
ajax - Ob die Anfrage eine AJAX-Anfrage ist
scheme - Das Serverprotokoll (http, https)
user_agent - Browserinformationen
type - Der Inhaltstyp
length - Die Inhaltslänge
query - Abfragezeichenfolgenparameter
data - Postdaten oder JSON-Daten
cookies - Cookie-Daten
files - Hochgeladene Dateien
secure - Ob die Verbindung sicher ist
accept - HTTP-Accept-Parameter
proxy_ip - Proxy-IP-Adresse des Clients
```

Sie können die Eigenschaften `query`, `data`, `cookies` und `files` als Arrays oder Objekte abrufen.

Um beispielsweise einen Abfragezeichenfolgenparameter zu erhalten, können Sie Folgendes tun:

``` php
$id = Flight::request()->query['id'];
```

Oder Sie können Folgendes tun:

``` php
$id = Flight::request()->query->id;
```

### Rohes Anfrage-Body

Um den roh HTTP-Anfrage-Body zu erhalten, zum Beispiel bei PUT-Anfragen, können Sie Folgendes tun:

``` php
$body = Flight::request()->getBody();
```

### JSON-Eingabe

Wenn Sie eine Anfrage mit dem Typ `application/json` und den Daten `{"id": 123}` senden, wird es über die Eigenschaft `data` verfügbar sein:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Anhalten

Sie können das Framework an jedem Punkt anhalten, indem Sie die `halt` Methode aufrufen:

``` php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

``` php
Flight::halt(200, 'Gleich zurück...');
```

Der Aufruf von `halt` verwirft alle Antwortinhalte bis zu diesem Punkt. Wenn Sie das Framework anhalten und die aktuelle Antwort ausgeben möchten, verwenden Sie die `stop` Methode:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTP-Caching

Flight bietet integrierte Unterstützung für HTTP-Level-Caching. Wenn die Caching-Bedingung erfüllt ist, wird Flight eine HTTP `304 Not Modified`-Antwort zurückgeben. Das nächste Mal, wenn der Client die gleiche Ressource anfordert, wird er aufgefordert, seine lokal zwischengespeicherte Version zu verwenden.

### Letzte Änderung

Sie können die `lastModified` Methode verwenden und einen UNIX-Zeitstempel übergeben, um das Datum und die Uhrzeit festzulegen, wann eine Seite zuletzt geändert wurde. Der Client wird weiterhin den Cache verwenden, bis sich der zuletzt geänderte Wert ändert.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

### ETag

`ETag`-Caching ähnelt `Last-Modified`, außer dass Sie eine beliebige ID für die Ressource angeben können:

``` php
Flight::route('/news', function(){
    Flight::etag('meine-einzigartige-id');
    echo 'Dieser Inhalt wird zwischengespeichert.';
});
```

Beachten Sie, dass sowohl `lastModified` als auch `etag` den Cachewert festlegen und überprüfen. Wenn der Cachewert bei Anfragen gleich ist, sendet Flight sofort eine `HTTP 304`-Antwort und stoppt die Verarbeitung.

## <a name="json"></a> JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die JSON-codiert werden sollen:

``` php
Flight::json(array('id' => 123));
```

Für JSONP-Anfragen können Sie optional den Abfrageparameter-Namen angeben, den Sie verwenden, um Ihre Callback-Funktion zu definieren:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Wenn Sie also eine GET-Anfrage mit `?q=my_func` stellen, sollten Sie die Ausgabe erhalten:

``` json
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter-Namen angeben, wird standardmäßig `jsonp` verwendet.

## <a name="configuration"></a> Konfiguration

Sie können bestimmte Verhaltensweisen von Flight anpassen, indem Sie Konfigurationswerte über die `set` Methode festlegen.

``` php
Flight::set('flight.log_errors', true);
```

Die folgende Liste umfasst alle verfügbaren Konfigurationseinstellungen:

``` html 
flight.base_url - Überschreibt die Basis-URL der Anfrage. (Standard: null)
flight.case_sensitive - Fallunempfindliches Matching für URLs. (Standard: false)
flight.handle_errors - Ermöglicht es Flight, alle Fehler intern zu behandeln. (Standard: true)
flight.log_errors - Protokolliert Fehler in der Fehlerprotokolldatei des Webservers. (Standard: false)
flight.views.path - Verzeichnis mit Ansichtsvorlagendateien. (Standard: ./views)
flight.views.extension - Erweiterung der Vorlagendatei. (Standard: .php)
```

## <a name="frameworkmethods"></a> Framework-Methoden

Flight wurde entwickelt, um einfach zu bedienen und zu verstehen zu sein. Die folgende Liste umfasst die vollständige Menge an Methoden für das Framework. Sie besteht aus Kernmethoden, die reguläre statische Methoden sind, und erweiterbaren Methoden, die abgebildete Methoden sind, die gefiltert oder überschrieben werden können.

### Kernmethoden

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Erstellt eine benutzerdefinierte Framework-Methode.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registriert eine Klasse zu einer Framework-Methode.
Flight::before(string $name, callable $callback) // Fügt einen Filter vor einer Framework-Methode hinzu.
Flight::after(string $name, callable $callback) // Fügt einen Filter nach einer Framework-Methode hinzu.
Flight::path(string $path) // Fügt einen Pfad zum automatischen Laden von Klassen hinzu.
Flight::get(string $key) // Holt eine Variable.
Flight::set(string $key, mixed $value) // Setzt eine Variable.
Flight::has(string $key) // Überprüft, ob eine Variable gesetzt ist.
Flight::clear(array|string $key = []) // Löscht eine Variable.
Flight::init() // Initialisiert das Framework mit den Standardwerten.
Flight::app() // Holt die Anwendungsobjektinstanz
```

### Erweiterbare Methoden

```php
Flight::start() // Startet das Framework.
Flight::stop() // Stoppt das Framework und sendet eine Antwort.
Flight::halt(int $code = 200, string $message = '') // Stoppt das Framework mit einem optionalen Statuscode und einer Nachricht.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Ordnet ein URL-Muster einem Callback zu.
Flight::group(string $pattern, callable $callback) // Erstellt Gruppen für URLs, das Muster muss eine Zeichenfolge sein.
Flight::redirect(string $url, int $code) // Leitet zu einer anderen URL um.
Flight::render(string $file, array $data, ?string $key = null) // Rendert eine Vorlagendatei.
Flight::error(Throwable $error) // Sendet eine HTTP 500-Antwort.
Flight::notFound() // Sendet eine HTTP 404-Antwort.
Flight::etag(string $id, string $type = 'string') // Führt ETag-HTTP-Caching durch.
Flight::lastModified(int $time) // Führt das zuletzt geänderte HTTP-Caching durch.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSON-Antwort.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Sendet eine JSONP-Antwort.
```

Alle benutzerdefinierten Methoden, die mit `map` und `register` hinzugefügt werden, können ebenfalls gefiltert werden.


## <a name="frameworkinstance"></a> Framework-Instanz

Anstatt Flight als globale statische Klasse auszuführen, können Sie es optional als Objektinstanz ausführen.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'hallo welt!';
});

$app->start();
```

Anstatt also die statische Methode aufzurufen, würden Sie die Instanzmethode mit demselben Namen auf dem Engine-Objekt aufrufen.
