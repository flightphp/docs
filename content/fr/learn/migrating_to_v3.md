# Migration vers v3

La compatibilité ascendante a été en grande partie maintenue, mais il y a des changements dont vous devez être conscient lorsque vous migrez de v2 à v3.

## Mise en tampon de sortie

[La mise en tampon de sortie](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) est le processus par lequel la sortie générée par un script PHP est stockée dans un tampon (interne à PHP) avant d'être envoyée au client. Cela vous permet de modifier la sortie avant qu'elle ne soit envoyée au client.

Dans une application MVC, le Contrôleur est le "gestionnaire" et il gère ce que fait la vue. Avoir une sortie générée en dehors du contrôleur (ou dans le cas de Flights parfois une fonction anonyme) casse le modèle MVC. Ce changement vise à être plus en accord avec le modèle MVC et à rendre le framework plus prévisible et plus facile à utiliser.

En v2, la mise en tampon de sortie était gérée d'une manière où elle ne fermait pas de manière cohérente son propre tampon de sortie, ce qui rendait [les tests unitaires](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) et [le streaming](https://github.com/flightphp/core/issues/413) plus difficiles. Pour la majorité des utilisateurs, ce changement ne vous affectera probablement pas. Cependant, si vous émettez du contenu en dehors des callables et des contrôleurs (par exemple dans un hook), vous risquez probablement de rencontrer des problèmes. Émettre du contenu dans des hooks, et avant que le framework s'exécute réellement, aurait pu fonctionner par le passé, mais cela ne fonctionnera pas à l'avenir.

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
	// cela fonctionnera en fait
	echo '<p>Cette phrase Hello World vous est offerte par la lettre "H"</p>';
});

Flight::before('start', function(){
	// des choses comme cela provoqueront une erreur
	echo '<html><head><title>Ma Page</title></head><body>';
});

Flight::route('/', function(){
	// c'est en fait très bien
	echo 'Hello World';

	// Cela devrait également fonctionner très bien
	Flight::hello();
});

Flight::after('start', function(){
	// cela provoquera une erreur
	echo '<div>Votre page s'est chargée en '.(microtime(true) - START_TIME).' secondes</div></body></html>';
});
```

### Activer le comportement de rendu v2

Pouvez-vous toujours conserver votre ancien code tel qu'il est sans le réécrire pour le faire fonctionner avec v3 ? Oui, vous le pouvez ! Vous pouvez activer le comportement de rendu v2 en définissant l'option de configuration `flight.v2.output_buffering` sur `true`. Cela vous permettra de continuer à utiliser l'ancien comportement de rendu, mais il est recommandé de le corriger à l'avenir. Dans la v4 du framework, cela sera supprimé.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Maintenant cela fonctionnera très bien
	echo '<html><head><title>Ma Page</title></head><body>';
});

// plus de code 
```