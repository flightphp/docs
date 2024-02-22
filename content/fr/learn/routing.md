# Routage

> **Remarque :** Vous voulez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight est réalisé en faisant correspondre un modèle d'URL avec une fonction de rappel ou un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'Bonjour le monde!';
});
```

La fonction de rappel peut être n'importe quel objet appelable. Vous pouvez donc utiliser une fonction régulière :

```php
function hello(){
    echo 'Bonjour le monde!';
}

Flight::route('/', 'hello');
```

Ou une méthode de classe :

```php
class Greeting {
    public static function hello() {
        echo 'Bonjour le monde!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Ou une méthode d'objet :

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Bonjour, {$this->name} !";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Les itinéraires sont mis en correspondance dans l'ordre où ils sont définis. Le premier itinéraire correspondant à une demande sera invoqué.

## Routage par méthode

Par défaut, les modèles de routage sont associés à toutes les méthodes de demande. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J´ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J´ai reçu une requête POST.';
});
```

Vous pouvez également mapper plusieurs méthodes vers un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J´ai reçu une requête GET ou POST.';
});
```

De plus, vous pouvez obtenir l'objet Router qui dispose de quelques méthodes d'aide que vous pouvez utiliser :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'Bonjour le monde!';
});

// demande GET
$router->get('/users', function() {
	echo 'utilisateurs';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressions régulières

Vous pouvez utiliser des expressions régulières dans vos itinéraires :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou des
paramètres nommés avec expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

## Paramètres nommés

Vous pouvez spécifier des paramètres nommés dans vos itinéraires qui seront transmis
à votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id) !";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant
le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** La correspondance de groupes de regex `()` avec des paramètres nommés n'est pas prise en charge. :'\(

## Paramètres optionnels

Vous pouvez spécifier des paramètres nommés qui sont facultatifs pour la correspondance en encapsulant
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

Tous les paramètres optionnels qui ne correspondent pas seront transmis en tant que `NULL`.

## Caractères génériques

La mise en correspondance est effectuée uniquement sur des segments d'URL individuels. Si vous voulez faire correspondre plusieurs
segments, vous pouvez utiliser le caractère générique `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour faire correspondre toutes les demandes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faites quelque chose
});
```

## Passage

Vous pouvez passer l'exécution au prochain itinéraire correspondant en retournant `true` depuis
votre fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifier une condition
  if ($nom !== "Bob") {
    // Continuer vers le prochain itinéraire
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Aliasing d'itinéraire

Vous pouvez attribuer un alias à un itinéraire, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle, par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');

// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

Ceci est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, supposons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id`.
Avec l'alias en place, vous n'avez pas à modifier partout où vous faites référence à l'alias car l'alias renverra maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'aliasing d'itinéraire fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');
});


// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

## Info d'itinéraire

Si vous voulez inspecter les informations d'itinéraire correspondant, vous pouvez demander que l'objet d'itinéraire
soit transmis à votre fonction de rappel en passant `true` comme troisième paramètre dans
la méthode d'itinéraire. L'objet d'itinéraire sera toujours le dernier paramètre transmis à votre
fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP mises en correspondance
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière mise en correspondance
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le schéma d'URL
  $route->splat;

  // Affiche le chemin de l'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Affiche quels middleware sont assignés à cela
  $route->middleware;

  // Affiche l'alias assigné à cet itinéraire
  $route->alias;
}, true);
```

## Regroupement d'itinéraire

Il peut arriver que vous vouliez regrouper des itinéraires liés ensemble (comme `/api/v1`).
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
	// Flight::get() obtient des variables, il ne définit pas un itinéraire ! Voir le contexte objet ci-dessous
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

	// Flight::get() obtient des variables, il ne définit pas un itinéraire ! Voir le contexte objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec Contexte d'Objet

Vous pouvez toujours utiliser le regroupement d'itinéraire avec l'objet `Engine` de la manière suivante :

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

## Diffusion en continu

Vous pouvez désormais diffuser des réponses au client en utilisant la méthode `streamWithHeaders()`.
Ceci est utile pour l'envoi de grands fichiers, de processus longs ou la génération de réponses volumineuses.
La diffusion d'un itinéraire est gérée un peu différemment qu'un itinéraire régulier.

> **Remarque :** La diffusion de réponses n'est disponible que si vous avez défini [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) sur false.

```php
Flight::route('/utilisateurs-en-streaming', function() {

	// de quelque façon que vous extrayez vos données, juste à titre d'exemple...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Cela est nécessaire pour envoyer les données au client
		ob_flush();
	}
	echo '}';

// Voici comment vous allez définir les en-têtes avant de commencer la diffusion.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// code d'état facultatif, par défaut à 200
	'status' => 200
]);