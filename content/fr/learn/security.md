# Sécurité

La sécurité est primordiale lorsqu'il s'agit d'applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont en sécurité. Flight offre un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web.

## En-têtes

Les en-têtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser des en-têtes pour prévenir le détournement de clic, les attaques XSS et autres. Il existe plusieurs façons d'ajouter ces en-têtes à votre application.

Deux excellents sites web pour vérifier la sécurité de vos en-têtes sont [securityheaders.com](https://securityheaders.com/) et [observatory.mozilla.org](https://observatory.mozilla.org/).

### Ajouter Manuellement

Vous pouvez ajouter manuellement ces en-têtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définir l'en-tête X-Frame-Options pour prévenir le détournement de clic
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définir l'en-tête Content-Security-Policy pour prévenir les attaques XSS
// Remarque : cet en-tête peut devenir très complexe, vous voudrez
// consulter des exemples sur Internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définir l'en-tête X-XSS-Protection pour prévenir les attaques XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définir l'en-tête X-Content-Type-Options pour prévenir le sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définir l'en-tête Referrer-Policy pour contrôler les informations de référence envoyées
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définir l'en-tête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définir l'en-tête Permissions-Policy pour contrôler les fonctionnalités et les API qui peuvent être utilisées
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces en-têtes peuvent être ajoutés en haut de vos fichiers `bootstrap.php` ou `index.php`.

### Ajouter en tant que Filtre

Vous pouvez également les ajouter dans un filtre/hook comme suit :

```php
// Ajouter les en-têtes dans un filtre
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

### Ajouter en tant que Middleware

Vous pouvez également les ajouter en tant que classe de middleware. C'est une bonne façon de garder votre code propre et organisé.

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

// index.php ou partout où vous avez vos routes
// À noter, ce groupe à chaîne vide agit comme un middleware global pour
// toutes les routes. Bien sûr, vous pourriez faire la même chose et simplement ajouter
// ceci uniquement à des routes spécifiques.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

La falsification de requête intersite (CSRF) est un type d'attaque où un site web malveillant peut faire envoyer une requête au site web de l'utilisateur par le biais de son navigateur. Cela peut être utilisé pour effectuer des actions sur votre site web sans la connaissance de l'utilisateur. Flight ne fournit pas de mécanisme de protection CSRF intégré, mais vous pouvez facilement mettre en œuvre le vôtre en utilisant un middleware.

### Configuration

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lorsque le formulaire est soumis.

```php
// Générer un jeton CSRF et le stocker dans la session de l'utilisateur
// (en supposant que vous ayez créé un objet session et l'ayez attaché à Flight)
// Vous n'avez besoin de générer qu'un seul jeton par session (afin qu'il fonctionne
// à travers plusieurs onglets et requêtes pour le même utilisateur)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Utiliser le jeton CSRF dans votre formulaire -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- autres champs de formulaire -->
</form>
```

#### Utilisation de Latte

Vous pouvez également définir une fonction personnalisée pour afficher le jeton CSRF dans vos templates Latte.

```php
// Définir une fonction personnalisée pour afficher le jeton CSRF
// Remarque: la vue a été configurée avec Latte comme moteur de vue
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Et maintenant dans vos templates Latte, vous pouvez utiliser la fonction `csrf()` pour afficher le jeton CSRF.

```html
<form method="post">
	{csrf()}
	<!-- autres champs de formulaire -->
</form>
```

Court et simple, n'est-ce pas ?

### Vérifier le Jeton CSRF

Vous pouvez vérifier le jeton CSRF en utilisant des filtres d'événement :

```php
// Ce middleware vérifie si la requête est une requête POST et si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturer le jeton csrf des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Jeton CSRF invalide');
		}
	}
});
```

Ou vous pouvez utiliser une classe de middleware :

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
				Flight::halt(403, 'Jeton CSRF invalide');
			}
		}
	}
}

// index.php ou partout où vous avez vos routes
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

La contamination de script intersite (XSS) est un type d'attaque où un site web malveillant peut injecter du code dans votre site web. La plupart de ces opportunités viennent des valeurs des formulaires que vos utilisateurs rempliront. Vous ne devriez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont les meilleurs hackeurs du monde. Ils peuvent injecter du code JavaScript ou HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe de vue de Flight, vous pouvez facilement échapper la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur est ingénieux et essaye d'utiliser ceci comme nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela produira : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que classe de vue, cela échappera également automatiquement cela.
Flight::view()->render('template', ['name' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devriez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposons toujours qu'ils sont à l'affût. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir les injections SQL.

```php
// En supposant que vous avez Flight::db() enregistré en tant qu'objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, ceci peut facilement être fait en une seule ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des espaces réservés ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste que vous ne ferez jamais JAMAIS quelque chose comme ça...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// parce que que se passe-t-il si $username = "' OR 1=1; -- "; 
// Après la construction de la requête elle ressemble à cela
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait, 
// c'est une attaque par injection SQL très courante qui renverra tous les utilisateurs.
```

## CORS

Le partage de ressources inter-origines (CORS) est un mécanisme qui permet à de nombreuses ressources (par ex., polices, JavaScript, etc.) sur une page web d'être demandées à partir d'un autre domaine en dehors du domaine d'origine de la ressource. Flight n'a pas de fonctionnalité intégrée, mais cela peut facilement être géré avec un hook pour s'exécuter avant que la méthode `Flight::start()` ne soit appelée.

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
		// personnalisez vos hôtes autorisés ici.
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

// index.php ou partout où vous avez vos routes
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## Conclusion

La sécurité est primordiale et il est important de veiller à ce que vos applications web soient sécurisées. Flight offre un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important de rester vigilant et de tout mettre en œuvre pour protéger les données de vos utilisateurs. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours la sortie et utilisez des instructions préparées pour prévenir les injections SQL. Utilisez toujours des middlewares pour protéger vos routes contre les attaques CSRF et CORS. Si vous faites tout cela, vous serez bien parti pour construire des applications web sécurisées.