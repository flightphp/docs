# Migration zu v3

Die Abwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, auf die Sie achten sollten, wenn Sie von v2 auf v3 migrieren.

## Verhalten der Ausgabepufferung (3.5.0)

[Ausgabepufferung](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die Ausgabe, die von einem PHP-Skript generiert wird, in einem Puffer (intern von PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dadurch können Sie die Ausgabe modifizieren, bevor sie zum Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die Ansicht tut. Das Generieren von Ausgaben außerhalb des Controllers (oder in Flights Fall manchmal eine anonyme Funktion) verstößt gegen das MVC-Muster. Diese Änderung soll mehr im Einklang mit dem MVC-Muster stehen und das Framework vorhersehbarer und einfacher zu verwenden machen.

In v2 wurde die Ausgabepufferung so gehandhabt, dass der eigene Ausgabepuffer nicht konsistent geschlossen wurde, was [Unit Tests](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [Streaming](https://github.com/flightphp/core/issues/413) erschwerte. Für die meisten Benutzer hat diese Änderung tatsächlich keine Auswirkungen. Wenn Sie jedoch Inhalte außerhalb von Aufrufbaren und Controllern (zum Beispiel in einem Hook) echoen, werden Sie wahrscheinlich auf Probleme stoßen. Das Echoen von Inhalten in Hooks und vor der tatsächlichen Ausführung des Frameworks hat möglicherweise in der Vergangenheit funktioniert, wird jedoch zukünftig nicht mehr funktionieren.

### Wo Probleme auftreten könnten
```php
// index.php
require 'vendor/autoload.php';

// Nur ein Beispiel
define('START_TIME', microtime(true));

function hello() {
	echo 'Hallo Welt';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// Das wird tatsächlich funktionieren
	echo '<p>Diese Begrüßung wurde Ihnen vom Buchstaben "H" präsentiert</p>';
});

Flight::before('start', function(){
	// Dinge wie diese werden einen Fehler verursachen
	echo '<html><head><title>Meine Seite</title></head><body>';
});

Flight::route('/', function(){
	// Das ist eigentlich in Ordnung
	echo 'Hallo Welt';

	// Dies sollte auch in Ordnung sein
	Flight::hello();
});

Flight::after('start', function(){
	// Das wird einen Fehler verursachen
	echo '<div>Ihre Seite wurde in '.(microtime(true) - START_TIME).' Sekunden geladen</div></body></html>';
});
```

### Aktivieren des v2-Renderingverhaltens

Können Sie Ihren alten Code so lassen, wie er ist, ohne eine Neuschreibung vorzunehmen, um ihn mit v3 zum Laufen zu bringen? Ja, das können Sie! Sie können das v2-Renderingverhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dadurch können Sie weiterhin das alte Renderingverhalten nutzen, aber es wird empfohlen, es zukünftig zu korrigieren. In v4 des Frameworks wird dies entfernt.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Jetzt wird dies in Ordnung sein
	echo '<html><head><title>Meine Seite</title></head><body>';
});

// mehr Code 
```

## Änderungen im Dispatcher (3.7.0)

Wenn Sie direkt statische Methoden für `Dispatcher` aufgerufen haben, wie z.B. `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, usw., müssen Sie Ihren Code aktualisieren, um diese Methoden nicht mehr direkt aufzurufen. `Dispatcher` wurde in eine stärker objektorientierte Form umgewandelt, damit Dependency Injection Containers auf einfachere Weise verwendet werden können. Wenn Sie eine Methode ähnlich wie Dispatcher aufrufen müssen, können Sie manuell etwas wie `$result = $class->$method(...$params);` oder `call_user_func_array()` verwenden.