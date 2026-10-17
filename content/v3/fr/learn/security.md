# Sécurité

## Aperçu

La sécurité est un enjeu majeur pour les applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont protégées. Flight fournit un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web.

Le [squelette](https://github.com/flightphp/skeleton) officiel inclut également un fichier **`SECURITY.md`** dédié et un middleware pour les en-têtes de sécurité, afin que les [outils de codage IA](/learn/ai) (et les humains) aient un endroit délibéré pour les secrets, les en-têtes et les règles XSS/SQL — séparé du style de codage général dans `AGENTS.md`.

## Comprendre

Il existe un certain nombre de menaces de sécurité courantes dont vous devez être conscient lors de la création d'applications web. Parmi les menaces les plus courantes, on trouve :
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- Injection SQL
- Cross Origin Resource Sharing (CORS)

Les [modèles](/learn/templates) aident contre les attaques XSS en échappant la sortie par défaut (Twig et Latte le font ; profitez de cet avantage). Les [sessions](/awesome-plugins/session) peuvent aider contre le CSRF en stockant un jeton CSRF dans la session de l'utilisateur, comme indiqué ci-dessous. L'utilisation de requêtes préparées avec PDO — ou d'helpers de [SimplePdo](/learn/simple-pdo) — aide à prévenir les injections SQL. Le CORS peut être géré avec un simple hook avant que `Flight::start()` ne soit appelé.

Toutes ces méthodes fonctionnent ensemble pour aider à sécuriser vos applications web. Il doit toujours être à l'esprit d'apprendre et de comprendre les meilleures pratiques de sécurité. Ne demandez pas à un assistant IA de « désactiver le CSP » ou d'affaiblir les en-têtes simplement pour qu'une page se charge sans comprendre le compromis.

## Utilisation de base

### En-têtes

Les en-têtes HTTP sont l'un des moyens les plus simples de sécuriser vos applications web. Vous pouvez utiliser les en-têtes pour empêcher le clickjacking, les attaques XSS et d'autres types d'attaques. Il existe plusieurs façons d'ajouter ces en-têtes à votre application.

Deux excellents sites pour vérifier la sécurité de vos en-têtes sont [securityheaders.com](https://securityheaders.com/) et [observatory.mozilla.org](https://observatory.mozilla.org/). Après avoir configuré le code ci-dessous, vous pouvez facilement vérifier que vos en-têtes fonctionnent avec ces deux sites.

Le squelette inclut `App\Middleware\SecurityHeadersMiddleware` (CSP avec nonce par requête, options de frame, HSTS, etc.). Préférez étendre cela délibérément plutôt que de désactiver les en-têtes.

#### Ajouter manuellement

Vous pouvez ajouter manuellement ces en-têtes en utilisant la méthode `header` sur l'objet `Flight\Response`.
```php
// Définit l'en-tête X-Frame-Options pour empêcher le clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Définit l'en-tête Content-Security-Policy pour empêcher les attaques XSS
// Remarque : cet en-tête peut devenir très complexe, c'est pourquoi vous voudrez
//  consulter des exemples sur Internet pour votre application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Définit l'en-tête X-XSS-Protection pour empêcher les attaques XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Définit l'en-tête X-Content-Type-Options pour empêcher le MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Définit l'en-tête Referrer-Policy pour contrôler la quantité d'informations de référent envoyée
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Définit l'en-tête Strict-Transport-Security pour forcer HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Définit l'en-tête Permissions-Policy pour contrôler les fonctionnalités et API disponibles
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Ces en-têtes peuvent être ajoutés en haut de vos fichiers `routes.php` ou `index.php`.

#### Ajouter en tant que filtre

Vous pouvez également les ajouter dans un filtre / hook comme suit :

```php
// Ajoute les en-têtes dans un filtre
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

#### Ajouter en tant que middleware

Vous pouvez également les ajouter sous forme de classe middleware, ce qui offre la plus grande flexibilité quant aux routes auxquelles les appliquer. En général, ces en-têtes doivent être appliqués à toutes les réponses HTML et API.

Chemin et espace de noms de style squelette (**la casse du dossier correspond à `App\Middleware`**) :

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
		// Préférez un nonce CSP défini lors du bootstrap si vous avez des scripts inline (le squelette définit csp_nonce)
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

// app/config/routes.php — groupe de chaîne vide = middleware global pour toutes les routes
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// autres routes
}, [SecurityHeadersMiddleware::class]);
```

Les projets plus anciens peuvent encore utiliser `app/middlewares` et `app\middlewares` ; cela fonctionne si les dossiers correspondent. Les nouvelles applications basées sur le squelette utilisent **`app/Middleware/`** et **`App\Middleware`**. Voir [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Le Cross Site Request Forgery (CSRF) est un type d'attaque où un site web malveillant peut amener le navigateur d'un utilisateur à envoyer une requête à votre site web. Cela peut être utilisé pour effectuer des actions sur votre site web à l'insu de l'utilisateur. Flight ne fournit pas de mécanisme intégré de protection CSRF, mais vous pouvez facilement implémenter le vôtre en utilisant un middleware.

#### Configuration

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lors de la soumission du formulaire. Nous utiliserons le plugin [flightphp/session](/awesome-plugins/session) pour gérer les sessions.

```php
// Génère un jeton CSRF et le stocke dans la session de l'utilisateur
// (en supposant que vous ayez créé un objet session et l'ayez attaché à Flight)
// consultez la documentation des sessions pour plus d'informations
Flight::register('session', flight\Session::class);

// Vous n'avez besoin de générer qu'un seul jeton par session (pour qu'il fonctionne
// sur plusieurs onglets et requêtes pour le même utilisateur)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Utilisation du modèle PHP par défaut de Flight

```html
<!-- Utilisez le jeton CSRF dans votre formulaire -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- autres champs du formulaire -->
</form>
```

##### Utilisation de Twig (par défaut du squelette)

Enregistrez une fonction Twig ou transmettez le jeton dans chaque vue de formulaire. Exemple minimal avec un global et un champ de formulaire :

```php
// Lors de la configuration de Twig (par exemple services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# autres champs #}
</form>
```

##### Utilisation de Latte

Vous pouvez également définir une fonction personnalisée pour générer le jeton CSRF dans vos modèles Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// autres configurations...

	// Définit une fonction personnalisée pour générer le jeton CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Et maintenant, dans vos modèles Latte, vous pouvez utiliser la fonction `csrf()` pour générer le jeton CSRF.

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
	// autres routes
}, [CsrfMiddleware::class]);
```

##### Filtres d'événements

```php
// Ce middleware vérifie si la requête est une requête POST et, si c'est le cas, vérifie que le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// récupère le jeton CSRF depuis les valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// ou pour une réponse JSON
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Le Cross Site Scripting (XSS) est un type d'attaque où une entrée de formulaire malveillante peut injecter du code dans votre site web. La plupart de ces opportunités proviennent de valeurs de formulaires que vos utilisateurs finaux rempliront. Vous ne devez **jamais** faire confiance aux sorties de vos utilisateurs ! Considérez toujours qu'ils sont les meilleurs pirates du monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe de vue de Flight ou un moteur de template comme [Twig](/awesome-plugins/twig) ou [Latte](/awesome-plugins/latte), vous pouvez facilement échapper la sortie pour prévenir les attaques XSS.

```php
// Imaginons que l'utilisateur soit malin et essaie d'utiliser ceci comme nom
$name = '<script>alert("XSS")</script>';

// Ceci échappera la sortie
Flight::view()->set('name', $name);
// Cela affichera : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (par défaut du squelette) et Latte échappent automatiquement par défaut — privilégiez-les plutôt qu'un echo PHP brut
Flight::render('template', ['name' => $name]);
// Twig : {{ name }}  → échappé
// Évitez |raw / les sorties non échappées à moins que le contenu soit entièrement fiable
```

### Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations depuis votre base de données ou effectuer des actions sur celle-ci. Encore une fois, vous ne devez **jamais** faire confiance aux entrées de vos utilisateurs ! Considérez toujours qu'ils sont là pour nuire. Utilisez des requêtes préparées — les helpers de [SimplePdo](/learn/simple-pdo) en font le chemin par défaut.

```php
// En supposant que Flight::db() est enregistré en tant que SimplePdo (ou injectez SimplePdo dans le contrôleur)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (recommandé) — une seule ligne avec des paramètres liés
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Même idée avec des placeholders ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

Dans les contrôleurs de style squelette, préférez l'injection par constructeur de `SimplePdo` plutôt que `Flight::db()` afin que les tests et le code généré par l'IA restent cohérents ([DIC](/learn/dependency-injection-container)).

#### Exemple non sécurisé

Voici pourquoi nous utilisons des requêtes préparées SQL pour nous protéger contre des exemples innocents comme celui-ci :

```php
// l'utilisateur final remplit un formulaire web.
// pour la valeur du formulaire, le pirate met quelque chose comme ceci :
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Après la construction de la requête, elle ressemble à ceci
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque par injection SQL très courante qui retournera tous les utilisateurs.

var_dump($users); // cela affichera tous les utilisateurs de la base de données, pas seulement le nom d'utilisateur unique
```

### Secrets et configuration

- Placez les secrets dans **`.env`** (ou dans l'environnement réel), et non dans des exemples de `config.php` versionnés.
- Règle du squelette : valeurs par défaut littérales dans `config.php` ; fusionnez avec l'environnement au bootstrap ; **ne lisez pas** `$_ENV` dans les contrôleurs — inject