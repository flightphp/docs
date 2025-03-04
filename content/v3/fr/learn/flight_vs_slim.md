# Vol Flight vs Slim

## Qu'est-ce que Slim?
[Slim](https://slimframework.com) est un micro-framework PHP qui vous aide à écrire rapidement des applications web et des APIs simples mais puissantes.

Beaucoup des inspirations pour certaines fonctionnalités de la v3 de Vol sont en fait venues de Slim. Regrouper les routes et exécuter des middlewares dans un ordre spécifique sont deux fonctionnalités qui ont été inspirées par Slim. Slim v3 est sorti orienté vers la simplicité, mais il y a eu des [avis divergents](https://github.com/slimphp/Slim/issues/2770) concernant la v4.

## Avantages par rapport à Vol

- Slim a une communauté plus large de développeurs, qui à leur tour créent des modules pratiques pour vous aider à ne pas réinventer la roue.
- Slim suit de nombreuses interfaces et normes communes dans la communauté PHP, ce qui augmente l'interopérabilité.
- Slim a une documentation décente et des tutoriels qui peuvent être utilisés pour apprendre le framework (rien ne vaut Laravel ou Symfony cependant).
- Slim dispose de diverses ressources telles que des tutoriels YouTube et des articles en ligne qui peuvent être utilisés pour apprendre le framework.
- Slim vous permet d'utiliser les composants que vous souhaitez pour gérer les fonctionnalités de routage de base car il est conforme à PSR-7.

## Inconvénients par rapport à Vol

- Étonnamment, Slim n'est pas aussi rapide que vous pourriez le penser pour un micro-framework. Consultez les [benchmarks TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) pour plus d'informations.
- Vol est destiné à un développeur qui cherche à construire une application web légère, rapide et facile à utiliser.
- Vol n'a pas de dépendances, alors que [Slim a quelques dépendances](https://github.com/slimphp/Slim/blob/4.x/composer.json) que vous devez installer.
- Vol est orienté vers la simplicité et la facilité d'utilisation.
- Une des fonctionnalités principales de Vol est qu'il fait de son mieux pour maintenir la compatibilité ascendante. Le passage de Slim v3 à v4 était un changement majeur.
- Vol est destiné aux développeurs qui se lancent dans l'univers des frameworks pour la première fois.
- Vol peut également être utilisé pour des applications de niveau entreprise, mais il n'a pas autant d'exemples et de tutoriels que Slim. Cela nécessitera également plus de discipline de la part du développeur pour maintenir les choses organisées et bien structurées.
- Vol donne au développeur plus de contrôle sur l'application, tandis que Slim peut introduire de la magie en coulisses.
- Vol dispose d'un simple [PdoWrapper](/awesome-plugins/pdo-wrapper) qui peut être utilisé pour interagir avec votre base de données. Slim vous oblige à utiliser une bibliothèque tierce.
- Vol possède un plugin de [permissions](/awesome-plugins/permissions) qui peut être utilisé pour sécuriser votre application. Slim vous oblige à utiliser une bibliothèque tierce.
- Vol a un ORM appelé [active-record](/awesome-plugins/active-record) qui peut être utilisé pour interagir avec votre base de données. Slim vous oblige à utiliser une bibliothèque tierce.
- Vol a une application CLI appelée [runway](/awesome-plugins/runway) qui peut être utilisée pour exécuter votre application à partir de la ligne de commande. Slim ne le fait pas.