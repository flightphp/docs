# Routen

Routing in Flight erfolgt durch das Abgleichen eines URL-Musters mit einer Rückruffunktion.

```php
Flight::route('/', function(){
    echo 'Hallo Welt!';
});
```

Der Rückruf kann jedes Objekt sein, das aufrufbar ist. Sie können also eine normale Funktion verwenden:

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
        $this->name = 'John Doe';
    }

    public function hallo() {
        echo "Hallo, {$this->name}!";
    }
}

$gruß = new Gruß();

Flight::route('/', array($gruß, 'hallo'));
```

Die Routen werden in der Reihenfolge abgeglichen, in der sie definiert sind. Die erste übereinstimmende Route für eine Anfrage wird aufgerufen.

## Methodenrouten

Standardmäßig werden Routenmuster mit allen Anforderungsmethoden übereinstimmen. Sie können auf bestimmte Methoden reagieren, indem Sie einen Bezeichner vor der URL platzieren.

```php
Flight::route('GET /', function () {
  echo 'Ich habe eine GET-Anfrage erhalten.';
});

Flight::route('POST /', function () {
  echo 'Ich habe eine POST-Anfrage erhalten.';
});
```

Sie können auch mehrere Methoden zu einem einzelnen Rückruf zuordnen, indem Sie einen `|`-Trenner verwenden:

```php
Flight::route('GET|POST /', function () {
  echo 'Ich habe entweder eine GET- oder eine POST-Anfrage erhalten.';
});
```

## Reguläre Ausdrücke

Sie können reguläre Ausdrücke in Ihren Routen verwenden:

```php
Flight::route('/benutzer/[0-9]+', function () {
  // Dies wird mit /benutzer/1234 übereinstimmen
});
```

## Benannte Parameter

Sie können benannte Parameter in Ihren Routen angeben, die an Ihre Rückruffunktion übergeben werden.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "Hallo, $name ($id)!";
});
```

Sie können auch reguläre Ausdrücke mit Ihren benannten Parametern einschließen, indem Sie den `:`-Trenner verwenden:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Dies wird mit /bob/123 übereinstimmen
  // Aber nicht mit /bob/12345
});
```

Das Zuordnen von Regex-Gruppen `()` mit benannten Parametern wird nicht unterstützt.

## Optionale Parameter

Sie können benannte Parameter angeben, die optional für die Übereinstimmung sind, indem Sie Segmente in Klammern einschließen.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Dies wird mit den folgenden URLs übereinstimmen:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Alle optionalen Parameter, die nicht übereinstimmen, werden als NULL übergeben.

## Platzhalter

Die Übereinstimmung erfolgt nur auf einzelnen URL-Segmenten. Wenn Sie mehrere Segmente übereinstimmen möchten, können Sie den `*` Platzhalter verwenden.

```php
Flight::route('/blog/*', function () {
  // Dies wird mit /blog/2000/02/01 übereinstimmen
});
```

Um alle Anfragen an einen einzigen Rückruf zu leiten, können Sie folgendes tun:

```php
Flight::route('*', function () {
  // Mach etwas
});
```

## Weiterleitung

Sie können die Ausführung an die nächste übereinstimmende Route weiterleiten, indem Sie `true` aus Ihrer Rückruffunktion zurückgeben.

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

## Routeninformationen

Wenn Sie die übereinstimmenden Routeninformationen untersuchen möchten, können Sie anfordern, dass das Routenobjekt an Ihre Rückruffunktion übergeben wird, indem Sie `true` als dritten Parameter in der Routenmethode übergeben. Das Routenobjekt wird immer der letzte Parameter, der an Ihre Rückruffunktion übergeben wird.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array der mit den HTTP-Methoden übereinstimmenden
  $route->methods;

  // Array der benannten Parameter
  $route->params;

  // Übereinstimmender regulärer Ausdruck
  $route->regex;

  // Enthält den Inhalt von '*' in dem URL-Muster
  $route->splat;
}, true);
```

## Routengruppen

Manchmal möchten Sie möglicherweise zusammenhängende Routen zusammenfassen (z. B. `/api/v1`). Dies können Sie durch Verwendung der `group`-Methode erreichen:

```php
Flight::group('/api/v1', function () {
  Flight::route('/benutzer', function () {
    // Übereinstimmungen /api/v1/benutzer
  });

  Flight::route('/beiträge', function () {
    // Übereinstimmungen /api/v1/beiträge
  });
});
```

Sie können sogar Gruppen von Gruppen verschachteln:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
    // Flight::get() ruft Variablen ab, setzt aber keine Route! Siehe Objektkontext unten
    Flight::route('GET /benutzer', function () {
      // Übereinstimmungen GET /api/v1/benutzer
    });

    Flight::post('/beiträge', function () {
      // Übereinstimmungen POST /api/v1/beiträge
    });

    Flight::put('/beiträge/1', function () {
      // Übereinstimmungen PUT /api/v1/beiträge
    });
  });
  Flight::group('/v2', function () {

    // Flight::get() ruft Variablen ab, setzt aber keine Route! Siehe Objektkontext unten
    Flight::route('GET /benutzer', function () {
      // Übereinstimmungen GET /api/v2/benutzer
    });
  });
});
```

### Gruppierung mit Objektkontext

Sie können weiterhin Routengruppen mit dem `Engine`-Objekt auf folgende Weise verwenden:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/benutzer', function () {
    // Übereinstimmungen GET /api/v1/benutzer
  });

  $router->post('/beiträge', function () {
    // Übereinstimmungen POST /api/v1/beiträge
  });
});
```

## RoutenAlias

Sie können einem Route einen Alias zuweisen, damit die URL später in Ihrem Code dynamisch generiert werden kann (z. B. in einer Vorlage).

```php
Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');

// später im Code irgendwo
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

Dies ist besonders hilfreich, wenn sich Ihre URL ändert. In obigem Beispiel wurde Benutzer beispielsweise stattdessen nach `/admin/benutzer/@id` verschoben. Mit dem Alias müssen Sie nirgendwo den Alias ändern, da der Alias nun `/admin/benutzer/5` wie im obigen Beispiel zurückgibt.

Routenalias funktionieren auch in Gruppen:

```php
Flight::group('/benutzer', function() {
    Flight::route('/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');
});


// später im Code irgendwo
Flight::getUrl('benutzer_ansicht', [ 'id' => 5 ]); // gibt '/benutzer/5' zurück
```

## Routen-Middleware

Flight unterstützt Routen- und Gruppenrouten-Middleware. Middleware ist eine Funktion, die vor (oder nach) dem Routenrückruf ausgeführt wird. Dies ist eine großartige Möglichkeit, API-Authentifizierungsprüfungen in Ihrem Code hinzuzufügen oder zu validieren, dass der Benutzer die Berechtigung für den Zugriff auf die Route hat.

Hier ist ein grundlegendes Beispiel:

```php
// Wenn Sie nur eine anonyme Funktion bereitstellen, wird sie vor dem Routenrückruf ausgeführt. 
// es gibt keine "nach" Middleware-Funktionen außer für Klassen (siehe unten)
Flight::route('/pfad', function() { echo ' Hier bin ich!'; })->addMiddleware(function() {
	echo 'Middleware zuerst!';
});

Flight::start();

// Dies gibt "Middleware zuerst! Hier bin ich!" aus
```

Es gibt einige sehr wichtige Hinweise zum Middleware, die Sie beachten sollten, bevor Sie sie verwenden:
- Middleware-Funktionen werden in der Reihenfolge ausgeführt, in der sie der Route hinzugefügt wurden. Die Ausführung erfolgt ähnlich wie dies [Slim Framework handhabt](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - "Before"-Funktionen werden in der Hinzufügereihenfolge ausgeführt, und "After"-Funktionen werden in umgekehrter Reihenfolge ausgeführt.
- Wenn Ihre Middleware-Funktion `false` zurückgibt, wird die gesamte Ausführung gestoppt und es wird ein 403 Forbidden Fehler ausgelöst. Wahrscheinlich möchten Sie dies mit einer `Flight::redirect()` oder etwas Ähnlichem eleganter behandeln.
- Wenn Sie Parameter von Ihrer Route benötigen, werden sie als einzelnes Array an Ihre Middleware-Funktion übergeben. (`function($params) { ... }` oder `public function before($params) {}`). Der Grund dafür ist, dass Sie Ihre Parameter in Gruppen strukturieren können und in einigen dieser Gruppen können Ihre Parameter tatsächlich in einer anderen Reihenfolge angezeigt werden, was die Middleware-Funktion durch Verweis auf den falschen Parameter unterbrechen würde. Auf diese Weise können Sie auf sie nach Namen anstelle von Position zugreifen.

### Middleware-Klassen

Middleware kann auch als Klasse registriert werden. Wenn Sie die "After"-Funktionalität benötigen, müssen Sie eine Klasse verwenden.

```php
class MeineMiddleware {
	public function before($params) {
		echo 'Middleware zuerst!';
	}

	public function after($params) {
		echo 'Middleware zuletzt!';
	}
}

$MeineMiddleware = new MeineMiddleware();
Flight::route('/pfad', function() { echo ' Hier bin ich! '; })->addMiddleware($MeineMiddleware); // auch ->addMiddleware([ $MeineMiddleware, $MeineMiddleware2 ]);

Flight::start();

// Dies wird "Middleware zuerst! Hier bin ich! Middleware zuletzt!" anzeigen
```

### Middleware-Gruppen

Sie können eine Routengruppe hinzufügen, und dann wird jede Route in dieser Gruppe dieselbe Middleware haben. Dies ist nützlich, wenn Sie eine Gruppe von Routen zusammenfassen müssen, z. B. durch eine Auth-Middleware, um den API-Schlüssel im Header zu überprüfen.

```php

// am Ende der Gruppenmethode hinzugefügt
Flight::group('/api', function() {
    Flight::route('/benutzer', function() { echo 'benutzer'; }, false, 'benutzer');
	Flight::route('/benutzer/@id', function($id) { echo 'benutzer:'.$id; }, false, 'benutzer_ansicht');
}, [ new ApiAuthMiddleware() ]);