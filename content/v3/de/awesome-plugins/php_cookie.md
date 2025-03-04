# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) ist eine einfache Bibliothek zum Verwalten von Cookies in Ihrer App.

## Installation

Die Installation ist mit Composer einfach.

```bash
composer require overclokk/cookie
```

## Verwendung

Die Verwendung ist so einfach wie das Registrieren einer neuen Methode in der Flight-Klasse.

```php
use Overclokk\Cookie\Cookie;

/*
 * Setzen Sie dies in Ihrer Bootstrap- oder public/index.php-Datei
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Setze ein Cookie

		// Sie möchten, dass dies falsch ist, damit Sie eine neue Instanz erhalten
		// verwenden Sie den folgenden Kommentar, wenn Sie eine Autovervollständigung wünschen
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // Name des Cookies
			'1', // der Wert, den Sie setzen möchten
			86400, // Anzahl der Sekunden, die das Cookie dauern soll
			'/', // Pfad, auf dem das Cookie verfügbar sein wird
			'example.com', // Domain, auf der das Cookie verfügbar sein wird
			true, // das Cookie wird nur über eine sichere HTTPS-Verbindung übertragen
			true // das Cookie ist nur über das HTTP-Protokoll verfügbar
		);

		// optional, wenn Sie die Standardwerte beibehalten und eine schnelle Möglichkeit haben möchten, ein Cookie lange zu setzen
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Überprüfen Sie, ob Sie das Cookie haben
		if (Flight::cookie()->has('stay_logged_in')) {
			// bringe sie z.B. in den Dashboard-Bereich.
			Flight::redirect('/dashboard');
		}
	}
}