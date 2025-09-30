# Flight vs Slim

## Qu'est-ce que Slim ?
[Slim](https://slimframework.com) est un micro-framework PHP qui vous aide à écrire rapidement des applications web simples mais puissantes et des API.

Beaucoup d'inspirations pour certaines fonctionnalités de la version 3 de Flight proviennent en fait de Slim. La regroupement des routes et l'exécution du middleware dans un ordre spécifique sont deux fonctionnalités inspirées de Slim. Slim v3 est sorti avec un accent sur la simplicité, mais il y a eu des [avis mitigés](https://github.com/slimphp/Slim/issues/2770) concernant la v4.

## Avantages par rapport à Flight

- Slim dispose d'une communauté plus large de développeurs, qui à leur tour créent des modules pratiques pour vous aider à ne pas réinventer la roue.
- Slim suit de nombreuses interfaces et normes courantes dans la communauté PHP, ce qui augmente l'interopérabilité.
- Slim a une documentation décente et des tutoriels qui peuvent être utilisés pour apprendre le framework (rien de comparable à Laravel ou Symfony cependant).
- Slim offre divers ressources comme des tutoriels YouTube et des articles en ligne qui peuvent être utilisés pour apprendre le framework.
- Slim vous permet d'utiliser les composants que vous voulez pour gérer les fonctionnalités de routage de base, car il est conforme à PSR-7.

## Inconvénients par rapport à Flight

- Étonnamment, Slim n'est pas aussi rapide que vous pourriez le penser pour un micro-framework. Consultez les 
  [benchmarks TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  pour plus d'informations.
- Flight est conçu pour un développeur qui cherche à construire une application web légère, rapide et facile à utiliser.
- Flight n'a aucune dépendance, alors que [Slim a quelques dépendances](https://github.com/slimphp/Slim/blob/4.x/composer.json) que vous devez installer.
- Flight est conçu pour la simplicité et la facilité d'utilisation.
- L'une des fonctionnalités principales de Flight est qu'il fait de son mieux pour maintenir la compatibilité arrière. Le passage de Slim v3 à v4 a été un changement cassant.
- Flight est destiné aux développeurs qui s'aventurent pour la première fois dans le monde des frameworks.
- Flight peut également gérer des applications de niveau entreprise, mais il n'a pas autant d'exemples et de tutoriels que Slim.
  Cela nécessitera également plus de discipline de la part du développeur pour garder les choses organisées et bien structurées.
- Flight donne au développeur plus de contrôle sur l'application, alors que Slim peut introduire de la magie en coulisses.
- Flight dispose d'un simple [PdoWrapper](/learn/pdo-wrapper) qui peut être utilisé pour interagir avec votre base de données. Slim nécessite l'utilisation d'une bibliothèque tierce.
- Flight a un plugin [permissions](/awesome-plugins/permissions) qui peut être utilisé pour sécuriser votre application. Slim nécessite l'utilisation d'une bibliothèque tierce.
- Flight a un ORM appelé [active-record](/awesome-plugins/active-record) qui peut être utilisé pour interagir avec votre base de données. Slim nécessite l'utilisation d'une bibliothèque tierce.
- Flight a une application CLI appelée [runway](/awesome-plugins/runway) qui peut être utilisée pour exécuter votre application depuis la ligne de commande. Slim n'en a pas.