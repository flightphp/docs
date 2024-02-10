# Pourquoi un Framework ?

Certains programmeurs sont catégoriquement opposés à l'utilisation de frameworks. Ils soutiennent que les frameworks sont gonflés, lents et difficiles à apprendre. Ils disent que les frameworks sont inutiles et que vous pouvez écrire un meilleur code sans eux. Il y a certainement des points valides à faire concernant les inconvénients de l'utilisation des frameworks. Cependant, il y a aussi de nombreux avantages à utiliser des frameworks.

## Raisons d'utiliser un Framework

Voici quelques raisons pour lesquelles vous pourriez envisager d'utiliser un framework :

- **Développement Rapide** : Les frameworks fournissent beaucoup de fonctionnalités prêtes à l'emploi. Cela signifie que vous pouvez construire des applications web plus rapidement. Vous n'avez pas besoin d'écrire autant de code car le framework fournit une grande partie des fonctionnalités dont vous avez besoin.
- **Consistance** : Les frameworks offrent une manière cohérente de faire les choses. Cela facilite votre compréhension du fonctionnement du code et facilite la compréhension de votre code par d'autres développeurs. Si vous avez un script par script, vous pourriez perdre la cohérence entre les scripts, surtout si vous travaillez avec une équipe de développeurs.
- **Sécurité** : Les frameworks fournissent des fonctionnalités de sécurité qui aident à protéger vos applications web contre les menaces de sécurité courantes. Cela signifie que vous n'avez pas à vous soucier autant de la sécurité car le framework s'occupe en grande partie de cela pour vous.
- **Communauté** : Les frameworks ont de grandes communautés de développeurs qui contribuent au framework. Cela signifie que vous pouvez obtenir de l'aide d'autres développeurs lorsque vous avez des questions ou des problèmes. Cela signifie également qu'il y a beaucoup de ressources disponibles pour vous aider à apprendre comment utiliser le framework.
- **Meilleures Pratiques** : Les frameworks sont construits en utilisant les meilleures pratiques. Cela signifie que vous pouvez apprendre du framework et utiliser les mêmes meilleures pratiques dans votre propre code. Cela peut vous aider à devenir un meilleur programmeur. Parfois, vous ne savez pas ce que vous ne savez pas et cela peut vous jouer des tours à la fin.
- **Extensibilité** : Les frameworks sont conçus pour être étendus. Cela signifie que vous pouvez ajouter votre propre fonctionnalité au framework. Cela vous permet de construire des applications web adaptées à vos besoins spécifiques.

Flight est un micro-framework. Cela signifie qu'il est petit et léger. Il ne fournit pas autant de fonctionnalités que des frameworks plus importants comme Laravel ou Symfony. Cependant, il fournit beaucoup des fonctionnalités dont vous avez besoin pour construire des applications web. Il est également facile à apprendre et à utiliser. Cela en fait un bon choix pour construire des applications web rapidement et facilement. Si vous êtes nouveau aux frameworks, Flight est un excellent framework pour débutants pour commencer. Cela vous aidera à découvrir les avantages de l'utilisation des frameworks sans vous submerger de trop de complexité. Après avoir acquis de l'expérience avec Flight, il sera plus facile de passer à des frameworks plus complexes comme Laravel ou Symfony, cependant, Flight peut toujours vous permettre de créer une application robuste et réussie.

## Qu'est-ce que le Routage ?

Le routage est au cœur du framework Flight, mais qu'est-ce que c'est exactement ? Le routage est le processus de prendre une URL et de la faire correspondre à une fonction spécifique dans votre code. C'est ainsi que vous pouvez faire en sorte que votre site web fasse des choses différentes en fonction de l'URL demandée. Par exemple, vous pouvez vouloir afficher le profil d'un utilisateur lorsqu'il visite `/utilisateur/1234`, mais afficher une liste de tous les utilisateurs lorsqu'ils visitent `/utilisateurs`. Tout cela se fait via le routage.

Ça pourrait fonctionner quelque chose comme ça :

- Un utilisateur se rend sur votre navigateur et tape `http://exemple.com/utilisateur/1234`.
- Le serveur reçoit la requête, examine l'URL et la transmet à votre code d'application Flight.
- Supposons que dans votre code Flight vous avez quelque chose comme `Flight::route('/utilisateur/@id', ['UserController', 'voirProfilUtilisateur']);`. Votre code d'application Flight regarde l'URL et voit qu'elle correspond à une route que vous avez définie, puis exécute le code que vous avez défini pour cette route.
- Le routeur Flight exécutera alors et appellera la méthode `voirProfilUtilisateur($id)` dans la classe `UserController`, en passant le `1234` en tant qu'argument `$id` dans la méthode.
- Le code dans votre méthode `voirProfilUtilisateur()` s'exécutera alors et fera ce que vous lui avez dit de faire. Vous pourriez finir par afficher du HTML pour la page de profil de l'utilisateur, ou s'il s'agit d'une API RESTful, vous pourriez renvoyer une réponse JSON avec les informations de l'utilisateur.
- Flight emballe tout cela joliment, génère les en-têtes de réponse et les renvoie au navigateur de l'utilisateur.
- L'utilisateur est rempli de joie et se donne une accolade chaleureuse !

### Et pourquoi est-ce important ?

Avoir un routeur centralisé adéquat peut réellement rendre votre vie beaucoup plus facile ! Cela pourrait être difficile à voir au début. Voici quelques raisons pour lesquelles :

- **Routage Centralisé** : Vous pouvez garder toutes vos routes au même endroit. Cela facilite la visualisation des routes que vous avez et de ce qu'elles font. Cela rend également plus facile de les modifier si nécessaire.
- **Paramètres de Route** : Vous pouvez utiliser des paramètres de route pour transmettre des données à vos méthodes de route. C'est un excellent moyen de garder votre code propre et organisé.
- **Groupes de Routes** : Vous pouvez regrouper des routes ensemble. C'est idéal pour garder votre code organisé et pour appliquer [middleware](middleware) à un groupe de routes.
- **Alias de Route** : Vous pouvez assigner un alias à une route, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple). Par exemple : au lieu de coder en dur `/utilisateur/1234`, vous pourriez plutôt faire référence à l'alias `vue_utilisateur` et transmettre l'`id` en tant que paramètre. Cela simplifie les choses au cas où vous décideriez de le changer en `/admin/utilisateur/1234` plus tard. Vous n'aurez pas à changer toutes vos URL codées en dur, juste l'URL attachée à la route.
- **Middleware de Route** : Vous pouvez ajouter des middleware à vos routes. Les middleware sont incroyablement puissants pour ajouter des comportements spécifiques à votre application comme l'authentification qu'un certain utilisateur peut accéder à une route ou à un groupe de routes.

Je suis sûr que vous êtes familier avec la manière script par script de créer un site web. Vous pourriez avoir un fichier appelé `index.php` qui contient un tas de déclarations `if` pour vérifier l'URL, puis exécuter une fonction spécifique en fonction de l'URL. C'est une forme de routage, mais ce n'est pas très organisé et ça peut rapidement devenir incontrôlable. Le système de routage de Flight est une manière beaucoup plus organisée et puissante de gérer le routage.

Ceci ?

```php

// /utilisateur/vue_profil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	voirProfilUtilisateur($id);
}

// /utilisateur/editer_profil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	éditerProfilUtilisateur($id);
}

// etc...
```

Ou ça ?

```php

// index.php
Flight::route('/utilisateur/@id', ['UserController', 'voirProfilUtilisateur']);
Flight::route('/utilisateur/@id/editer', ['UserController', 'éditerProfilUtilisateur']);

// Peut-être dans votre app/controllers/UserController.php
class UserController {
	public function voirProfilUtilisateur($id) {
		// faire quelque chose
	}

	public function éditerProfilUtilisateur($id) {
		// faire quelque chose
	}
}
```

Espérons que vous pouvez commencer à voir les avantages d'utiliser un système de routage centralisé. C'est beaucoup plus facile à gérer et à comprendre à long terme !

## Requêtes et Réponses

Flight fournit un moyen simple et facile de gérer les requêtes et réponses. C'est le cœur de ce qu'un framework web fait. Il prend une requête du navigateur d'un utilisateur, la traite, puis renvoie une réponse. C'est ainsi que vous pouvez construire des applications web qui font des choses comme afficher le profil d'un utilisateur, permettre à un utilisateur de se connecter, ou permettre à un utilisateur de publier un nouvel article de blog.

### Requêtes

Une requête est ce que le navigateur d'un utilisateur envoie à votre serveur lorsqu'il visite votre site web. Cette requête contient des informations sur ce que l'utilisateur veut faire. Par exemple, elle pourrait contenir des informations sur quelle URL l'utilisateur veut visiter, quelles données l'utilisateur veut envoyer à votre serveur, ou quel type de données l'utilisateur veut recevoir de votre serveur. Il est important de savoir qu'une requête est en lecture seule. Vous ne pouvez pas modifier la requête, mais vous pouvez la lire.

Flight fournit un moyen simple d'accéder aux informations sur la requête. Vous pouvez accéder aux informations sur la requête en utilisant la méthode `Flight::request()`. Cette méthode renvoie un objet `Request` qui contient des informations sur la requête. Vous pouvez utiliser cet objet pour accéder aux informations sur la requête, telles que l'URL, la méthode, ou les données que l'utilisateur a envoyées à votre serveur.

### Réponses

Une réponse est ce que votre serveur renvoie au navigateur d'un utilisateur lorsqu'il visite votre site web. Cette réponse contient des informations sur ce que votre serveur veut faire. Par exemple, elle pourrait contenir des informations sur quel type de données votre serveur veut envoyer à l'utilisateur, quel type de données votre serveur veut recevoir de l'utilisateur, ou quel type de données votre serveur veut Stocker sur l'ordinateur de l'utilisateur.

Flight fournit un moyen simple d'envoyer une réponse au navigateur d'un utilisateur. Vous pouvez envoyer une réponse en utilisant la méthode `Flight::response()`. Cette méthode prend un objet `Response` en tant qu'argument et envoie la réponse au navigateur de l'utilisateur. Vous pouvez utiliser cet objet pour envoyer une réponse au navigateur de l'utilisateur, comme du HTML, du JSON, ou un fichier. Flight vous aide à générer automatiquement certaines parties de la réponse pour faciliter les choses, mais vous avez finalement le contrôle sur ce que vous renvoyez à l'utilisateur.

