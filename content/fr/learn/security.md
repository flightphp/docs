# Sécurité

La sécurité est primordiale lorsqu'il s'agit d'applications web. Vous voulez vous assurer que votre application est sécurisée et que les données de vos utilisateurs sont en sécurité. Flight propose plusieurs fonctionnalités pour vous aider à sécuriser vos applications web.

## Cross-Site Request Forgery (CSRF)

La falsification de demande intersite (CSRF) est un type d'attaque où un site Web malveillant peut faire en sorte que le navigateur d'un utilisateur envoie une demande à votre site Web. Cela peut être utilisé pour effectuer des actions sur votre site Web sans que l'utilisateur le sache. Flight ne fournit pas de mécanisme de protection CSRF intégré, mais vous pouvez facilement mettre en œuvre le vôtre en utilisant un middleware.

Voici un exemple de la façon dont vous pourriez mettre en œuvre la protection CSRF en utilisant des filtres d'événements :

```php
// Ce middleware vérifie si la requête est une requête POST et si c'est le cas, il vérifie si le jeton CSRF est valide
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture le jeton csrf à partir des valeurs du formulaire
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Jeton CSRF invalide');
		}
	}
});
```

## Cross-Site Scripting (XSS)

Les attaques de scripts intersites (XSS) sont un type d'attaque où un site Web malveillant peut injecter du code dans votre site Web. La plupart de ces failles proviennent des valeurs des formulaires que vos utilisateurs finaux rempliront. Vous ne devez **jamais** faire confiance à la sortie de vos utilisateurs ! Supposez toujours qu'ils sont les meilleurs pirates du monde. Ils peuvent injecter du JavaScript ou du HTML malveillant dans votre page. Ce code peut être utilisé pour voler des informations à vos utilisateurs ou pour effectuer des actions sur votre site Web. En utilisant la classe de vue de Flight, vous pouvez facilement échapper à la sortie pour prévenir les attaques XSS.

```php
// Supposons que l'utilisateur est intelligent et essaye d'utiliser ceci comme nom
$name = '<script>alert("XSS")</script>';

// Cela échappera la sortie
Flight::view()->set('name', $name);
// Cela affichera : &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si vous utilisez quelque chose comme Latte enregistré en tant que votre classe de vue, il va également échapper automatiquement cela.
Flight::view()->render('modèle', ['nom' => $name]);
```

## Injection SQL

L'injection SQL est un type d'attaque où un utilisateur malveillant peut injecter du code SQL dans votre base de données. Cela peut être utilisé pour voler des informations de votre base de données ou effectuer des actions sur votre base de données. Encore une fois, vous ne devez **jamais** faire confiance à l'entrée de vos utilisateurs ! Supposons toujours qu'ils sont avides de sang. Vous pouvez utiliser des instructions préparées dans vos objets `PDO` pour prévenir les injections SQL.

```php
// En supposant que vous ayez Flight::db() enregistré en tant qu'objet PDO
$déclaration = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$déclaration->execute([':username' => $username]);
$users = $déclaration->fetchAll();

// Si vous utilisez la classe PdoWrapper, cela peut facilement être fait en une ligne
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Vous pouvez faire la même chose avec un objet PDO avec des espaces réservés ?
$déclaration = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Promettez juste que vous ne ferez JAMAIS quelque chose comme ceci...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}'");
// parce que que se passerait-il si $username = "' OU 1=1;"; Après la construction de la requête, cela ressemblerait à
// ceci
// SELECT * FROM users WHERE username = '' OU 1=1;
// Cela semble étrange, mais c'est une requête valide qui fonctionnera. En fait,
// c'est une attaque d'injection SQL très courante qui renverra tous les utilisateurs.
```

## CORS

Le partage des ressources entre origines (CORS) est un mécanisme qui permet à de nombreuses ressources (telles que les polices, JavaScript, etc.) d'une page web d'être demandées à partir d'un autre domaine en dehors du domaine à partir duquel la ressource provient. Flight n'a pas de fonctionnalité intégrée, mais cela peut être facilement géré avec un middleware ou des filtres d'événements similaires à CSRF.

```php
Flight::route('/utilisateurs', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## Conclusion

La sécurité est primordiale et il est important de veiller à ce que vos applications web soient sécurisées. Flight propose plusieurs fonctionnalités pour vous aider à sécuriser vos applications web, mais il est important d'être toujours vigilant et de faire tout ce qui est en votre pouvoir pour protéger les données de vos utilisateurs. Supposez toujours le pire et ne faites jamais confiance à l'entrée de vos utilisateurs. Échappez toujours à la sortie et utilisez des instructions préparées pour prévenir les injections SQL. Utilisez toujours un middleware pour protéger vos routes des attaques CSRF et CORS. Si vous faites toutes ces choses, vous serez bien en chemin pour créer des applications web sécurisées.