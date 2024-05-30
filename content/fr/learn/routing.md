# Routage

> **Remarque :** Vous souhaitez en savoir plus sur le routage ? Consultez la page ["pourquoi un framework ?"](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight est réalisé en faisant correspondre un modèle d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'bonjour le monde !';
});
```

> Les routes sont appariées dans l'ordre où elles sont définies. La première route à correspondre à une requête sera invoquée.

### Rappels/Fonctions
Le rappel peut être n'importe quel objet appelable. Ainsi, vous pouvez utiliser une fonction régulière :

```php
function bonjour(){
    echo 'bonjour le monde !';
}

Flight::route('/', 'bonjour');
```

### Classes
Vous pouvez également utiliser une méthode statique d'une classe :

```php
class Salutation {
    public static function bonjour() {
        echo 'bonjour le monde !';
    }
}

Flight::route('/', [ 'Salutation','bonjour' ]);
```

Ou en créant d'abord un objet puis en appelant la méthode :

```php

// Greeting.php
class Salutation
{
    public function __construct() {
        $this->name = 'Jean Dupont';
    }

    public function bonjour() {
        echo "Bonjour, {$this->name}!";
    }
}

// index.php
$salutation = new Salutation();

Flight::route('/', [ $salutation, 'bonjour' ]);
// Vous pouvez également le faire sans créer l'objet en premier
// Remarque : Aucun argument ne sera injecté dans le constructeur
Flight::route('/', [ 'Salutation', 'bonjour' ]);
```

#### Injection de Dépendances via DIC (Container d'Injection de Dépendances)
Si vous souhaitez utiliser l'injection de dépendances via un conteneur (PSR-11, PHP-DI, Dice, etc), le seul type de routes où cela est disponible est soit en créant directement l'objet vous-même et en utilisant le conteneur pour créer votre objet, ou en utilisant des chaînes pour définir la classe et la méthode à appeler. Vous pouvez consulter la page [Injection de Dépendances](/learn/extending) pour plus d'informations.

Voici un exemple rapide :

```php

use flight\database\PdoWrapper;

// Greeting.php
class Salutation
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function bonjour(int $id) {
		// faire quelque chose avec $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT nom FROM utilisateurs WHERE id = ?", [ $id ]);
		echo "Bonjour, le monde ! Mon nom est {$name} !";
	}
}

// index.php

// Configurer le conteneur avec les paramètres dont vous avez besoin
// Consultez la page d'injection de dépendances pour plus d'informations sur PSR-11
$dice = new \Dice\Dice();

// N'oubliez pas de réaffecter la variable avec '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;nom_bd=test', 
		'root',
		'mot_de_passe'
	]
]);

// Enregistrer le gestionnaire de conteneur
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Routes comme d'habitude
Flight::route('/bonjour/@id', [ 'Salutation', 'bonjour' ]);
// ou
Flight::route('/bonjour/@id', 'Salutation->bonjour');
// ou
Flight::route('/bonjour/@id', 'Salutation::bonjour');

Flight::start();
```

## Routage par Méthode

Par défaut, les modèles de route sont appariés contre toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une requête POST.';
});

// Vous ne pouvez pas utiliser Flight::get() pour les routes car c'est une méthode pour obtenir des variables, pas pour créer une route.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Vous pouvez également mapper plusieurs méthodes vers un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu une requête GET ou POST.';
});
```

De plus, vous pouvez obtenir l'objet Router qui possède certaines méthodes d'aide que vous pouvez utiliser :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'bonjour le monde!';
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

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

## Paramètres Nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** Il n'est pas possible de faire correspondre les groupes regex `()` avec des paramètres nommés. :'\(

## Paramètres Optionnels

Vous pouvez spécifier des paramètres nommés optionnels pour la correspondance en enveloppant les segments entre parenthèses.

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

Tous les paramètres optionnels qui ne sont pas mis en correspondance seront transmis en tant que `NULL`.

## Jokers

La correspondance est effectuée uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour faire correspondre toutes les demandes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en renvoyant `true` à partir de votre fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifiez une certaine condition
  if ($nom !== "Bob") {
    // Continuer vers la route suivante
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Alias de Route

Vous pouvez attribuer un alias à une route, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

Cela est particulièrement utile si votre URL change. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id` à la place. Avec l'alias, vous n'avez pas à changer partout où vous référencez l'alias car l'alias retournera maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'aliasing de route fonctionne également en groupe :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

## Informations sur la Route

Si vous souhaitez inspecter les informations de correspondance de la route, vous pouvez demander que l'objet route soit transmis à votre fonction de rappel en passant `true` comme troisième paramètre dans la méthode de route. L'objet route sera toujours le dernier paramètre transmis à votre fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP correspondant
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière de correspondance
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le modèle d'URL
  $route->splat;

  // Affiche le chemin d'URL....si vous en avez vraiment besoin
  $route->pattern;

  // Affiche quels middleware sont assignés à ceci
  $route->middleware;

  // Affiche l'alias assigné à cette route
  $route->alias;
}, true);
```

## Regrouper des Routes

Il peut arriver que vous souhaitiez regrouper des routes associées ensemble (comme `/api/v1`). Vous pouvez le faire en utilisant la méthode `group` :

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
	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
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

	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec Contexte d'Objet

Vous pouvez toujours utiliser le regroupement de routes avec l'objet `Engine` de la manière suivante :

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

## Flux

Vous pouvez désormais diffuser des réponses au client en utilisant la méthode `streamWithHeaders()`. Cela est utile pour envoyer de gros fichiers, des processus longs ou générer de grandes réponses. Diffuser une route est géré un peu différemment qu'une route régulière.

> **Remarque :** La diffusion de réponses n'est disponible que si vous avez défini [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) sur false.

### Flux avec En-têtes Manuels

Vous pouvez diffuser une réponse au client en utilisant la méthode `stream()` sur une route. Si vous faites cela, vous devez définir toutes les méthodes manuellement avant de renvoyer quelque chose au client. Cela se fait avec la fonction `header()` de PHP ou la méthode `Flight::response()->setRealHeader()`.

```php
Flight::route('/@nomfichier', function($nomfichier) {

	// évidemment, vous devez nettoyer le chemin et tout.
	$nomFichierSecurise = basename($nomfichier);

	// Si vous avez des en-têtes supplémentaires à définir ici après l'exécution de la route
	// vous devez les définir avant de renvoyer quoi que ce soit en sortie.
	// Ils doivent tous être un appel brut à la fonction header() ou 
	// un appel à la méthode Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$nomFichierSecurise.'"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$nomFichierSecurise.'"');

	$donneesFichier = file_get_contents('/chemin/vers/les/fichiers/'.$nomFichierSecurise);

	// Gestion des erreurs et autres
	if(empty($donneesFichier)) {
		Flight::halt(404, 'Fichier non trouvé');
	}

	// définir manuellement la longueur du contenu si vous le souhaitez
	header('Content-Length: '.filesize($nomfichier));

	// Diffuser les données vers le client
	echo $donneesFichier;

// C'est la ligne magique ici
})->stream();
```

### Flux avec En-têtes

Vous pouvez également utiliser la méthode `streamWithHeaders()` pour définir les en-têtes avant de commencer le streaming.

```php
Flight::route('/utilisateurs-en-flux', function() {

	// vous pouvez ajouter d'autres en-têtes que vous souhaitez ici
	// vous devez simplement utiliser header() ou Flight::response()->setRealHeader()

	// cependant vous obtenez vos données, juste à titre d'exemple...
	$utilisateurs_stmt = Flight::db()->query("SELECT id, prenom, nom FROM utilisateurs");

	echo '{';
	$nombreUtilisateurs = count($utilisateurs);
	while($utilisateur = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($utilisateur);
		if(--$nombreUtilisateurs > 0) {
			echo ',';
		}

		// Ceci est nécessaire pour envoyer les données au client
		ob_flush();
	}
	echo '}';

// C'est ainsi que vous définirez les en-têtes avant de commencer le streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="utilisateurs.json"',
	// code d'état optionnel, par défaut à 200
	'status' => 200
]);
```