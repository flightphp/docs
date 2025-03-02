# Apprendre

Cette page est un guide pour apprendre Flight. Elle couvre les bases du framework et comment l'utiliser.

## <a name="routing"></a> Routage

Le routage dans Flight se fait en faisant correspondre un motif d'URL avec une fonction de rappel.

``` php
Flight::route('/', function(){
    echo 'bonjour le monde!';
});
```

La fonction de rappel peut être n'importe quel objet qui est appelable. Vous pouvez donc utiliser une fonction régulière :

``` php
function hello(){
    echo 'bonjour le monde!';
}

Flight::route('/', 'hello');
```

Ou une méthode de classe :

``` php
class Greeting {
    public static function hello() {
        echo 'bonjour le monde!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Ou une méthode d'objet :

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Bonjour, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Les routes sont appariées dans l'ordre dans lequel elles sont définies. La première route à correspondre à une demande sera invoquée.

### Routage par Méthode

Par défaut, les motifs de routage sont comparés à toutes les méthodes de demande. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

``` php
Flight::route('GET /', function(){
    echo 'J'ai reçu une demande GET.';
});

Flight::route('POST /', function(){
    echo 'J'ai reçu une demande POST.';
});
```

Vous pouvez également mapper plusieurs méthodes à une seule fonction de rappel en utilisant un délimiteur `|` :

``` php
Flight::route('GET|POST /', function(){
    echo 'J'ai reçu soit une demande GET soit une demande POST.';
});
```

### Expressions Régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

``` php
Flight::route('/user/[0-9]+', function(){
    // Cela correspondra à /user/1234
});
```

### Paramètres Nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront passés à votre fonction de rappel.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "bonjour, $name ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Cela correspondra à /bob/123
    // Mais ne correspondra pas à /bob/12345
});
```

### Paramètres Optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en enveloppant des segments entre parenthèses.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Cela correspondra aux URL suivantes :
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Tous les paramètres optionnels qui ne correspondent pas seront passés comme NULL.

### Jokers

La correspondance se fait uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs segments, vous pouvez utiliser le joker `*`.

``` php
Flight::route('/blog/*', function(){
    // Cela correspondra à /blog/2000/02/01
});
```

Pour router toutes les demandes vers une seule fonction de rappel, vous pouvez faire :

``` php
Flight::route('*', function(){
    // Faire quelque chose
});
```

### Passage

Vous pouvez passer l'exécution à la prochaine route correspondante en retournant `true` de votre fonction de rappel.

``` php
Flight::route('/user/@name', function($name){
    // Vérifiez une condition
    if ($name != "Bob") {
        // Continuez vers la prochaine route
        return true;
    }
});

Flight::route('/user/*', function(){
    // Cela sera appelé
});
```

### Info de Route

Si vous souhaitez inspecter les informations de la route correspondante, vous pouvez demander que l'objet de la route soit passé à votre fonction de rappel en passant `true` comme troisième paramètre dans la méthode de route. L'objet de la route sera toujours le dernier paramètre passé à votre fonction de rappel.

``` php
Flight::route('/', function($route){
    // Tableau des méthodes HTTP correspondant
    $route->methods;

    // Tableau des paramètres nommés
    $route->params;

    // Expression régulière correspondante
    $route->regex;

    // Contient le contenu de tout '*' utilisé dans le motif d'URL
    $route->splat;
}, true);
```
### Groupement de Routes

Il peut arriver que vous souhaitiez grouper les routes connexes ensemble (comme `/api/v1`).
Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Correspond à /api/v1/users
  });

  Flight::route('/posts', function () {
	// Correspond à /api/v1/posts
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /users', function () {
	  // Correspond à GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Correspond à POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Correspond à PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /users', function () {
	  // Correspond à GET /api/v2/users
	});
  });
});
```

#### Groupement avec Contexte d'Objet

Vous pouvez toujours utiliser le groupement de routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Correspond à GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Correspond à POST /api/v1/posts
  });
});
```

### Aliasing de Routes

Vous pouvez assigner un alias à une route, afin que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/users/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');

// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // renverra '/users/5'
```

Ceci est particulièrement utile si votre URL venait à changer. Dans l'exemple ci-dessus, supposons que les utilisateurs aient été déplacés vers `/admin/users/@id` à la place.
Avec l'alias en place, vous n'avez pas à changer quoi que ce soit où vous faites référence à l'alias, car l'alias renverra désormais `/admin/users/5` comme dans l'exemple ci-dessus.

L'alias de route fonctionne également dans les groupes :

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'user_view');
});

// plus tard dans le code quelque part
Flight::getUrl('user_view', [ 'id' => 5 ]); // renverra '/users/5'
```

## <a name="extending"></a> Étendre

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes, d'enregistrer vos propres classes, ou même de remplacer des classes et des méthodes existantes.

### Mappage des Méthodes

Pour mapper votre propre méthode personnalisée, vous utilisez la fonction `map` :

``` php
// Mapper votre méthode
Flight::map('hello', function($name){
    echo "bonjour $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
```

### Enregistrement de Classes

Pour enregistrer votre propre classe, vous utilisez la fonction `register` :

``` php
// Enregistrer votre classe
Flight::register('user', 'User');

// Obtenir une instance de votre classe
$user = Flight::user();
```

La méthode d'enregistrement vous permet également de passer des paramètres à votre constructeur de classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion à la base de données :

``` php
// Enregistrer la classe avec des paramètres de constructeur
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Obtenir une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement après la construction de la classe. Cela vous permet d'effectuer des procédures de configuration pour votre nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

``` php
// La fonction de rappel sera passée à l'objet qui a été construit
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée.
Pour obtenir une nouvelle instance de la classe, il vous suffit de passer `false` comme paramètre :

``` php
// Instance partagée de la classe
$shared = Flight::db();

// Nouvelle instance de la classe
$new = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## <a name="overriding"></a> Surcharge

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos propres besoins, sans avoir à modifier le code.

Par exemple, lorsque Flight ne parvient pas à faire correspondre une URL à une route, elle invoque la méthode `notFound` qui envoie une réponse générique `HTTP 404`. Vous pouvez remplacer ce comportement en utilisant la méthode `map` :

``` php
Flight::map('notFound', function(){
    // Afficher une page 404 personnalisée
    include 'errors/404.html';
});
```

Flight permet également de remplacer des composants principaux du framework.
Par exemple, vous pouvez remplacer la classe Router par défaut par votre propre classe personnalisée :

``` php
// Enregistrer votre classe personnalisée
Flight::register('router', 'MyRouter');

// Lorsque Flight charge l'instance Router, elle chargera votre classe
$myrouter = Flight::router();
```

Les méthodes du framework comme `map` et `register` ne peuvent cependant pas être remplacées. Vous obtiendrez une erreur si vous essayez de le faire.

## <a name="filtering"></a> Filtrage

Flight vous permet de filtrer les méthodes avant et après leur appel. Il n'y a pas de hooks prédéfinis que vous devez mémoriser. Vous pouvez filtrer n'importe laquelle des méthodes par défaut du framework ainsi que toutes les méthodes personnalisées que vous avez mappées.

Une fonction de filtre ressemble à ceci :

``` php
function(&$params, &$output) {
    // Code de filtrage
}
```

En utilisant les variables passées, vous pouvez manipuler les paramètres d'entrée et/ou la sortie.

Vous pouvez faire exécuter un filtre avant une méthode en faisant :

``` php
Flight::before('start', function(&$params, &$output){
    // Faire quelque chose
});
```

Vous pouvez faire exécuter un filtre après une méthode en faisant :

``` php
Flight::after('start', function(&$params, &$output){
    // Faire quelque chose
});
```

Vous pouvez ajouter autant de filtres que vous le souhaitez à n'importe quelle méthode. Ils seront appelés dans l'ordre dans lequel ils sont déclarés.

Voici un exemple du processus de filtrage :

``` php
// Mapper une méthode personnalisée
Flight::map('hello', function($name){
    return "Bonjour, $name!";
});

// Ajouter un filtre avant
Flight::before('hello', function(&$params, &$output){
    // Manipuler le paramètre
    $params[0] = 'Fred';
});

// Ajouter un filtre après
Flight::after('hello', function(&$params, &$output){
    // Manipuler la sortie
    $output .= " Passez une bonne journée!";
});

// Invoker la méthode personnalisée
echo Flight::hello('Bob');
```

Cela devrait afficher :

``` html
Bonjour Fred! Passez une bonne journée!
```

Si vous avez défini plusieurs filtres, vous pouvez briser la chaîne en retournant `false` dans l'une de vos fonctions de filtre :

``` php
Flight::before('start', function(&$params, &$output){
    echo 'un';
});

Flight::before('start', function(&$params, &$output){
    echo 'deux';

    // Cela mettra fin à la chaîne
    return false;
});

// Cela ne sera pas appelé
Flight::before('start', function(&$params, &$output){
    echo 'trois';
});
```

Notez que les méthodes principales telles que `map` et `register` ne peuvent pas être filtrées car elles sont appelées directement et non invoquées dynamiquement.

## <a name="variables"></a> Variables

Flight vous permet de sauvegarder des variables afin qu'elles puissent être utilisées n'importe où dans votre application.

``` php
// Sauvegarder votre variable
Flight::set('id', 123);

// Ailleurs dans votre application
$id = Flight::get('id');
```
Pour voir si une variable a été définie, vous pouvez faire :

``` php
if (Flight::has('id')) {
     // Faire quelque chose
}
```

Vous pouvez effacer une variable en faisant :

``` php
// Efface la variable id
Flight::clear('id');

// Efface toutes les variables
Flight::clear();
```

Flight utilise également des variables à des fins de configuration.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Vues

Flight fournit quelques fonctionnalités de templating de base par défaut. Pour afficher un modèle de vue, appelez la méthode `render` avec le nom du fichier modèle et des données de modèle optionnelles :

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Les données de modèle que vous passez sont automatiquement injectées dans le modèle et peuvent être référencées comme une variable locale. Les fichiers de modèle sont simplement des fichiers PHP. Si le contenu du fichier modèle `hello.php` est :

``` php
Bonjour, '<?php echo $name; ?>'!
```

La sortie serait :

``` html
Bonjour, Bob!
```

Vous pouvez également définir manuellement des variables de vue en utilisant la méthode set :

``` php
Flight::view()->set('name', 'Bob');
```

La variable `name` est maintenant disponible dans toutes vos vues. Vous pouvez donc simplement faire :

``` php
Flight::render('hello');
```

Notez que lorsque vous spécifiez le nom du modèle dans la méthode render, vous pouvez omettre l'extension `.php`.

Par défaut, Flight recherchera un répertoire `views` pour les fichiers modèles. Vous pouvez définir un chemin alternatif pour vos modèles en définissant la configuration suivante :

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Mises en Page

Il est courant pour les sites Web d'avoir un seul fichier modèle de mise en page avec un contenu interchangeable. Pour rendre le contenu à utiliser dans une mise en page, vous pouvez passer un paramètre optionnel à la méthode `render`.

``` php
Flight::render('header', array('heading' => 'Bonjour'), 'header_content');
Flight::render('body', array('body' => 'Monde'), 'body_content');
```

Votre vue aura donc des variables sauvegardées appelées `header_content` et `body_content`.
Vous pouvez ensuite rendre votre mise en page en faisant :

``` php
Flight::render('layout', array('title' => 'Page d\'accueil'));
```

Si les fichiers de modèle ressemblent à ceci :

`header.php` :

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php` :

``` php
<div><?php echo $body; ?></div>
```

`layout.php` :

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

La sortie serait :

``` html
<html>
<head>
<title>Page d'accueil</title>
</head>
<body>
<h1>Bonjour</h1>
<div>Monde</div>
</body>
</html>
```

### Vues Personnalisées

Flight vous permet d'échanger le moteur de vue par défaut simplement en enregistrant votre propre classe de vue. Voici comment vous utiliseriez le moteur de template [Smarty](http://www.smarty.net/) pour vos vues :

``` php
// Charger la bibliothèque Smarty
require './Smarty/libs/Smarty.class.php';

// Enregistrer Smarty comme la classe de vue
// Passez également une fonction de rappel pour configurer Smarty lors du chargement
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Assigner des données de modèle
Flight::view()->assign('name', 'Bob');

// Afficher le modèle
Flight::view()->display('hello.tpl');
```

Pour être complet, vous devriez également remplacer la méthode render par défaut de Flight :

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Gestion des Erreurs

### Erreurs et Exceptions

Toutes les erreurs et exceptions sont capturées par Flight et passées à la méthode `error`. Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Internal Server Error` avec quelques informations sur l'erreur.

Vous pouvez remplacer ce comportement selon vos propres besoins :

``` php
Flight::map('error', function(Exception $ex){
    // Gérer l'erreur
    echo $ex->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas enregistrées dans le serveur web. Vous pouvez activer cette fonction en changeant la configuration :

``` php
Flight::set('flight.log_errors', true);
```

### Non Trouvé

Lorsque l'URL ne peut pas être trouvée, Flight appelle la méthode `notFound`. Le comportement par défaut est d'envoyer une réponse `HTTP 404 Not Found` avec un message simple.

Vous pouvez remplacer ce comportement selon vos propres besoins :

``` php
Flight::map('notFound', function(){
    // Gérer non trouvé
});
```

## <a name="redirects"></a> Redirections

Vous pouvez rediriger la demande actuelle en utilisant la méthode `redirect` et en passant une nouvelle URL :

``` php
Flight::redirect('/new/location');
```

Par défaut, Flight envoie un code d'état HTTP 303. Vous pouvez éventuellement définir un code personnalisé :

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Demandes

Flight encapsule la demande HTTP dans un seul objet, qui peut être accédé en faisant :

``` php
$request = Flight::request();
```

L'objet de demande fournit les propriétés suivantes :

``` html
url - L'URL demandée
base - Le sous-répertoire parent de l'URL
method - La méthode de demande (GET, POST, PUT, DELETE)
referrer - L'URL référente
ip - Adresse IP du client
ajax - Si la demande est une demande AJAX
scheme - Le protocole du serveur (http, https)
user_agent - Informations sur le navigateur
type - Le type de contenu
length - La longueur du contenu
query - Paramètres de chaîne de requête
data - Données Post ou données JSON
cookies - Données de cookies
files - Fichiers téléchargés
secure - Si la connexion est sécurisée
accept - Paramètres d'acceptation HTTP
proxy_ip - Adresse IP du proxy du client
```

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files` en tant que tableaux ou objets.

Ainsi, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

``` php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

``` php
$id = Flight::request()->query->id;
```

### Corps de Demande RAW

Pour obtenir le corps brut de la demande HTTP, par exemple lors du traitement des demandes PUT, vous pouvez faire :

``` php
$body = Flight::request()->getBody();
```

### Entrée JSON

Si vous envoyez une demande avec le type `application/json` et les données `{"id": 123}`, elles seront disponibles à partir de la propriété `data` :

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

``` php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` et un message optionnels :

``` php
Flight::halt(200, 'Soyez de retour sous peu...');
```

Appeler `halt` supprimera tout contenu de réponse jusqu'à ce point. Si vous souhaitez arrêter le framework et afficher la réponse actuelle, utilisez la méthode `stop` :

``` php
Flight::stop();
```

## <a name="httpcaching"></a> Mise en Cache HTTP

Flight fournit un support intégré pour la mise en cache au niveau HTTP. Si la condition de mise en cache est remplie, Flight renverra une réponse HTTP `304 Not Modified`. La prochaine fois que le client demandera la même ressource, il sera invité à utiliser sa version mise en cache localement.

### Dernière Modification

Vous pouvez utiliser la méthode `lastModified` et passer un horodatage UNIX pour définir la date et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera à utiliser son cache jusqu'à ce que la valeur de dernière modification change.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Ce contenu sera mis en cache.';
});
```

### ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant que vous souhaitez pour la ressource :

``` php
Flight::route('/news', function(){
    Flight::etag('mon-id-unique');
    echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit que l'appel de `lastModified` ou `etag` définira et vérifiera également la valeur du cache. Si la valeur du cache est identique entre les demandes, Flight enverra immédiatement une réponse `HTTP 304` et cessera le traitement.

## <a name="json"></a> JSON

Flight fournit un support pour l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON, vous passez des données à encoder en JSON :

``` php
Flight::json(array('id' => 123));
```

Pour les demandes JSONP, vous pouvez éventuellement passer le nom du paramètre de requête que vous utilisez pour définir votre fonction de rappel :

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Ainsi, lors d'une demande GET utilisant `?q=my_func`, vous devriez recevoir la sortie :

``` json
my_func({"id":123});
```

Si vous ne passez pas de nom de paramètre de requête, il sera par défaut `jsonp`.

## <a name="configuration"></a> Configuration

Vous pouvez personnaliser certains comportements de Flight en définissant des valeurs de configuration via la méthode `set`.

``` php
Flight::set('flight.log_errors', true);
```

Voici une liste de tous les paramètres de configuration disponibles :

``` html 
flight.base_url - Remplace l'URL de base de la demande. (par défaut : null)
flight.case_sensitive - Correspondance sensible à la casse pour les URL. (par défaut : false)
flight.handle_errors - Permet à Flight de gérer toutes les erreurs en interne. (par défaut : true)
flight.log_errors - Enregistre les erreurs dans le fichier journal des erreurs du serveur web. (par défaut : false)
flight.views.path - Répertoire contenant les fichiers modèles de vue. (par défaut : ./views)
flight.views.extension - Extension de fichier modèle de vue. (par défaut : .php)
```

## <a name="frameworkmethods"></a> Méthodes du Framework

Flight est conçu pour être facile à utiliser et à comprendre. Voici l'ensemble complet des méthodes pour le framework. Il se compose de méthodes principales, qui sont des méthodes statiques régulières, et de méthodes extensibles, qui sont des méthodes mappées pouvant être filtrées ou remplacées.

### Méthodes Principales

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crée une méthode de framework personnalisée.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Enregistre une classe à une méthode de framework.
Flight::before(string $name, callable $callback) // Ajoute un filtre avant une méthode de framework.
Flight::after(string $name, callable $callback) // Ajoute un filtre après une méthode de framework.
Flight::path(string $path) // Ajoute un chemin pour le chargement automatique des classes.
Flight::get(string $key) // Obtient une variable.
Flight::set(string $key, mixed $value) // Définit une variable.
Flight::has(string $key) // Vérifie si une variable est définie.
Flight::clear(array|string $key = []) // Efface une variable.
Flight::init() // Initialise le framework avec ses paramètres par défaut.
Flight::app() // Retourne l'instance de l'objet application
```

### Méthodes Extensibles

```php
Flight::start() // Démarre le framework.
Flight::stop() // Arrête le framework et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le framework avec un code d'état et un message optionnels.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Mappe un motif d'URL à une fonction de rappel.
Flight::group(string $pattern, callable $callback) // Crée un groupement d'URLs, le motif doit être une chaîne.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::render(string $file, array $data, ?string $key = null) // Rend un fichier modèle.
Flight::error(Throwable $error) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'string') // Effectue la mise en cache HTTP ETag.
Flight::lastModified(int $time) // Effectue la mise en cache HTTP de dernière modification.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSONP.
```

Toutes les méthodes personnalisées ajoutées avec `map` et `register` peuvent également être filtrées.

## <a name="frameworkinstance"></a> Instance du Framework

Au lieu d'exécuter Flight en tant que classe statique globale, vous pouvez éventuellement l'exécuter en tant qu'instance d'objet.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'bonjour le monde!';
});

$app->start();
```

Ainsi, au lieu d'appeler la méthode statique, vous appelleriez la méthode d'instance avec le même nom sur l'objet Engine.