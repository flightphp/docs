# Flight PHP Framework

Flight est un framework rapide, simple et extensible pour PHP — conçu pour les développeurs qui veulent accomplir les choses rapidement, sans tracas. Que vous construisiez une application web classique, une API ultra-rapide, ou que vous expérimentiez avec les derniers outils alimentés par l'IA, la faible empreinte et la conception directe de Flight en font un choix parfait. Flight est conçu pour être léger, mais il peut aussi gérer les exigences d'architecture d'entreprise.

## Pourquoi choisir Flight ?

- **Convivial pour les débutants :** Flight est un excellent point de départ pour les nouveaux développeurs PHP. Sa structure claire et sa syntaxe simple vous aident à apprendre le développement web sans vous perdre dans le code boilerplate.
- **Apprécié des pros :** Les développeurs expérimentés adorent Flight pour sa flexibilité et son contrôle. Vous pouvez passer d'un prototype minuscule à une application complète sans changer de framework.
- **Compatible avec les versions antérieures :** Nous valorisons votre temps. Flight v3 est une augmentation de v2, en conservant presque toute la même API. Nous croyons en l'évolution, pas en la révolution — plus de « casser le monde » à chaque sortie d'une version majeure.
- **Zéro dépendances :** Le cœur de Flight est complètement sans dépendances — pas de polyfills, pas de paquets externes, pas même d'interfaces PSR. Cela signifie moins de vecteurs d'attaque, une empreinte plus petite, et pas de changements cassants surprises provenant des dépendances en amont. Les plugins optionnels peuvent inclure des dépendances, mais le cœur restera toujours léger et sécurisé.
- **Orienté IA :** La surcharge minimale et l'architecture propre de Flight le rendent idéal pour intégrer des outils et des API IA. Que vous construisiez des chatbots intelligents, des tableaux de bord pilotés par l'IA, ou que vous vouliez simplement expérimenter, Flight se met de côté pour que vous puissiez vous concentrer sur l'essentiel. L'[application squelette](https://github.com/flightphp/skeleton) est livrée avec des fichiers d'instructions pré-construits pour les principaux assistants de codage IA dès la sortie de la boîte ! [En savoir plus sur l'utilisation de l'IA avec Flight](/learn/ai)

## Aperçu vidéo

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Assez simple, n'est-ce pas ?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">En savoir plus</a> sur Flight dans la documentation !
    </div>
  </div>
</div>

## Démarrage rapide

Pour une installation rapide et minimaliste, installez-le avec Composer :

```bash
composer require flightphp/core
```

Ou vous pouvez télécharger un zip du dépôt [ici](https://github.com/flightphp/core). Ensuite, vous auriez un fichier `index.php` de base comme suit :

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

C'est tout ! Vous avez une application Flight de base. Vous pouvez maintenant exécuter ce fichier avec `php -S localhost:8000` et visiter `http://localhost:8000` dans votre navigateur pour voir la sortie.

## Application squelette/Boilerplate

Il y a une application exemple pour vous aider à démarrer votre projet avec Flight. Elle a une mise en page structurée, des configurations de base toutes prêtes et gère les scripts composer dès le départ ! Consultez [flightphp/skeleton](https://github.com/flightphp/skeleton) pour un projet prêt à l'emploi, ou visitez la page [exemples](examples) pour de l'inspiration. Vous voulez voir comment l'IA s'intègre ? [Explorez des exemples alimentés par l'IA](/learn/ai).

## Installation de l'application squelette

Assez simple !

```bash
# Créer le nouveau projet
composer create-project flightphp/skeleton my-project/
# Entrer dans le répertoire de votre nouveau projet
cd my-project/
# Lancer le serveur de développement local pour démarrer immédiatement !
composer start
```

Cela créera la structure du projet, configurera les fichiers dont vous avez besoin, et vous serez prêt à partir !

## Haute performance

Flight est l'un des frameworks PHP les plus rapides disponibles. Son cœur léger signifie moins de surcharge et plus de vitesse — parfait pour les applications traditionnelles et les projets modernes alimentés par l'IA. Vous pouvez voir tous les benchmarks sur [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)

Voici le benchmark ci-dessous avec quelques autres frameworks PHP populaires.

| Framework   | Reqs/sec Plaintext | Reqs/sec JSON |
| ----------- | ------------------ | ------------- |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |


## Flight et l'IA

Curieux de savoir comment il gère l'IA ? [Découvrez](/learn/ai) comment Flight facilite le travail avec votre LLM de codage préféré !

## Stabilité et compatibilité avec les versions antérieures

Nous valorisons votre temps. Nous avons tous vu des frameworks qui se réinventent complètement tous les deux ans, laissant les développeurs avec du code cassé et des migrations coûteuses. Flight est différent. Flight v3 a été conçu comme une augmentation de v2, ce qui signifie que l'API que vous connaissez et aimez n'a pas été supprimée. En fait, la plupart des projets v2 fonctionneront sans aucun changement en v3. 

Nous nous engageons à garder Flight stable pour que vous puissiez vous concentrer sur la construction de votre application, pas sur la réparation de votre framework.

# Communauté

Nous sommes sur Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Et Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribution

Il y a deux façons de contribuer à Flight :

1. Contribuer au framework principal en visitant le [dépôt principal](https://github.com/flightphp/core).
2. Aider à améliorer la documentation ! Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous repérez une erreur ou voulez améliorer quelque chose, n'hésitez pas à soumettre une pull request. Nous adorons les mises à jour et les nouvelles idées — surtout autour de l'IA et des nouvelles technologies !

# Exigences

Flight nécessite PHP 7.4 ou supérieur.

**Note :** PHP 7.4 est supporté car, au moment de la rédaction (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de problèmes pour ces utilisateurs. Le framework supporte aussi PHP >8.

# Licence

Flight est publié sous la [licence MIT](https://github.com/flightphp/core/blob/master/LICENSE).