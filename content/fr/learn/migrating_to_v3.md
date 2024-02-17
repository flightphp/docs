# Migration vers v3

La compatibilité ascendante a été en grande partie conservée, mais il existe quelques changements dont vous devez être conscient lors de la migration de v2 à v3.

## Mise en tampon de sortie

[La mise en tampon de sortie](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) est le processus par lequel la sortie générée par un script PHP est stockée dans un tampon (interne à PHP) avant d'être envoyée au client. Cela vous permet de modifier la sortie avant qu'elle ne soit envoyée au client.

Dans une application MVC, le Contrôleur est le "gestionnaire" et il gère ce que fait la vue. Avoir une sortie générée en dehors du contrôleur (ou dans le cas de Flight parfois une fonction anonyme) rompt le schéma MVC. Ce changement vise à être plus conforme au schéma MVC et à rendre le framework plus prévisible et plus facile à utiliser.

En v2, la mise en tampon de sortie était gérée de telle manière qu'elle ne fermait pas de manière cohérente son propre tampon de sortie et cela rendait les [tests unitaires](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) et [le streaming](https://github.com/flightphp/core/issues/413) plus difficile. Pour la majorité des utilisateurs, ce changement pourrait en réalité ne pas vous affecter. Cependant, si vous émettez du contenu en dehors des fonctions d'appel et des contrôleurs (par exemple dans un hook), vous risquez probablement de rencontrer des problèmes. Émettre du contenu dans des hooks, et avant que le framework n'exécute réellement le code, pouvait fonctionner dans le passé, mais cela ne fonctionnera pas à l'avenir.

### Où vous pourriez rencontrer des problèmes
```php
// index.php
require 'vendor/autoload.php';

// juste un exemple
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// cela fonctionnera en réalité
	echo '<p>Cette phrase Hello World vous est offerte par la lettre "H"</p>';
});

Flight::before('start', function(){
	// des choses comme celle-ci provoqueront une erreur
	echo '<html><head><title>Ma Page</title></head><body>';
});

Flight::route('/', function(){
	// c'est tout à fait correct
	echo 'Hello World';

	// Cela devrait également fonctionner correctement
	Flight::hello();
});

Flight::after('start', function(){
	// cela provoquera une erreur
	echo '<div>Votre page s'est chargée en '.(microtime(true) - START_TIME).' secondes</div></body></html>';
});
```

### Activation du comportement de rendu v2

Pouvez-vous toujours garder votre ancien code tel qu'il est sans le réécrire pour le faire fonctionner avec v3 ? Oui, vous le pouvez ! Vous pouvez activer le comportement de rendu v2 en définissant l'option de configuration `flight.v2.output_buffering` sur `true`. Cela vous permettra de continuer à utiliser l'ancien comportement de rendu, mais il est recommandé de le corriger pour l'avenir. Dans la version 4 du framework, cela sera supprimé.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Maintenant cela fonctionnera correctement
	echo '<html><head><title>Ma Page</title></head><body>';
});

// plus de code 
```