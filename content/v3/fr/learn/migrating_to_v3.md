# Migration vers v3

La compatibilité ascendante a été en grande partie maintenue, mais il y a des changements dont vous devez être conscient lorsque vous migrez de la v2 à la v3.

## Comportement de l'Empaquetage de Sortie (3.5.0)

[Empaquetage de sortie](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) est le processus par lequel la sortie générée par un script PHP est stockée dans un tampon (interne à PHP) avant d'être envoyée au client. Cela vous permet de modifier la sortie avant qu'elle ne soit envoyée au client.

Dans une application MVC, le contrôleur est le "manager" et il gère ce que fait la vue. Avoir une sortie générée en dehors du contrôleur (ou dans le cas de Flight parfois une fonction anonyme) rompt avec le modèle MVC. Ce changement vise à être plus en conformité avec le modèle MVC et à rendre le framework plus prévisible et plus facile à utiliser.

Dans la v2, l'empaquetage de sortie était géré d'une manière où il ne fermait pas systématiquement son propre tampon de sortie, ce qui rendait les [tests unitaires](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) et le [streaming](https://github.com/flightphp/core/issues/413) plus difficiles. Pour la majorité des utilisateurs, ce changement ne devrait pas vraiment vous affecter. Cependant, si vous émettez du contenu en dehors des appels de fonction et des contrôleurs (par exemple dans un hook), vous risquez probablement de rencontrer des problèmes. Émettre du contenu dans les hooks, et avant que le framework ne s'exécute réellement, a pu fonctionner par le passé, mais cela ne fonctionnera pas à l'avenir.

### Où vous pourriez rencontrer des problèmes
```php
// index.php
require 'vendor/autoload.php';

// juste un exemple
define('START_TIME', microtime(true));

function hello() {
	echo 'Bonjour le monde';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// cela ira en réalité
	echo '<p>Cette phrase Bonjour le monde vous est offerte par la lettre "B"</p>';
});

Flight::before('start', function(){
	// des choses comme cela vont causer une erreur
	echo '<html><head><title>Ma Page</title></head><body>';
});

Flight::route('/', function(){
	// c'est en réalité tout à fait bien
	echo 'Bonjour le monde';

	// Cela devrait également fonctionner
	Flight::hello();
});

Flight::after('start', function(){
	// cela va causer une erreur
	echo '<div>Votre page s'est chargée en '.(microtime(true) - START_TIME).' secondes</div></body></html>';
});
```

### Activation du Comportement de Rendu v2

Pouvez-vous conserver votre vieux code tel quel sans le réécrire pour le faire fonctionner avec la v3? Oui, vous le pouvez! Vous pouvez activer le comportement de rendu v2 en paramétrant l'option de configuration `flight.v2.output_buffering` sur `true`. Cela vous permettra de continuer à utiliser l'ancien comportement de rendu, mais il est recommandé de le corriger pour l'avenir. Dans la v4 du framework, cela sera supprimé.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Maintenant cela fonctionnera parfaitement
	echo '<html><head><title>Ma page</title></head><body>';
});

// plus de code 
```

## Changements du Dispatcher (3.7.0)

Si vous appelez directement des méthodes statiques pour `Dispatcher` telles que `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., vous devrez mettre à jour votre code pour ne pas appeler directement ces méthodes. `Dispatcher` a été converti pour être plus orienté objet afin que les conteneurs d'injection de dépendances puissent être utilisés plus facilement. Si vous avez besoin d'appeler une méthode de manière similaire à la manière dont le Dispatcher le faisait, vous pouvez utiliser manuellement quelque chose comme `$result = $class->$method(...$params);` ou `call_user_func_array()` à la place.

## Changements de `halt()` `stop()` `redirect()` et `error()` (3.10.0)

Avant la version 3.10.0, le comportement par défaut était d'effacer à la fois les en-têtes et le corps de la réponse. Cela a été modifié pour effacer uniquement le corps de la réponse. Si vous devez également effacer les en-têtes, vous pouvez utiliser `Flight::response()->clear()`.