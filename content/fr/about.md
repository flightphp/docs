# Qu'est-ce que Flight ?

Flight est un framework rapide, simple et extensible pour PHP. Il est assez polyvalent et peut être utilisé pour construire n'importe quel type d'application web. Il est conçu avec la simplicité à l'esprit et est écrit d'une manière qui est facile à comprendre et à utiliser.

Flight est un excellent framework pour les débutants qui découvrent PHP et qui veulent apprendre à construire des applications web. C'est aussi un excellent framework pour les développeurs expérimentés qui veulent plus de contrôle sur leurs applications web. Il est conçu pour construire facilement une API RESTful, une simple application web ou une application web complexe.

## Démarrage rapide

```php
<?php

// si installé avec composer
require 'vendor/autoload.php';
// ou si installé manuellement par fichier zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Assez simple, n'est-ce pas ? [Apprenez-en plus sur Flight dans la documentation !](learn)

### Application squelette/modèle

Il existe une application exemple qui peut vous aider à démarrer avec le framework Flight. Rendez-vous sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour des instructions sur la façon de commencer ! Vous pouvez également visiter la page des [exemples](examples) pour trouver de l'inspiration sur certaines des choses que vous pouvez réaliser avec Flight.

# Communauté

Nous sommes sur Matrix Chat avec nous à [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribution

Il y a deux façons de contribuer à Flight : 

1. Vous pouvez contribuer au framework de base en visitant le [dépôt principal](https://github.com/flightphp/core).
1. Vous pouvez contribuer à la documentation. Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur ou souhaitez améliorer quelque chose, n'hésitez pas à le corriger et à soumettre une demande de tirage ! Nous essayons de rester à jour, mais les mises à jour et les traductions de langue sont les bienvenues.

# Prérequis

Flight nécessite PHP 7.4 ou supérieur.

**Remarque :** PHP 7.4 est pris en charge car au moment de la rédaction (2024), PHP 7.4 est la version par défaut pour certaines distributions LTS de Linux. Forcer un passage à PHP >8 causerait beaucoup de maux de tête pour ces utilisateurs. Le framework prend également en charge PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).