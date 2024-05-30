## Extension

Le framework a été conçu pour être extensible. Le framework est livré avec un ensemble de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes, d'enregistrer vos propres classes, ou même de remplacer des classes et des méthodes existantes.

Si vous recherchez un DIC (Conteneur d'Injection de Dépendance), rendez-vous sur la page du [Conteneur d'Injection de Dépendance](dependency-injection-container).

## Mapping des Méthodes

Pour mapper votre propre méthode personnalisée, vous utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('bonjour', function (string $nom) {
  echo "bonjour $nom!";
});

// Appeler votre méthode personnalisée
Flight::bonjour('Bob');
```

Cela est utilisé davantage lorsque vous devez transmettre des variables à votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme ci-dessous est davantage pour transmettre une configuration, puis appeler votre classe préconfigurée.

## Enregistrement des Classes

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register` :

```php
// Enregistrer votre classe
Flight::register('utilisateur', User::class);

// Obtenir une instance de votre classe
$user = Flight::utilisateur();
```

La méthode register vous permet également de transmettre des paramètres au constructeur de votre classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée. Vous pouvez définir les paramètres du constructeur en transmettant un tableau supplémentaire. Voici un exemple de chargement d'une connexion à une base de données :

```php
// Enregistrer une classe avec des paramètres de constructeur
Flight::register('bd', PDO::class, ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot depasse']);

// Obtenir une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','utilisateur','mot depasse');
//
$bd = Flight::bd();

// et si vous en aviez besoin plus tard dans votre code, vous appelez simplement à nouveau la même méthode
class SomeController {
  public function __construct() {
	$this->bd = Flight::bd();
  }
}
```

Si vous transmettez un paramètre de rappel supplémentaire, il sera exécuté immédiatement après la construction de la classe. Cela vous permet d'effectuer des procédures de configuration pour votre nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// L'objet construit sera passé au rappel
Flight::register(
  'bd',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot depasse'],
  function (PDO $bd) {
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée. Pour obtenir une nouvelle instance d'une classe, transmettez simplement `false` en tant que paramètre :

```php
// Instance partagée de la classe
$partagé = Flight::bd();

// Nouvelle instance de la classe
$nouveau = Flight::bd(false);
```

Gardez à l'esprit que les méthodes mappées priment sur les classes enregistrées. Si vous déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Remplacement des Méthodes du Framework

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos besoins, sans avoir à modifier de code.

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il appelle la méthode `notFound` qui renvoie une réponse générique `HTTP 404`. Vous pouvez remplacer ce comportement en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher une page d'erreur 404 personnalisée
  inclure 'erreurs/404.html';
});
```

Flight vous permet également de remplacer des composants principaux du framework. Par exemple, vous pouvez remplacer la classe de Route par défaut par votre propre classe personnalisée :

```php
// Enregistrer votre classe personnalisée
Flight::register('router', MyRouter::class);

// Lorsque Flight charge l'instance de Route, il chargera votre classe
$monroutage = Flight::router();
```

Cependant, les méthodes du framework telles que `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez une erreur si vous essayez de le faire.