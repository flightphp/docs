# Migration zu v3

Die Abwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, die Sie beachten sollten, wenn Sie von v2 zu v3 migrieren. Es gibt einige Änderungen, die zu sehr mit Designmustern kollidiert sind, sodass Anpassungen vorgenommen werden mussten.

## Verhalten des Output Buffering

_v3.5.0_

[Output buffering](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die Ausgabe, die von einem PHP-Skript generiert wird, in einem Puffer (intern in PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dies ermöglicht es Ihnen, die Ausgabe zu modifizieren, bevor sie an den Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die View tut. Ausgaben, die außerhalb des Controllers generiert werden (oder im Fall von Flight manchmal eine anonyme Funktion), brechen das MVC-Muster. Diese Änderung dient dazu, mehr im Einklang mit dem MVC-Muster zu sein und das Framework vorhersehbarer und einfacher zu bedienen zu machen.

In v2 wurde das Output Buffering so gehandhabt, dass es seinen eigenen Output-Puffer nicht konsistent schloss, was [Unit-Tests](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [Streaming](https://github.com/flightphp/core/issues/413) schwieriger machte. Für die Mehrheit der Nutzer könnte diese Änderung Sie tatsächlich nicht beeinflussen. Wenn Sie jedoch Inhalte außerhalb von Callables und Controllern ausgeben (z. B. in einem Hook), stoßen Sie wahrscheinlich auf Probleme. Das Ausgeben von Inhalten in Hooks und vor der tatsächlichen Ausführung des Frameworks hat in der Vergangenheit möglicherweise funktioniert, wird aber künftig nicht mehr funktionieren.

### Wo Sie Probleme haben könnten
```php
// index.php
require 'vendor/autoload.php';

// nur ein Beispiel
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// das wird tatsächlich in Ordnung sein
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// Dinge wie das werden einen Fehler verursachen
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// das ist tatsächlich in Ordnung
	echo 'Hello World';

	// Das sollte auch in Ordnung sein
	Flight::hello();
});

Flight::after('start', function(){
	// das wird einen Fehler verursachen
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Aktivieren des v2-Rendering-Verhaltens

Können Sie Ihren alten Code so lassen, wie er ist, ohne eine Umstellung durchzuführen, um ihn mit v3 kompatibel zu machen? Ja, das können Sie! Sie können das v2-Rendering-Verhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dies ermöglicht es Ihnen, das alte Rendering-Verhalten weiterhin zu verwenden, aber es wird empfohlen, es künftig zu beheben. In v4 des Frameworks wird dies entfernt werden.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Jetzt wird das in Ordnung sein
	echo '<html><head><title>My Page</title></head><body>';
});

// mehr Code 
```

## Änderungen am Dispatcher

_v3.7.0_

Wenn Sie statische Methoden für `Dispatcher` direkt aufgerufen haben, wie z. B. `Dispatcher::invokeMethod()`, `Dispatcher::execute()` usw., müssen Sie Ihren Code aktualisieren, um diese Methoden nicht mehr direkt aufzurufen. `Dispatcher` wurde zu einem objektorientierteren Ansatz umgewandelt, damit Dependency Injection Container einfacher verwendet werden können. Wenn Sie eine Methode ähnlich wie der Dispatcher aufrufen müssen, können Sie manuell etwas wie `$result = $class->$method(...$params);` oder `call_user_func_array()` verwenden.

## Änderungen an `halt()` `stop()` `redirect()` und `error()`

_v3.10.0_

Das Standardverhalten vor 3.10.0 war, sowohl die Header als auch den Response-Body zu löschen. Dies wurde geändert, sodass nur noch der Response-Body gelöscht wird. Wenn Sie auch die Header löschen müssen, können Sie `Flight::response()->clear()` verwenden.