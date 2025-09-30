# Starpamats

## Pārskats

Flight atbalsta maršruta un grupas maršruta vidusprogrammatūru. Vidusprogrammatūra ir jūsu lietojumprogrammas daļa, kur kods tiek izpildīts pirms 
( vai pēc) maršruta atsauksmes. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai lai pārbaudītu, 
vai lietotājam ir atļauja piekļūt maršrutam.

## Saprašana

Vidusprogrammatūra var ievērojami vienkāršot jūsu lietojumprogrammu. Tā vietā, lai izmantotu sarežģītu abstraktas klases mantojumu vai metožu pārrakstīšanu, vidusprogrammatūra 
ļauj jums kontrolēt savus maršrutus, pievienojot tiem savu pielāgotu lietojumprogrammas loģiku. Jūs varat domāt par vidusprogrammatūru gandrīz kā
par sendviču. Jums ir maize no ārpuses, un tad slāņi ar sastāvdaļām, piemēram, salātiem, tomātiem, gaļām un sieru. Tad iedomājieties,
ka katrs pieprasījums ir kā iekost sendvičā, kur jūs ēdat ārējos slāņus vispirms un pakāpeniski nokļūstat līdz kodolam.

Šeit ir vizuāls attēlojums, kā darbojas vidusprogrammatūra. Tad mēs parādīsim jums praktisku piemēru, kā tas darbojas.

```text
Lietotāja pieprasījums URL /api ----> 
	Vidusprogrammatūra->before() izpildīta ----->
		Atsaucamā funkcija/metode, kas pievienota /api, izpildīta un atbilde ģenerēta ------>
	Vidusprogrammatūra->after() izpildīta ----->
Lietotājs saņem atbildi no servera
```

Un šeit ir praktisks piemērs:

```text
Lietotājs pāriet uz URL /dashboard
	LoggedInMiddleware->before() izpildīta
		before() pārbauda derīgu pieteikšanās sesiju
			ja jā, nedarīt neko un turpināt izpildi
			ja nē, novirzīt lietotāju uz /login
				Atsaucamā funkcija/metode, kas pievienota /api, izpildīta un atbilde ģenerēta
	LoggedInMiddleware->after() neko nav definēts, tāpēc ļauj izpildei turpināties
Lietotājs saņem dashboard HTML no servera
```

### Izpildes secība

Vidusprogrammatūras funkcijas tiek izpildītas tajā secībā, kā tās tiek pievienotas maršrutam. Izpilde ir līdzīga tam, kā [Slim Framework apstrādā šo](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

`before()` metodes tiek izpildītas pievienotās secībā, un `after()` metodes tiek izpildītas pretējā secībā.

Piem.: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Pamata izmantošana

Jūs varat izmantot vidusprogrammatūru kā jebkuru atsaucamu metodi, tostarp anonīmu funkciju vai klasi (ieteicams)

### Anonīma funkcija

Šeit ir vienkāršs piemērs:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// This will output "Middleware first! Here I am!"
```

> **Piezīme:** Izmantojot anonīmu funkciju, vienīgā interpretētā metode ir `before()` metode. Jūs **nevarat** definēt `after()` uzvedību ar anonīmu klasi.

### Izmantojot klases

Vidusprogrammatūru var (un vajadzētu) reģistrēt kā klasi. Ja jums vajadzīga "after" funkcionalitāte, jūs **jāizmanto** klase.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// also ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// This will display "Middleware first! Here I am! Middleware last!"
```

Jūs varat definēt tikai vidusprogrammatūras klases nosaukumu, un tā uzreiz radīs klases экземпlāru.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Piezīme:** Ja jūs nodod tikai vidusprogrammatūras nosaukumu, tā automātiski tiks izpildīta, izmantojot [atkarību injekcijas konteineru](dependency-injection-container), un vidusprogrammatūra tiks izpildīta ar parametriem, kas tai nepieciešami. Ja jums nav reģistrēts atkarību injekcijas konteiners, tas pēc noklusējuma nodos `flight\Engine` экземпlāru `__construct(Engine $app)` metodē.

### Izmantojot maršrutus ar parametriem

Ja jums vajadzīgi parametri no jūsu maršruta, tie tiks nodoti kā viens masīvs jūsu vidusprogrammatūras funkcijā. (`function($params) { ... }` vai `public function before($params) { ... }`). Iemesls tam ir tas, ka jūs varat strukturēt savus parametrus grupās, un dažās no tām jūsu parametri var parādīties citā secībā, kas salauztu vidusprogrammatūras funkciju, atsaucoties uz nepareizo parametru. Šādā veidā jūs varat piekļūt tiem pēc nosaukuma, nevis pozīcijas.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId may or may not be passed in
		$jobId = $params['jobId'] ?? 0;

		// maybe if there's no job ID, you don't need to lookup anything.
		if($jobId === 0) {
			return;
		}

		// perform a lookup of some kind in your database
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// This group below still gets the parent middleware
	// But the parameters are passed in one single array 
	// in the middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// more routes...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Grupēšana maršrutus ar vidusprogrammatūru

Jūs varat pievienot maršruta grupu, un tad katrs maršruts šajā grupā būs ar to pašu vidusprogrammatūru. Tas ir 
noderīgi, ja jums vajag grupēt vairākus maršrutus, piemēram, ar Auth vidusprogrammatūru, lai pārbaudītu API atslēgu galvenē.

```php

// added at the end of the group method
Flight::group('/api', function() {

	// This "empty" looking route will actually match /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// This will match /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// This will match /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Ja jūs vēlaties piemērot globālu vidusprogrammatūru visiem jūsu maršrutiem, jūs varat pievienot "tukšu" grupu:

```php

// added at the end of the group method
Flight::group('', function() {

	// This is still /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// And this is still /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // or [ new ApiAuthMiddleware() ], same thing
```

### Izplatīti izmantošanas gadījumi

#### API atslēgas validācija
Ja jūs vēlaties aizsargāt savus `/api` maršrutus, pārbaudot, vai API atslēga ir pareiza, jūs varat viegli to apstrādāt ar vidusprogrammatūru.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// do a lookup in your database for the api key
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// more routes...
}, [ ApiMiddleware::class ]);
```

Tagad visi jūsu API maršruti ir aizsargāti ar šo API atslēgas validācijas vidusprogrammatūru, ko jūs esat iestatījis! Ja jūs pievienosiet vairāk maršrutus maršrutētāja grupai, tiem uzreiz būs tā pati aizsardzība!

#### Pieteikšanās validācija

Vai jūs vēlaties aizsargāt dažus maršrutus, lai tie būtu pieejami tikai pieteikušies lietotājiem? To var viegli sasniegt ar vidusprogrammatūru!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// more routes...
}, [ LoggedInMiddleware::class ]);
```

#### Maršruta parametra validācija

Vai jūs vēlaties aizsargāt savus lietotājus no vērtību maiņas URL, lai piekļūtu datiem, kas tiem nepieder? To var atrisināt ar vidusprogrammatūru!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// perform a lookup of some kind in your database
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// more routes...
}, [ RouteSecurityMiddleware::class ]);
```

## Apstrāde vidusprogrammatūras izpildes

Pieņemsim, jums ir autentifikācijas vidusprogrammatūra, un jūs vēlaties novirzīt lietotāju uz pieteikšanās lapu, ja viņš nav 
autentificēts. Jums ir vairākas opcijas rīcībā:

1. Jūs varat atgriezt false no vidusprogrammatūras funkcijas, un Flight automātiski atgriezīs 403 Forbidden kļūdu, bet bez pielāgošanas.
1. Jūs varat novirzīt lietotāju uz pieteikšanās lapu, izmantojot `Flight::redirect()`.
1. Jūs varat izveidot pielāgotu kļūdu vidusprogrammatūrā un apturēt maršruta izpildi.

### Vienkāršs un tiešs

Šeit ir vienkāršs `return false;` piemērs:

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// since it's true, everything just keeps on going
	}
}
```

### Novirzīšanas piemērs

Šeit ir piemērs, kā novirzīt lietotāju uz pieteikšanās lapu:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Pielāgota kļūdas piemērs

Pieņemsim, jums vajag mest JSON kļūdu, jo jūs veidojat API. Jūs varat to izdarīt šādi:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// or
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// or
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Skatīt arī
- [Maršrutēšana](/learn/routing) - Kā kartēt maršrutus uz kontrolieriem un renderēt skatus.
- [Pieprasījumi](/learn/requests) - Saprašana, kā apstrādāt ienākošos pieprasījumus.
- [Atbildes](/learn/responses) - Kā pielāgot HTTP atbildes.
- [Atkarību injekcija](/learn/dependency-injection-container) - Vienkāršo objektu izveidi un pārvaldību maršrutos.
- [Kāpēc ietvars?](/learn/why-frameworks) - Saprašana par ietvara, piemēram, Flight, priekšrocībām.
- [Vidusprogrammatūras izpildes stratēģijas piemērs](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Traucējummeklēšana
- Ja jums ir novirzīšana jūsu vidusprogrammatūrā, bet jūsu lietojumprogramma, šķiet, nenovirzās, pārliecinieties, ka pievienojat `exit;` paziņojumu jūsu vidusprogrammatūrā.

## Izmaiņu žurnāls
- v3.1: Pievienots atbalsts vidusprogrammatūrai.