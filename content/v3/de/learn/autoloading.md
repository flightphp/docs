# Autoloading

## Überblick

Autoloading ist ein Konzept in PHP, bei dem Sie ein Verzeichnis oder Verzeichnisse angeben, aus denen Klassen geladen werden. Dies ist viel vorteilhafter als die Verwendung von `require` oder `include`, um Klassen zu laden. Es ist auch eine Voraussetzung für die Verwendung von Composer-Paketen.

## Verständnis

Standardmäßig wird jede `Flight`-Klasse dank Composer automatisch für Sie autoloaded. Wenn Sie jedoch Ihre eigenen Klassen autoloaden möchten, können Sie die Methode `Flight::path()` verwenden, um ein Verzeichnis anzugeben, aus dem Klassen geladen werden.

Die Verwendung eines Autoloaders kann Ihren Code auf erhebliche Weise vereinfachen. Statt dass Dateien mit einer Vielzahl von `include`- oder `require`-Anweisungen am Anfang beginnen, um alle in dieser Datei verwendeten Klassen zu erfassen, können Sie Ihre Klassen stattdessen dynamisch aufrufen, und sie werden automatisch eingeschlossen.

## Grundlegende Verwendung

Nehmen wir an, wir haben einen Verzeichnisbaum wie den folgenden:

```text
# Beispielpfad
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - enthält die Controller für dieses Projekt
│   ├── translations
│   ├── UTILS - enthält Klassen nur für diese Anwendung (dies ist absichtlich in Großbuchstaben für ein späteres Beispiel)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Sie haben vielleicht bemerkt, dass dies die gleiche Dateistruktur wie diese Dokumentationsseite ist.

Sie können jedes Verzeichnis zum Laden wie folgt angeben:

```php

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// Keine Namensräume erforderlich

// Alle autoloaded Klassen sollten im Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
class MyController {

	public function index() {
		// etwas tun
	}
}
```

## Namensräume

Wenn Sie Namensräume haben, wird es tatsächlich sehr einfach, dies zu implementieren. Sie sollten die Methode `Flight::path()` verwenden, um das Stammverzeichnis (nicht das Dokumentenroot oder den `public/`-Ordner) Ihrer Anwendung anzugeben.

```php

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```

Nun sieht Ihr Controller so aus. Schauen Sie sich das Beispiel unten an, aber achten Sie auf die Kommentare für wichtige Informationen.

```php
/**
 * app/controllers/MyController.php
 */

// Namensräume sind erforderlich
// Namensräume sind identisch mit der Verzeichnisstruktur
// Namensräume müssen dem gleichen Groß-/Kleinschreibungsstil der Verzeichnisstruktur folgen
// Namensräume und Verzeichnisse dürfen keine Unterstriche enthalten (es sei denn, Loader::setV2ClassLoading(false) ist gesetzt)
namespace app\controllers;

// Alle autoloaded Klassen sollten im Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Ab 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie Loader::setV2ClassLoading(false) ausführen;
class MyController {

	public function index() {
		// etwas tun
	}
}
```

Und wenn Sie eine Klasse in Ihrem utils-Verzeichnis autoloaden möchten, würden Sie im Wesentlichen dasselbe tun:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// Der Namensraum muss der Verzeichnisstruktur und dem Groß-/Kleinschreibungsstil entsprechen (beachten Sie, dass das UTILS-Verzeichnis in Großbuchstaben ist
//     wie im Dateibaum oben)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// etwas tun
	}
}
```

## Unterstriche in Klassennamen

Ab 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie `Loader::setV2ClassLoading(false);` ausführen. 
Dies ermöglicht es Ihnen, Unterstriche in Ihren Klassennamen zu verwenden. 
Dies wird nicht empfohlen, ist aber für diejenigen verfügbar, die es benötigen.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// Keine Namensräume erforderlich

class My_Controller {

	public function index() {
		// etwas tun
	}
}
```

## Siehe auch
- [Routing](/learn/routing) - Wie man Routen zu Controllern zuweist und Views rendert.
- [Warum ein Framework?](/learn/why-frameworks) - Das Verständnis der Vorteile der Verwendung eines Frameworks wie Flight.

## Fehlerbehebung
- Wenn Sie nicht herausfinden können, warum Ihre namespaced Klassen nicht gefunden werden, denken Sie daran, `Flight::path()` zum Stammverzeichnis in Ihrem Projekt zu verwenden, nicht zu Ihrem `app/`- oder `src/`-Verzeichnis oder Äquivalent.

### Klasse nicht gefunden (Autoloading funktioniert nicht)

Es könnte ein paar Gründe dafür geben, dass dies nicht passiert. Unten sind einige Beispiele, aber stellen Sie sicher, dass Sie auch den [Autoloading](/learn/autoloading)-Abschnitt überprüfen.

#### Falscher Dateiname
Der häufigste Grund ist, dass der Klassenname nicht zum Dateinamen passt.

Wenn Sie eine Klasse namens `MyClass` haben, sollte die Datei `MyClass.php` heißen. Wenn Sie eine Klasse namens `MyClass` haben und die Datei `myclass.php` heißt, 
kann der Autoloader sie nicht finden.

#### Falscher Namensraum
Wenn Sie Namensräume verwenden, sollte der Namensraum zur Verzeichnisstruktur passen.

```php
// ...code...

// Wenn Ihr MyController im app/controllers-Verzeichnis ist und namespaced
// Dies wird nicht funktionieren.
Flight::route('/hello', 'MyController->hello');

// Sie müssen eine dieser Optionen wählen
Flight::route('/hello', 'app\controllers\MyController->hello');
// oder wenn Sie oben eine use-Anweisung haben

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// kann auch so geschrieben werden
Flight::route('/hello', MyController::class.'->hello');
// auch...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` nicht definiert

In der Skeleton-App ist dies in der `config.php`-Datei definiert, aber damit Ihre Klassen gefunden werden, müssen Sie sicherstellen, dass die `path()`-Methode definiert ist (wahrscheinlich zum Stammverzeichnis Ihres Verzeichnisses), bevor Sie sie verwenden.

```php
// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```

## Änderungsprotokoll
- v3.7.2 - Sie können Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie `Loader::setV2ClassLoading(false);` ausführen
- v2.0 - Autoload-Funktionalität hinzugefügt.