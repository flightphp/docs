# Pourquoi un Framework?

Certains programmeurs sont farouchement opposés à l'utilisation de frameworks. Ils soutiennent que les frameworks sont gonflés, lents et difficiles à apprendre. 
Ils disent que les frameworks sont inutiles et que vous pouvez écrire un meilleur code sans eux. 
Il y a certainement quelques points valides à faire valoir sur les inconvénients de l'utilisation des frameworks. Cependant, il y a aussi de nombreux avantages à utiliser des frameworks. 

## Raisons d'utiliser un Framework

Voici quelques raisons pour lesquelles vous pourriez envisager d'utiliser un framework:

- **Développement Rapide**: Les frameworks offrent beaucoup de fonctionnalités prêtes à l'emploi. Cela signifie que vous pouvez développer des applications web plus rapidement. Vous n'avez pas à écrire autant de code car le framework fournit une grande partie des fonctionnalités dont vous avez besoin.
- ** Cohérence**: Les frameworks offrent une manière cohérente de faire les choses. Cela facilite votre compréhension du fonctionnement du code et facilite la compréhension de votre code par d'autres développeurs. Si vous le faites script par script, vous pourriez perdre de la cohérence entre les scripts, surtout si vous travaillez avec une équipe de développeurs.
- **Sécurité**: Les frameworks proposent des fonctionnalités de sécurité qui aident à protéger vos applications web contre les menaces courantes en matière de sécurité. Cela signifie que vous n'avez pas à vous soucier autant de la sécurité car le framework s'occupe en grande partie de cela pour vous.
- **Communauté**: Les frameworks ont de grandes communautés de développeurs qui contribuent au framework. Cela signifie que vous pouvez obtenir de l'aide d'autres développeurs lorsque vous avez des questions ou des problèmes. Cela signifie également qu'il y a beaucoup de ressources disponibles pour vous aider à apprendre comment utiliser le framework.
- **Meilleures Pratiques**: Les frameworks sont construits en utilisant les meilleures pratiques. Cela signifie que vous pouvez apprendre du framework et utiliser les mêmes meilleures pratiques dans votre propre code. Cela peut vous aider à devenir un meilleur programmeur. Parfois, vous ne savez pas ce que vous ne savez pas et cela peut vous jouer des tours à la fin.
- **Extensibilité**: Les frameworks sont conçus pour être étendus. Cela signifie que vous pouvez ajouter votre propre fonctionnalité au framework. Cela vous permet de construire des applications web adaptées à vos besoins spécifiques.

Flight est un micro-framework. Cela signifie qu'il est petit et léger. Il ne fournit pas autant de fonctionnalités que des frameworks plus grands comme Laravel ou Symfony. 
Cependant, il fournit une grande partie des fonctionnalités dont vous avez besoin pour construire des applications web. Il est également facile à apprendre et à utiliser. 
Cela en fait un bon choix pour construire rapidement et facilement des applications web. Si vous êtes novice en frameworks, Flight est un excellent framework pour débutants. 
Cela vous aidera à découvrir les avantages de l'utilisation des frameworks sans vous submerger avec trop de complexité. 
Après avoir acquis de l'expérience avec Flight, il sera plus facile de passer à des frameworks plus complexes comme Laravel ou Symfony, 
cependant Flight peut toujours vous aider à créer une application robuste et réussie.

## Qu'est-ce que le Routage?

Le routage est au cœur du framework Flight, mais qu'est-ce que c'est exactement? Le routage est le processus de prendre une URL et de la faire correspondre à une fonction spécifique dans votre code. 
C'est ainsi que vous pouvez faire en sorte que votre site Web fasse différentes choses en fonction de l'URL demandée. Par exemple, vous pouvez vouloir afficher le profil d'un utilisateur lorsqu'il 
visite `/utilisateur/1234`, mais affichez une liste de tous les utilisateurs lorsqu'ils visitent `/utilisateurs`. Tout cela se fait grâce au routage.

Cela pourrait fonctionner un peu comme ceci:

- Un utilisateur se rend sur votre navigateur et tape `http://exemple.com/utilisateur/1234`.
- Le serveur reçoit la demande, examine l'URL et la transmet à votre code d'application Flight.
- Disons que dans votre code Flight vous avez quelque chose comme `Flight::route('/utilisateur/@id', [ 'ControlleurUtilisateur', 'voirProfilUtilisateur' ]);`. Votre code d'application Flight examine l'URL et constate qu'elle correspond à une route que vous avez définie, puis exécute le code que vous avez défini pour cette route. 
- Le routeur de Flight exécutera ensuite et appellera la méthode `voirProfilUtilisateur($id)` dans la classe `ControlleurUtilisateur`, en passant le `1234` en tant qu'argument `$id` dans la méthode.
- Le code dans votre méthode `voirProfilUtilisateur()` s'exécutera alors et fera ce que vous lui avez dit de faire. Vous pourriez finir par afficher un peu de HTML pour la page de profil de l'utilisateur, ou s'il s'agit d'une API RESTful, vous pourriez afficher une réponse JSON avec les informations de l'utilisateur.
- Flight emballe tout cela joliment, génère les en-têtes de réponse et les envoie de retour au navigateur de l'utilisateur.
- L'utilisateur est comblé de joie et se fait un câlin chaleureux!

### Et Pourquoi est-ce Important?

Avoir un routeur centralisé approprié peut réellement rendre votre vie beaucoup plus facile! Il peut être difficile de le voir au début. Voici quelques raisons:

- **Routage Centralisé**: Vous pouvez regrouper tous vos itinéraires en un seul endroit. Cela facilite la visualisation de tous les itinéraires que vous avez et de ce qu'ils font. Cela facilite également leur modification si nécessaire.
- **Paramètres de Route**: Vous pouvez utiliser des paramètres de route pour transmettre des données à vos méthodes de route. C'est un excellent moyen de garder votre code propre et organisé.
- **Groupes de Routes**: Vous pouvez regrouper des itinéraires ensemble. C'est idéal pour maintenir votre code organisé et pour appliquer [middleware](middleware) à un groupe d'itinéraires.
- **Alias de Route**: Vous pouvez attribuer un alias à un itinéraire, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple). Ex : au lieu de coder en dur `/utilisateur/1234` dans votre code, vous pourriez plutôt référencer l'alias `vue_utilisateur` et transmettre l'`id` en tant que paramètre. C'est merveilleux au cas où vous décideriez de le changer en `/admin/utilisateur/1234` plus tard. Vous n'aurez pas à changer toutes vos URLs codées en dur, juste l'URL attachée à la route.
- **Middlewares de Route**: Vous pouvez ajouter des middlewares à vos itinéraires. Les middlewares sont incroyablement efficaces pour ajouter des comportements spécifiques à votre application, comme l'authentification qu'un certain utilisateur peut accéder à un itinéraire ou à un groupe d'itinéraires.

Je suis sûr que vous êtes familier avec la manière scriptée de créer un site web. Vous pourriez avoir un fichier appelé `index.php` qui comporte une multitude de déclarations `if` pour vérifier l'URL et puis exécuter une fonction spécifique en fonction de l'URL. C'est une forme de routage, mais ce n'est pas très organisé et cela peut rapidement devenir incontrôlable. Le système de routage de Flight est une méthode beaucoup plus organisée et puissante pour gérer le routage.

Comme ceci ?

```php

// /utilisateur/voir_profil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	voirProfilUtilisateur($id);
}

// /utilisateur/editer_profil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editerProfilUtilisateur($id);
}

// etc...
```

Ou comme cela ?

```php

// index.php
Flight::route('/utilisateur/@id', [ 'ControlleurUtilisateur', 'voirProfilUtilisateur' ]);
Flight::route('/utilisateur/@id/editer', [ 'ControlleurUtilisateur', 'editerProfilUtilisateur' ]);

// Peut-être dans votre app/controllers/ControlleurUtilisateur.php
class ControlleurUtilisateur {
	public function voirProfilUtilisateur($id) {
		// faire quelque chose
	}

	public function editerProfilUtilisateur($id) {
		// faire quelque chose
	}
}
```

Espérons que vous commencez à voir les avantages d'utiliser un système de routage centralisé. C'est beaucoup plus facile à gérer et à comprendre à long terme!

## Demandes et Réponses

Flight fournit un moyen simple et facile de gérer les demandes et les réponses. C'est le cœur de ce qu'un framework web fait. Il prend une demande 
de navigateur d'utilisateur, la traite, puis envoie une réponse. C'est ainsi que vous pouvez construire des applications web qui font des choses comme afficher un profil d'utilisateur, permettre à un utilisateur de se connecter, ou permettre à un utilisateur de poster un nouveau billet de blog.

### Demandes

Une demande est ce que le navigateur de l'utilisateur envoie à votre serveur lorsqu'ils visitent votre site. Cette demande contient des informations sur ce que 
l'utilisateur veut faire. Par exemple, elle peut contenir des informations sur l'URL que l'utilisateur souhaite visiter, les données que l'utilisateur souhaite envoyer à votre 
serveur, ou le type de données que l'utilisateur souhaite recevoir de votre serveur. Il est important de savoir qu'une demande est en lecture seule. Vous ne pouvez pas 
changer la demande, mais vous pouvez la lire.

Flight fournit un moyen simple d'accéder aux informations sur la demande. Vous pouvez accéder aux informations sur la demande en utilisant la méthode `Flight::request()` 
Cette méthode renvoie un objet `Request` qui contient des informations sur la demande. Vous pouvez utiliser cet objet pour accéder aux informations sur la demande, 
telles que l'URL, la méthode, ou les données que l'utilisateur a envoyées à votre serveur.

### Réponses

Une réponse est ce que votre serveur envoie à nouveau au navigateur de l'utilisateur lorsqu'ils visitent votre site. Cette réponse contient des informations sur ce 
que votre serveur veut faire. Par exemple, elle peut contenir des informations sur le type de données que votre serveur souhaite envoyer à l'utilisateur, le type de données 
que votre serveur souhaite recevoir de l'utilisateur, ou le type de données que votre serveur souhaite stocker sur l'ordinateur de l'utilisateur.

Flight fournit un moyen simple d'envoyer une réponse au navigateur de l'utilisateur. Vous pouvez envoyer une réponse en utilisant la méthode `Flight::response()` 
Cette méthode prend un objet `Response` en argument et envoie la réponse au navigateur de l'utilisateur. Vous pouvez utiliser cet objet pour envoyer une réponse au 
navigateur de l'utilisateur, telle que HTML, JSON ou un fichier. Flight vous aide à générer automatiquement certaines parties de la réponse pour faciliter les choses, 
mais finalement vous avez le contrôle sur ce que vous renvoyez à l'utilisateur.