# Migration zu v3

Die Rückwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, über die Sie sich im Klaren sein sollten, wenn Sie von v2 auf v3 migrieren.

## Ausgabepufferung

[Die Ausgabepufferung](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die vom PHP-Skript generierte Ausgabe in einem Puffer (intern für PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dies ermöglicht es Ihnen, die Ausgabe zu ändern, bevor sie an den Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die Ansicht tut. Das Generieren von Ausgaben außerhalb des Controllers (oder in Flights Fall manchmal einer anonymen Funktion) bricht das MVC-Muster. Diese Änderung soll mehr im Einklang mit dem MVC-Muster sein und das Framework vorhersehbarer und einfacher zu verwenden machen.

In v2 wurde die Ausgabepufferung so gehandhabt, dass sie ihren eigenen Ausgabepuffer nicht konsistent schloss, was [Unittesting](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [streaming](https://github.com/flightphp/core/issues/413) erschwerte. Für die Mehrheit der Benutzer wird diese Änderung Sie möglicherweise nicht tatsächlich betreffen. Wenn Sie jedoch Inhalte außerhalb von Funktionsaufrufen und Controllern ausgeben (zum Beispiel in einem Hook), werden Sie wahrscheinlich auf Probleme stoßen. Das Ausgeben von Inhalten in Hooks und vor der tatsächlichen Ausführung des Frameworks hat möglicherweise in der Vergangenheit funktioniert, wird aber zukünftig nicht funktionieren.

### Wo es Probleme geben könnte
```php
// index.php
require 'vendor/autoload.php';

// nur ein Beispiel
define('START_TIME', microtime(true));

function hello() {
	echo 'Hallo Welt';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// das wird tatsächlich in Ordnung sein
	echo '<p>Dieser Hallo-Welt-Satz wurde Ihnen vom Buchstaben "H" präsentiert</p>';
});

Flight::before('start', function(){
	// Dinge wie diese werden einen Fehler verursachen
	echo '<html><head><title>Meine Seite</title></head><body>';
});

Flight::route('/', function(){
	// das ist tatsächlich in Ordnung
	echo 'Hallo Welt';

	// Dies sollte auch in Ordnung sein
	Flight::hello();
});

Flight::after('start', function(){
	// das wird einen Fehler verursachen
	echo '<div>Ihre Seite wurde in '.(microtime(true) - START_TIME).' Sekunden geladen</div></body></html>';
});
```

### Einschalten des v2-Renderverhaltens

Können Sie Ihren alten Code so lassen, wie er ist, ohne eine Neuschreibung vorzunehmen, um ihn mit v3 zum Laufen zu bringen? Ja, das können Sie! Sie können das v2-Renderverhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dadurch können Sie weiterhin das alte Renderverhalten verwenden, aber es wird empfohlen, es zukünftig zu beheben. In v4 des Frameworks wird dies entfernt.

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