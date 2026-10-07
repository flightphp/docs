# Drošība

## Pārskats

Drošība ir ļoti svarīga, runājot par tīmekļa lietotnēm. Jūs vēlaties pārliecināties, ka jūsu lietotne ir droša un ka jūsu lietotāju dati ir drošībā. Flight nodrošina vairākas funkcijas, kas palīdz nodrošināt jūsu tīmekļa lietotņu drošību.

Oficiālais [skeleton](https://github.com/flightphp/skeleton) arī piegādā īpašu **`SECURITY.md`** un drošības galveņu starpprogrammatūru, lai [AI kodēšanas rīkiem](/learn/ai) (un cilvēkiem) būtu viena apzināta vieta noslēpumiem, galvenēm un XSS/SQL noteikumiem — atsevišķi no vispārējā kodēšanas stila `AGENTS.md`.

## Izpratne

Ir vairāki izplatīti drošības apdraudējumi, par kuriem jums jāzina, veidojot tīmekļa lietotnes. Daži no visizplatītākajiem apdraudējumiem ir:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL injekcija
- Cross Origin Resource Sharing (CORS)

[Veidnes](/learn/templates) palīdz novērst XSS, pēc noklusējuma aizsargājot izvadi (Twig un Latte to dara; izmantojiet šo priekšrocību). [Sesijas](/awesome-plugins/session) var palīdzēt novērst CSRF, saglabājot CSRF pilnvaru lietotāja sesijā, kā aprakstīts tālāk. Sagatavoto vaicājumu izmantošana ar PDO — vai palīgfunkcijas [SimplePdo](/learn/simple-pdo) — palīdz novērst SQL injekciju. CORS var apstrādāt ar vienkāršu āķi pirms `Flight::start()` izsaukšanas.

Visas šīs metodes darbojas kopā, lai palīdzētu uzturēt jūsu tīmekļa lietotnes drošas. Jums vienmēr ir jādomā par drošības labāko prakšu apguvi un izpratni. Nelūdziet AI asistentam "atslēgt CSP" vai vājināt galvenes tikai tāpēc, lai lapa ielādētos, nesaprotot kompromisu.

## Pamata lietošana

### Galvenes

HTTP galvenes ir viens no vienkāršākajiem veidiem, kā nodrošināt tīmekļa lietotņu drošību. Varat izmantot galvenes, lai novērstu klikšķu nolaupīšanu, XSS un citus uzbrukumus. Ir vairāki veidi, kā varat pievienot šīs galvenes savai lietotnei.

Divas lieliskas vietnes, kur pārbaudīt savu galveņu drošību, ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/). Pēc zemāk esošā koda iestatīšanas varat viegli pārbaudīt, vai jūsu galvenes darbojas, izmantojot šīs divas vietnes.

Skeleton ietver `App\Middleware\SecurityHeadersMiddleware` (CSP ar pieprasījuma nonce, kadra opcijām, HSTS un citiem). Dodiet priekšroku tā apzinātai paplašināšanai, nevis galveņu izslēgšanai.

#### Pievienot manuāli

Varat manuāli pievienot šīs galvenes, izmantojot `header` metodi uz `Flight\Response` objekta.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķu nolaupīšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// piezīme: šī galvene var būt ļoti sarežģīta, tāpēc jums vajadzēs
//  konsultēties ar piemēriem internetā savai lietotnei
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz atsauces informācijas tiek nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galveni, lai kontrolētu, kuras funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Tās var pievienot jūsu `routes.php` vai `index.php` failu augšdaļā.

#### Pievienot kā filtru

Varat arī pievienot tās filtrā/āķī, kā parādīts zemāk: 

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

Varat arī pievienot tās kā starpprogrammatūras klasi, kas nodrošina vislielāko elastību, uz kuriem maršrutiem to piemērot. Kopumā šīs galvenes jāpiemēro visām HTML un API atbildēm.

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
		// Priekšroku dodiet CSP nonce no bootstrap, ja jums ir iekļauti skripti (skeleton iestata csp_nonce)
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
	// vairāk maršrutu
}, [SecurityHeadersMiddleware::class]);
```

Vecāki projekti joprojām var izmantot `app/middlewares` un `app\middlewares`; tas darbojas, ja mapes sakrīt. Jaunās skeleton lietotnes izmanto **`app/Middleware/`** un **`App\Middleware`**. Skatiet [Automātiskā ielāde](/learn/autoloading).

### Krustvietņu pieprasījumu viltošana (CSRF)

Krustvietņu pieprasījumu viltošana (CSRF) ir uzbrukuma veids, kur ļaunprātīga vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu vietni. To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat viegli ieviest savu, izmantojot starpprogrammatūru.

#### Iestatīšana

Vispirms jums ir jāģenerē CSRF pilnvara un jāsaglabā tā lietotāja sesijā. Pēc tam varat izmantot šo pilnvaru savās formās un pārbaudīt to, kad forma tiek iesniegta. Mēs izmantosim [flightphp/session](/awesome-plugins/session) spraudni sesiju pārvaldībai.

```php
// Ģenerējiet CSRF pilnvaru un saglabājiet to lietotāja sesijā
// (pieņemot, ka esat izveidojis sesijas objektu un pievienojis to Flight)
// skatiet sesijas dokumentāciju, lai iegūtu vairāk informācijas
Flight::register('session', flight\Session::class);

// Jums ir jāģenerē tikai viena pilnvara vienā sesijā (tāpēc tā darbojas 
// vairākās cilnēs un pieprasījumos vienam lietotājam)
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

Reģistrējiet Twig funkciju vai nododiet pilnvaru katrā formas skatā. Minimālais piemērs ar globālu + formas lauku:

```php
// Konfigurējot Twig (piemēram, services.php)
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

Varat arī iestatīt pielāgotu funkciju, lai izvadītu CSRF pilnvaru savās Latte veidnēs.

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

Un tagad savās Latte veidnēs varat izmantot `csrf()` funkciju, lai izvadītu CSRF pilnvaru.

```html
<form method="post">
	{csrf()}
	<!-- citi formas lauki -->
</form>
```

#### Pārbaudiet CSRF pilnvaru

Varat pārbaudīt CSRF pilnvaru, izmantojot vairākas metodes.

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
	// vairāk maršrutu
}, [CsrfMiddleware::class]);
```

##### Notikumu filtri

```php
// Šī starpprogrammatūra pārbauda, vai pieprasījums ir POST pieprasījums, un, ja tā, tā pārbauda, vai CSRF pilnvara ir derīga
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

### Krustvietņu skriptošana (XSS)

Krustvietņu skriptošana (XSS) ir uzbrukuma veids, kur ļaunprātīga formas ievade var injicēt kodu jūsu vietnē. Lielākā daļa šo iespēju rodas no formas vērtībām, ko aizpilda jūsu galalietotāji. Jums **nekad** nevajadzētu uzticēties savu lietotāju izvadei! Vienmēr pieņemiet, ka viņi visi ir labākie hakeri pasaulē. Viņi var injicēt ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skata klasi vai veidņu dzinēju, piemēram, [Twig](/awesome-plugins/twig) vai [Latte](/awesome-plugins/latte), varat viegli aizsargāt izvadi, lai novērstu XSS uzbrukumus.

```php
// Pieņemsim, ka lietotājs ir gudrs un mēģina izmantot šo kā savu vārdu
$name = '<script>alert("XSS")</script>';

// Tas aizsargās izvadi
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (skeleton noklusējums) un Latte pēc noklusējuma automātiski aizsargā — dodiet tiem priekšroku pār neapstrādātu PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → aizsargāts
// Izvairieties no |raw / neaizsargātas izvades, ja vien saturs nav pilnībā uzticams
```

### SQL injekcija

SQL injekcija ir uzbrukuma veids, kur ļaunprātīgs lietotājs var injicēt SQL kodu jūsu datubāzē. To var izmantot, lai nozagtu informāciju no jūsu datubāzes vai veiktu darbības jūsu datubāzē. Atkal, jums **nekad** nevajadzētu uzticēties lietotāju ievadei! Vienmēr pieņemiet, ka viņi cenšas jums nodarīt ļaunu. Izmantojiet sagatavotos vaicājumus — [SimplePdo](/learn/simple-pdo) palīgfunkcijas padara to par noklusējuma ceļu.

```php
// Pieņemot, ka esat reģistrējis Flight::db() kā SimplePdo (vai injicējis SimplePdo kontrolierī)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (vēlams) — vienrindas ar piesaistītiem parametriem
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Tāda pati ideja ar ? aizstājējiem
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Skeleton stila kontrolieros dodiet priekšroku `SimplePdo` konstruktora injekcijai, nevis `Flight::db()`, lai testi un AI ģenerētais kods paliktu konsekvents ([DIC](/learn/dependency-injection-container)).

#### Nedrošs piemērs

Zemāk ir iemesls, kāpēc mēs izmantojam SQL sagatavotos vaicājumus, lai aizsargātos no nevainīgiem piemēriem, kā zemāk:

```php
// galalietotājs aizpilda tīmekļa formu.
// formas vērtībai hakeris ievada kaut ko līdzīgu šim:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Pēc vaicājuma izveides tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.

var_dump($users); // tas izgūs visus lietotājus datubāzē, nevis tikai vienu lietotājvārdu
```

### Noslēpumi un konfigurācija

- Ievietojiet noslēpumus **`.env`** failā (vai reālajā vidē), nevis versijotu `config.php` paraugos.
- Skeleton noteikums: literālie noklusējumi `config.php`; apvienojiet vidi bootstrap laikā; **nelasiet** `$_ENV` kontrolieros — injicējiet konfigurāciju. Skatiet [Konfigurācija](/learn/configuration).
- Nekad neiesniedziet API atslēgas, DB paroles vai sesijas šifrēšanas atslēgas. Norādiet AI rīkiem uz **`SECURITY.md`**, lai tie neizgudrotu nedrošus saīsinājumus.

### JSONP atzvanīšanas validācija

Ja izmantojat Flight metodi `Flight::jsonp()`, ņemiet vērā, ka Flight validē JSONP atzvanīšanas parametra nosaukumu pret stingru atļauto saraksta regulāro izteiksmi (`/^[A-Za-z_$][\w$.]{0,127}$/`). Jebkurš atzvanīšanas nosaukums, kas neatbilst šim modelim, izraisīs Flight izņēmuma mešanu, novēršot patvaļīga JavaScript injicēšanu, izmantojot ļaunprātīgu atzvanīšanas vērtību.

Šī validācija ir iebūvēta un neprasa papildu konfigurāciju, taču par to ir vērts zināt, atkļūdojot negaidītas kļūdas no JSONP galapunktiem.

### CORS

Cross-Origin Resource Sharing (CORS) ir mehānisms, kas ļauj daudzus resursus (piemēram, fontus, JavaScript utt.) tīmekļa lapā pieprasīt no cita domēna ārpus domēna, no kura resurss radies. Flight nav iebūvētas funkcionalitātes, bet to var viegli apstrādāt ar āķi, kas jāizpilda pirms `Flight::start()` metodes izsaukšanas.

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

// bootstrap / maršruti — palaidiet pirms start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight konfigurācijas nostiprināšana

Flight atklāj vairākus dzinēja iestatījumus, kuriem ir tieša ietekme uz drošību. To pareiza iestatīšana ir viens no vienkāršākajiem veidiem, kā nostiprināt lietotni.

#### `flight.allow_method_override`

Pēc noklusējuma Flight ļauj klientiem ignorēt pieprasījuma HTTP metodi, izmantojot `X-HTTP-Method-Override` galveni vai `_method` lauku POST pamattekstā. Lai gan tas ir ērti HTML formām, kas var nosūtīt tikai `GET`/`POST`, tas var būt bīstami, ja to negaidāt — uzbrucējs var viltot `DELETE` vai `PUT` pieprasījumus, izmantojot parastu formu.

Ja jūsu lietotne nepaļaujas uz šo uzvedību (piemēram, jūs veidojat API, ko patērē moderni klienti vai JavaScript priekšgali, kas var nosūtīt jebkuru HTTP darbības vārdu), jums tas jāatspējo:

```php
// Savā index.php vai bootstrap failā, pirms Flight::start()
Flight::set('flight.allow_method_override', false);
```

Noklusējuma vērtība ir `true` saderības ar vecākām versijām dēļ, bet **ieteicams to iestatīt uz `false`** jebkurai lietotnei, kurai nav tieši nepieciešama ignorēšanas funkcija.

#### `flight.debug`

Flight ir `flight.debug` iestatījums, kas kontrolē, vai tiek parādīta detalizēta kļūdas informācija (izņēmuma ziņojums, kods un pilna izsekošanas steks) pārlūkā, kad notiek neapstrādāts izņēmums. Noklusējums ir `false`, kas nozīmē, ka tiek parādīts tikai vispārīgs `500 Internal Server Error` ziņojums — klientam netiek noplūdināta iekšēja informācija.

Nekad neiespējojiet to produkcijas serverī. Izmantojiet to tikai lokāli vai pirmsprodukcijas vidē:

```php
// Droši tikai vietējai izstrādei — NEKAD produkcijā
Flight::set('flight.debug', true);
```

Ja `flight.debug` ir `false` (noklusējums), jūs joprojām varat uztvert kļūdas, iespējojot `flight.log_errors`:

```php
// Reģistrējiet kļūdas servera pusē, neatklājot tās klientam
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight iebūvētā `View` klase labprāt iekļauj veidni no absolūtā ceļa vai no relatīva nosaukuma, kas izkāpj no `flight.views.path` (piemēram, ar `../`). Tas ir apzināti lietotnēm, kas apzināti koplieto veidnes starp mapēm, bet tas ir arī ceļa pārvietošanas risks, ja veidnes nosaukums kādreiz nāk no neuzticamas ievades.

`flight.views.restrict_to_path` pēc **noklusējuma ir izslēgts**, lai esošās lietotnes turpinātu darboties. Ieslēdziet to, ja vien jums nav dokumentēta iemesla to nedarīt:

```php
// Savā index.php vai bootstrap failā, pirms Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

Dzinējs piemēro šo iestatījumu `View::$restrictToPath`, kad skats tiek izveidots (tāds pats modelis kā `flight.views.path` un `flight.views.extension`). Kad tas ir ieslēgts:

- `render()` un `fetch()` iekļauj tikai failus, kuru reālais ceļš atrodas konfigurētajā skatu direktorijā (arī simboliskās saites, kas norāda ārpus tās, tiek noraidītas).
- `exists()` atgriež `false` šiem pašiem ceļiem, nevis met izņēmumu.
- `getTemplate()` pati par sevi nav mainīta — tā joprojām atgriež ceļus tāpat kā vienmēr.
- Bloķēts fails met `Template file is outside the views path.` Trūkstošs fails joprojām met esošo `Template file not found: ...` ziņojumu.

Ja izmantojat Twig vai Latte ar saviem failu sistēmas ielādētājiem, kas norāda uz jūsu skatu direktoriju, šie dzinēji jau ierobežo veidnes ar šo sakni. Tomēr ieslēdziet to Flight vietējam `View`, lai jebkurš kods, kas izsauc `Flight::view()->render()` / `fetch()`, iegūtu tādu pašu aizsardzību. Oficiālais [skeleton](https://github.com/flightphp/skeleton) to iespējo bootstrap failā.

#### Ieteicamā produkcijas konfigurācija

```php
// index.php vai piemērots no lietotnes konfigurācijas / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Kļūdu apstrāde

Produkcijā slēpiet sensitīvas kļūdu detaļas, lai izvairītos no informācijas noplūdes uzbrucējiem. Produkcijā reģistrējiet kļūdas, nevis parādiet tās ar `display_errors` iestatītu uz `0`.

```php
// Savā bootstrap.php vai index.php

// pievienojiet to savam app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Atspējot kļūdu parādīšanu
    ini_set('log_errors', 1);     // Tā vietā reģistrēt kļūdas
    ini_set('error_log', '/path/to/error.log');
}

// Savos maršrutos vai kontrolieros
// Izmantojiet Flight::halt() kontrolētām kļūdu atbildēm
Flight::halt(403, 'Access denied');
```

### Ievades sanitizācija

Nekad neuzticieties lietotāja ievadei. Sanitizējiet to, izmantojot [filter_var](https://www.php.net/manual/en/function.filter-var.php), pirms apstrādes, lai novērstu ļaunprātīgu datu iekļūšanu. Dodiet priekšroku ievades lasīšanai, izmantojot `$app->request()` (vai `Flight::request()`), nevis neapstrādātu `$_GET` / `$_POST` lietotnes kodā.

```php

// Pieņemsim, ka ir $_POST pieprasījums ar $_POST['input'] un $_POST['email']

// Sanitizējiet virknes ievadi
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitizējiet e-pastu
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Paroļu jaukšana

Glabājiet paroles droši un pārbaudiet tās droši, izmantojot PHP iebūvētās funkcijas, piemēram, [password_hash](https://www.php.net/manual/en/function.password-hash.php) un [password_verify](https://www.php.net/manual/en/function.password-verify.php). Paroles nekad nedrīkst glabāt vienkāršā tekstā, kā arī tās nedrīkst šifrēt ar atgriezeniskām metodēm. Jaukšana nodrošina, ka pat tad, ja jūsu datubāze tiek apdraudēta, faktiskās paroles paliek aizsargātas.

```php
$password = Flight::request()->data->password;
// Jauciet paroli, kad to glabājat (piemēram, reģistrācijas laikā)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Pārbaudiet paroli (piemēram, pieteikšanās laikā)
if (password_verify($password, $stored_hash)) {
    // Parole sakrīt
}
```

### Pieprasījumu biežuma ierobežošana

Aizsargājieties pret brute force uzbrukumiem vai pakalpojuma atteikuma uzbrukumiem, ierobežojot pieprasījumu biežumu ar kešatmiņu.

```php
// Pieņemot, ka esat instalējis un reģistrējis flightphp/cache
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
- [Sesijas](/awesome-plugins/session) - Kā droši pārvaldīt lietotāju sesijas.
- [Veidnes](/learn/templates) - Twig/Latte automātiskā aizsardzība un XSS.
- [SimplePdo](/learn/simple-pdo) - Datubāzes palīgfunkcijas ar sagatavotiem vaicājumiem.
- [PdoWrapper](/learn/pdo-wrapper) - Novecojis; izmantojiet SimplePdo jaunam kodam.
- [Starpprogrammatūra](/learn/middleware) - Kā izmantot starpprogrammatūru, lai vienkāršotu drošības galveņu pievienošanu.
- [Konfigurācija](/learn/configuration) - `.env` pret literālo konfigurāciju, produkcijas karodziņi.
- [AI un izstrādātāju pieredze](/learn/ai) - Saglabājiet drošības politiku `SECURITY.md` failā aģentiem.
- [Atbildes](/learn/responses) - Kā pielāgot HTTP atbildes ar drošām galvenēm.
- [Pieprasījumi](/learn/requests) - Kā apstrādāt un sanitizēt lietotāja ievadi.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - PHP funkcija ievades sanitizācijai.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - PHP funkcija drošai paroļu jaukšanai.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - PHP funkcija jaucētu paroļu pārbaudei.

## Problēmu novēršana
- Skatiet iepriekš sadaļu "Skatiet arī", lai iegūtu problēmu novēršanas informāciju saistībā ar problēmām ar Flight Framework komponentiem.
- Ja CSP bloķē jūsu skriptus, pievienojiet nonce (skeleton modelis) vai atļauto sarakstu konkrētiem izcelsmes avotiem — neiestatiet `script-src *` bez plāna.

## Izmaiņu žurnāls
- Dokumentācija – Skeleton `App\Middleware`, Twig CSRF/XSS piezīmes, SimplePdo, noslēpumi/`.env` un `SECURITY.md` AI draudzīgiem projektiem.
- Dokumentācija – Dokumentēts `flight.views.restrict_to_path` sadaļā Flight konfigurācijas nostiprināšana (opt-in ceļa ierobežošana vietējiem skatiem).
- v3.18.1 - Pievienota sadaļa Flight konfigurācijas nostiprināšana, aptverot `flight.allow_method_override`, `flight.debug` un JSONP atzvanīšanas validāciju.
- v3.1.0 - Pievienotas sadaļas par CORS, kļūdu apstrādi, ievades sanitizāciju, paroļu jaukšanu un pieprasījumu biežuma ierobežošanu.
- v2.0 - Pievienota aizsardzība noklusējuma skatiem, lai novērstu XSS.