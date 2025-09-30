# Sécurité

## Aperçu

La sécurité est une priorité majeure pour les applications web. Vous devez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont 
protégées. Flight fournit un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web.

## Compréhension

Il existe un certain nombre de menaces de sécurité courantes dont vous devez être conscient lors de la construction d'applications web. Parmi les menaces les plus courantes, on trouve :
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) aident avec XSS en échappant la sortie par défaut, afin que vous n'ayez pas à vous en souvenir. [Sessions](/awesome-plugins/session) peuvent aider avec CSRF en stockant un jeton CSRF dans la session de l'utilisateur comme indiqué ci-dessous. L'utilisation de requêtes préparées avec PDO peut aider à prévenir les attaques par injection SQL (ou en utilisant les méthodes pratiques dans la classe [PdoWrapper](/learn/pdo-wrapper)). CORS peut être géré avec un simple hook avant que `Flight::start()` ne soit appelé.

Toutes ces méthodes fonctionnent ensemble pour aider à garder vos applications web sécurisées. Il devrait toujours être à l'avant-plan de votre esprit d'apprendre et de comprendre les meilleures pratiques de sécurité.

## Utilisation de base

### En-têtes

Les en-têtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser des en-têtes pour prévenir le clickjacking, XSS et d'autres attaques. 
Il existe plusieurs façons d'ajouter ces en-têtes à votre application.

Deux excellents sites web pour vérifier la sécurité de vos en-têtes sont [securityheaders.com](https://securityheaders.com/) et 
[observatory.mozilla.org](https://observatory.mozilla.org/). Après avoir configuré le code ci-dessous, vous pouvez facilement vérifier que vos en-têtes fonctionnent avec ces deux sites.

#### Ajout manuel

Vous pouvez ajouter manuellement ces en-têtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définir l'en-tête X-Frame-Options pour prévenir le clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définir l'en-tête Content-Security-Policy pour prévenir XSS
// Note : cet en-tête peut devenir très complexe, vous devrez donc
//  consulter des exemples sur internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définir l'en-tête X-XSS-Protection pour prévenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définir l'en-tête X-Content-Type-Options pour prévenir le sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définir l'en-tête Referrer-Policy pour contrôler la quantité d'informations de référent envoyées
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définir l'en-tête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définir l'en-tête Permissions-Policy pour contrôler les fonctionnalités et API utilisables
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces en-têtes peuvent être ajoutés en haut de vos fichiers `routes.php` ou `index.php`.

#### Ajout en tant que filtre

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

#### Ajout en tant que middleware

Vous pouvez également les ajouter en tant que classe middleware, ce qui offre la plus grande flexibilité pour choisir les routes auxquelles les appliquer. En général, ces en-têtes devraient être appliqués à toutes les réponses HTML et API.

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

// index.php ou là où vous avez vos routes
// FYI, ce groupe de chaîne vide agit comme un middleware global pour
// toutes les routes. Bien sûr, vous pourriez faire la même chose et simplement ajouter
// cela uniquement à des routes spécifiques.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) est un type d'attaque où un site web malveillant peut faire en sorte que le navigateur d'un utilisateur envoie une requête à votre site web. 
Cela peut être utilisé pour effectuer des actions sur votre site web sans la connaissance de l'utilisateur. Flight ne fournit pas de mécanisme de protection CSRF intégré, 
mais vous pouvez facilement implémenter le vôtre en utilisant un middleware.

#### Configuration

D'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier quand 
le formulaire est soumis. Nous utiliserons le plugin [flightphp/session](/awesome-plugins/session) pour gérer les sessions.

```php
// Générer un jeton CSRF et le stocker dans la session de l'utilisateur
// (en supposant que vous ayez créé un objet session et l'avez attaché à Flight)
// voir la documentation des sessions pour plus d'informations
Flight::register('session', flight\Session::class);

// Vous n'avez besoin de générer qu'un seul jeton par session (afin qu'il fonctionne 
// sur plusieurs onglets et requêtes pour le même utilisateur)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Utilisation du template Flight PHP par défaut

```html
<!-- Utiliser le jeton CSRF dans votre formulaire -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- autres champs du formulaire -->
</form>
```

##### Utilisation de Latte

Vous pouvez également définir une fonction personnalisée pour afficher le jeton CSRF dans vos templates Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// autres configurations...

	// Définir une fonction personnalisée pour afficher le jeton CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Et maintenant dans vos templates Latte, vous pouvez utiliser la fonction `csrf()` pour afficher le jeton CSRF.

```html
<form method="post">
	{csrf()}
	<!-- autres champs du formulaire -->
</form>
```

#### Vérifier le jeton CSRF

Vous pouvez vérifier le jeton CSRF en utilisant plusieurs méthodes.

##### Middleware

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
				$this->app->halt(403, 'Jeton CSRF invalide');
			}
		}
	}
}

// index.php ou là où vous avez vos routes
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// plus de routes
}, [ CsrfMiddleware::class ]);
```

##### Filtres d'événements

```php
// Ce middleware vérifie si la requête est une requête POST et si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturer le jeton csrf des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Jeton CSRF invalide');
			// ou pour une réponse JSON
			Flight::jsonHalt(['error' => 'Jeton CSRF invalide'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) est un type d'attaque où une entrée de formulaire malveillante peut injecter du code dans votre site web. La plupart de ces opportunités proviennent 
des valeurs de formulaire que vos utilisateurs finaux rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont 
les meilleurs hackers au monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos 
utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe view de Flight ou un autre moteur de templating comme [Latte](/awesome-plugins/latte), vous pouvez facilement échapper la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur soit astucieux et essaie d'utiliser ceci comme nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela affichera : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré comme votre classe view, il échappera automatiquement cela.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations 
de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposez toujours qu'ils en ont après votre peau. L'utilisation de requêtes préparées dans vos objets `PDO` préviendra les injections SQL.

```php
// En supposant que vous ayez Flight::db() enregistré comme votre objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut être fait facilement en une ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des placeholders ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Exemple non sécurisé

Voici pourquoi nous utilisons des requêtes préparées SQL pour nous protéger d'exemples innocents comme celui-ci :

```php
// l'utilisateur final remplit un formulaire web.
// pour la valeur du formulaire, le hacker met quelque chose comme ceci :
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Après que la requête soit construite, cela ressemble à ceci
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque d'injection SQL très courante qui renverra tous les utilisateurs.

var_dump($users); // cela dumpera tous les utilisateurs dans la base de données, pas seulement le nom d'utilisateur unique
```

### CORS

Cross-Origin Resource Sharing (CORS) est un mécanisme qui permet à de nombreuses ressources (par exemple, polices, JavaScript, etc.) sur une page web d'être 
requises depuis un autre domaine en dehors du domaine d'origine de la ressource. Flight n'a pas de fonctionnalité intégrée, 
mais cela peut facilement être géré avec un hook à exécuter avant que la méthode `Flight::start()` ne soit appelée.

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

// index.php ou là où vous avez vos routes
$CorsUtil = new CorsUtil();

// Cela doit être exécuté avant que start ne s'exécute.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Gestion des erreurs
Masquez les détails d'erreurs sensibles en production pour éviter de divulguer des informations aux attaquants. En production, enregistrez les erreurs au lieu de les afficher avec `display_errors` défini à `0`.

```php
// Dans votre bootstrap.php ou index.php

// ajoutez ceci à votre app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Désactiver l'affichage des erreurs
    ini_set('log_errors', 1);     // Enregistrer les erreurs à la place
    ini_set('error_log', '/path/to/error.log');
}

// Dans vos routes ou contrôleurs
// Utilisez Flight::halt() pour des réponses d'erreur contrôlées
Flight::halt(403, 'Accès refusé');
```

### Assainissement des entrées
Ne faites jamais confiance à l'entrée utilisateur. Assainissez-la en utilisant [filter_var](https://www.php.net/manual/en/function.filter-var.php) avant de la traiter pour empêcher les données malveillantes de s'infiltrer.

```php

// Supposons une requête $_POST avec $_POST['input'] et $_POST['email']

// Assainir une entrée chaîne
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Assainir un email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hachage des mots de passe
Stockez les mots de passe de manière sécurisée et vérifiez-les en toute sécurité en utilisant les fonctions intégrées de PHP comme [password_hash](https://www.php.net/manual/en/function.password-hash.php) et [password_verify](https://www.php.net/manual/en/function.password-verify.php). Les mots de passe ne doivent jamais être stockés en texte clair, ni chiffrés avec des méthodes réversibles. Le hachage garantit que même si votre base de données est compromise, les mots de passe réels restent protégés.

```php
$password = Flight::request()->data->password;
// Hacher un mot de passe lors du stockage (par exemple, pendant l'inscription)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Vérifier un mot de passe (par exemple, pendant la connexion)
if (password_verify($password, $stored_hash)) {
    // Le mot de passe correspond
}
```

### Limitation de taux
Protégez contre les attaques par force brute ou les attaques par déni de service en limitant les taux de requêtes avec un cache.

```php
// En supposant que vous ayez flightphp/cache installé et enregistré
// Utilisation de flightphp/cache dans un filtre
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

## Voir aussi
- [Sessions](/awesome-plugins/session) - Comment gérer les sessions utilisateur de manière sécurisée.
- [Templates](/learn/templates) - Utilisation des templates pour échapper automatiquement la sortie et prévenir XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Interactions simplifiées avec la base de données avec des requêtes préparées.
- [Middleware](/learn/middleware) - Comment utiliser le middleware pour simplifier l'ajout d'en-têtes de sécurité.
- [Responses](/learn/responses) - Comment personnaliser les réponses HTTP avec des en-têtes sécurisés.
- [Requests](/learn/requests) - Comment gérer et assainir l'entrée utilisateur.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Fonction PHP pour l'assainissement des entrées.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Fonction PHP pour le hachage sécurisé des mots de passe.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Fonction PHP pour vérifier les mots de passe hachés.

## Dépannage
- Reportez-vous à la section "Voir aussi" ci-dessus pour des informations de dépannage liées aux problèmes avec les composants du Framework Flight.

## Journal des modifications
- v3.1.0 - Ajout de sections sur CORS, Gestion des erreurs, Assainissement des entrées, Hachage des mots de passe, et Limitation de taux.
- v2.0 - Ajout d'échappement pour les vues par défaut pour prévenir XSS.