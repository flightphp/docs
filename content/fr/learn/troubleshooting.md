# Dépannage

Cette page vous aidera à résoudre les problèmes courants auxquels vous pourriez être confrontés lors de l'utilisation de Flight.

## Problèmes Courants

### Erreur 404 Non Trouvé ou Comportement de Route Inattendu

Si vous rencontrez une erreur 404 Non Trouvé (mais vous jurez sur votre vie qu'elle est vraiment là et que ce n'est pas une faute de frappe), cela pourrait en fait être un problème lié au fait que vous renvoyez une valeur dans le point de terminaison de votre route au lieu de simplement l'afficher. La raison pour cela est intentionnel mais pourrait surprendre certains développeurs.

```php

Flight::route('/bonjour', function(){
	// Cela pourrait causer une erreur 404 Non Trouvé
	return 'Bonjour le monde';
});

// Ce que vous voulez probablement
Flight::route('/bonjour', function(){
	echo 'Bonjour le monde';
});

```

La raison pour cela est due à un mécanisme spécial intégré au routeur qui gère la sortie de retour comme un signal pour "passer à la route suivante". Vous pouvez voir ce comportement documenté dans la section [Routing](/learn/routing#passing).