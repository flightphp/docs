# Drošība

Drošība ir liela lieta, kad runa ir par tīmekļa lietojumprogrammām. Jums ir jāpārliecinās, ka jūsu lietojumprogramma ir droša un jūsu lietotāju dati ir drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt savas tīmekļa lietojumprogrammas. Jūs varat izmantot galvenes, lai novērstu klikšķu izviltības, XSS un citas uzbrukumus. Ir vairāki veidi, kā pievienot šīs galvenes savai lietojumprogrammai.

Divas lieliskas vietnes, kur pārbaudīt savu galvenu drošību, ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Varat manuāli pievienot šīs galvenes, izmantojot `header` metodi `Flight\Response` objektam.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķu izviltības
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šī galvene var kļūt ļoti sarežģīta, tāpēc jums būs
// jākonsultējas ar piemēriem internetā savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sīkdatņu izjaukšanu
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz atsauces informācijas ir nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galveni, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot augšpusē jūsu `bootstrap.php` vai `index.php` failos.

### Pievienot kā filtru

To var pievienot arī kā filtru/iekārtu šādi:

```php
// Pievienojiet galvenes filtrā
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

### Pievienot kā starpprogrammu

To var pievienot arī kā starpprogrammu klasi. Tas ir labs veids, kā saglabāt jūsu kodu tīru un organizētu.

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

// index.php vai kur vien ir jūsu maršruti
// FYI, šī tukšā virkne grupas darbojas kā globāla starpprogramma visiem maršrutiem. Protams, jūs varētu darīt to pašu un tikai pievienot to noteiktām maršrutām.
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'UserController', 'getUsers' ]);
	// vairāki maršruti
}, [ new SecurityHeadersMiddleware() ]);

## Pārkrāpšana ar krustu vietu pieprasījumu (CSRF)

Pārkrāpšana ar krustu vietu pieprasījumu (CSRF) ir uzbrukuma veids, kur ļaunprātīga vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu vietni. To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat viegli ieviest savu, izmantojot starpprogrammu.

### Iestatīšana

Vispirms jums ir jāģenerē CSRF žetons un jāsaglabā tas lietotāja sesijā. Tad jūs varat izmantot šo žetonu savos veidlapās un pārbaudīt to, kad forma tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (pieņemot, ka esat izveidojis sesijas objektu un piesaistījis to Flight)
// Jums ir jāģenerē tikai viens žetons uz sesiju (tāpēc tas darbojas
// vairākos cilnēs un pieprasījumos vienam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Izmantojiet CSRF žetonu savā formā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi veidlapas lauki -->
</form>
```

#### Izmantojot Latte

Jūs varat arī iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php
// Iestatiet pielāgoto funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats ir konfigurēts, lai Latte būtu skata dzinējs
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Un tagad savos Latte veidnēs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citi veidlapas lauki -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šī starpprogramma pārbauda, vai pieprasījums ir POST pieprasījums un ja ir, tā pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// uzņemiet CSRF žetonu no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī jūs varat izmantot starpprogrammu klasi:

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

// index.php vai kur vien ir jūsu maršruti
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'UserController', 'getUsers' ]);
	// citas maršruti
}, [ new CsrfMiddleware() ]);

## Krusta vietu skriptēšana (XSS)

Krusta vietu skriptēšana (XSS) ir uzbrukuma veids, kur ļaunprātīga vietne var ievietot kodu jūsu vietnē. Lielākā daļa šādu iespēju nāk no veidlapas vērtībām, ko aizpilda jūsu gala lietotāji. Jums **nekad** nevajadzētu uzticēties jūsu lietotāju izvadei! Vienmēr pieņemiet, ka visi viņi ir labākie hakkeri pasaulē. Viņi var ievietot ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skata klasi, jūs varat viegli izvairīties no izvades, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir prasmīgs un mēģina izmantot to kā savu vārdu
$vārds = '<script>alert("XSS")</script>';

// Tas izvairīsies no izvades
Flight::view()->set('vārds', $vārds);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu Latte kā jūsu skata klasi, tas arī automātiski iekļaus šo
Flight::view()->render('veidne', ['vārds' => $vārds]);
```

## SQL ieiešana

SQL ieiešana ir uzbrukuma veids, kur ļaunprātīgs lietotājs var ievietot SQL kodu jūsu datu bāzē. To var izmantot, lai nozagtu informāciju no jūsu datu bāzes vai veiktu darbības jūsu datu bāzē. Atkal jums **nekad** nevajadzētu uzticēties jūsu lietotāju ievadei! Vienmēr pieņemiet, ka viņi ir aizsardzībā. Jūs varat izmantot sagatavotus teikumus savos `PDO` objektos, lai novērstu SQL ieiešanu.

```php
// Pieņemot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$teikums = Flight::db()->prepare('IZVĒLĒT * NO lietotāji KUR lietotājvārds = :lietotājvārds');
$teikums->execute([':lietotājvārds' => $lietotājvārds]);
$lietotāji = $teikums->fetchAll();

// Ja izmantojat PdoWrapper klasi, to var viegli izdarīt vienā rindiņā
$lietotāji = Flight::db()->fetchAll('IZVĒLĒT * NO lietotāji, KUR lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => $lietotājvārds ]);

// To var izdarīt arī ar PDO objektu, izmantojot ? aizvietotājzīmes
$teikums = Flight::db()->fetchAll('IZVĒLĒT * NO lietotāji KUR lietotājvārds = ?', [ $lietotājvārds ]);

// Tāpēc, soliet, ka nekad NĒDARĪS kaut ko līdzīgu tam...
$lietotāji = Flight::db()->fetchAll("IZVĒLĒT * NO lietotāji KUR lietotājvārds = '{$lietotājvārds}' LIMITS 5");
// jo ja $lietotājmaksas = "' VAI 1=1; -- "; 
// Pēc tam, kad izveidots vaicājums, tas izskatās šādi
// IZVĒLĒT * NO lietotāji KUR lietotājvārds = '' VAI 1=1; -- LIMITS 5
// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL ieiešanas uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Pārkāpšanas izcelsmes resursu kopīgošana (CORS) ir mehānisms, kas ļauj daudziem resursiem (piemēram, fontiem, JavaScript utt.) tīmekļa lapā tikt pieprasītiem no citas domēna, kas neatrodas no domēna, no kura resurss nāk. Flight nepiedāvā iebūvētu funkcionalitāti, bet šo var viegli apstrādāt ar āķi, kas darbojas pirms tiek izsaukts `Flight::start()` metodes.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// pielāgojiet savus atļautos saiturus šeit.
		$allowed = [
		# Drošība

Drošība ir liela lieta, kad runa ir par tīmekļa lietojumprogrammām. Jums ir jāpārliecinās, ka jūsu lietojumprogramma ir droša un jūsu lietotāju dati ir drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt savas tīmekļa lietojumprogrammas. Jūs varat izmantot galvenes, lai novērstu klikšķu izviltības, XSS un citas uzbrukumus. Ir vairāki veidi, kā pievienot šīs galvenes savai lietojumprogrammai.

Divas lieliskas vietnes, kur pārbaudīt savu galvenu drošību, ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Varat manuāli pievienot šīs galvenes, izmantojot `header` metodi `Flight\Response` objektam.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķu izviltības
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šī galvene var kļūt ļoti sarežģīta, tāpēc jums būs
// jākonsultējas ar piemēriem internetā savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sīkdatņu izjaukšanu
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz atsauces informācijas ir nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galveni, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot augšpusē jūsu `bootstrap.php` vai `index.php` failos.

### Pievienot kā filtru

To var pievienot arī kā filtru/iekārtu šādi:

```php
// Pievienojiet galvenes filtrā
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

### Pievienot kā starpprogrammu

To var pievienot arī kā starpprogrammu klasi. Tas ir labs veids, kā saglabāt jūsu kodu tīru un organizētu.

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

// index.php vai kur vien ir jūsu maršruti
// FYI, šī tukšā virkne grupas darbojas kā globāla starpprogramma visiem maršrutiem. Protams, jūs varētu darīt to pašu un tikai pievienot to noteiktām maršrutām.
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'UserController', 'getUsers' ]);
	// vairāki maršruti
}, [ new SecurityHeadersMiddleware() ]);

## Pārkrāpšana ar krustu vietu pieprasījumu (CSRF)

Pārkrāpšana ar krustu vietu pieprasījumu (CSRF) ir uzbrukuma veids, kur ļaunprātīga vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu vietni. To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat viegli ieviest savu, izmantojot starpprogrammu.

### Iestatīšana

Vispirms jums ir jāģenerē CSRF žetons un jāsaglabā tas lietotāja sesijā. Tad jūs varat izmantot šo žetonu savos veidlapās un pārbaudīt to, kad forma tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (pieņemot, ka esat izveidojis sesijas objektu un piesaistījis to Flight)
// Jums ir jāģenerē tikai viens žetons uz sesiju (tāpēc tas darbojas
// vairākos cilnēs un pieprasījumos vienam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Izmantojiet CSRF žetonu savā formā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi veidlapas lauki -->
</form>
```

#### Izmantojot Latte

Jūs varat arī iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php
// Iestatiet pielāgoto funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats ir konfigurēts, lai Latte būtu skata dzinējs
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Un tagad savos Latte veidnēs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citi veidlapas lauki -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šī starpprogramma pārbauda, vai pieprasījums ir POST pieprasījums un ja ir, tā pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// uzņemiet CSRF žetonu no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī jūs varat izmantot starpprogrammu klasi:

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

// index.php vai kur vien ir jūsu maršruti
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'UserController', 'getUsers' ]);
	// citas maršruti
}, [ new CsrfMiddleware() ]);

## Krusta vietu skriptēšana (XSS)

Krusta vietu skriptēšana (XSS) ir uzbrukuma veids, kur ļaunprātīga vietne var ievietot kodu jūsu vietnē. Lielākā daļa šādu iespēju nāk no veidlapas vērtībām, ko aizpilda jūsu gala lietotāji. Jums **nekad** nevajadzētu uzticēties jūsu lietotāju izvadei! Vienmēr pieņemiet, ka visi viņi ir labākie hakkeri pasaulē. Viņi var ievietot ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skata klasi, jūs varat viegli izvairīties no izvades, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir prasmīgs un mēģina izmantot to kā savu vārdu
$vārds = '<script>alert("XSS")</script>';

// Tas izvairīsies no izvades
Flight::view()->set('vārds', $vārds);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu Latte kā jūsu skata klasi, tas arī automātiski iekļaus šo
Flight::view()->render('veidne', ['vārds' => $vārds]);
```

## SQL ieiešana

SQL ieiešana ir uzbrukuma veids, kur ļaunprātīgs lietotājs var ievietot SQL kodu jūsu datu bāzē. To var izmantot, lai nozagtu informāciju no jūsu datu bāzes vai veiktu darbības jūsu datu bāzē. Atkal jums **nekad** nevajadzētu uzticēties jūsu lietotāju ievadei! Vienmēr pieņemiet, ka viņi ir aizsardzībā. Jūs varat izmantot sagatavotus teikumus savos `PDO` objektos, lai novērstu SQL ieiešanu.

```php
// Pieņemot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$teikums = Flight::db()->prepare('IZVĒLĒT * NO lietotāji KUR lietotājvārds = :lietotājvārds');
$teikums->execute([':lietotājvārds' => $lietotājvārds]);
$lietotāji = $teikums->fetchAll();

// Ja izmantojat PdoWrapper klasi, to var viegli izdarīt vienā rindiņā
$lietotāji = Flight::db()->fetchAll('IZVĒLĒT * NO lietotāji, KUR lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => $lietotājvārds ]);

// To var izdarīt arī ar PDO objektu, izmantojot ? aizvietotājzīmes
$teikums = Flight::db()->fetchAll('IZVĒLĒT * NO lietotāji KUR lietotājvārds = ?', [ $lietotājvārds ]);

// Just promise you will never EVER do something like this...
$lietotāji = Flight::db()->fetchAll("IZVĒLĒT * NO lietotāji KUR lietotājvārds = '{$lietotājvārds}' LIMITS 5");
// because what if $username = "' OR 1=1; -- "; 
// After the query is build it looks like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.
```

## CORS

Pārkāpšanas izcelsmes resursu kopīgošana (CORS) ir mehānisms, kas ļauj daudziem resursiem (piemēram, fontiem, JavaScript utt.) tīmekļa lapā tikt pieprasītiem no citas domēna, kas neatrodas no domēna, no kura resurss nāk. Flight nepiedāvā iebūvētu funkcionalitāti, bet šo var viegli apstrādāt ar āķi, kas darbojas pirms tiek izsaukts `Flight::start()` metodes.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// pielāgojiet savus atļautos saiturus šeit.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php or wherever you have your routes
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```