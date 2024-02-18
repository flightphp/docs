# Upgrade auf v3

Die Abwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, über die Sie Bescheid wissen sollten, wenn Sie von v2 auf v3 migrieren.

## Ausgabepufferung

[Die Ausgabepufferung](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ist der Prozess, bei dem die Ausgabe, die von einem PHP-Skript generiert wird, in einem Puffer (innerhalb von PHP) gespeichert wird, bevor sie an den Client gesendet wird. Dies ermöglicht es Ihnen, die Ausgabe zu modifizieren, bevor sie an den Client gesendet wird.

In einer MVC-Anwendung ist der Controller der "Manager" und er verwaltet, was die Ansicht tut. Wenn die Ausgabe außerhalb des Controllers generiert wird (oder in Flights Fall manchmal eine anonyme Funktion ist), wird das MVC-Muster unterbrochen. Diese Änderung soll mehr im Einklang mit dem MVC-Muster sein und das Framework vorhersehbarer und einfacher zu verwenden machen.

In v2 wurde die Ausgabepufferung so behandelt, dass sie ihren eigenen Ausgabepuffer nicht konsistent geschlossen hat, was [Unit-Tests](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) und [Streaming](https://github.com/flightphp/core/issues/413) erschwerte. Für die Mehrheit der Benutzer wird diese Änderung Sie wahrscheinlich nicht beeinflussen. Wenn Sie jedoch Inhalte außerhalb von Aufrufbaren und Controllern echoen (zum Beispiel in einem Hook), werden Sie wahrscheinlich auf Probleme stoßen. Das Echoen von Inhalten in Hooks und vor dem tatsächlichen Ausführen des Frameworks hat in der Vergangenheit möglicherweise funktioniert, wird es aber in Zukunft nicht mehr tun.

### Wo Sie auf Probleme stoßen könnten
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
	// dies wird tatsächlich funktionieren
	echo '<p>Dieser Hallo Welt Satz wurde Ihnen von dem Buchstaben "H" präsentiert</p>';
});

Flight::before('start', function(){
	// Dinge wie diese werden einen Fehler verursachen
	echo '<html><head><title>Meine Seite</title></head><body>';
});

Flight::route('/', function(){
	// das ist tatsächlich in Ordnung
	echo 'Hallo Welt';

	// Das sollte auch in Ordnung sein
	Flight::hello();
});

Flight::after('start', function(){
	// dies wird einen Fehler verursachen
	echo '<div>Deine Seite wurde in '.(microtime(true) - START_TIME).' Sekunden geladen</div></body></html>';
});
```

### Einschalten des v2-Renderverhaltens

Können Sie Ihren alten Code so lassen, wie er ist, ohne eine Neuschreibung vorzunehmen, um ihn mit v3 zum Laufen zu bringen? Ja, können Sie! Sie können das v2-Renderverhalten aktivieren, indem Sie die Konfigurationsoption `flight.v2.output_buffering` auf `true` setzen. Dadurch können Sie weiterhin das alte Renderverhalten verwenden, aber es wird empfohlen, es in Zukunft zu beheben. In v4 des Frameworks wird dies entfernt werden.

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