# Vol / FF (F3)

## Qu'est-ce que Fat-Free?
[Fat-Free](https://fatfreeframework.com) (affectueusement connu sous le nom de **F3**) est un micro-framework PHP puissant et facile à utiliser conçu pour vous aider à construire des applications web dynamiques et robustes - rapidement!

Vol se compare à Fat-Free de nombreuses manières et est probablement le cousin le plus proche en termes de fonctionnalités et de simplicité. Fat-Free a
beaucoup de fonctionnalités que Vol n'a pas, mais il a aussi beaucoup de fonctionnalités que Flight a. Fat-Free commence à montrer son âge
et n'est pas aussi populaire qu'auparavant.

Les mises à jour deviennent moins fréquentes et la communauté n'est plus aussi active qu'auparavant. Le code est assez simple, mais parfois le manque de
discipline syntaxique peut le rendre difficile à lire et à comprendre. Il fonctionne pour PHP 8.3, mais le code lui-même ressemble toujours à s'il vivait dans
PHP 5.3.

## Avantages par rapport à Vol

- Fat-Free a quelques étoiles de plus sur GitHub que Flight.
- Fat-Free a une documentation décente, mais elle manque de clarté dans certains domaines.
- Fat-Free dispose de ressources éparses telles que des tutoriels YouTube et des articles en ligne pouvant être utilisés pour apprendre le framework.
- Fat-Free a [quelques plugins utiles](https://fatfreeframework.com/3.8/api-reference) intégrés qui sont parfois utiles.
- Fat-Free a un ORM intégré appelé Mapper qui peut être utilisé pour interagir avec votre base de données. Flight a [active-record](/awesome-plugins/active-record).
- Fat-Free a des Sessions, Caching et la localisation intégrés. Flight nécessite l'utilisation de bibliothèques tierces, mais c'est couvert dans la [documentation](/awesome-plugins).
- Fat-Free a un petit groupe de [plugins créés par la communauté](https://fatfreeframework.com/3.8/development#Community) qui peuvent être utilisés pour étendre le framework. Flight a certains couverts dans les pages de [documentation](/awesome-plugins) et [exemples](/examples).
- Fat-Free, comme Flight, n'a pas de dépendances.
- Fat-Free, comme Flight, vise à donner aux développeurs le contrôle sur leur application et une expérience de développement simple.
- Fat-Free maintient la compatibilité ascendante comme Flight le fait (partiellement car les mises à jour deviennent [moins fréquentes](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, comme Flight, est destiné aux développeurs qui s'aventurent pour la première fois dans le monde des frameworks.
- Fat-Free a un moteur de template intégré qui est plus robuste que le moteur de template de Vol. Flight recommande [Latte](/awesome-plugins/latte) pour y parvenir.
- Fat-Free a une commande de type CLI "route" unique où vous pouvez construire des applications CLI à l'intérieur de Fat-Free lui-même et le traiter comme une requête `GET`. Flight réalise cela avec [runway](/awesome-plugins/runway).

## Inconvénients par rapport à Vol

- Fat-Free a quelques tests d'implémentation et a même sa propre classe de [test](https://fatfreeframework.com/3.8/test) qui est très basique. Cependant,
  ce n'est pas testé à 100% en unité comme Flight.
- Vous devez utiliser un moteur de recherche tel que Google pour effectivement rechercher le site de documentation.
- Flight a un mode sombre sur leur site de documentation. (laisser tomber le micro)
- Fat-Free a quelques modules qui sont lamentablement non maintenus.
- Flight a un simple [PdoWrapper](/awesome-plugins/pdo-wrapper) qui est un peu plus simple que la classe `DB\SQL` intégrée de Fat-Free.
- Flight a un plugin de [permissions](/awesome-plugins/permissions) qui peut être utilisé pour sécuriser votre application. Slim vous demande d'utiliser
  une bibliothèque tierce.
- Flight a un ORM appelé [active-record](/awesome-plugins/active-record) qui ressemble plus à un ORM que le Mapper de Fat-Free.
  Le bénéfice ajouté de `active-record` est que vous pouvez définir des relations entre les enregistrements pour des jointures automatiques là où le Mapper de Fat-Free
  vous demande de créer [des vues SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Étonnamment, Fat-Free n'a pas d'espace de noms racine. Flight est mis en espace de noms tout au long pour ne pas entrer en collision avec votre propre code.
  la classe `Cache` est le plus grand contrevenant ici.
- Fat-Free n'a pas de middleware. Au lieu de cela, il y a des crochets `beforeroute` et `afterroute` qui peuvent être utilisés pour filtrer les requêtes et les réponses dans les contrôleurs.
- Fat-Free ne peut pas regrouper les routes.
- Fat-Free a un gestionnaire de conteneur d'injection de dépendance, mais la documentation est incroyablement clairsemée sur la façon de l'utiliser.
- Le débogage peut devenir un peu délicat car pratiquement tout est stocké dans ce qu'on appelle le [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)