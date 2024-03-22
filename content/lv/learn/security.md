# Drošība

Drošība ir liela problēma, runājot par tīmekļa lietotnēm. Jums jānodrošina, lai jūsu lietotne būtu droša un jūsu lietotāju dati būtu drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt drošību savām tīmekļa lietotnēm.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt drošību jūsu tīmekļa lietotnēm. Jūs varat izmantot galvenes, lai novērstu klikšķinājumu krāpšanu, XSS un citas uzbrukuma formas. Ir vairāki veidi, kā pievienot šīs galvenes savai lietotnei.

Divas lieliskas vietnes, kurās varat pārbaudīt savu galvenu drošību, ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Jūs varat manuāli pievienot šīs galvenes, izmantojot `header` metodi objektā `Flight\Response`.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķinājumu krāpšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šī galvene var kļūt ļoti sarežģīta, tāpēc jums vajadzēs
// konsultēties ar piemēriem internetā jūsu lietotnei
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz informācijas tiek nosūtīta par nosūtītāju
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galveni, lai kontrolētu, kādas funkcijas un API varēs izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot virspusē jūsu `bootstrap.php` vai `index.php` failos.

### Pievienot kā filtru

Jūs tos varat pievienot arī kā filtru/aksi, piemēram:

```php
// Pievienot galvenes kā filtru
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Pievienot kā starpziņu

Jūs tos varat pievienot arī kā starpziņu klasi. Tas ir labs veids, kā saglabāt jūsu kodu tīru un kārtotu.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php vai kur jums ir marsruta
// Pamanījums, šis tukšais virknes grupa darbojas kā vispārējā starpziņa visiem maršrutiem. Protams, jūs varētu darīt to pašu un pievienot to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/lietotaji', [ 'UserController', 'getUsers' ]);
	// vairāk maršrutu
}, [ new SecurityHeadersMiddleware() ]);