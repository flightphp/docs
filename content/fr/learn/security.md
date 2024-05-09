# Sécurité

La sécurité est cruciale lorsqu'il s'agit d'applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont en sécurité. Flight propose un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web.

## En-têtes

Les en-têtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser les en-têtes pour prévenir le clickjacking, le XSS et d'autres attaques. Il existe plusieurs façons d'ajouter ces en-têtes à votre application.

Deux excellents sites pour vérifier la sécurité de vos en-têtes sont [securityheaders.com](https://securityheaders.com/) et [observatory.mozilla.org](https://observatory.mozilla.org/).

### Ajouter Manuellement

Vous pouvez ajouter manuellement ces en-têtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définir l'en-tête X-Frame-Options pour prévenir le clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définir l'en-tête Content-Security-Policy pour prévenir le XSS
// Remarque : cet en-tête peut devenir très complexe, donc vous devrez
// consulter des exemples sur Internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définir l'en-tête X-XSS-Protection pour prévenir le XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définir l'en-tête X-Content-Type-Options pour prévenir le reniflage MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définir l'en-tête Referrer-Policy pour contrôler la quantité d'informations d'origine envoyées
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définir l'en-tête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définir l'en-tête Permissions-Policy pour contrôler quelles fonctionnalités et APIs peuvent être utilisées
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces en-têtes peuvent être ajoutés en haut de vos fichiers `bootstrap.php` ou `index.php`.

### Ajouter en tant que Filtre

Vous pouvez également les ajouter dans un filtre/hook comme suit: 

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

Vous pouvez également les ajouter en tant que classe middleware. C'est une bonne façon de garder votre code propre et organisé.

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

// index.php ou où vous avez vos routes
// Pour info, cette chaîne de caractères vide agit comme un middleware global pour
// toutes les routes. Bien sûr, vous pourriez faire la même chose et l'ajouter juste
// à des routes spécifiques.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new SecurityHeadersMiddleware() ]);
```


## Demandes de falsification de requête inter-sites (CSRF)

La falsification de requête inter-sites (CSRF) est un type d'attaque où un site web malveillant peut faire envoyer une requête au site web d'un utilisateur par le biais de son navigateur. Cela peut être utilisé pour effectuer des actions sur votre site web sans que l'utilisateur le sache. Flight ne fournit pas de mécanisme de protection CSRF intégré, mais vous pouvez facilement implémenter le vôtre en utilisant un middleware.

### Configuration

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lorsque le formulaire est soumis.

```php
// Générer un jeton CSRF et le stocker dans la session de l'utilisateur
// (en supposant que vous avez créé un objet de session et l'avez attaché à Flight)
// Vous n'avez besoin de générer qu'un seul jeton par session (pour qu'il fonctionne
// sur plusieurs onglets et requêtes pour le même utilisateur)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Utilisez le jeton CSRF dans votre formulaire -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- autres champs de formulaire -->
</form>
```

#### Utilisation de Latte

Vous pouvez également définir une fonction personnalisée pour afficher le jeton CSRF dans vos modèles Latte.

```php
// Définir une fonction personnalisée pour afficher le jeton CSRF
// Remarque : View a été configuré avec Latte comme moteur de vue
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Et maintenant dans vos modèles Latte, vous pouvez utiliser la fonction `csrf()` pour afficher le jeton CSRF.

```html
<form method="post">
	{csrf()}
	<!-- autres champs de formulaire -->
</form>
```

Court et simple, n'est-ce pas ?

### Vérifier le Jeton CSRF

Vous pouvez vérifier le jeton CSRF en utilisant des filtres d'événements:

```php
// Ce middleware vérifie si la requête est une requête POST et si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturer le jeton csrf des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Jetno CSRF invalide');
		}
	}
});
```

Ou vous pouvez utiliser une classe de middleware:

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
				Flight::halt(403, 'Jetno CSRF invalide');
			}
		}
	}
}

// index.php ou où vous avez vos routes
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new CsrfMiddleware() ]);
```


## Cross Site Scripting (XSS)

Le Cross Site Scripting (XSS) est un type d'attaque où un site web malveillant peut injecter du code dans votre site web. La plupart de ces vulnérabilités proviennent des valeurs de formulaires que vos utilisateurs rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont les meilleurs hackers du monde. Ils peuvent injecter du code JavaScript ou HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe de vue de Flight, vous pouvez facilement échapper à la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur soit astucieux et essaie d'utiliser ceci comme son nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela affichera : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que votre classe de vue, il échappera également automatiquement cela.
Flight::view()->render('template', ['name' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposez toujours qu'ils sont à vos trousses. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir l'injection SQL.

```php
// En supposant que vous avez Flight::db() enregistré en tant qu'objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut facilement être fait en une seule ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des espaces réservés ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste de ne JAMAIS faire quelque chose comme ceci...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// car que se passe-t-il si $username = "' OR 1=1; -- "; 
// Après la construction de la requête, cela ressemble à ceci
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque d'injection de SQL très courante qui renverra tous les utilisateurs.
```

## CORS

Le partage des ressources inter-origines (CORS) est un mécanisme qui permet à de nombreuses ressources (par ex., polices, JavaScript, etc.) sur une page web d'être demandées à partir d'un autre domaine en dehors du domaine d'origine de la ressource. Flight n'a pas de fonctionnalité intégrée, mais cela peut facilement être géré avec des middlewares ou des filtres d'événements similaires à CSRF.

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
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers",
					$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
				);
			}
			$response->send();
			exit(0);
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

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN']);
		}
	}
}

// index.php ou où vous avez vos routes
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusion

La sécurité est primordiale et il est important de vous assurer que vos applications web sont sécurisées. Flight offre un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important d'être toujours vigilant et de vous assurer de faire tout ce qui est en votre pouvoir pour protéger les données de vos utilisateurs. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours la sortie et utilisez des instructions préparées pour prévenir les injections SQL. Utilisez toujours des middlewares pour protéger vos routes des attaques CSRF et CORS. Si vous faites toutes ces choses, vous serez bien parti pour construire des applications web sécurisées.