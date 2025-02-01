# Qu'est-ce que Flight ?

Flight est un framework PHP rapide, simple et extensible. Il est assez polyvalent et peut être utilisé pour construire tout type d'application web. Il est conçu avec simplicité à l'esprit et est écrit de manière à être facile à comprendre et à utiliser.

Flight est un excellent framework pour les débutants qui découvrent PHP et souhaitent apprendre à créer des applications web. C'est aussi un excellent framework pour les développeurs expérimentés qui souhaitent plus de contrôle sur leurs applications web. Il est conçu pour construire facilement une API RESTful, une application web simple ou une application web complexe.

## Démarrage rapide

```php
<?php

// si installé avec composer
require 'vendor/autoload.php';
// ou si installé manuellement via un fichier zip
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="Lecteur vidéo YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Assez simple, non ?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">En savoir plus sur Flight dans la documentation !</a>

    </div>
  </div>
</div>

### Application de squelette/boilerplate

Il existe une application exemple qui peut vous aider à démarrer avec le framework Flight. Allez sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour des instructions sur comment commencer ! Vous pouvez également visiter la page [examples](examples) pour trouver l'inspiration sur certaines des choses que vous pouvez faire avec Flight.

# Communauté

Nous sommes sur Matrix Discutez avec nous à [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Contribuer

Il existe deux façons de contribuer à Flight :

1. Vous pouvez contribuer au framework de base en visitant le [repository principal](https://github.com/flightphp/core). 
1. Vous pouvez contribuer à la documentation. Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur ou si vous souhaitez améliorer quelque chose, n'hésitez pas à le corriger et à soumettre une demande de tirage ! Nous essayons de rester à jour, mais les mises à jour et les traductions sont les bienvenues.

# Exigences

Flight nécessite PHP 7.4 ou une version ultérieure.

**Remarque :** PHP 7.4 est supporté car au moment de la rédaction (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer une migration vers PHP >8 causerait beaucoup de désagréments pour ces utilisateurs. Le framework supporte également PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).