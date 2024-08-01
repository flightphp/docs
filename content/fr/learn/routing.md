## Routage

> **Remarque :** Vous voulez en savoir plus sur le routage ? Consultez la page ["Why a framework?"](/learn/why-frameworks) pour une explication plus détaillée.

Le routage de base dans Flight se fait en associant un motif d'URL avec une fonction de rappel ou un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'Bonjour le monde !';
});
```

> Les routes sont associées dans l'ordre où elles sont définies. La première route à correspondre à une requête sera invoquée.

### Rappels/Fonctions

Le rappel peut être n'importe quel objet qui est appelable. Vous pouvez donc utiliser une fonction normale :

```php
function hello() {
    echo 'Bonjour le monde !';
}

Flight::route('/', 'hello');
```

### Classes

Vous pouvez également utiliser une méthode statique d'une classe :

```php
class Greeting {
    public static function hello() {
        echo 'Bonjour le monde !';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Ou en créant d'abord un objet et en appelant ensuite la méthode :

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

Flight::route('/', [ $greeting, 'hello' ]);
// Vous pouvez également le faire sans créer d'abord l'objet
// Remarque : Aucun argument ne sera injecté dans le constructeur
Flight::route('/', [ 'Greeting', 'hello' ]);
// De plus, vous pouvez utiliser cette syntaxe plus courte
Flight::route('/', 'Greeting->hello');
// ou
Flight::route('/', Greeting::class.'->hello');
```

#### Injection de dépendances via le DIC (conteneur d'injection de dépendances)
Si vous souhaitez utiliser l'injection de dépendances via un conteneur (PSR-11, PHP-DI, Dice, etc.), le seul type de routes où cela est disponible est soit en créant directement l'objet vous-même et en utilisant le conteneur pour créer votre objet, soit vous pouvez utiliser des chaînes pour définir la classe et la méthode à appeler. Vous pouvez consulter la page [Injection de dépendances](/learn/extending) pour plus d'informations.

Voici un exemple rapide :

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// faire quelque chose avec $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Bonjour, le monde ! Mes prénom est {$name} !";
	}
}

// index.php

// Configurez le conteneur avec les paramètres dont vous avez besoin
// Consultez la page d'injection de dépendances pour plus d'informations sur PSR-11
$dice = new \Dice\Dice();

// N'oubliez pas de réaffecter la variable avec '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Enregistrer le gestionnaire de conteneur
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes comme d'habitude
Flight::route('/bonjour/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/bonjour/@id', 'Greeting->hello');
// ou
Flight::route('/bonjour/@id', 'Greeting::hello');

Flight::start();
```

## Routage par Méthode

Par défaut, les motifs de route sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une requête POST.';
});

// Vous ne pouvez pas utiliser Flight::get() pour les routes car c'est une méthode
//   pour obtenir des variables, et non pour créer une route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu soit une requête GET, soit une requête POST.';
});
```

De plus, vous pouvez obtenir l'objet Route qui a quelques méthodes d'aide à utiliser :

```php

$router = Flight::router();

// associe toutes les méthodes
$router->map('/', function() {
	echo 'Bonjour le monde !';
});

// requête GET
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
  // Ceci correspondra à /utilisateur/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou
des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

## Paramètres Nom-més

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à
votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id) !";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant
le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Ceci correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** Il n'est pas possible d'associer des groupes regex `()` avec des paramètres nommés. :'\(

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

Tous les paramètres optionnels qui ne sont pas associés seront transmis en tant que `NULL`.

## Jokers

La correspondance se fait uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs
segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour faire correspondre toutes les requêtes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en retournant `true` depuis
votre fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifier certaines conditions
  if ($nom !== "Bob") {
    // Continuer à la prochaine route
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Ceci sera appelé
});
```

## Aliasing des Routes

Vous pouvez attribuer un alias à une route, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

Cela est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, supposons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id`. Avec l'alias, vous n'avez pas à modifier partout où vous référencez l'alias car l'alias renverra maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'alias de route fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

## Infos sur la Route

Si vous voulez inspecter les informations de la route correspondante, vous pouvez demander que l'objet de route soit transmis à votre fonction de rappel en passant `true` comme troisième paramètre dans la méthode de route. L'objet de route sera toujours le dernier paramètre transmis à votre fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP associées
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Correspondance d'expression régulière
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Affiche le chemin de l'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Affiche quels middleware sont assignés à cela
  $route->middleware;

  // Montre l'alias assigné à cette route
  $route->alias;
}, true);
```

## Groupage de Routes

Il peut arriver que vous vouliez regrouper des routes liées ensemble (comme `/api/v1`).
Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/utilisateurs', function () {
	// Correspond à /api/v1/utilisateurs
  });

  Flight::route('/publications', function () {
	// Correspond à /api/v1/publications
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() récupère des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v1/utilisateurs
	});

	Flight::post('/publications', function () {
	  // Correspond à POST /api/v1/publications
	});

	Flight::put('/publications/1', function () {
	  // Correspond à PUT /api/v1/publications
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() récupère des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Groupage avec Contexte d'Objet

Vous pouvez toujours utiliser le groupage de routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // utilisez la variable $router
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/publications', function () {
	// Correspond à POST /api/v1/publications
  });
});
```

## Streaming

Vous pouvez désormais diffuser des réponses au client en utilisant la méthode `streamWithHeaders()`.
Ceci est utile pour envoyer de gros fichiers, des processus longs ou générer de grandes réponses.
La diffusion d'une route est gérée un peu différemment qu'une route régulière.

> **Remarque :** La diffusion de réponses n'est disponible que si vous avez défini [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) sur false.

### Diffusion avec En-têtes Manuels

Vous pouvez diffuser une réponse au client en utilisant la méthode `stream()` sur une route. Si vous
faites cela, vous devez définir toutes les méthodes manuellement avant de renvoyer quoi que ce soit au client.
Ceci est fait avec la fonction php `header()` ou la méthode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@nomFichier', function($nomFichier) {

	// vous auriez évidemment à assainir le chemin et tout.
	$nomFichierSecurise = basename($nomFichier);

	// Si vous avez des en-têtes supplémentaires à définir ici après que la route ait été exécutée
	// vous devez les définir avant d'afficher quoi que ce soit.
	// Ils doivent tous être un appel brut à la fonction header() ou
	// un appel à la méthode Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$nomFichierSecurise.'"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$nomFichierSafe.'"');

	$donneesFichier = file_get_contents('/chemin/vers/fichiers/'.$nomFichierSecurise);

	// Gestion des erreurs et autres
	if(empty($donneesFichier)) {
		Flight::halt(404, 'Fichier non trouvé');
	}

	// définir manuellement la longueur du contenu si vous le souhaitez
	header('Content-Length: '.filesize($nomFichier));

	// Diffuser les données vers le client
	echo $donneesFichier;

// Voici la ligne magique ici
})->stream();
```

### Diffusion avec En-têtes

Vous pouvez également utiliser la méthode `streamWithHeaders()` pour définir les en-têtes avant de commencer la diffusion.

```php
Flight::route('/stream-utilisateurs', function() {

	// vous pouvez ajouter tous les en-têtes supplémentaires que vous souhaitez ici
	// vous devez simplement utiliser header() ou Flight::response()->setRealHeader()

	// peu importe comment vous extrayez vos données, juste à titre d'exemple...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Cela est requis pour envoyer les données au client
		ob_flush();
	}
	echo '}';

// Voici comment vous allez définir les en-têtes avant de commencer la diffusion.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// code d'état facultatif, par défaut à 200
	'status' => 200
]);
```