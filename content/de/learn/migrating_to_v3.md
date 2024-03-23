# Migration zu v3

Die Abwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, über die Sie Bescheid wissen sollten, wenn Sie von v2 auf v3 migrieren.

## Ausgabepufferverhalten (3.5.0)

[Output-Pufferung](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die Ausgabe, die von einem PHP-Skript generiert wird, in einem Puffer (intern zu PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dies ermöglicht es Ihnen, die Ausgabe zu ändern, bevor sie an den Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die Ansicht tut. Das Generieren von Ausgaben außerhalb des Controllers (oder in Flights Fall manchmal eine anonyme Funktion) bricht das MVC-Muster. Diese Änderung soll mehr im Einklang mit dem MVC-Muster sein und das Framework vorhersehbarer und einfacher zu verwenden machen.

In v2 wurde die Ausgabepufferung so behandelt, dass sie ihren eigenen Ausgabepuffer nicht konsistent schloss, was [Unittests](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [Streaming](https://github.com/flightphp/core/issues/413) erschwerte. Für die Mehrheit der Benutzer dürfte diese Änderung Sie tatsächlich nicht beeinflussen. Wenn Sie jedoch Inhalte außerhalb von Aufrufbaren und Controllern ausgeben (zum Beispiel in einem Hook), werden Sie wahrscheinlich auf Probleme stoßen. Das Ausgeben von Inhalten in Hooks und vor der tatsächlichen Ausführung des Frameworks hat möglicherweise in der Vergangenheit funktioniert, aber es wird zukünftig nicht mehr funktionieren.

### Wo Sie möglicherweise Probleme haben
```php
// index.php
require 'vendor/autoload.php';

// nur ein Beispiel
define('STARTZEIT', microtime(true));

function hallo() {
	echo 'Hallo Welt';
}

Flight::map('hallo', 'hallo');
Flight::after('hallo', function(){
	// das wird tatsächlich in Ordnung sein
	echo '<p>Dieser Hallo-Welt-Satz wurde Ihnen vom Buchstaben "H" präsentiert</p>';
});

Flight::before('start', function(){
	// Dinge wie diese führen zu einem Fehler
	echo '<html><head><title>Meine Seite</title></head><body>';
});

Flight::route('/', function(){
	// dies ist eigentlich in Ordnung
	echo 'Hallo Welt';

	// Dies sollte auch in Ordnung sein
	Flight::hallo();
});

Flight::after('start', function(){
	// das wird einen Fehler verursachen
	echo '<div>Ihre Seite wurde in '.(microtime(true) - STARTZEIT).' Sekunden geladen</div></body></html>';
});
```

### Aktivieren des v2-Renderingverhaltens

Können Sie Ihren alten Code so lassen, wie er ist, ohne eine Neuschreibung vorzunehmen, um ihn mit v3 zum Laufen zu bringen? Ja, können Sie! Sie können das v2-Renderingverhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dadurch können Sie das alte Renderingverhalten weiterhin verwenden, aber es wird empfohlen, es zukünftig zu beheben. In v4 des Frameworks wird dies entfernt sein.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Jetzt wird das in Ordnung sein
	echo '<html><head><title>Meine Seite</title></head><body>';
});

// mehr Code 
```

## Dispatcher-Änderungen (3.7.0)

Wenn Sie bisher statische Methoden für `Dispatcher` wie `Dispatcher::invokeMethod()` oder `Dispatcher::execute()` direkt aufgerufen haben, müssen Sie Ihren Code aktualisieren, um diese Methoden nicht mehr direkt aufzurufen. `Dispatcher` wurde in eine mehr objektorientierte Form konvertiert, damit Dependency Injection Container auf eine einfachere Weise verwendet werden können. Wenn Sie eine Methode ähnlich wie Dispatcher aufrufen müssen, können Sie manuell etwas wie `$ergebnis = $klasse->$methode(...$parameter);` oder `call_user_func_array()` verwenden.