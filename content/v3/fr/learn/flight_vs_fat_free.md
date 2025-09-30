# Flight vs Fat-Free

## Qu'est-ce que Fat-Free ?
[Fat-Free](https://fatfreeframework.com) (affectueusement connu sous le nom de **F3**) est un micro-framework PHP puissant mais facile à utiliser, conçu pour vous aider à créer des applications web dynamiques et robustes - rapidement !

Flight se compare à Fat-Free de nombreuses manières et est probablement le cousin le plus proche en termes de fonctionnalités et de simplicité. Fat-Free possède
beaucoup de fonctionnalités que Flight n'a pas, mais il a aussi beaucoup de fonctionnalités que Flight possède. Fat-Free commence à montrer son âge
et n'est plus aussi populaire qu'avant.

Les mises à jour deviennent moins fréquentes et la communauté n'est plus aussi active qu'autrefois. Le code est suffisamment simple, mais parfois le manque de
discipline syntaxique peut le rendre difficile à lire et à comprendre. Il fonctionne pour PHP 8.3, mais le code lui-même ressemble encore à celui de
PHP 5.3.

## Avantages par rapport à Flight

- Fat-Free a quelques étoiles de plus sur GitHub que Flight.
- Fat-Free dispose d'une documentation décente, mais elle manque de clarté dans certains domaines.
- Fat-Free propose quelques ressources éparses comme des tutoriels YouTube et des articles en ligne qui peuvent être utilisés pour apprendre le framework.
- Fat-Free intègre [quelques plugins utiles](https://fatfreeframework.com/3.8/api-reference) qui sont parfois pratiques.
- Fat-Free dispose d'un ORM intégré appelé Mapper qui peut être utilisé pour interagir avec votre base de données. Flight propose [active-record](/awesome-plugins/active-record).
- Fat-Free intègre des Sessions, du Cache et de la localisation. Flight nécessite l'utilisation de bibliothèques tierces, mais cela est couvert dans la [documentation](/awesome-plugins).
- Fat-Free dispose d'un petit groupe de [plugins créés par la communauté](https://fatfreeframework.com/3.8/development#Community) qui peuvent être utilisés pour étendre le framework. Flight en couvre certains dans les pages [documentation](/awesome-plugins) et [exemples](/examples).
- Fat-Free, comme Flight, n'a aucune dépendance.
- Fat-Free, comme Flight, vise à donner au développeur le contrôle sur son application et une expérience de développement simple.
- Fat-Free maintient la compatibilité arrière comme Flight (en partie parce que les mises à jour deviennent [moins fréquentes](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, comme Flight, est destiné aux développeurs qui s'aventurent pour la première fois dans le monde des frameworks.
- Fat-Free dispose d'un moteur de templates intégré plus robuste que celui de Flight. Flight recommande [Latte](/awesome-plugins/latte) pour cela.
- Fat-Free propose une commande CLI unique de type "route" qui permet de créer des applications CLI au sein de Fat-Free lui-même et de la traiter comme une requête `GET`. Flight y parvient avec [runway](/awesome-plugins/runway).

## Inconvénients par rapport à Flight

- Fat-Free dispose de quelques tests d'implémentation et même de sa propre [classe de test](https://fatfreeframework.com/3.8/test) qui est très basique. Cependant,
  elle n'est pas testée à 100 % par unités comme Flight.
- Vous devez utiliser un moteur de recherche comme Google pour rechercher réellement sur le site de documentation.
- Flight propose un mode sombre sur son site de documentation. (mic drop)
- Fat-Free a certains modules qui sont lamentablement non maintenus.
- Flight dispose d'un [PdoWrapper](/learn/pdo-wrapper) simple qui est un peu plus simple que la classe `DB\SQL` intégrée de Fat-Free.
- Flight propose un [plugin de permissions](/awesome-plugins/permissions) qui peut être utilisé pour sécuriser votre application. Fat-Free nécessite l'utilisation d'une
  bibliothèque tierce.
- Flight dispose d'un ORM appelé [active-record](/awesome-plugins/active-record) qui ressemble plus à un ORM que le Mapper de Fat-Free.
  L'avantage supplémentaire de `active-record` est que vous pouvez définir des relations entre les enregistrements pour des jointures automatiques, tandis que le Mapper de Fat-Free
  nécessite de créer des [vues SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Étonnamment, Fat-Free n'a pas d'espace de noms racine. Flight est namespace tout au long pour éviter les collisions avec votre propre code.
  La classe `Cache` est la plus grande offenseuse ici.
- Fat-Free n'a pas de middleware. À la place, il y a des hooks `beforeroute` et `afterroute` qui peuvent être utilisés pour filtrer les requêtes et réponses dans les contrôleurs.
- Fat-Free ne peut pas grouper les routes.
- Fat-Free dispose d'un gestionnaire de conteneur d'injection de dépendances, mais la documentation est incroyablement sparse sur la façon de l'utiliser.
- Le débogage peut devenir un peu délicat car tout est essentiellement stocké dans ce qu'on appelle le [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)