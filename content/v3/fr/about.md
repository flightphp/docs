# Qu'est-ce que Flight?

Flight est un framework rapide, simple et extensible pour PHP. Il est assez polyvalent et peut être utilisé pour construire tout type d'application web. Il est conçu avec simplicité à l'esprit et est écrit de manière à être facile à comprendre et à utiliser.

Flight est un excellent framework pour les débutants qui découvrent PHP et qui souhaitent apprendre à créer des applications web. C'est également un excellent framework pour les développeurs expérimentés qui souhaitent plus de contrôle sur leurs applications web. Il est conçu pour permettre de construire facilement une API RESTful, une application web simple ou une application web complexe.

## Démarrage rapide

Installez-le d'abord avec Composer

```bash
composer require flightphp/core
```

ou vous pouvez télécharger un zip du dépôt [ici](https://github.com/flightphp/core). Vous auriez ensuite un fichier `index.php` de base comme suit :

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

C'est tout ! Vous avez une application Flight de base. Vous pouvez maintenant exécuter ce fichier avec `php -S localhost:8000` et visiter `http://localhost:8000` dans votre navigateur pour voir la sortie.

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

## Est-ce rapide ?

Oui ! Flight est rapide. C'est l'un des frameworks PHP les plus rapides disponibles. Vous pouvez voir tous les benchmarks sur [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Voir le benchmark ci-dessous avec quelques autres frameworks PHP populaires.

| Framework | Reqs/plaintext/sec | Reqs/JSON/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Application Skeleton/Boilerplate

Il existe une application d'exemple qui peut vous aider à démarrer avec le framework Flight. Allez sur [flightphp/skeleton](https://github.com/flightphp/skeleton) pour les instructions sur la façon de commencer ! Vous pouvez également visiter la page des [exemples](examples) pour vous inspirer de certaines des choses que vous pouvez faire avec Flight.

# Communauté

Nous sommes sur Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Et Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contributions

Il existe deux façons de contribuer à Flight :

1. Vous pouvez contribuer au framework central en visitant le [dépôt principal](https://github.com/flightphp/core).
1. Vous pouvez contribuer à la documentation. Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous remarquez une erreur ou si vous souhaitez améliorer quelque chose, n'hésitez pas à le corriger et à soumettre une demande de tirage ! Nous essayons de rester à jour, mais les mises à jour et les traductions de langue sont les bienvenues.

# Exigences

Flight nécessite PHP 7.4 ou supérieur.

**Remarque :** PHP 7.4 est pris en charge car au moment de la rédaction (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de problèmes pour ces utilisateurs. Le framework prend également en charge PHP >8.

# Licence

Flight est publié sous la licence [MIT](https://github.com/flightphp/core/blob/master/LICENSE).