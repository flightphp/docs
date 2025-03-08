# Drošība

Drošība ir svarīga, runājot par tīmekļa lietojumprogrammām. Jums jānodrošina, ka jūsu lietojumprogramma ir droša un ka jūsu lietotāju dati ir 
droši. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt jūsu tīmekļa lietojumprogrammas.

## Virsraksti

HTTP virsraksti ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammas. Jūs varat izmantot virsrakstus, lai novērstu klikšķināšanu, XSS un citus uzbrukumus. 
Ir vairāki veidi, kā jūs varat pievienot šos virsrakstus savam lietojumprogrammai.

Divas lieliskas vietnes, lai pārbaudītu jūsu virsrakstu drošību, ir [securityheaders.com](https://securityheaders.com/) un 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot manuāli

Jūs varat manuāli pievienot šos virsrakstus, izmantojot `header` metodi `Flight\Response` objektā.
```php
// Iestatiet X-Frame-Options virsrakstu, lai novērstu klikšķināšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy virsrakstu, lai novērstu XSS
// Piezīme: šis virsraksts var kļūt ļoti sarežģīts, tāpēc jums būs jākonsultējas
//  ar piemēriem internetā jūsu lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection virsrakstu, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options virsrakstu, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy virsrakstu, lai kontrolētu, cik daudz atsaucēju informācijas tiek nosūtīta
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security virsrakstu, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy virsrakstu, lai kontrolētu, kādas funkcijas un API var tikt izmantotas
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šos var pievienot jūsu `bootstrap.php` vai `index.php` failu augšpusē.

### Pievienot kā filtru

Jūs varat tos pievienot arī filtrā/ūdenī, piemēram, sekojošā:

```php
// Pievieno virsrakstus filtrā
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

### Pievienot kā vidusdaļu

Jūs varat tos pievienot arī kā vidusdaļu. Tas ir labs veids, kā uzturēt savu kodu tīru un sakārtotu.

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

// index.php vai kur jums ir jūsu maršruti
// FYI, šī tukšā virkne darbojas kā globālais vidusmērķis
// visiem maršrutiem. Protams, jūs varētu to darīt tāpat un pievienot
// to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// vairāk maršrutu
}, [ new SecurityHeadersMiddleware() ]);
```


## Krustojuma vietnes pieprasījums (CSRF)

Krustojuma vietnes pieprasījums (CSRF) ir uzbrukuma veids, kad ļaunprātīgs vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu vietni. 
To var izmantot, lai veiktu darbības jūsu vietnē, nenojaušot lietotāju. Flight nesniedz iebūvētu CSRF aizsardzības 
mehānismu, bet jūs to varat viegli īstenot paši, izmantojot vidusdaļu.

### Iestatījumi

Vispirms jums jāizveido CSRF tokens un jāuzglabā tas lietotāja sesijā. To varat izmantot savos veidlapās un pārbaudīt, kad 
veidlapa tiek iesniegta.

```php
// Izveidojiet CSRF tokenu un uzglabājiet to lietotāja sesijā
// (pieņemot, ka esat izveidojis sesijas objektu un pievienojis to Flight)
// skatiet sesijas dokumentāciju, lai iegūtu vairāk informācijas
Flight::register('session', \Ghostff\Session\Session::class);

// Jums ir jāizveido tikai viens tokens uz sesiju (lai tas darbotos 
// vairākās cilnēs un pieprasījumos tam pašam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Izmantojiet CSRF tokenu savā veidlapā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citas veidlapas lauki -->
</form>
```

#### Izmantojot Latte

Jūs varat arī iestatīt pielāgotu funkciju, lai izvadītu CSRF tokenu jūsu Latte veidnēs.

```php
// Iestatiet pielāgotu funkciju, lai izvadītu CSRF tokenu
// Piezīme: Skats ir konfigurēts ar Latte kā skatu motoru
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Un tagad savās Latte veidnēs jūs varat izmantot `csrf()` funkciju, lai izvadītu CSRF tokenu.

```html
<form method="post">
	{csrf()}
	<!-- citas veidlapas lauki -->
</form>
```

Īsi un vienkārši, vai ne?

### Pārbaudiet CSRF tokenu

Jūs varat pārbaudīt CSRF tokenu, izmantojot notikumu filtrus:

```php
// Šī vidusdaļa pārbauda, vai pieprasījums ir POST pieprasījums, un, ja tas ir, pārbauda, vai CSRF tokens ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// sagūstiet csrf tokenu no veidlapas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF tokens');
			// vai JSON atbildes
			Flight::jsonHalt(['error' => 'Nederīgs CSRF tokens'], 403);
		}
	}
});
```

Vai jūs varat izmantot vidusdaļas klasi:

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
				Flight::halt(403, 'Nederīgs CSRF tokens');
			}
		}
	}
}

// index.php vai kur jums ir jūsu maršruti
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// vairāk maršrutu
}, [ new CsrfMiddleware() ]);
```

## Krustojuma vietnes skriptu (XSS)

Krustojuma vietnes skriptu (XSS) ir uzbrukuma veids, kad ļaunprātīgs vietne var injicēt kodu jūsu vietnē. Lielākā daļa no šīm iespējām nāk 
no veidlapas vērtībām, kuras aizpildīs jūsu beigu lietotāji. Jums **nekad** nevajadzētu uzticēties izvadei no jūsu lietotājiem! Vienmēr pieņemiet, ka visi no viņiem ir 
labākie hakeri pasaulē. Viņi var injicēt ļaunprātīgu JavaScript vai HTML jūsu lapā. Šis kods var tikt izmantots, lai zagtu informāciju no jūsu 
lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skatu klasi, jūsu izejas datus var viegli novērst, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir gudrs un mēģina to izmantot kā savu vārdu
$name = '<script>alert("XSS")</script>';

// Tas aizsargās izvadi
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja jūs izmantojat kaut ko līdzīgu Latte, kas reģistrēts kā jūsu skatu klase, tas arī automātiski aizsargās šo.
Flight::view()->render('template', ['name' => $name]);
```

## SQL injekcija

SQL injekcija ir uzbrukuma veids, kad ļaunprātīgs lietotājs var injicēt SQL kodu jūsu datubāzē. To var izmantot, lai zagtu informāciju 
no jūsu datubāzes vai veiktu darbības jūsu datubāzē. Atkal jums **nekad** nevajadzētu uzticēties ievadei no jūsu lietotājiem! Vienmēr pieņemiet, ka viņi ir 
izsalkuši pēc asins. Jūs varat izmantot sagatavotos pieprasījumus savos `PDO` objektos, lai novērstu SQL injekciju.

```php
// Pieņemot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Ja jūs izmantojat PdoWrapper klasi, tas var tikt viegli paveikts vienā rindā
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Jūs varat darīt to pašu ar PDO objektu ar ? vietturēm
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Tikai soliet, ka jūs nekad NEIZDARIET kaut ko līdzīgu ...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// jo kas ja $username = "' OR 1=1; -- "; 
// Pēc vaicājuma izvešanas tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Tas izskatās dīvaini, taču tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Krustojuma resursu koplietošanas (CORS) mehānisms ļauj daudziem resursiem (piemēram, fontiem, JavaScript utt.) tīmekļa lapā 
tikt pieprasītiem no citas domēna, kas atrodas ārpus vietnes, no kuras resurss radās. Flight nesniedz iebūvētu funkcionalitāti, 
bet to var viegli apstrādāt ar ūdeni, lai to izpildītu pirms `Flight::start()` metodes izsaukšanas.

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
		// pielāgojiet šeit atļautās viesu vietnes.
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

// index.php vai kur jums ir jūsu maršruti
$CorsUtil = new CorsUtil();

// Šis ir jāizpilda pirms uzsākšanas.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Kļūdu apstrāde
Slēpiet jutīgas kļūdu detaļas ražošanā, lai izvairītos no informācijas noplūdes uzbrucējiem.

```php
// Jūsu bootstrap.php vai index.php

// flightphp/skeleton, tas ir app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Atspējot kļūdu parādīšanu
    ini_set('log_errors', 1);     // Reģistrējiet kļūdas
    ini_set('error_log', '/path/to/error.log');
}

// Jūsu maršrutos vai kontrolieros
// Izmantojiet Flight::halt() paredzētiem kļūdu atbildēm
Flight::halt(403, 'Piekļuve liegta');
```

## Ievades sanitizācija
Nekad neuzticieties lietotāja ievadei. Sanitizējiet to pirms apstrādes, lai novērstu ļaunprātīgu datu iekļūšanu.

```php

// Pieņemsim, ka ir $_POST pieprasījums ar $_POST['input'] un $_POST['email']

// Sanitizējiet virkne ievadi
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitizējiet e-pastu
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Paroles hashing
Uzglabājiet paroles droši un pārliecinieties par tām droši, izmantojot PHP iebūvētās funkcijas.

```php
$password = Flight::request()->data->password;
// Hash a password when storing (e.g., during registration)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verify a password (e.g., during login)
if (password_verify($password, $stored_hash)) {
    // Parole atbilst
}
```

## Pieprasījumu ierobežošana
Aizsargājiet pret brutālu spēku uzbrukumiem, ierobežojot pieprasījumu ātrumu, izmantojot kešatmiņu.

```php
// Pieņemot, ka jums ir flightphp/cache instalēts un reģistrēts
// Izmantojot flightphp/cache vidusdaļā
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Pārāk daudz pieprasījumu');
    }
    
    $cache->set($key, $attempts + 1, 60); // Atjaunot pēc 60 sekundēm
});
```

## Secinājums

Drošība ir svarīga un ir būtiski nodrošināt, lai jūsu tīmekļa lietojumprogrammas būtu drošas. Flight sniedz vairākas funkcijas, lai palīdzētu jums 
nodrošināt jūsu tīmekļa lietojumprogrammas, taču ir svarīgi vienmēr būt uzmanīgam un nodrošināt, lai jūs darītu visu iespējamo, lai saglabātu jūsu lietotāju 
datus drošībā. Vienmēr pieņemiet sliktāko un nekad neuzticieties ievadei no jūsu lietotājiem. Vienmēr aizbēdziet izvadi un izmantojiet sagatavotos pieprasījumus, lai novērstu SQL 
injekcijas. Vienmēr izmantojiet vidusdaļas, lai aizsargātu savas maršrutu no CSRF un CORS uzbrukumiem. Ja jūs darāt visu šo, jūs būsit labi ceļā, lai izveidotu drošas tīmekļa lietojumprogrammas.