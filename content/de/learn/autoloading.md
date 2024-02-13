# Autoloading

Autoloading ist ein Konzept in PHP, bei dem Sie ein Verzeichnis oder Verzeichnisse angeben, aus denen Klassen geladen werden sollen. Dies ist wesentlich vorteilhafter als die Verwendung von `require` oder `include`, um Klassen zu laden. Es ist auch eine Voraussetzung für die Verwendung von Composer-Paketen.

Standardmäßig wird jede `Flight`-Klasse dank Composer automatisch für Sie geladen. Wenn Sie jedoch Ihre eigenen Klassen automatisch laden möchten, können Sie die Methode `Flight::path` verwenden, um ein Verzeichnis anzugeben, aus dem Klassen geladen werden sollen.

## Grundlegendes Beispiel

Angenommen, wir haben einen Verzeichnisbaum wie folgt:

```text
# Beispiel-Pfad
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - enthält die Controller für dieses Projekt
│   ├── translations
│   ├── UTILS - enthält Klassen nur für diese Anwendung (dies ist absichtlich alles in Großbuchstaben für ein späteres Beispiel)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Sie haben vielleicht festgestellt, dass dies die gleiche Dateistruktur wie diese Dokumentationsseite ist.

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

// Keine Namensräume erforderlich

// Alle automatisch geladenen Klassen sollten Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Es ist eine Anforderung, dass Sie keinen Unterstrich im Klassennamen haben dürfen
class MyController {

	public function index() {
		// etwas machen
	}
}
```

## Namensräume

Wenn Sie Namensräume haben, ist es tatsächlich sehr einfach, dies zu implementieren. Verwenden Sie die Methode `Flight::path()`, um das Stammverzeichnis (nicht das Dokumentstammverzeichnis oder den `public/`-Ordner) Ihrer Anwendung anzugeben.

```php

/**
 * public/index.php
 */

// Fügen Sie einen Pfad zum Autoloader hinzu
Flight::path(__DIR__.'/../');
```

So könnte Ihr Controller dann aussehen. Schauen Sie sich das folgende Beispiel an, aber achten Sie auf die Kommentare für wichtige Informationen.

```php
/**
 * app/controllers/MyController.php
 */

// Namensräume sind erforderlich
// Namensräume entsprechen der Verzeichnisstruktur
// Namensräume müssen der gleichen Großschreibung wie die Verzeichnisstruktur folgen
// Namensräume und Verzeichnisse dürfen keine Unterstriche enthalten
namespace app\controllers;

// Alle automatisch geladenen Klassen sollten Pascal Case sein (jedes Wort großgeschrieben, keine Leerzeichen)
// Es ist eine Anforderung, dass Sie keinen Unterstrich im Klassennamen haben dürfen
class MyController {

	public function index() {
		// etwas machen
	}
}
```

Und wenn Sie eine Klasse in Ihrem UTILS-Verzeichnis automatisch laden möchten, würden Sie im Grunde dasselbe tun:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// Der Namespace muss der Verzeichnisstruktur und Großschreibung entsprechen (beachten Sie, dass das UTILS-Verzeichnis alles in Großbuchstaben ist
//     wie im obigen Dateibaum)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// etwas machen
	}
}
```