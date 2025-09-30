# Migration vers v3

La compatibilité arrière a été maintenue dans l'ensemble, mais il y a certains changements dont vous devriez être conscient lors de la migration de v2 vers v3. Il y a des changements qui entraient trop en conflit avec les patrons de conception, donc certains ajustements ont dû être faits.

## Comportement de mise en tampon de sortie

_v3.5.0_

[Output buffering](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) est le processus par lequel la sortie générée par un script PHP est stockée dans un tampon (interne à PHP) avant d'être envoyée au client. Cela vous permet de modifier la sortie avant qu'elle ne soit envoyée au client.

Dans une application MVC, le Contrôleur est le « gestionnaire » et il gère ce que fait la vue. Avoir une sortie générée en dehors du contrôleur (ou dans le cas de Flight, parfois une fonction anonyme) rompt le patron MVC. Ce changement vise à être plus en ligne avec le patron MVC et à rendre le framework plus prévisible et plus facile à utiliser.

En v2, la mise en tampon de sortie était gérée d'une manière où elle ne fermait pas de manière cohérente son propre tampon de sortie, ce qui rendait [les tests unitaires](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) et [le streaming](https://github.com/flightphp/core/issues/413) plus difficiles. Pour la majorité des utilisateurs, ce changement ne vous affectera peut-être pas réellement. Cependant, si vous affichez du contenu en dehors des callables et des contrôleurs (par exemple dans un hook), vous risquez d'avoir des problèmes. Afficher du contenu dans les hooks, et avant que le framework ne s'exécute réellement, pouvait fonctionner dans le passé, mais cela ne fonctionnera plus à l'avenir.

### Où vous pourriez avoir des problèmes
```php
// index.php
require 'vendor/autoload.php';

// just an example
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// this will actually be fine
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// things like this will cause an error
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// this is actually just fine
	echo 'Hello World';

	// This should be just fine as well
	Flight::hello();
});

Flight::after('start', function(){
	// this will cause an error
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Activation du comportement de rendu v2

Pouvez-vous encore garder votre ancien code tel quel sans faire de réécriture pour le faire fonctionner avec v3 ? Oui, vous le pouvez ! Vous pouvez activer le comportement de rendu v2 en définissant l'option de configuration `flight.v2.output_buffering` à `true`. Cela vous permettra de continuer à utiliser l'ancien comportement de rendu, mais il est recommandé de le corriger à l'avenir. En v4 du framework, cela sera supprimé.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Now this will be just fine
	echo '<html><head><title>My Page</title></head><body>';
});

// more code 
```

## Changements du Dispatcher

_v3.7.0_

Si vous avez directement appelé des méthodes statiques pour `Dispatcher` telles que `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., vous devrez mettre à jour votre code pour ne plus appeler directement ces méthodes. `Dispatcher` a été converti pour être plus orienté objet afin que les Conteneurs d'Injection de Dépendances puissent être utilisés plus facilement. Si vous avez besoin d'invoquer une méthode de manière similaire à ce que faisait Dispatcher, vous pouvez manuellement utiliser quelque chose comme `$result = $class->$method(...$params);` ou `call_user_func_array()` à la place.

## Changements de `halt()` `stop()` `redirect()` et `error()`

_v3.10.0_

Le comportement par défaut avant 3.10.0 était d'effacer à la fois les en-têtes et le corps de la réponse. Cela a été changé pour ne vider que le corps de la réponse. Si vous avez besoin d'effacer également les en-têtes, vous pouvez utiliser `Flight::response()->clear()`.