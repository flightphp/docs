# Qu'est-ce que Flight ?

Flight est un framework rapide, simple et extensible pour PHP.
Flight vous permet de construire rapidement et facilement des applications web RESTful.

```php
<?php

// si installé avec composer
require 'vendor/autoload.php';
// ou si installé manuellement via un fichier zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'bonjour le monde!';
});

Flight::start();
```

Assez simple non? [Apprenez-en plus sur Flight !](learn)

## Application Skeleton
Il existe une application exemple qui peut vous aider à démarrer avec le framework Flight. Rendez-vous sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour obtenir des instructions sur la façon de commencer !

# Communauté

Nous sommes sur Matrix ! Discutez avec nous à [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribution

Ce site web est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur, n'hésitez pas à la corriger et à soumettre une pull request !
Nous essayons de rester à jour sur les choses, mais les mises à jour et les traductions de langues sont les bienvenues.

# Exigences

Flight nécessite PHP 7.4 ou supérieur.

**Remarque :** PHP 7.4 est pris en charge car au moment de la rédaction actuelle (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de maux de tête pour ces utilisateurs. Le framework prend également en charge PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).