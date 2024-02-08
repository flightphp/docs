# Qu'est-ce que Flight?

Flight est un framework rapide, simple et extensible pour PHP. Il est assez polyvalent et peut être utilisé pour construire n'importe quel type d'application web. Il est conçu en gardant à l'esprit la simplicité et est écrit de manière à être facile à comprendre et à utiliser.

Flight est un excellent framework pour les débutants qui découvrent PHP et qui souhaitent apprendre à construire des applications web. C'est aussi un excellent framework pour les développeurs expérimentés qui veulent construire des applications web rapidement et facilement. Il est conçu pour construire facilement une API RESTful, une application web simple ou une application web complexe.

```php
<?php

// si installé avec Composer
require 'vendor/autoload.php';
// ou si installé manuellement par fichier zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

Assez simple non? [En savoir plus sur Flight!](learn)

## Démarrage Rapide
Il y a une application exemple qui peut vous aider à commencer avec le Framework Flight. Rendez-vous sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour des instructions sur la façon de commencer! Vous pouvez également visiter la page des [exemples](examples) pour vous inspirer sur certaines des choses que vous pouvez faire avec Flight.

# Communauté

Nous sommes sur Matrix! Discutez avec nous sur [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuer

Il y a deux façons de contribuer à Flight: 

1. Vous pouvez contribuer au framework principal en visitant le [dépôt principal](https://github.com/flightphp/core). 
1. Vous pouvez contribuer à la documentation. Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur ou souhaitez améliorer quelque chose, n'hésitez pas à le corriger et à soumettre une demande de pull! Nous essayons de rester à jour, mais les mises à jour et les traductions de langues sont les bienvenues.

# Exigences

Flight nécessite PHP 7.4 ou version ultérieure.

**Remarque:** PHP 7.4 est supporté car au moment de la rédaction (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de maux de tête pour ces utilisateurs. Le framework supporte également PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).