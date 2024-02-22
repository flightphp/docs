# Drošība

Drošība ir liela lieta, runājot par tīmekļa lietojumprogrammām. Jums jānodrošina, ka jūsu lietojumprogramma ir droša un jūsu lietotāju dati ir drošībā. Flight piedāvā vairākas funkcijas, lai palīdzētu nodrošināt jūsu tīmekļa lietojumprogrammas drošību.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammas drošību. Jūs varat izmantot galvenes, lai novērstu klikšķināšanas ievilkšanu, XSS un citas uzbrukuma veidus. Ir vairāki veidi, kā pievienot šīs galvenes savai lietojumprogrammai.

Divas lieliskas tīmekļa vietnes, kurās varat pārbaudīt savu galvenu drošību ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Šīs galvenes varat pievienot manuāli, izmantojot `header` metodi objektam `Flight\Response`.
```php
// Iestatiet X-Frame-Options galvu, lai novērstu klikšķināšanas ievilkšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy, lai novērstu XSS
// Piezīme: šī galva var kļūt ļoti sarežģīta, tāpēc jums būs jākonsultējas ar piemēriem tīmeklī, pielietojot to savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galvu, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options, lai novērstu MIME pārbaudi
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galvu, lai kontrolētu, cik daudz referrera informācijas tiek nosūtīta
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galvu, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galvu, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot virsotnē savā failā `bootstrap.php` vai `index.php`.

### Pievienot kā filtru

Jūs varat tos pievienot arī kā filtru/ķekaru, tāpat kā šādi:

```php
// Pievienojiet galvenes ķekarā
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

### Pievienot kā starpposmu

Jūs arī varat tos pievienot kā starpposmu klasi. Tas ir labs veids, kā turēt savu kodu tīru un organizētu.

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

// index.php vai jebkur citur, kur ir jūsu maršruti
// FYI, šis tukšais sākuma grupa darbojas kā globālais starpposms
// visiem maršrutiem. Protams, jūs varētu darīt to pašu un pievienot
// to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/lietotaji', [ 'LietotajaKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns SecurityHeadersMiddleware() ]);
```


## Krustvietas pieprasījumu viltosana (CSRF)

Krustvietas pieprasījumu viltosana (CSRF) ir veids, kā uzbruc ļaunprātīga tīmekļa vietne, kas var padarīt lietotāja pārlūkprogrammu nosūtīt pieprasījumu uz jūsu tīmekļa vietni. To var izmantot, lai veiktu darbības jūsu tīmekļa vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat ērti izveidot savu, izmantojot starpposmus.

### Iestatīšana

Vispirms jums ir jāģenerē CSRF žetons un jāsaglabā to lietotāja sesijā. Pēc tam jūs varat izmantot šo žetonu savos veidlapos un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (ņemot vērā, ka esat izveidojis sesijas objektu un piesaistījis to Flight)
// Jums ir jāģenerē tikai vienu žetonu sesiju (tāpēc tas darbojas
// pārāk vairākās cilnēs un pieprasījumos vienam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Lietojiet CSRF žetonu savā veidlapā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi veidlapas lauki -->
</form>
```

#### Izmantojot Latte

Jūs arī varat iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php
// Iestatiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats ir konfigurēts ar Latte kā skata dzinēju
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Tagad savos Latte veidņos varat izmantot funkciju `csrf()`, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citi veidlapas lauki -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu ar notikumu filtriem:

```php
// Šis starpposms pārbauda, vai pieprasījums ir POST pieprasījums un, ja tā ir, tas pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// iegūt CSRF žetonu no veidlapas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī varat izmantot starpposmu klasi:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Nederīgs CSRF žetons');
			}
		}
	}
}

// index.php vai jebkur citur, kur ir jūsu maršruti
Flight::group('', function(Router $router) {
	$router->get('/lietotaji', [ 'LietotajaKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns CsrfMiddleware() ]);
```


## Krustvietas skriptēšana (XSS)

Krustvietas skriptēšana (XSS) ir veids, kā uzbruc ļaunprātīga tīmekļa vietne var ievietot kodu jūsu tīmekļa vietnē. Lielākā daļa šo iespēju nāk no formu vērtībām, ko aizpildīs jūsu gala lietotāji. Jums **nekad** nevajadzētu uzticēties saviem lietotāju izvades datiem! Vienu vienmēr pieņemiet, ka visi ir labākie hakkeri pasaulē. Viņi var ievietot ļaunu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagt informāciju no jūsu lietotājiem vai veikt darbības jūsu tīmekļa vietnē. Izmantojot Flight skata klasi, jūs varat viegli izvairīties no izvades, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir prasmīgs un mēģina izmantot to kā savu vārdu
$nosaukums = '<script>alert("XSS")</script>';

// Šis izvairīsies no izvades
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko tādu kā Latte reģistrēts kā jūsu skata klase, tas arī automātiski izvairīsies no šāda veida
Flight::view()->render('veidne', ['name' => $name]);
```

## SQL injekcija

SQL injekcija ir veids, kā ļaunprātīgs lietotājs var injicēt SQL kodu jūsu datubāzē. To var izmantot, lai nozagt informāciju no jūsu datubāzes vai veikt darbības jūsu datubāzē. Vēlreiz jums **nekad** nevajadzētu uzticēties savu lietotāju ievades datiem! Vienu vienmēr pieņemiet, ka viņi ir asinīs. Jūs varat izmantot sagatavotas izteiksmes savos `PDO` objektos, lai novērstu SQL injekciju.

```php
// Paredzot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$teikums = Flight::db()->prepare('SELECT * FROM lietotaji WHERE lietotājvārds = :lietotājvārds');
$teikums->execute([':lietotājvārds' => $lietotājvārds]);
$lietotāji = $teikums->fetchAll();

// Ja izmantojat PdoWrapper klasi, šo var viegli izdarīt vienā rindā
$lietotāji = Flight::db()->fetchAll('SELECT * FROM lietotaji WHERE lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => $lietotājvārds ]);

// Jūs varat paveikt to pašu ar PDO objektu ar ? atrašanās vietām
$teikums = Flight::db()->fetchAll('SELECT * FROM lietotaji WHERE lietotājvārds = ?', [ $lietotājvārds ]);

// Tikai sola, ka jūs nekad NEPĀKARĪS NO darīt kaut ko tādu kā...
$lietotāji = Flight::db()->fetchAll("SELECT * FROM lietotaji WHERE lietotājvārds = '{$lietotājvārds}' LIMIT 5");
// jo kas notiek, ja $lietotājvārds = "' OR 1=1; -- ";
// Pēc vaicājuma ir izveidots tas izskatās šādi
// IZVĒLIETIES * NO lietotaji KUR lietotājvārds = '' VAI 1=1; -- LIMIT 5
// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Krusta-originu resursu kopīgošana (CORS) ir mehānisms, kas ļauj pieprasīt daudzus resursus (piemēram, fontus, JavaScript utt.) no tīmekļa lapas citā domēnā, neatkarīgi no avota domēna. Flight neietver iebūvētu funkcionalitāti, bet šo var viegli rīkoties, izmantojot starpposmus vai notikumu filtrus, līdzīgi kā CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->atļautAvotus();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function atļautAvotus(): void
	{
# Drošība

Drošība ir liela lieta, runājot par tīmekļa lietojumprogrammām. Jums jānodrošina, ka jūsu lietojumprogramma ir droša un jūsu lietotāju dati ir drošībā. Flight piedāvā vairākas funkcijas, lai palīdzētu nodrošināt jūsu tīmekļa lietojumprogrammas drošību.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammas drošību. Jūs varat izmantot galvenes, lai novērstu klikšķināšanas ievilkšanu, XSS un citas uzbrukuma veidus. Ir vairāki veidi, kā pievienot šīs galvenes savai lietojumprogrammai.

Divas lieliskas tīmekļa vietnes, kurās varat pārbaudīt savu galvenu drošību ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Šīs galvenes varat pievienot manuāli, izmantojot `header` metodi objektam `Flight\Response`.
```php
// Iestatiet X-Frame-Options galvu, lai novērstu klikšķināšanas ievilkšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy, lai novērstu XSS
// Piezīme: šī galva var kļūt ļoti sarežģīta, tāpēc jums būs jākonsultējas ar piemēriem tīmeklī, pielietojot to savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galvu, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options, lai novērstu MIME pārbaudi
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galvu, lai kontrolētu, cik daudz referrera informācijas tiek nosūtīta
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galvu, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galvu, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot virsotnē savā failā `bootstrap.php` vai `index.php`.

### Pievienot kā filtru

Jūs varat tos pievienot arī kā filtru/ķekaru, tāpat kā šādi:

```php
// Pievienojiet galvenes ķekarā
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

### Pievienot kā starpposmu

Jūs arī varat tos pievienot kā starpposmu klasi. Tas ir labs veids, kā turēt savu kodu tīru un organizētu.

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

// index.php vai jebkur citur, kur ir jūsu maršruti
// FYI, šis tukšais sākuma grupa darbojas kā globālais starpposms
// visiem maršrutiem. Protams, jūs varētu darīt to pašu un pievienot
// to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/lietotaji', [ 'LietotajaKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns SecurityHeadersMiddleware() ]);
```


## Krustvietas pieprasījumu viltosana (CSRF)

Krustvietas pieprasījumu viltosana (CSRF) ir veids, kā uzbruc ļaunprātīga tīmekļa vietne, kas var padarīt lietotāja pārlūkprogrammu nosūtīt pieprasījumu uz jūsu tīmekļa vietni. To var izmantot, lai veiktu darbības jūsu tīmekļa vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat ērti izveidot savu, izmantojot starpposmus.

### Iestatīšana

Vispirms jums ir jāģenerē CSRF žetons un jāsaglabā to lietotāja sesijā. Pēc tam jūs varat izmantot šo žetonu savos veidlapos un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (ņemot vērā, ka esat izveidojis sesijas objektu un piesaistījis to Flight)
// Jums ir jāģenerē tikai vienu žetonu sesiju (tāpēc tas darbojas
// pārāk vairākās cilnēs un pieprasījumos vienam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Lietojiet CSRF žetonu savā veidlapā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi veidlapas lauki -->
</form>
```

#### Izmantojot Latte

Jūs arī varat iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php
// Iestatiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats ir konfigurēts ar Latte kā skata dzinēju
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Tagad savos Latte veidņos varat izmantot funkciju `csrf()`, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citi veidlapas lauki -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu ar notikumu filtriem:

```php
// Šis starpposms pārbauda, vai pieprasījums ir POST pieprasījums un, ja tā ir, tas pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// iegūt CSRF žetonu no veidlapas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī varat izmantot starpposmu klasi:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Nederīgs CSRF žetons');
			}
		}
	}
}

// index.php vai jebkur citur, kur ir jūsu maršruti
Flight::group('', function(Router $router) {
	$router->get('/lietotaji', [ 'LietotajaKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns CsrfMiddleware() ]);
```


## Krustvietas skriptēšana (XSS)

Krustvietas skriptēšana (XSS) ir veids, kā uzbruc ļaunprātīga tīmekļa vietne var ievietot kodu jūsu tīmekļa vietnē. Lielākā daļa šo iespēju nāk no formu vērtībām, ko aizpildīs jūsu gala lietotāji. Jums **nekad** nevajadzētu uzticēties saviem lietotāju izvades datiem! Vienu vienmēr pieņemiet, ka visi ir labākie hakkeri pasaulē. Viņi var ievietot ļaunu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagt informāciju no jūsu lietotājiem vai veikt darbības jūsu tīmekļa vietnē. Izmantojot Flight skata klasi, jūs varat viegli izvairīties no izvades, lai novērstu XSS uzbrukumus.

```php
// Let's assume the user is clever as tries to use this as their name
$name = '<script>alert("XSS")</script>';

// This will escape the output
Flight::view()->set('name', $name);
// This will output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// If you use something like Latte registered as your view class, it will also auto escape this.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection is a type of attack where a malicious user can inject SQL code into your database. This can be used to steal information from your database or perform actions on your database. Again you should **never** trust input from your users! Always assume they are out for blood. You can use prepared statements in your `PDO` objects will  prevent SQL injection.

```php
// Assuming you have Flight::db() registered as your PDO object
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// If you use the PdoWrapper class, this can easily be done in one line
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// You can do the same thing with a PDO object with ? placeholders
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Just promise you will never EVER do something like this...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// because what if $username = "' OR 1=1; -- "; 
// After the query is build it looks like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.
```

## CORS

Cross-Origin Resource Sharing (CORS) is a mechanism that allows many resources (e.g., fonts, JavaScript, etc.) on a web page to be requested from another domain outside the domain from which the resource originated. Flight does not have built in functionality but this can easily be handled with middleware of event filters similar to CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// customize your allowed hosts here.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php or wherever you have your routes
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusion

Security is a big deal and it's important to make sure your web applications are secure. Flight provides a number of features to help you secure your web applications, but it's important to always be vigilant and make sure you're doing everything you can to keep your users' data safe. Always assume the worst and never trust input from your users. Always escape output and use prepared statements to prevent SQL injection. Always use middleware to protect your routes from CSRF and CORS attacks. If you do all of these things, you'll be well on your way to building secure web applications.