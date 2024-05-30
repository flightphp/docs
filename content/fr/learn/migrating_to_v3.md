# Migration vers v3

La compatibilité ascendante a été en grande partie maintenue, mais il y a quelques changements dont vous devez être conscient lorsque vous migrez de v2 à v3.

## Comportement du tampon de sortie (3.5.0)

[La mise en tampon de sortie](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) est le processus par lequel la sortie générée par un script PHP est stockée dans un tampon (interne à PHP) avant d'être envoyée au client. Cela vous permet de modifier la sortie avant qu'elle ne soit envoyée au client.

Dans une application MVC, le contrôleur est le "gestionnaire" et il gère ce que fait la vue. Avoir une sortie générée en dehors du contrôleur (ou dans le cas de Flight parfois une fonction anonyme) casse le modèle MVC. Ce changement vise à être plus en phase avec le modèle MVC et à rendre le framework plus prévisible et plus facile à utiliser.

En v2, la mise en tampon de sortie était gérée de manière à ce qu'elle ne fermait pas de manière cohérente son propre tampon de sortie, ce qui rendait les [tests unitaires](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) et le [streaming](https://github.com/flightphp/core/issues/413) plus difficiles. Pour la majorité des utilisateurs, ce changement ne vous affectera en réalité peut-être pas. Cependant, si vous faites un écho de contenu en dehors des rappels et des contrôleurs (par exemple dans un crochet), vous risquez probablement de rencontrer des problèmes. Émettre du contenu dans des crochets, et avant que le framework ne s'exécute réellement, a pu fonctionner dans le passé, mais cela ne fonctionnera pas à l'avenir.

### Où vous pourriez rencontrer des problèmes
```php
// index.php
require 'vendor/autoload.php';

// juste un exemple
define('HEURE_DE_DEBUT', microtime(true));

function bonjour() {
	echo 'Bonjour le monde';
}

Flight::map('bonjour', 'bonjour');
Flight::after('bonjour', function(){
	// cela fonctionnera en fait
	echo '<p>Cette phrase Bonjour le monde vous est offerte par la lettre "B"</p>';
});

Flight::before('start', function(){
	// des choses comme ça provoqueront une erreur
	echo '<html><head><title>Ma page</title></head><body>';
});

Flight::route('/', function(){
	// cela va en fait bien
	echo 'Bonjour le monde';

	// Ceci devrait également bien fonctionner
	Flight::bonjour();
});

Flight::after('start', function(){
	// cela provoquera une erreur
	echo '<div>Votre page s'est chargée en '.(microtime(true) - HEURE_DE_DEBUT).' secondes</div></body></html>';
});
```

### Activer le comportement de rendu v2

Pouvez-vous toujours garder votre ancien code tel qu'il est sans le réécrire pour le faire fonctionner avec v3 ? Oui, vous le pouvez ! Vous pouvez activer le comportement de rendu v2 en définissant l'option de configuration `flight.v2.output_buffering` sur `true`. Cela vous permettra de continuer à utiliser l'ancien comportement de rendu, mais il est recommandé de le corriger pour l'avenir. Dans la v4 du framework, cela sera supprimé.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Maintenant cela fonctionnera très bien
	echo '<html><head><title>Ma page</title></head><body>';
});

// plus de code
```

## Changements du Dispatcher (3.7.0)

Si vous avez directement appelé des méthodes statiques pour `Dispatcher` telles que `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc. vous devrez mettre à jour votre code pour ne pas appeler directement ces méthodes. `Dispatcher` a été converti pour être plus orienté objet afin que les conteneurs d'injection de dépendances puissent être utilisés de manière plus facile. Si vous devez invoquer une méthode similaire à la façon dont Dispatcher le faisait, vous pouvez utiliser manuellement quelque chose comme `$result = $class->$method(...$params);` ou `call_user_func_array()` à la place.