# Migration zu v3

Die Abwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, über die Sie informiert sein sollten, wenn Sie von v2 auf v3 migrieren.

## Verhalten beim Output-Puffer (3.5.0)

[Output buffering](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die Ausgabe, die von einem PHP-Skript generiert wird, in einem Puffer (intern zu PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dies ermöglicht es Ihnen, die Ausgabe zu ändern, bevor sie an den Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die Ansicht tut. Das Generieren von Ausgaben außerhalb des Controllers (oder im Fall von Flights manchmal einer anonymen Funktion) bricht das MVC-Muster. Diese Änderung soll mehr im Einklang mit dem MVC-Muster stehen und das Framework vorhersehbarer und einfacher zu verwenden machen.

In v2 wurde die Ausgabepufferung auf eine Weise behandelt, bei der der eigene Ausgabepuffer nicht konsistent geschlossen wurde, was [Unit-Tests](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [Streaming](https://github.com/flightphp/core/issues/413) erschwert hat. Für die Mehrheit der Benutzer dürfte sich diese Änderung tatsächlich nicht auf Sie auswirken. Wenn Sie jedoch Inhalte außerhalb von Funktionsaufrufen und Controllern ausgeben (zum Beispiel in einem Hook), werden Sie wahrscheinlich Probleme haben. Das Ausgeben von Inhalten in Hooks und vor der tatsächlichen Ausführung des Frameworks hat möglicherweise in der Vergangenheit funktioniert, wird aber zukünftig nicht mehr funktionieren.

### Wo Sie Probleme haben könnten
```php
// index.php
require 'vendor/autoload.php';

// Beispiel
define('START_TIME', microtime(true));

function hello() {
	echo 'Hallo Welt';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// Dies wird tatsächlich in Ordnung sein
	echo '<p>Dieser Hallo-Welt-Satz wurde Ihnen vom Buchstaben "H" präsentiert</p>';
});

Flight::before('start', function(){
	// Dinge wie diese werden einen Fehler verursachen
	echo '<html><head><title>Meine Seite</title></head><body>';
});

Flight::route('/', function(){
	// Das ist tatsächlich in Ordnung
	echo 'Hallo Welt';

	// Dies sollte auch in Ordnung sein
	Flight::hello();
});

Flight::after('start', function(){
	// Dies wird einen Fehler verursachen
	echo '<div>Ihre Seite wurde in '.(microtime(true) - START_TIME).' Sekunden geladen</div></body></html>';
});
```

### Aktivieren des v2-Rendering-Verhaltens

Können Sie Ihren alten Code weiterhin so lassen, wie er ist, ohne eine Neuschreibung vorzunehmen, um ihn mit v3 zum Laufen zu bringen? Ja, das können Sie! Sie können das v2-Rendering-Verhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dadurch können Sie weiterhin das alte Rendering-Verhalten verwenden, aber es wird empfohlen, es zukünftig zu korrigieren. In v4 des Frameworks wird dies entfernt.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Nun wird das in Ordnung sein
	echo '<html><head><title>Meine Seite</title></head><body>';
});

// mehr Code 
```

## Dispatcher-Änderungen (3.7.0)

Wenn Sie bisher direkt statische Methoden für `Dispatcher` wie `Dispatcher::invokeMethod()`, `Dispatcher::execute()` usw. aufgerufen haben, müssen Sie Ihren Code aktualisieren, um diese Methoden nicht mehr direkt aufzurufen. `Dispatcher` wurde in eine mehr objektorientierte Form umgewandelt, sodass Dependency Injection Container auf eine einfachere Weise verwendet werden können. Wenn Sie eine Methode ähnlich wie Dispatcher aufrufen müssen, können Sie manuell etwas wie `$result = $class->$method(...$params);` oder `call_user_func_array()` verwenden.

## Änderungen an `halt()` `stop()` `redirect()` und `error()` (3.10.0)

Das Standardverhalten vor 3.10.0 bestand darin, sowohl die Header als auch den Antworttext zu löschen. Dies wurde geändert, um nur den Antworttext zu löschen. Wenn Sie auch die Header löschen müssen, können Sie `Flight::response()->clear()` verwenden.