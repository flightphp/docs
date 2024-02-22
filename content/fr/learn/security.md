# Sécurité

La sécurité est primordiale lorsqu'il s'agit d'applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont protégées. Flight fournit plusieurs fonctionnalités pour vous aider à sécuriser vos applications web.

## Entêtes

Les entêtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser les entêtes pour prévenir le détournement de clic, les attaques XSS et autres types d'attaques. Il existe plusieurs façons d'ajouter ces entêtes à votre application.

Deux excellents sites pour vérifier la sécurité de vos entêtes sont [securityheaders.com](https://securityheaders.com/) et [observatory.mozilla.org](https://observatory.mozilla.org/).

### Ajouter Manuellement

Vous pouvez ajouter manuellement ces entêtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définir l'entête X-Frame-Options pour prévenir le détournement de clic
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définir l'entête Content-Security-Policy pour prévenir les attaques XSS
// Remarque : cet entête peut devenir très complexe, vous voudrez donc
// consulter des exemples sur internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définir l'entête X-XSS-Protection pour prévenir les attaques XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définir l'entête X-Content-Type-Options pour prévenir le sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définir l'entête Referrer-Policy pour contrôler la quantité d'informations de referrer envoyées
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définir l'entête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définir l'entête Permissions-Policy pour contrôler les fonctionnalités et APIs utilisables
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces entêtes peuvent être ajoutés en haut de vos fichiers `bootstrap.php` ou `index.php`.

### Ajouter en tant que Filtre

Vous pouvez également les ajouter dans un filtre/hook comme suit:
```php
// Ajouter les entêtes dans un filtre
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

// index.php ou où vous avez vos routes
// FYI, ce groupe de chaînes vides sert de middleware global pour
// toutes les routes. Bien sûr, vous pourriez faire la même chose et ajouter
// cela uniquement à des routes spécifiques.
Flight::group('', function(Router $router) {
	$router->get('/utilisateurs', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new SecurityHeadersMiddleware() ]);
```


## Contrefaçon de Demande Inter-Site (CSRF)

La contrefaçon de demande inter-site (CSRF) est un type d'attaque où un site web malveillant peut faire envoyer une demande au site web d'un utilisateur par le biais de son navigateur. Cela peut être utilisé pour effectuer des actions sur votre site sans que l'utilisateur en ait connaissance. Flight ne propose pas de mécanisme intégré de protection CSRF, mais vous pouvez facilement mettre en place le vôtre en utilisant un middleware.

### Configuration

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lors de la soumission du formulaire.
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
	<!-- autres champs du formulaire -->
</form>
```

#### Utilisation de Latte

Vous pouvez également définir une fonction personnalisée pour afficher le jeton CSRF dans vos templates Latte.
```php
// Définir une fonction personnalisée pour afficher le jeton CSRF
// Remarque : la Vue a été configurée avec Latte comme moteur de vue
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Et maintenant, dans vos templates Latte, vous pouvez utiliser la fonction `csrf()` pour afficher le jeton CSRF.
```html
<form method="post">
	{csrf()}
	<!-- autres champs du formulaire -->
</form>
```

Court et simple, n'est-ce pas ?

### Vérifier le Jeton CSRF

Vous pouvez vérifier le jeton CSRF en utilisant des filtres d'événement:
```php
// Ce middleware vérifie si la requête est une requête POST et, si c'est le cas, vérifie si le jeton CSRF est valide
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
				Flight::halt(403, 'Jeton CSRF invalide');
			}
		}
	}
}

// index.php ou où vous avez vos routes
Flight::group('', function(Router $router) {
	$router->get('/utilisateurs', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new CsrfMiddleware() ]);
```


## Injection de Scripts (XSS)

L'injection de scripts entre sites (XSS) est un type d'attaque où un site web malveillant peut injecter du code dans votre site web. La plupart de ces opportunités proviennent des valeurs des formulaires que vos utilisateurs rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont les meilleurs pirates du monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou effectuer des actions sur votre site. En utilisant la classe de vue de Flight, vous pouvez facilement échapper à la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur soit astucieux et essaie d'utiliser ceci comme son nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela produira : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que classe de vue, cela échappera également automatiquement.
Flight::view()->render('template', ['name' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur celle-ci. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposez toujours qu'ils sont là pour nuire. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir les injections SQL.

```php
// En supposant que vous ayez Flight::db() enregistré en tant que votre objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut être facilement fait en une ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des placeholders ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste de ne JAMAIS faire quelque chose comme ceci...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// car que se passe-t-il si $username = "' OR 1=1; -- ";
// Après la construction de la requête, cela ressemble à ceci
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque par injection SQL très courante qui renverra tous les utilisateurs.
```

## CORS

Le partage des ressources entre origines (CORS) est un mécanisme qui permet à de nombreuses ressources (par exemple, polices de caractères, JavaScript, etc.) d'une page web d'être demandées depuis un autre domaine en dehors du domaine d'origine de la ressource. Flight n'a pas de fonctionnalité intégrée, mais cela peut être facilement géré avec un middleware ou des filtres d'événement similaires à CSRF.

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
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php ou où vous avez vos routes
Flight::route('/utilisateurs', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusion

La sécurité est primordiale et il est important de s'assurer que vos applications web sont sécurisées. Flight propose plusieurs fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important d'être toujours vigilant et de vous assurer de tout mettre en œuvre pour protéger les données de vos utilisateurs. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours les sorties et utilisez des instructions préparées pour prévenir les injections SQL. Utilisez toujours un middleware pour protéger vos routes des attaques CSRF et CORS. Si vous faites toutes ces choses, vous serez bien parti pour développer des applications web sécurisées.