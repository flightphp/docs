# Drošība

## Pārskats

Drošība ir ļoti svarīga tīmekļa lietotnēs. Jums jāpārliecinās, ka jūsu lietotne ir droša un ka jūsu lietotāju dati ir
drošībā. Flight nodrošina vairākas funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietotnes.

Oficiālais [skeleton](https://github.com/flightphp/skeleton) arī piegādā īpašu **`SECURITY.md`** un drošības galveņu starpprogrammatūru, lai [AI kodēšanas rīkiem](/learn/ai) (un cilvēkiem) būtu viena apzināta vieta noslēpumiem, galvenēm un XSS/SQL noteikumiem — atsevišķi no vispārējā kodēšanas stila `AGENTS.md`.

## Izpratne

Veidojot tīmekļa lietotnes, jāapzinās vairāki izplatīti drošības apdraudējumi. Daži no izplatītākajiem apdraudējumiem
ir:
- Starpvietņu pieprasījumu viltošana (CSRF)
- Starpvietņu skriptošana (XSS)
- SQL injekcija
- Resursu koplietošana starp izcelsmēm (CORS)

[Veidnes](/learn/templates) palīdz pret XSS, pēc noklusējuma veicot izvades aizbēgšanu (Twig un Latte to dara; izmantojiet šo priekšrocību). [Sesijas](/awesome-plugins/session) var palīdzēt pret CSRF, glabājot CSRF pilnvaru lietotāja sesijā, kā aprakstīts tālāk. Sagatavotu vaicājumu izmantošana ar PDO — vai palīgfunkcijas [SimplePdo](/learn/simple-pdo) — palīdz novērst SQL injekciju. CORS var apstrādāt ar vienkāršu āķi pirms `Flight::start()` izsaukšanas.

Visas šīs metodes darbojas kopā, lai palīdzētu uzturēt jūsu tīmekļa lietotnes drošas. Vienmēr jāpatur prātā, ka jāmācās un jāsaprot drošības labākās prakses. Nelūdziet AI asistentam “atspējot CSP” vai vājināt galvenes tikai tāpēc, lai lapa ielādētos, nesaprotot kompromisu.

## Pamata lietojums

### Galvenes

HTTP galvenes ir viens no vienkāršākajiem veidiem, kā aizsargāt savas tīmekļa lietotnes. Varat izmantot galvenes, lai novērstu clickjacking, XSS un citus uzbrukumus.
Ir vairāki veidi, kā pievienot šīs galvenes savai lietotnei.

Divi lieliski tīmekļa resursi jūsu galveņu drošības pārbaudei ir [securityheaders.com](https://securityheaders.com/) un
[observatory.mozilla.org](https://observatory.mozilla.org/). Pēc zemāk esošā koda iestatīšanas varat viegli pārbaudīt, vai jūsu galvenes darbojas, izmantojot šos divus tīmekļa resursus.

Skeleton ietver `App\Middleware\SecurityHeadersMiddleware` (CSP ar nonce uz katru pieprasījumu, frame opcijas, HSTS un citas). Labāk apzināti paplašināt to, nevis izslēgt galvenes.

#### Pievienot manuāli

Šīs galvenes varat pievienot manuāli, izmantojot metodi `header` objektā `Flight\Response`.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šī galvene var būt ļoti sarežģīta, tāpēc jums vajadzēs
//  konsultēties ar piemēriem internetā savai lietotnei
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME noteikšanu
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz referrera informācijas tiek sūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galveni, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Tās var pievienot jūsu `routes.php` vai `index.php` failu augšdaļā.

#### Pievienot kā filtru

Varat tās pievienot arī filtrā/āķī šādi:

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

#### Pievienot kā starpprogrammatūru

Varat tās pievienot arī kā starpprogrammatūras klasi, kas nodrošina vislielāko elastību attiecībā uz to, kuriem maršrutiem to piemērot. Parasti šīs galvenes jāpiemēro visām HTML un API atbildēm.

Skeleton stila ceļš un nosaukumvieta (**mapes reģistrs atbilst `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Dodiet priekšroku CSP nonce no bootstrap, ja jums ir iekļauti skripti (skeleton iestata csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — tukša virknes grupa = globālā starpprogrammatūra visiem maršrutiem
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// more routes
}, [SecurityHeadersMiddleware::class]);
```

Vecāki projekti joprojām var izmantot `app/middlewares` un `app\middlewares`; tas darbojas, ja mapes atbilst. Jaunie skeleton projekti izmanto **`app/Middleware/`** un **`App\Middleware`**. Skatiet [Automātiskā ielāde](/learn/autoloading).

### Starpvietņu pieprasījumu viltošana (CSRF)

Starpvietņu pieprasījumu viltošana (CSRF) ir uzbrukuma veids, kad ļaunprātīga vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu vietni.
To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības
mehānismu, bet jūs varat viegli ieviest savu, izmantojot starpprogrammatūru.

#### Iestatīšana

Vispirms jums jāģenerē CSRF pilnvara un jāsaglabā tā lietotāja sesijā. Pēc tam varat izmantot šo pilnvaru savās formās un pārbaudīt to, kad
forma tiek iesniegta. Mēs izmantosim [flightphp/session](/awesome-plugins/session) spraudni sesiju pārvaldībai.

```php
// Ģenerējiet CSRF pilnvaru un saglabājiet to lietotāja sesijā
// (pieņemot, ka esat izveidojis sesijas objektu un pievienojis to Flight)
// skatiet sesijas dokumentāciju, lai iegūtu vairāk informācijas
Flight::register('session', flight\Session::class);

// Jums ir jāģenerē tikai viena pilnvara uz sesiju (tāpēc tā darbojas
// vairākās cilnēs un pieprasījumos vienam un tam pašam lietotājam)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Izmantojot noklusējuma PHP Flight veidni

```html
<!-- Izmantojiet CSRF pilnvaru savā formā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi formas lauki -->
</form>
```

##### Izmantojot Twig (skeleton noklusējums)

Reģistrējiet Twig funkciju vai nododiet pilnvaru katrā formas skatā. Minimāls piemērs ar globālo + formas lauku:

```php
// Konfigurējot Twig (piem., services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# citi lauki #}
</form>
```

##### Izmantojot Latte

Varat arī iestatīt pielāgotu funkciju, lai Latte veidnēs izvadītu CSRF pilnvaru.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// citas konfigurācijas...

	// Iestatiet pielāgotu funkciju, lai izvadītu CSRF pilnvaru
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Un tagad savās Latte veidnēs varat izmantot funkciju `csrf()`, lai izvadītu CSRF pilnvaru.

```html
<form method="post">
	{csrf()}
	<!-- citi formas lauki -->
</form>
```

#### Pārbaudīt CSRF pilnvaru

CSRF pilnvaru varat pārbaudīt, izmantojot vairākas metodes.

##### Starpprogrammatūra

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// more routes
}, [CsrfMiddleware::class]);
```

##### Notikumu filtri

```php
// Šī starpprogrammatūra pārbauda, vai pieprasījums ir POST pieprasījums, un, ja tā, pārbauda, vai CSRF pilnvara ir derīga
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// iegūstiet csrf pilnvaru no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// vai JSON atbildei
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Starpvietņu skriptošana (XSS)

Starpvietņu skriptošana (XSS) ir uzbrukuma veids, kad ļaunprātīga formas ievade var ievadīt kodu jūsu vietnē. Lielākā daļa šo iespēju rodas
no formas vērtībām, ko aizpildīs jūsu galalietotāji. Jums **nekad** nevajadzētu uzticēties savu lietotāju izvadei! Vienmēr pieņemiet, ka visi no viņiem ir
labākie hakeri pasaulē. Viņi var ievadīt ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagtu informāciju no jūsu
lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skatu klasi vai veidņu dzinēju, piemēram, [Twig](/awesome-plugins/twig) vai [Latte](/awesome-plugins/latte), varat viegli aizbēgt izvadi, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir tik gudrs, ka mēģina to izmantot kā savu vārdu
$name = '<script>alert("XSS")</script>';

// Tas aizbēgs izvadi
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (skeleton noklusējums) un Latte pēc noklusējuma automātiski aizbēg — dodiet tām priekšroku pār neapstrādātu PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → aizbēgts
// Izvairieties no |raw / neaizbēgtas izvades, ja vien saturs nav pilnībā uzticams
```

### SQL injekcija

SQL injekcija ir uzbrukuma veids, kad ļaunprātīgs lietotājs var ievadīt SQL kodu jūsu datubāzē. To var izmantot, lai nozagtu informāciju
no jūsu datubāzes vai veiktu darbības jūsu datubāzē. Atkal jums **nekad** nevajadzētu uzticēties savu lietotāju ievadei! Vienmēr pieņemiet, ka viņi
tiecas pēc asiņu izliešanas. Izmantojiet sagatavotus vaicājumus — [SimplePdo](/learn/simple-pdo) palīgfunkcijas padara to par noklusējuma ceļu.

```php
// Pieņemot, ka Flight::db() ir reģistrēts kā SimplePdo (vai ievadiet SimplePdo kontrolierī)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (ieteicamais) — vienrindīgi ar piesaistītiem parametriem
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Tā pati ideja ar ? vietturiem
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Skeleton stila kontrolieros dodiet priekšroku `SimplePdo` konstruktora injekcijai, nevis `Flight::db()`, lai testi un AI ģenerētais kods paliktu konsekventi ([DIC](/learn/dependency-injection-container)).

#### Nedrošs piemērs

Tālāk redzamais ir iemesls, kāpēc mēs izmantojam SQL sagatavotos vaicājumus, lai aizsargātos no naiviem piemēriem, kā zemāk:

```php
// galalietotājs aizpilda tīmekļa formu.
// formas vērtībā hakeris ievieto kaut ko šādu:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Pēc vaicājuma izveides tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas strādās. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.

var_dump($users); // tas izdrukās visus lietotājus datubāzē, nevis tikai vienu lietotājvārdu
```

### Noslēpumi un konfigurācija

- Ievietojiet noslēpumus **`.env`** (vai reālajā vidē), nevis versiju kontrolē iekļautos `config.php` paraugos.
- Skeleton noteikums: burtiski noklusējumi `config.php`; apvienojiet vidi bootstrap laikā; **nelasiet** `$_ENV` kontrolieros — tā vietā injicējiet konfigurāciju. Skatiet [Konfigurācija](/learn/configuration).
- Nekad neiekļaujiet versiju kontrolē API atslēgas, DB paroles vai sesijas šifrēšanas atslēgas. Norādiet AI rīkiem uz **`SECURITY.md`**, lai tie neizgudrotu nedrošus saīsinājumus.

### JSONP atzvanīšanas validācija

Ja izmantojat Flight metodi `Flight::jsonp()`, ņemiet vērā, ka Flight validē JSONP atzvanīšanas parametra nosaukumu pret stingru atļauto saraksta regulāro izteiksmi (`/^[A-Za-z_$][\w$.]{0,127}$/`). Jebkurš atzvanīšanas nosaukums, kas neatbilst šim modelim, liks Flight izmest izņēmumu, novēršot patvaļīga JavaScript ievadīšanu ar ļaunprātīgu atzvanīšanas vērtību.

Šī validācija ir iebūvēta un neprasa papildu konfigurāciju, taču par to ir vērts zināt, atkļūdojot negaidītas kļūdas no JSONP galapunktiem.

### CORS

Resursu koplietošana starp izcelsmēm (CORS) ir mehānisms, kas ļauj daudzus resursus (piem., fontus, JavaScript utt.) tīmekļa lapā
pieprasīt no citas domēnas ārpus domēnas, no kuras resurss radies. Flight nav iebūvētas funkcionalitātes,
bet to var viegli apstrādāt ar āķi, kas jāizpilda pirms `Flight::start()` metodes izsaukšanas.

```php
// app/Utils/CorsUtil.php  (skeleton: PascalCase Utils mape → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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
		// pielāgojiet atļautos resursdatorus šeit.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / routes — izpildīt pirms start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight konfigurācijas nostiprināšana

Flight atklāj vairākus dzinēja iestatījumus, kuriem ir tieša ietekme uz drošību. Pareiza to iestatīšana ir viens no vienkāršākajiem veidiem, kā nostiprināt savu lietotni.

#### `flight.allow_method_override`

Pēc noklusējuma Flight ļauj klientiem ignorēt pieprasījuma HTTP metodi, izmantojot `X-HTTP-Method-Override` galveni vai `_method` lauku POST pamattekstā. Lai gan tas ir ērti HTML formām, kas var nosūtīt tikai `GET`/`POST`, tas var būt bīstami, ja to negaidāt — uzbrucējs varētu viltot `DELETE` vai `PUT` pieprasījumus caur parastu formu.

Ja jūsu lietotne nepaļaujas uz šo uzvedību (piem., jūs veidojat API, ko patērē moderni klienti vai JavaScript priekšgali, kas var nosūtīt jebkuru HTTP darbības vārdu), jums tas jāatspējo:

```php
// Jūsu index.php vai bootstrap failā, pirms Flight::start()
Flight::set('flight.allow_method_override', false);
```

Noklusējuma vērtība ir `true` saderībai ar iepriekšējām versijām, bet **iestatīt to uz `false` ir ļoti ieteicams** jebkurai lietotnei, kurai nav tiešas vajadzības pēc ignorēšanas funkcijas.

#### `flight.debug`

Flight ir iestatījums `flight.debug`, kas kontrolē, vai pārlūkā tiek attēlota detalizēta kļūdas informācija (izņēmuma ziņojums, kods un pilna izsaukumu steks), kad notiek neapstrādāts izņēmums. Noklusējums ir `false`, kas nozīmē, ka tiek parādīts tikai vispārīgs `500 Internal Server Error` ziņojums — klientam netiek izpausta nekāda iekšēja informācija.

Nekad neiespējojiet to produkcijas serverī. Izmantojiet to tikai lokāli vai pirmsprodukcijas vidē:

```php
// Droši tikai vietējai izstrādei — NEKAD produkcijā
Flight::set('flight.debug', true);
```

Kad `flight.debug` ir `false` (noklusējums), jūs joprojām varat uztvert kļūdas, iespējojot `flight.log_errors`:

```php
// Reģistrējiet kļūdas servera pusē, neizpaužot tās klientam
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight iebūvētā `View` klase labprāt iekļaus veidni no absolūtā ceļa vai no relatīva nosaukuma, kas izkāpj ārpus `flight.views.path` (piemēram, ar `../`). Tas ir apzināti lietotnēm, kas apzināti koplieto veidnes starp mapēm, taču tas ir arī ceļa caurskatīšanas risks, ja veidnes nosaukums kādreiz nāk no neuzticamas ievades.

`flight.views.restrict_to_path` pēc **noklusējuma ir izslēgts**, lai esošās lietotnes turpinātu darboties. Ieslēdziet to, ja vien jums nav dokumentēta iemesla to nedarīt:

```php
// Jūsu index.php vai bootstrap failā, pirms Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Dzinējs piemēro šo iestatījumu `View::$restrictToPath`, kad skats tiek izveidots (tāds pats modelis kā `flight.views.path` un `flight.views.extension`). Kad tas ir ieslēgts:

- `render()` un `fetch()` iekļauj tikai failus, kuru reālais ceļš atrodas konfigurētajā skatu direktorijā (arī simboliskās saites, kas norāda uz ārpusi, tiek noraidītas).
- `exists()` šiem pašiem ceļiem atgriež `false`, nevis izmet izņēmumu.
- `getTemplate()` pats par sevi nav mainīts — tas joprojām atgriež ceļus tāpat kā vienmēr.
- Bloķēts fails izmet `Template file is outside the views path.` Pazudušam failam joprojām tiek izmests esošais ziņojums `Template file not found: ...`.

Ja izmantojat Twig vai Latte ar saviem failu sistēmas ielādētājiem, kas norāda uz jūsu skatu direktoriju, šie dzinēji jau ierobežo veidnes līdz šai saknei. Tomēr ieslēdziet šo Flight vietējam `View`, lai jebkurš kods, kas izsauc `Flight::view()->render()` / `fetch()`, iegūtu tādu pašu aizsardzību. Oficiālais [skeleton](https://github.com/flightphp/skeleton) to iespējo bootstrap.

#### Ieteicamā produkcijas konfigurācija

```php
// index.php vai piemērots no lietotnes config / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Kļūdu apstrāde
Produkcijā slēpiet sensitīvu kļūdu informāciju, lai izvairītos no informācijas noplūdes uzbrucējiem. Produkcijā reģistrējiet kļūdas, nevis attēlojiet tās, iestatot `display_errors` uz `0`.

```php
// Jūsu bootstrap.php vai index.php

// pievienojiet to savam app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Atspējot kļūdu attēlošanu
    ini_set('log_errors', 1);     // Tā vietā reģistrēt kļūdas
    ini_set('error_log', '/path/to/error.log');
}

// Jūsu maršrutos vai kontrolieros
// Izmantojiet Flight::halt() kontrolētām kļūdu atbildēm
Flight::halt(403, 'Access denied');
```

### Ievades attīrīšana
Nekad neuzticieties lietotāja ievadei. Attīriet to, izmantojot [filter_var](https://www.php.net/manual/en/function.filter-var.php), pirms apstrādes, lai novērstu ļaunprātīgu datu iekļūšanu. Labāk lasiet ievadi, izmantojot `$app->request()` (vai `Flight::request()`), nevis neapstrādātus `$_GET` / `$_POST` lietotnes kodā.

```php

// Pieņemsim, ka ir $_POST pieprasījums ar $_POST['input'] un $_POST['email']

// Attīrīt virknes ievadi
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Attīrīt e-pastu
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Paroļu hešēšana
Glabājiet paroles droši un pārbaudiet tās droši, izmantojot PHP iebūvētās funkcijas, piemēram, [password_hash](https://www.php.net/manual/en/function.password-hash.php) un [password_verify](https://www.php.net/manual/en/function.password-verify.php). Paroles nekad nedrīkst glabāt vienkāršā tekstā, kā arī tās nedrīkst šifrēt ar atgriezeniskām metodēm. Hešēšana nodrošina, ka pat tad, ja jūsu datubāze tiek apdraudēta, faktiskās paroles paliek aizsargātas.

```php
$password = Flight::request()->data->password;
// Jaucējiet paroli, to glabājot (piem., reģistrācijas laikā)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Pārbaudiet paroli (piem., pieteikšanās laikā)
if (password_verify($password, $stored_hash)) {
    // Parole sakrīt
}
```

### Pieprasījumu biežuma ierobežošana
Aizsargājieties pret brute force uzbrukumiem vai pakalpojuma atteikuma uzbrukumiem, ierobežojot pieprasījumu biežumu ar kešatmiņu.

```php
// Pieņemot, ka jums ir instalēts un reģistrēts flightphp/cache
// Izmantojot flightphp/cache filtrā
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Atiestatīt pēc 60 sekundēm
});
```

## Skatiet arī
- [Sesijas](/awesome-plugins/session) — kā droši pārvaldīt lietotāju sesijas.
- [Veidnes](/learn/templates) — Twig/Latte automātiskā aizbēgšana un XSS.
- [SimplePdo](/learn/simple-pdo) — datubāzes palīgfunkcijas ar sagatavotiem vaicājumiem.
- [PdoWrapper](/learn/pdo-wrapper) — novecojis; jaunam kodam izmantojiet SimplePdo.
- [Starpprogrammatūra](/learn/middleware) — kā izmantot starpprogrammatūru, lai vienkāršotu drošības galveņu pievienošanu.
- [Konfigurācija](/learn/configuration) — `.env` pret burtisku konfigurāciju, produkcijas karodziņi.
- [AI un izstrādātāja pieredze](/learn/ai) — glabājiet drošības politiku `SECURITY.md` aģentiem.
- [Atbildes](/learn/responses) — kā pielāgot HTTP atbildes ar drošām galvenēm.
- [Pieprasījumi](/learn/requests) — kā apstrādāt un attīrīt lietotāja ievadi.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) — PHP funkcija ievades attīrīšanai.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) — PHP funkcija drošai paroļu hešēšanai.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) — PHP funkcija hešētu paroļu pārbaudei.

## Problēmu novēršana
- Skatiet iepriekšējo sadaļu “Skatiet arī”, lai iegūtu informāciju par problēmu novēršanu saistībā ar Flight Framework komponentiem.
- Ja CSP bloķē jūsu skriptus, pievienojiet nonce (skeleton modelis) vai iekļaujiet atļauto sarakstā konkrētas izcelsmes — neiestatiet `script-src *` bez plāna.

## Izmaiņu žurnāls
- Dokumentācija — Skeleton `App\Middleware`, Twig CSRF/XSS piezīmes, SimplePdo, noslēpumi/`.env` un `SECURITY.md` AI draudzīgiem projektiem.
- Dokumentācija — dokumentēts `flight.views.restrict_to_path` sadaļā Flight konfigurācijas nostiprināšana (pēc izvēles ceļa ierobežošana vietējām veidnēm).
- v3.18.1 — Pievienota Flight konfigurācijas nostiprināšanas sadaļa, kas aptver `flight.allow_method_override`, `flight.debug` un JSONP atzvanīšanas validāciju.
- v3.1.0 — Pievienotas sadaļas par CORS, kļūdu apstrādi, ievades attīrīšanu, paroļu hešēšanu un pieprasījumu biežuma ierobežošanu.
- v2.0 — Pievienota aizbēgšana noklusējuma veidnēm, lai novērstu XSS.