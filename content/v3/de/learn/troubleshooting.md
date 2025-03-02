# Fehlerbehebung

Diese Seite hilft Ihnen bei der Fehlerbehebung von häufig auftretenden Problemen, auf die Sie bei der Verwendung von Flight stoßen könnten.

## Häufige Probleme

### 404 Nicht gefunden oder unerwartetes Routenverhalten

Wenn Sie einen 404-Nicht gefunden Fehler sehen (aber Sie schwören, dass er wirklich da ist und es sich nicht um einen Tippfehler handelt), könnte dies tatsächlich ein Problem sein, wenn Sie einen Wert in Ihrem Routen-Endpunkt zurückgeben, anstatt ihn einfach auszugeben. Der Grund dafür ist beabsichtigt, könnte aber einige Entwickler überraschen.

```php
Flight::route('/hallo', function(){
	// Dies könnte einen 404 Nicht gefunden Fehler verursachen
	return 'Hallo Welt';
});

// Was Sie wahrscheinlich wollen
Flight::route('/hallo', function(){
	echo 'Hallo Welt';
});
```

Der Grund dafür liegt in einem speziellen Mechanismus, der in den Router eingebaut ist und die Rückgabewerte als Anweisung für "zur nächsten Route gehen" behandelt. Das Verhalten ist im [Routing](/learn/routing#passing) Abschnitt dokumentiert.

### Klasse nicht gefunden (Autoloading funktioniert nicht)

Dafür könnte es einige Gründe geben, warum dies nicht passiert. Unten sind einige Beispiele aufgeführt, aber stellen Sie sicher, dass Sie auch den [Autoloading](/learn/autoloading) Abschnitt überprüfen.

#### Falscher Dateiname
Am häufigsten ist, dass der Klassenname nicht mit dem Dateinamen übereinstimmt.

Wenn Sie eine Klasse namens `MeineKlasse` haben, sollte die Datei `MeineKlasse.php` genannt werden. Wenn Sie eine Klasse namens `MeineKlasse` haben und die Datei `meineklasse.php` genannt wird, kann der Autoloader sie nicht finden.

#### Falscher Namespace
Wenn Sie Namespaces verwenden, sollte der Namespace mit der Verzeichnisstruktur übereinstimmen.

```php
// Code

// Wenn Ihr MyController im app/controllers Verzeichnis ist und benannt ist
// dann funktioniert dies nicht.
Flight::route('/hallo', 'MeinController->hallo');

// Sie müssen eine dieser Optionen auswählen
Flight::route('/hallo', 'app\controllers\MeinController->hallo');
// oder wenn Sie eine Verwendungserklärung oben haben

use app\controllers\MeinController;

Flight::route('/hallo', [ MeinController::class, 'hallo' ]);
// kann auch geschrieben werden
Flight::route('/hallo', MeinController::class.'->hallo');
// auch...
Flight::route('/hallo', [ 'app\controllers\MeinController', 'hallo' ]);
```

#### `path()` nicht definiert

Im Skelett-App ist dies im `config.php` File definiert, aber damit Ihre Klassen gefunden werden, müssen Sie sicherstellen, dass die `path()` Methode definiert ist (wahrscheinlich zum Stammverzeichnis Ihres Verzeichnisses), bevor Sie versuchen, sie zu verwenden.

```php
// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```  