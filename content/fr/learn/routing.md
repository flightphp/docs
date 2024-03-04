# Routage

> **Remarque :** Vous voulez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight est effectué en associant un motif d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'bonjour le monde!';
});
```

Le rappel peut être un objet qui est appelable. Vous pouvez donc utiliser une fonction régulière :

```php
function bonjour(){
    echo 'bonjour le monde!';
}

Flight::route('/', 'bonjour');
```

Ou une méthode de classe :

```php
class Salutation {
    public static function bonjour() {
        echo 'bonjour le monde!';
    }
}

Flight::route('/', array('Salutation', 'bonjour'));
```

Ou une méthode d'objet :

```php

// Greeting.php
class Salutation
{
    public function __construct() {
        $this->nom = 'Jean Dupont';
    }

    public function bonjour() {
        echo "Bonjour, {$this->nom} !";
    }
}

// index.php
$salutation = new Salutation();

Flight::route('/', array($salutation, 'bonjour'));
```

Les routes sont associées dans l'ordre où elles sont définies. La première route à correspondre à une requête sera invoquée.

## Routage par Méthode

Par défaut, les motifs de routage sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une requête POST.';
});
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu une requête GET ou POST.';
});
```

De plus, vous pouvez récupérer l'objet Router qui a quelques méthodes d'aide à utiliser :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'bonjour le monde !';
});

// Requête GET
$router->get('/utilisateurs', function() {
	echo 'utilisateurs';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressions Régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à entretenir.

## Paramètres Nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id) !";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** La correspondance des groupes regex `()` avec des paramètres nommés n'est pas prise en charge. :'(

## Paramètres Optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en enveloppant
les segments entre parenthèses.

```php
Flight::route(
  '/blog(/@annee(/@mois(/@jour)))',
  function(?string $annee, ?string $mois, ?string $jour) {
    // Cela correspondra aux URL suivantes :
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Tous les paramètres optionnels non correspondants seront passés en tant que `NULL`.

## Jokers

La correspondance se fait uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs
segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour router toutes les requêtes vers un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Transmission

Vous pouvez passer l'exécution à la route correspondante suivante en retournant `true` de votre
fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifier une condition
  if ($nom !== "Bob") {
    // Continuer vers la prochaine route
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Attribution d'Alias de Route

Vous pouvez attribuer un alias à une route, de sorte que l'URL puisse être générée dynamiquement ultérieurement dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

Cela est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id` au lieu de cela.
Avec l'attribution d'alias en place, vous n'avez pas à modifier partout où vous faites référence à l'alias car l'alias renverra maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'attribution d'alias de route fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

## Informations sur la Route

Si vous souhaitez inspecter les informations sur la route correspondante, vous pouvez demander que l'objet route soit passé à votre fonction de rappel en passant `true` comme troisième paramètre dans
la méthode de routage. L'objet route sera toujours le dernier paramètre passé à votre
fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP correspondantes
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Affiche le chemin URL....si vous en avez vraiment besoin
  $route->pattern;

  // Montre quel middleware est assigné à cela
  $route->middleware;

  // Affiche l'alias attribué à cette route
  $route->alias;
}, true);
```

## Regroupement des Routes

Il peut arriver que vous vouliez regrouper des routes liées ensemble (comme `/api/v1`).
Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/utilisateurs', function () {
	// Correspond à /api/v1/utilisateurs
  });

  Flight::route('/articles', function () {
	// Correspond à /api/v1/articles
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, il ne définit pas de route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v1/utilisateurs
	});

	Flight::post('/articles', function () {
	  // Correspond à POST /api/v1/articles
	});

	Flight::put('/articles/1', function () {
	  // Correspond à PUT /api/v1/articles
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtient des variables, il ne définit pas de route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec Contexte d'Objet

Vous pouvez toujours utiliser le regroupement des routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // utilisez la variable $router
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/articles', function () {
	// Correspond à POST /api/v1/articles
  });
});
```

## Diffusion en Continu

Vous pouvez maintenant diffuser des réponses vers le client en utilisant la méthode `streamWithHeaders()`. 
C'est utile pour envoyer de gros fichiers, des processus longs, ou générer de grandes réponses. 
Diffuser une route est géré un peu différemment qu'une route régulière.

> **Remarque :** La diffusion des réponses n'est disponible que si vous avez défini [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) sur false.

```php
Flight::route('/utilisateurs-en-streaming', function() {

	// cependant vous récupérez vos données, juste à titre d'exemple...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// C'est nécessaire pour envoyer les données au client
		ob_flush();
	}
	echo '}';

// Voici comment vous définirez les en-têtes avant de commencer la diffusion.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// code d'état facultatif, par défaut à 200
	'status' => 200
]);
```