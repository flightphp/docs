# Cadre PHP Flight

Flight est un framework rapide, simple et extensible pour PHP, conçu pour les développeurs qui veulent accomplir les choses rapidement, sans complications. Que vous construisiez une application web classique, une API ultra-rapide, ou que vous expérimentiez avec les derniers outils alimentés par l'IA, la petite empreinte et la conception directe de Flight en font un choix parfait. Flight est destiné à être léger, mais il peut aussi gérer les exigences d'une architecture d'entreprise.

## Pourquoi choisir Flight ?

- **Accessible aux débutants :** Flight est un excellent point de départ pour les nouveaux développeurs PHP. Sa structure claire et sa syntaxe simple vous aident à apprendre le développement web sans vous perdre dans du code boilerplate.
- **Aimé par les professionnels :** Les développeurs expérimentés adorent Flight pour sa flexibilité et son contrôle. Vous pouvez passer d'un prototype minuscule à une application complète sans changer de framework.
- **Compatible avec l'IA :** La surcharge minimale et l'architecture propre de Flight en font un outil idéal pour intégrer des outils et des API d'IA. Que vous construisiez des chatbots intelligents, des tableaux de bord pilotés par l'IA, ou que vous souhaitiez simplement expérimenter, Flight s'efface pour que vous puissiez vous concentrer sur l'essentiel. L'[application squelette](https://github.com/flightphp/skeleton) est fournie avec des fichiers d'instructions pré-construits pour les principaux assistants de codage d'IA dès la sortie de la boîte ! [En savoir plus sur l'utilisation de l'IA avec Flight](/learn/ai)

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

Pour une installation rapide et basique, installez-le avec Composer :

```bash
composer require flightphp/core
```

Ou vous pouvez télécharger un zip du dépôt [ici](https://github.com/flightphp/core). Ensuite, vous aurez un fichier `index.php` de base comme suit :

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

C'est tout ! Vous avez une application Flight de base. Vous pouvez maintenant exécuter ce fichier avec `php -S localhost:8000` et visiter `http://localhost:8000` dans votre navigateur pour voir le résultat.

## Application squelette/modèle

Il y a un exemple d'application pour vous aider à démarrer votre projet avec Flight. Elle dispose d'une mise en page structurée, de configurations de base toutes prêtes et gère les scripts Composer dès le départ ! Jetez un œil à [flightphp/skeleton](https://github.com/flightphp/skeleton) pour un projet prêt à l'emploi, ou visitez la page [exemples](examples) pour trouver de l'inspiration. Vous voulez voir comment l'IA s'intègre ? [Explorez des exemples alimentés par l'IA](/learn/ai).

## Installation de l'application squelette

C'est facile !

```bash
# Créez le nouveau projet
composer create-project flightphp/skeleton my-project/
# Entrez dans le répertoire de votre nouveau projet
cd my-project/
# Lancez le serveur de développement local pour commencer immédiatement !
composer start
```

Cela créera la structure du projet, configurera les fichiers dont vous avez besoin, et vous serez prêt à partir !

## Performances élevées

Flight est l'un des frameworks PHP les plus rapides du marché. Son cœur léger signifie moins de surcharge et plus de vitesse, parfait pour les applications traditionnelles et les projets modernes alimentés par l'IA. Vous pouvez voir tous les benchmarks sur [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks).

Voici le benchmark ci-dessous avec d'autres frameworks PHP populaires.

| Framework | Reqs/sec en texte brut | Reqs/sec en JSON |
| --------- | ------------ | ------------ |
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

# Communauté

Nous sommes sur Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Et Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Contribution

Il y a deux façons de contribuer à Flight :

1. Contribuez au framework principal en visitant le [dépôt principal](https://github.com/flightphp/core).
2. Aidez à améliorer les docs ! Ce site de documentation est hébergé sur [Github](https://github.com/flightphp/docs). Si vous repérez une erreur ou souhaitez améliorer quelque chose, n'hésitez pas à soumettre une pull request. Nous adorons les mises à jour et les nouvelles idées, surtout autour de l'IA et des nouvelles technologies !

# Exigences

Flight nécessite PHP 7.4 ou une version supérieure.

**Note :** PHP 7.4 est pris en charge car, au moment de l'écriture (2024), PHP 7.4 est la version par défaut pour certaines distributions Linux LTS. Forcer un passage à PHP >8 causerait beaucoup de problèmes pour ces utilisateurs. Le framework prend aussi en charge PHP >8.

# Licence

Flight est publié sous la [licence MIT](https://github.com/flightphp/core/blob/master/LICENSE).