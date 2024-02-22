# Sicherheit

Sicherheit ist bei Webanwendungen von großer Bedeutung. Sie möchten sicherstellen, dass Ihre Anwendung sicher ist und die Daten Ihrer Benutzer geschützt sind. Flight bietet eine Reihe von Funktionen, um Ihnen bei der Sicherung Ihrer Webanwendungen zu helfen.

## Überschriften

HTTP-Überschriften sind eine der einfachsten Möglichkeiten, um Ihre Webanwendungen zu sichern. Sie können Überschriften verwenden, um Clickjacking, XSS und andere Angriffe zu verhindern. Es gibt mehrere Möglichkeiten, diese Überschriften in Ihre Anwendung einzufügen.

Zwei großartige Websites zur Überprüfung der Sicherheit Ihrer Überschriften sind [securityheaders.com](https://securityheaders.com/) und [observatory.mozilla.org](https://observatory.mozilla.org/).

### Manuell hinzufügen

Sie können diese Überschriften manuell hinzufügen, indem Sie die `header`-Methode des `Flight\Response`-Objekts verwenden.
```php
// Setzen Sie die X-Frame-Options-Überschrift, um Clickjacking zu verhindern
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Setzen Sie die Content-Security-Policy-Überschrift, um XSS zu verhindern
// Hinweis: Diese Überschrift kann sehr komplex werden, daher sollten Sie
//  für Ihre Anwendung Beispiele im Internet konsultieren
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Setzen Sie die X-XSS-Protection-Überschrift, um XSS zu verhindern
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Setzen Sie die X-Content-Type-Options-Überschrift, um MIME-Sniffing zu verhindern
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Setzen Sie die Referrer-Policy-Überschrift, um zu steuern, wie viele Referrer-Informationen gesendet werden
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Setzen Sie die Strict-Transport-Security-Überschrift, um HTTPS zu erzwingen
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Setzen Sie die Permissions-Policy-Überschrift, um zu steuern, welche Funktionen und APIs verwendet werden können
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Diese können am Anfang Ihrer `bootstrap.php` oder `index.php`-Dateien hinzugefügt werden.

### Als Filter hinzufügen

Sie können sie auch in einem Filter/Hook wie folgt hinzufügen:

```php
// Füge die Überschriften einem Filter hinzu
Flight::before('start', function() {
	Flight::response()->header( 'X-Frame-Options', 'SAMEORIGIN' );
	Flight::response()->header( "Content-Security-Policy", "default-src 'self'" );
	Flight::response()->header( 'X-XSS-Protection', '1; mode=block' );
	Flight::response()->header( 'X-Content-Type-Options', 'nosniff' );
	Flight::response()->header( 'Referrer-Policy', 'no-referrer-when-downgrade' );
	Flight::response()->header( 'Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload' );
	Flight::response()->header( 'Permissions-Policy', 'geolocation=()' );
});
```

### Als Middleware hinzufügen

Sie können sie auch als Middleware-Klasse hinzufügen. Dies ist eine gute Möglichkeit, Ihren Code sauber und organisiert zu halten.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header( 'X-Frame-Options', 'SAMEORIGIN' );
		Flight::response()->header( "Content-Security-Policy", "default-src 'self'" );
		Flight::response()->header( 'X-XSS-Protection', '1; mode=block' );
		Flight::response()->header( 'X-Content-Type-Options', 'nosniff' );
		Flight::response()->header( 'Referrer-Policy', 'no-referrer-when-downgrade' );
		Flight::response()->header( 'Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload' );
		Flight::response()->header( 'Permissions-Policy', 'geolocation=()' );
	}
}

// index.php oder wo immer Ihre Routen sind
// Übrigens fungiert diese leere String-Gruppe als globales Middleware für
// alle Routen. Natürlich könnten Sie dasselbe tun und es nur zu bestimmten Routen hinzufügen.
Flight::group('', function( Router $router ) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// weitere Routen
}, [ new SecurityHeadersMiddleware() ]);