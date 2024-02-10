# Sécurité

La sécurité est d'une grande importance lorsqu'il s'agit d'applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont en sécurité. Flight fournit un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web.

## Cross Site Request Forgery (CSRF)

La falsification de demande inter-sites (CSRF) est un type d'attaque où un site web malveillant peut faire en sorte que le navigateur d'un utilisateur envoie une requête à votre site web. Cela peut être utilisé pour effectuer des actions sur votre site sans que l'utilisateur le sache. Flight ne fournit pas de mécanisme intégré de protection CSRF, mais vous pouvez facilement mettre en place le vôtre en utilisant un middleware.

Tout d'abord, vous devez générer un jeton CSRF et le stocker dans la session de l'utilisateur. Vous pouvez ensuite utiliser ce jeton dans vos formulaires et le vérifier lorsque le formulaire est soumis.

```php
// Générer un jeton CSRF et le stocker dans la session de l'utilisateur
// (en supposant que vous ayez créé un objet de session et l'ayez attaché à Flight)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Utilisez le jeton CSRF dans votre formulaire -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- autres champs de formulaire -->
</form>
```

Et ensuite vous pouvez vérifier le jeton CSRF en utilisant des filtres d'événement :

```php
// Ce middleware vérifie si la requête est une requête POST et si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturer le jeton csrf des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Jeton CSRF non valide');
		}
	}
});
```

## Cross Site Scripting (XSS)

Les attaques de script intersite (XSS) sont un type d'attaque où un site web malveillant peut injecter du code dans votre site. La plupart de ces opportunités viennent des valeurs de formulaire que vos utilisateurs finaux rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont les meilleurs hackers du monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou effectuer des actions sur votre site web. En utilisant la classe de vue de Flight, vous pouvez facilement échapper à la sortie pour prévenir les attaques XSS.

```php

// Supposons que l'utilisateur est astucieux et essaie d'utiliser cela comme son nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela produira : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que votre classe de vue, cela échappera également automatiquement ceci.
Flight::view()->render('template', ['name' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposons toujours qu'ils sont à l'affût. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir les injections SQL.

```php

// En supposant que vous avez Flight::db() enregistré en tant que votre objet PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut facilement se faire en une seule ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des espaces réservés ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste que vous ne ferez JAMAIS quelque chose comme ceci...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// parce que et si $username = "' OU 1=1; -- "; Après la construction de la requête, cela ressemble à
// ceci
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque par injection SQL très courante qui renverra tous les utilisateurs.
```

## CORS

Le partage des ressources entre origines (CORS) est un mécanisme qui permet à de nombreuses ressources (par exemple, polices, JavaScript, etc.) d'une page web d'être demandées depuis un autre domaine en dehors de celui à partir duquel la ressource est originaire. Flight n'a pas de fonctionnalité intégrée, mais cela peut être facilement géré avec un middleware ou des filtres d'événements similaires à CSRF.

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
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Conclusion

La sécurité est essentielle et il est important de s'assurer que vos applications web sont sécurisées. Flight fournit un certain nombre de fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important d'être toujours vigilant et de vous assurer de tout faire pour protéger les données de vos utilisateurs. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours la sortie et utilisez des instructions préparées pour prévenir les injections SQL. Utilisez toujours un middleware pour protéger vos routes des attaques CSRF et CORS. Si vous faites tout cela, vous serez bien parti pour développer des applications web sécurisées.