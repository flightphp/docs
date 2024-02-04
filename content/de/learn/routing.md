### Routen  

Das Routing in Flight erfolgt durch Zuordnung eines URL-Musters zu einer Rückruffunktion.  

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```  

Der Rückruf kann jedes Objekt sein, das aufrufbar ist. Daher können Sie eine normale Funktion verwenden:  

```php
function hallo(){
    echo 'Hallo Welt!';
}

Flight::route('/', 'hallo');
```  

Oder eine Klassenmethode:  

```php
class Gruß {
    public static function hallo() {
        echo 'Hallo Welt!';
    }
}

Flight::route('/', array('Gruß','hallo'));
```  

Oder eine Objektmethode:  

```php
class Gruß
{
    public function __construct() {
        $this->name = 'Max Mustermann';
    }

    public function hallo() {
        echo "Hallo, {$this->name}!";
    }
}

$gruß = new Gruß();

Flight::route('/', array($gruß, 'hallo'));
```  

Die Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste übereinstimmende Route wird aufgerufen.  

## Methoden-Routing  

Standardmäßig werden Routenmuster mit allen Anforderungsmethoden abgeglichen. Sie können auf bestimmte Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.  

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});
```  

Sie können auch mehrere Methoden mit einer einzelnen Rückruffunktion durch Verwendung eines `|`-Trennzeichens zuordnen:  

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```  

## Reguläre Ausdrücke  

Sie können reguläre Ausdrücke in Ihren Routen verwenden:  

```php
Flight::route('/benutzer/[0-9]+', function () {
  // Dies wird zu /benutzer/1234 passen
});
```  

## Benannte Parameter  

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.  

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```  

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einbeziehen, indem Sie das `:`-Trennzeichen verwenden:  

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies wird zu /bob/123 passen
  // Aber wird nicht zu /bob/12345 passen
});
```  

Das Zuordnen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt.  

## Optionale Parameter  

Sie können benannte Parameter angeben, die optional für die Übereinstimmung sind, indem Sie Segmente in Klammern einschließen.  

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies wird zu den folgenden URLs passen:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```  

Alle optionalen Parameter, die nicht übereinstimmen, werden als NULL übergeben.  

## Platzhalter  

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente abgleichen möchten, können Sie den `*`-Platzhalter verwenden.  

```php
Flight::route('/blog/*', function () {
  // Dies wird zu /blog/2000/02/01 passen
});
```  

Um alle Anfragen an eine einzige Rückruffunktion weiterzuleiten, können Sie folgendes tun:  

```php
Flight::route('*', function () {
  // Mach etwas
});
```  

## Weitergabe  

Sie können die Ausführung an die nächste passende Route weiterleiten, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.  

```php
Flight::route('/benutzer/@name', function (string $name) {
  // Überprüfen Sie eine Bedingung
  if ($name !== "Bob") {
    // Weiter zur nächsten Route
    return true;
  }
});

Flight::route('/benutzer/*', function () {
  // Dies wird aufgerufen
});
```  

## Routeninformation  

Wenn Sie die übereinstimmenden Routeninformationen überprüfen möchten, können Sie die Routenobjektanforderung anfordern, indem Sie `true` als dritten Parameter an die Routenmethode übergeben. Das Routenobjekt wird immer als letzter Parameter an Ihre Rückruffunktion übergeben.  

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der mit HTTP-Methoden abgeglichenen
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von '*' in dem URL-Muster
  $route->splat;
}, true);
```  

## Routengruppierung  

Es gibt Zeiten, in denen Sie zusammenhängende Routen gruppieren möchten (zum Beispiel `/api/v1`). Dies können Sie durch Verwendung der `group`-Methode tun:  

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
	// Übereinstimmend mit /api/v1/benutzer
  });

  Flight::route('/posts', function () {
	// Übereinstimmend mit /api/v1/posts
  });
});
```  

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() erhält Variablen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Übereinstimmend mit GET /api/v1/benutzer
	});

	Flight::post('/posts', function () {
	  // Übereinstimmend mit POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Übereinstimmend mit PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() erhält Variablen, setzt jedoch keine Route! Siehe Objektkontext unten
	Flight::route('GET /benutzer', function () {
	  // Übereinstimmend mit GET /api/v2/benutzer
	});
  });
});
```  

### Gruppierung mit Objektkontext  

Sie können weiterhin Routengruppen mit dem `Engine`-Objekt auf die folgende Weise verwenden:  

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/benutzer', function () {
	// Übereinstimmend mit GET /api/v1/benutzer
  });

  $router->post('/posts', function () {
	// Übereinstimmend mit POST /api/v1/posts
  });
});
```  

## Routenalias  

Sie können einem Routen einen Alias zuweisen, sodass die URL später dynamisch in Ihrem Code generiert werden kann (z. B. wie eine Vorlage).  

```php
Flight::route('/benutzer/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');

// später irgendwo im Code
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```  

Dies ist besonders hilfreich, wenn sich Ihre URL zufällig ändert. Im obigen Beispiel wurde z. B. 'users' stattdessen nach `/admin/users/@id` verschoben. Durch das Aliasieren müssen Sie nichts ändern, an keiner Stelle wird auf das Alias verwiesen, da das Alias nun `/admin/users/5` wie im obigen Beispiel zurückgibt.  

Routenaliasierung funktioniert auch in Gruppen:  

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');
});


// später irgendwo im Code
Flight::getUrl('user_view', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```  

## Routen-Middleware  

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routenrückruf ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu validieren, ob der Benutzer die Berechtigung hat, auf die Route zuzugreifen.  

Hier ist ein Grundbeispiel:  

```php
// Wenn Sie nur eine anonyme Funktion bereitstellen, wird sie vor dem Routenrückruf ausgeführt. 
// Es gibt keine "Nach"-Middleware-Funktionen außer für Klassen (siehe unten)
Flight::route('/pfad', function() { echo ' Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```  

Es gibt einige sehr wichtige Hinweise zu Middleware, die Sie beachten müssen, bevor Sie sie verwenden:  
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt werden. Die Ausführung ähnelt der [Handhabung durch das Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Vor-Middleware werden in der hinzugefügten Reihenfolge ausgeführt, und Nach-Middleware werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion false zurückgibt, wird die gesamte Ausführung gestoppt und es wird ein Fehler 403 Forbidden ausgelöst. Wahrscheinlich möchten Sie das eleganter mit einem `Flight::redirect()` oder etwas Ähnlichem behandeln.
- Wenn Sie Parameter von Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen können Ihre Parameter tatsächlich in einer anderen Reihenfolge angezeigt werden, was die Middleware-Funktion brechen würde, indem sie auf den falschen Parameter verweist. Auf diese Weise können Sie auf sie nach Namen statt nach Position zugreifen.

### Middleware-Klassen  

Middleware kann auch als Klasse registriert werden. Wenn Sie die "Nach"-Funktionalität benötigen, müssen Sie eine Klasse verwenden.  

```php
class MeinMiddleware {
	public function before($params) {
		echo 'Middleware zuerst!';
	}

	public function after($params) {
		echo 'Middleware zuletzt!';
	}
}

$MeinMiddleware = new MeinMiddleware();
Flight::route('/pfad', function() { echo ' Hier bin ich! '; })->addMiddleware($MeinMiddleware); // auch ->addMiddleware([ $MeinMiddleware, $MeinMiddleware2 ]);

Flight::start();

// Dies zeigt "Middleware zuerst! Hier bin ich! Middleware zuletzt!" an
```  

### Middleware-Gruppen  

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe auch dieselbe Middleware haben. Dies ist nützlich, wenn Sie eine Gruppe von Routen z. B. anhand einer Auth-Middleware gruppieren müssen, um den API-Schlüssel im Header zu überprüfen.  

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('/api', function() {
    Flight::route('/benutzer', function() { echo 'Benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'Benutzer:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```  