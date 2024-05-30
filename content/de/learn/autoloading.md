# Autoloaden

Autoloaden ist ein Konzept in PHP, bei dem Sie ein Verzeichnis oder Verzeichnisse zum Laden von Klassen angeben. Dies ist viel vorteilhafter als die Verwendung von `require` oder `include` zum Laden von Klassen. Es ist auch eine Anforderung für die Verwendung von Composer-Paketen.

Standardmäßig wird jede `Flight`-Klasse dank Composer automatisch für Sie autoloadet. Wenn Sie jedoch Ihre eigenen Klassen autoloaden möchten, können Sie die Methode `Flight::path` verwenden, um ein Verzeichnis zum Laden von Klassen anzugeben.

## Grundbeispiel

Angenommen, wir haben einen Verzeichnisbaum wie folgt:

```text
# Beispiel-Pfad
/home/user/projekt/mein-flight-projekt/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - enthält die Controller für dieses Projekt
│   ├── translations
│   ├── UTILS - enthält Klassen nur für diese Anwendung (das ist absichtlich alles in Großbuchstaben für ein späteres Beispiel)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Sie haben vielleicht bemerkt, dass dies die gleiche Dateistruktur wie diese Dokumentationsseite ist.

Sie können jedes zu ladende Verzeichnis wie folgt angeben:

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

// kein Namespacing erforderlich

// Alle autoloadeten Klassen sollten Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie Loader::setV2ClassLoading(false); ausführen
class MyController {

	public function index() {
		// etwas tun
	}
}
```

## Namensräume

Wenn Sie Namensräume haben, wird dies tatsächlich sehr einfach zu implementieren. Sie sollten die Methode `Flight::path()` verwenden, um das Stammverzeichnis (nicht das Dokument-Stammverzeichnis oder den `public/`-Ordner) Ihrer Anwendung anzugeben.

```php

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```

So könnte Ihr Controller aussehen. Werfen Sie einen Blick auf das folgende Beispiel, achten Sie jedoch auf die Kommentare für wichtige Informationen.

```php
/**
 * app/controllers/MyController.php
 */

// Namensräume sind erforderlich
// Namensräume entsprechen der Verzeichnisstruktur
// Namensräume müssen der Groß- und Kleinschreibung der Verzeichnisstruktur entsprechen
// Namensräume und Verzeichnisse dürfen keine Unterstriche enthalten (sofern Loader::setV2ClassLoading(false) nicht festgelegt ist)
namespace app\controllers;

// Alle autoloadeten Klassen sollten Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie Loader::setV2ClassLoading(false); ausführen
class MyController {

	public function index() {
		// etwas tun
	}
}
```

Und wenn Sie eine Klasse in Ihrem utils-Verzeichnis autoloaden möchten, würden Sie im Grunde dasselbe tun:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// Der Namensraum muss der Verzeichnisstruktur und der Groß- und Kleinschreibung entsprechen (beachten Sie das UTILS-Verzeichnis in Großbuchstaben
//     wie im obigen Dateibaum)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// etwas tun
	}
}
```

## Unterstriche in Klassennamen

Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie `Loader::setV2ClassLoading(false);` ausführen. Dadurch können Sie Unterstriche in Ihren Klassennamen verwenden. Dies wird nicht empfohlen, steht aber für diejenigen zur Verfügung, die es benötigen.

```php

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

// kein Namespacing erforderlich

class My_Controller {

	public function index() {
		// etwas tun
	}
}
```