# Drošība

## Pārskats

Drošība ir svarīga lieta, kad runa ir par tīmekļa lietojumprogrammām. Jūs vēlaties pārliecināties, ka jūsu lietojumprogramma ir droša un ka jūsu lietotāju dati ir 
drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt jūsu tīmekļa lietojumprogrammu drošību.

## Izpratne

Ir vairākas izplatītas drošības draudi, par kuriem jums jāzina, būvējot tīmekļa lietojumprogrammas. Daži no visizplatītākajiem draudiem
ietver:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) palīdz ar XSS, automātiski aizbēgot izvadi pēc noklusējuma, tāpēc jums nav jāatceras to darīt. [Sessions](/awesome-plugins/session) var palīdzēt ar CSRF, uzglabājot CSRF žetonu lietotāja sesijā, kā aprakstīts zemāk. Izmantojot sagatavotus paziņojumus ar PDO, var palīdzēt novērst SQL injekcijas uzbrukumus (vai izmantojot ērtas metodes [PdoWrapper](/learn/pdo-wrapper) klasē). CORS var apstrādāt ar vienkāršu āķi pirms `Flight::start()` tiek izsaukts.

Visas šīs metodes darbojas kopā, lai palīdzētu saglabāt jūsu tīmekļa lietojumprogrammu drošību. Jums vienmēr jādomā par labākajām drošības praksēm, lai mācītos un saprastu tās.

## Pamata Izmantošana

### Virsraksti

HTTP virsraksti ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammu drošību. Jūs varat izmantot virsrakstus, lai novērstu clickjacking, XSS un citus uzbrukumus. 
Ir vairākas veidi, kā jūs varat pievienot šos virsrakstus savai lietojumprogrammai.

Divas lieliskas vietnes, kur pārbaudīt jūsu virsrakstu drošību, ir [securityheaders.com](https://securityheaders.com/) un 
[observatory.mozilla.org](https://observatory.mozilla.org/). Pēc tam, kad iestatīsiet zemāk esošo kodu, jūs viegli varēsiet pārbaudīt, vai jūsu virsraksti darbojas, izmantojot šīs divas vietnes.

#### Pievienot Manuāli

Jūs varat manuāli pievienot šos virsrakstus, izmantojot `header` metodi uz `Flight\Response` objekta.
```php
// Iestatīt X-Frame-Options virsrakstu, lai novērstu clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatīt Content-Security-Policy virsrakstu, lai novērstu XSS
// Piezīme: šis virsraksts var kļūt ļoti sarežģīts, tāpēc jums būs jāmeklē
//  piemēri internetā savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatīt X-XSS-Protection virsrakstu, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatīt X-Content-Type-Options virsrakstu, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatīt Referrer-Policy virsrakstu, lai kontrolētu, cik daudz referrer informācijas tiek nosūtīta
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatīt Strict-Transport-Security virsrakstu, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatīt Permissions-Policy virsrakstu, lai kontrolētu, kuras funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šos var pievienot jūsu `routes.php` vai `index.php` failu augšdaļā.

#### Pievienot kā Filtru

Jūs varat tos arī pievienot filtrā/āķī, kā sekojošā: 

```php
// Pievienot virsrakstus filtrā
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

#### Pievienot kā Vidutāju

Jūs varat tos arī pievienot kā vidutāja klasi, kas nodrošina lielāko elastību, kurām maršrutiem to piemērot. Vispārīgi, šie virsraksti jāpiemēro visām HTML un API atbildēm.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php vai kur jums ir jūsu maršruti
// FYI, šī tukšā virkne darbojas kā globāls vidutājs visiem
// maršrutiem. Protams, jūs varētu izdarīt to pašu un pievienot
// to tikai specifiskiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// vairāk maršrutu
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) ir uzbrukuma veids, kur ļaunprātīga vietne var likt lietotāja pārlūkprogrammai nosūtīt pieprasījumu uz jūsu vietni. 
To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, 
bet jūs varat viegli ieviest savu, izmantojot vidutāju.

#### Iestatīšana

Vispirms jums jāģenerē CSRF žetons un jāglabā tas lietotāja sesijā. Tad jūs varat izmantot šo žetonu savās formās un pārbaudīt to, kad 
forma tiek iesniegta. Mēs izmantosim [flightphp/session](/awesome-plugins/session) spraudni, lai pārvaldītu sesijas.

```php
// Ģenerēt CSRF žetonu un glabāt to lietotāja sesijā
// (pieņemot, ka jūs esat izveidojis sesijas objektu un pievienojis to Flight)
// skatiet sesijas dokumentāciju vairāk informācijai
Flight::register('session', flight\Session::class);

// Jums jāģenerē tikai viens žetons uz sesiju (tā tas darbojas 
// visās vairākās cilnēs un pieprasījumos tam pašam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Izmantojot noklusējuma PHP Flight Veidni

```html
<!-- Izmantot CSRF žetonu savā formā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citas formas lauki -->
</form>
```

##### Izmantojot Latte

Jūs varat arī iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// citas konfigurācijas...

	// Iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Un tagad savos Latte veidņos jūs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citas formas lauki -->
</form>
```

#### Pārbaudīt CSRF Žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot vairākas metodes.

##### Vidutājs

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php vai kur jums ir jūsu maršruti
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// vairāk maršrutu
}, [ CsrfMiddleware::class ]);
```

##### Notikuma Filtri

```php
// Šis vidutājs pārbauda, vai pieprasījums ir POST pieprasījums un, ja ir, tas pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// uztvert csrf žetonu no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// vai JSON atbildei
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) ir uzbrukuma veids, kur ļaunprātīga formas ievade var injicēt kodu jūsu vietnē. Lielākā daļa no šīm iespējām nāk 
no formas vērtībām, kuras jūsu gala lietotāji aizpildīs. Jums **nekad** nevajadzētu uzticēties izvadai no jūsu lietotājiem! Vienmēr pieņemiet, ka visi no viņiem ir 
labākie hakeri pasaulē. Viņi var injicēt ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagt informāciju no jūsu 
lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skata klasi vai citu veidņu dzinēju, piemēram, [Latte](/awesome-plugins/latte), jūs varat viegli aizbēgt izvadi, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir gudrs un mēģina izmantot šo kā savu vārdu
$name = '<script>alert("XSS")</script>';

// Tas aizbēgs izvadi
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja jūs izmantojat kaut ko tādu kā Latte, kas reģistrēts kā jūsu skata klase, tas arī automātiski aizbēgs šo.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection ir uzbrukuma veids, kur ļaunprātīgs lietotājs var injicēt SQL kodu jūsu datubāzē. To var izmantot, lai nozagt informāciju 
no jūsu datubāzes vai veiktu darbības jūsu datubāzē. Atkal jums **nekad** nevajadzētu uzticēties ievadei no jūsu lietotājiem! Vienmēr pieņemiet, ka viņi ir 
izslāpuši pēc asinīm. Jūs varat izmantot sagatavotus paziņojumus jūsu `PDO` objektos, kas novērsīs SQL injekciju.

```php
// Pieņemot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Ja jūs izmantojat PdoWrapper klasi, to var viegli izdarīt vienā rindā
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Jūs varat izdarīt to pašu ar PDO objektu ar ? aizstājējiem
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Nedroša Piemēra

Zemāk ir iemesls, kāpēc mēs izmantojam SQL sagatavotus paziņojumus, lai aizsargātu no nevainīgiem piemēriem, piemēram, zemāk:

```php
// gala lietotājs aizpilda tīmekļa formu.
// formas vērtībai hakeris ievada kaut ko šādu:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Pēc vaicājuma būvēšanas tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.

var_dump($users); // tas izdrukās visus lietotājus datubāzē, nevis tikai to vienu lietotājvārdu
```

### CORS

Cross-Origin Resource Sharing (CORS) ir mehānisms, kas ļauj daudziem resursiem (piem., fonti, JavaScript utt.) tīmekļa lapā tikt 
pieprasītiem no cita domēna ārpus domēna, no kura resurss radies. Flight nav iebūvētas funkcionalitātes, 
bet to var viegli apstrādāt ar āķi, kas darbojas pirms `Flight::start()` metodes izsaukšanas.

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
		// pielāgojiet jūsu atļautās saimnieces šeit.
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

// Tam jādarbojas pirms start darbojas.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Kļūdu Apstrāde
Slēpjiet sensitīvas kļūdu detaļas produkcijā, lai izvairītos no informācijas noplūdes uzbrucējiem. Produkcijā reģistrējiet kļūdas, nevis rādiet tās ar `display_errors` iestatītu uz `0`.

```php
// Jūsu bootstrap.php vai index.php

// pievienojiet šo jūsu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Izslēgt kļūdu rādīšanu
    ini_set('log_errors', 1);     // Reģistrēt kļūdas
    ini_set('error_log', '/path/to/error.log');
}

// Jūsu maršrutos vai kontroļeros
// Izmantojiet Flight::halt() kontrolētai kļūdu atbildēm
Flight::halt(403, 'Access denied');
```

### Ievades Sanitizācija
Nekad neuzticieties lietotāja ievadei. Sanitizējiet to, izmantojot [filter_var](https://www.php.net/manual/en/function.filter-var.php), pirms apstrādes, lai novērstu ļaunprātīgu datu iekļūšanu.

```php

// Pieņemsim POST pieprasījumu ar $_POST['input'] un $_POST['email']

// Sanitizēt virkni ievadi
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitizēt e-pastu
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Paroles Hāšošana
Glabājiet paroles droši un pārbaudiet tās droši, izmantojot PHP iebūvētās funkcijas, piemēram, [password_hash](https://www.php.net/manual/en/function.password-hash.php) un [password_verify](https://www.php.net/manual/en/function.password-verify.php). Paroles nekad nevajadzētu glabāt vienkāršā tekstā, nedrīkst tās šifrēt ar atgriezeniskām metodēm. Hāšošana nodrošina, ka pat ja jūsu datubāze ir kompromitēta, faktiskās paroles paliek aizsargātas.

```php
$password = Flight::request()->data->password;
// Hāšot paroli, kad glabājat (piem., reģistrācijas laikā)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Pārbaudīt paroli (piem., pieteikšanās laikā)
if (password_verify($password, $stored_hash)) {
    // Parole sakrīt
}
```

### Ātruma Ierobežošana
Aizsargājiet pret brute force uzbrukumiem vai servisa atteikuma uzbrukumiem, ierobežojot pieprasījumu ātrumu ar kešu.

```php
// Pieņemot, ka jums ir uzstādīts un reģistrēts flightphp/cache
// Izmantojot flightphp/cache filtrā
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Atjaunot pēc 60 sekundēm
});
```

## Skatīt Arī
- [Sessions](/awesome-plugins/session) - Kā droši pārvaldīt lietotāja sesijas.
- [Templates](/learn/templates) - Izmantojot veidnes, lai automātiski aizbēgtu izvadi un novērstu XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Vienkāršotas datubāzes mijiedarbības ar sagatavotiem paziņojumiem.
- [Middleware](/learn/middleware) - Kā izmantot vidutājus, lai vienkāršotu drošības virsrakstu pievienošanas procesu.
- [Responses](/learn/responses) - Kā pielāgot HTTP atbildes ar drošiem virsrakstiem.
- [Requests](/learn/requests) - Kā apstrādāt un sanitizēt lietotāja ievadi.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - PHP funkcija ievades sanitizācijai.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - PHP funkcija drošai paroles hāšošanai.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - PHP funkcija hāšoto parolu pārbaudei.

## Traucējummeklēšana
- Atsauce uz "Skatīt Arī" sadaļu iepriekš, lai iegūtu traucējummeklēšanas informāciju, kas saistīta ar problēmām Flight Framework komponentos.

## Izmaiņu Žurnāls
- v3.1.0 - Pievienotas sadaļas par CORS, Kļūdu Apstrādi, Ievades Sanitizāciju, Paroles Hāšošanu un Ātruma Ierobežošanu.
- v2.0 - Pievienota aizbēgšana noklusējuma skatiem, lai novērstu XSS.