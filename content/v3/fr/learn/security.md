# Sécurité

La sécurité est un élément important en ce qui concerne les applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont sûres. Flight fournit plusieurs fonctionnalités pour vous aider à sécuriser vos applications web.

## En-têtes

Les en-têtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser des en-têtes pour prévenir le clickjacking, le XSS et d'autres attaques. Il existe plusieurs moyens d'ajouter ces en-têtes à votre application.

Deux excellents sites à consulter pour vérifier la sécurité de vos en-têtes sont [securityheaders.com](https://securityheaders.com/) et 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Ajouter Manuellement

Vous pouvez ajouter manuellement ces en-têtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définissez l'en-tête X-Frame-Options pour prévenir le clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définissez l'en-tête Content-Security-Policy pour prévenir le XSS
// Remarque : cet en-tête peut devenir très complexe, donc vous voudrez
//  consulter des exemples sur internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définissez l'en-tête X-XSS-Protection pour prévenir le XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définissez l'en-tête X-Content-Type-Options pour prévenir le sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définissez l'en-tête Referrer-Policy pour contrôler la quantité d'informations sur le référent
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définissez l'en-tête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définissez l'en-tête Permissions-Policy pour contrôler quelles fonctionnalités et APIs peuvent être utilisées
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces en-têtes peuvent être ajoutés en haut de vos fichiers `bootstrap.php` ou `index.php`.

### Ajouter comme un Filtre

Vous pouvez également les ajouter dans un filtre/crochet comme le suivant : 

```php
// Ajoutez les en-têtes dans un filtre
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

### Ajouter comme un Middleware

Vous pouvez également les ajouter comme une classe middleware. C'est un bon moyen de garder votre code propre et organisé.

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

// index.php ou où que vous ayez vos routes
// FYI, ce groupe de chaîne vide agit comme un middleware global pour
// toutes les routes. Bien sûr, vous pourriez faire la même chose et juste ajouter
// cela uniquement à des routes spécifiques.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new SecurityHeadersMiddleware() ]);
```


## Contre les attaques CSRF (Cross Site Request Forgery)

Le Cross Site Request Forgery (CSRF) est un type d'attaque où un site web malveillant peut amener le navigateur d'un utilisateur à envoyer une requête à votre site web. Cela peut être utilisé pour effectuer des actions sur votre site web sans que l'utilisateur ne le sache. Flight ne fournit pas de mécanisme de protection CSRF intégré, mais vous pouvez facilement implémenter le vôtre en utilisant un middleware.

### Configuration

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lorsque le formulaire est soumis.

```php
// Générez un jeton CSRF et stockez-le dans la session de l'utilisateur
// (en supposant que vous avez créé un objet de session et l'avez attaché à Flight)
// consultez la documentation de la session pour plus d'informations
Flight::register('session', \Ghostff\Session\Session::class);

// Vous n'avez besoin de générer qu'un seul jeton par session (afin qu'il fonctionne
// à travers plusieurs onglets et requêtes pour le même utilisateur)
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

#### Utiliser Latte

Vous pouvez également définir une fonction personnalisée pour afficher le jeton CSRF dans vos modèles Latte.

```php
// Définissez une fonction personnalisée pour afficher le jeton CSRF
// Remarque : La vue a été configurée avec Latte en tant que moteur de vue
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Et maintenant, dans vos modèles Latte, vous pouvez utiliser la fonction `csrf()` pour afficher le jeton CSRF.

```html
<form method="post">
	{csrf()}
	<!-- autres champs de formulaire -->
</form>
```

Court et simple, non ?

### Vérifier le Jeton CSRF

Vous pouvez vérifier le jeton CSRF en utilisant des filtres d'événements :

```php
// Ce middleware vérifie si la requête est une requête POST et, si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturez le jeton csrf à partir des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Jeton CSRF invalide');
			// ou pour une réponse JSON
			Flight::jsonHalt(['error' => 'Jeton CSRF invalide'], 403);
		}
	}
});
```

Ou vous pouvez utiliser une classe middleware :

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

// index.php ou où que vous ayez vos routes
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Le Cross Site Scripting (XSS) est un type d'attaque où un site web malveillant peut injecter du code dans votre site web. La plupart de ces opportunités proviennent des valeurs de formulaire que vos utilisateurs finaux rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposons toujours qu'ils soient les meilleurs hackers du monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations de vos utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe de vue de Flight, vous pouvez facilement échapper à la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur soit assez intelligent pour essayer d'utiliser ceci comme son nom
$name = '<script>alert("XSS")</script>';

// Cela va échapper à la sortie
Flight::view()->set('name', $name);
// Cela va produire : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que votre classe de vue, cela échappera également automatiquement ceci.
Flight::view()->render('template', ['name' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposons toujours qu'ils soient en guerre. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir l'injection SQL.

```php
// En supposant que vous ayez Flight::db() enregistré comme votre objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut facilement être fait en une seule ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des espaces réservés ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste que vous ne ferez jamais JAMAIS quelque chose comme ça...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// parce que si $username = "' OR 1=1; -- "; 
// Après la requête, ça ressemble à ça
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque d'injection SQL très courante qui retourne tous les utilisateurs.
```

## CORS

Le Cross-Origin Resource Sharing (CORS) est un mécanisme qui permet de demander de nombreuses ressources (par exemple, des polices, du JavaScript, etc.) sur une page web à partir d'un autre domaine que celui dont la ressource est originaire. Flight n'a pas de fonctionnalité intégrée, mais cela peut facilement être géré avec un crochet à exécuter avant que la méthode `Flight::start()` ne soit appelée.

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

// index.php ou où que vous ayez vos routes
$CorsUtil = new CorsUtil();

// Cela doit être exécuté avant que le démarrage ne s'exécute.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Gestion des Erreurs
Cachez les détails sensibles des erreurs en production pour éviter de divulguer des informations aux attaquants.

```php
// Dans votre bootstrap.php ou index.php

// dans flightphp/skeleton, cela se trouve dans app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Désactiver l'affichage des erreurs
    ini_set('log_errors', 1);     // Logger les erreurs à la place
    ini_set('error_log', '/path/to/error.log');
}

// Dans vos routes ou contrôleurs
// Utilisez Flight::halt() pour des réponses d'erreur contrôlées
Flight::halt(403, 'Accès refusé');
```

## Assainissement des Entrées
Ne faites jamais confiance aux entrées des utilisateurs. Assainissez-les avant de les traiter pour éviter que des données malveillantes ne se glissent.

```php

// Supposons une requête $_POST avec $_POST['input'] et $_POST['email']

// Assainissez une entrée de chaîne
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Assainissez un email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Hachage de Mot de Passe
Stockez les mots de passe de manière sécurisée et vérifiez-les en toute sécurité en utilisant les fonctions intégrées de PHP.

```php
$password = Flight::request()->data->password;
// Hachez un mot de passe lors du stockage (par exemple, lors de l'enregistrement)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Vérifiez un mot de passe (par exemple, lors de la connexion)
if (password_verify($password, $stored_hash)) {
    // Le mot de passe correspond
}
```

## Limitation de Taux
Protégez-vous contre les attaques de force brute en limitant les taux de requêtes avec un cache.

```php
// En supposant que vous ayez flightphp/cache installé et enregistré
// Utilisation de flightphp/cache dans un middleware
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Trop de requêtes');
    }
    
    $cache->set($key, $attempts + 1, 60); // Réinitialiser après 60 secondes
});
```

## Conclusion

La sécurité est un élément important et il est essentiel de s'assurer que vos applications web sont sécurisées. Flight propose un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important de toujours être vigilant et de s'assurer que vous faites tout ce qui est en votre pouvoir pour garder les données de vos utilisateurs en sécurité. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours à la sortie et utilisez des instructions préparées pour prévenir l'injection SQL. Utilisez toujours des middleware pour protéger vos routes contre les attaques CSRF et CORS. Si vous faites toutes ces choses, vous serez bien en route pour construire des applications web sécurisées.