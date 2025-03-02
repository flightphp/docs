# Autoloading

Autoloading ist ein Konzept in PHP, bei dem Sie ein Verzeichnis oder Verzeichnisse angeben, aus denen Klassen geladen werden sollen. Dies ist weitaus vorteilhafter als die Verwendung von `require` oder `include`, um Klassen zu laden. Es ist auch eine Voraussetzung für die Verwendung von Composer-Paketen.

Standardmäßig wird jede `Flight`-Klasse automatisch dank Composer geladen. Wenn Sie jedoch Ihre eigenen Klassen automatisch laden möchten, können Sie die `Flight::path()`-Methode verwenden, um ein Verzeichnis zum Laden von Klassen anzugeben.

## Grundbeispiel

Angenommen, wir haben einen Verzeichnisbaum wie folgt:

```text
# Beispiel-Pfad
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - enthält die Controller für dieses Projekt
│   ├── translations
│   ├── UTILS - enthält Klassen nur für diese Anwendung (dies ist alles großgeschrieben, um später ein Beispiel zu geben)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Sie haben möglicherweise festgestellt, dass dies die gleiche Dateistruktur wie diese Dokumentationsseite ist.

Sie können jedes Verzeichnis wie folgt zum Laden angeben:

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

// keine Namensräume erforderlich

// Alle automatisch geladenen Klassen sollten Pascal-Fall sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie Loader::setV2ClassLoading(false); ausführen;
class MyController {

	public function index() {
		// etwas machen
	}
}
```

## Namensräume

Wenn Sie Namensräume haben, wird es tatsächlich sehr einfach, dies zu implementieren. Verwenden Sie die `Flight::path()`-Methode, um das Stammverzeichnis (nicht das Dokumentenstammverzeichnis oder den `public/`-Ordner) Ihrer Anwendung anzugeben.

```php

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```

So könnte Ihr Controller aussehen. Schauen Sie sich das folgende Beispiel an, aber achten Sie auf die Kommentare für wichtige Informationen.

```php
/**
 * app/controllers/MyController.php
 */

// Namensräume sind erforderlich
// Namensräume entsprechen der Verzeichnisstruktur
// Namensräume müssen der gleichen Groß- und Kleinschreibung wie die Verzeichnisstruktur folgen
// Namensräume und Verzeichnisse dürfen keine Unterstriche enthalten (sofern Loader::setV2ClassLoading(false) nicht festgelegt ist)
namespace app\controllers;

// Alle automatisch geladenen Klassen sollten Pascal-Fall sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie Loader::setV2ClassLoading(false); ausführen;
class MyController {

	public function index() {
		// etwas machen
	}
}
```

Und wenn Sie eine Klasse in Ihrem "utils"-Verzeichnis automatisch laden möchten, würden Sie im Grunde dasselbe tun:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// Der Namensraum muss der Verzeichnisstruktur und Groß- und Kleinschreibung entsprechen (beachten Sie, dass das UTILS-Verzeichnis alle großgeschrieben ist
//     wie im obigen Dateibaum)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// etwas machen
	}
}
```

## Unterstriche in Klassennamen

Ab Version 3.7.2 können Sie Pascal_Snake_Case für Ihre Klassennamen verwenden, indem Sie `Loader::setV2ClassLoading(false);` ausführen.
Dadurch können Sie Unterstriche in Ihren Klassennamen verwenden.
Dies wird nicht empfohlen, steht aber für diejenigen zur Verfügung, die es benötigen.

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

// keine Namensräume erforderlich

class My_Controller {

	public function index() {
		// etwas machen
	}
}
```