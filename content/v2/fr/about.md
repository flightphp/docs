# Qu'est-ce que Flight ?

Flight est un framework rapide, simple et extensible pour PHP.
Flight vous permet de créer rapidement et facilement des applications Web RESTful.

``` php
require 'flight/Flight.php';

// Définir une route
Flight::route('/', function(){
  echo 'hello world!';
});

// Démarrer l'application
Flight::start();
```

[En savoir plus](learn)

# Exigences

Flight nécessite PHP 7.4 ou une version supérieure.

# Licence

Flight est publié sous la licence [MIT](https://github.com/mikecao/flight/blob/master/LICENSE).

# Communauté

Nous sommes sur Matrix ! Discutez avec nous à [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuer

Ce site Web est hébergé sur [Github](https://github.com/mikecao/flightphp.com).
Les mises à jour et les traductions sont les bienvenues.