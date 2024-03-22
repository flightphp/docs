# Qu'est-ce que Flight ?

Flight est un framework rapide, simple et extensible pour PHP. Il est assez polyvalent et peut être utilisé pour construire tout type d'application web. Il est conçu en gardant à l'esprit la simplicité et est écrit d'une manière facile à comprendre et à utiliser.

Flight est un excellent framework pour les débutants qui sont nouveaux en PHP et veulent apprendre à construire des applications web. C'est aussi un excellent framework pour les développeurs expérimentés qui veulent plus de contrôle sur leurs applications web. Il est conçu pour construire facilement une API RESTful, une application web simple ou une application web complexe.

## Démarrage rapide

```php
<?php

// si installé avec Composer
require 'vendor/autoload.php';
// ou si installé manuellement par fichier zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'bonjour le monde!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Lecteur vidéo YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Assez simple, n'est-ce pas ? [Apprenez-en plus sur Flight dans la documentation !](learn)

### Application squelette/modèle

Il existe une application exemple qui peut vous aider à démarrer avec le Framework Flight. Rendez-vous sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour des instructions sur la façon de démarrer ! Vous pouvez également visiter la page des [exemples](examples) pour vous inspirer sur certaines choses que vous pouvez faire avec Flight.

# Communauté

Nous sommes sur Matrix ! Discutez avec nous sur [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribution

Il y a deux façons de contribuer à Flight : 

1. Vous pouvez contribuer au framework principal en visitant le [dépôt principal](https://github.com/flightphp/core).
1. Vous pouvez contribuer à la documentation. Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur ou souhaitez améliorer quelque chose, n'hésitez pas à le corriger et à soumettre une pull request ! Nous essayons de rester à jour, mais les mises à jour et les traductions de langues sont les bienvenues.

# Conditions requises

Flight nécessite PHP 7.4 ou supérieur.

**Remarque :** PHP 7.4 est pris en charge car, au moment de la rédaction actuelle (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de maux de tête pour ces utilisateurs. Le framework prend également en charge PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).