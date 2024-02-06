# Substitution

Flight vous permet de substituer sa fonctionnalité par défaut pour répondre à vos besoins,
sans avoir à modifier aucun code.

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il invoque la méthode `notFound`
qui envoie une réponse `HTTP 404` générique. Vous pouvez substituer ce comportement
en utilisant la méthode `map`:

```php
Flight::map('notFound', function() {
  // Afficher une page d'erreur 404 personnalisée
  include 'errors/404.html';
});
```

Flight vous permet également de remplacer les composants principaux du framework.
Par exemple, vous pouvez remplacer la classe de routeur par défaut par votre propre classe personnalisée:

```php
// Enregistrer votre classe personnalisée
Flight::register('router', MyRouter::class);

// Lorsque Flight charge l'instance de routeur, il chargera votre classe
$myrouter = Flight::router();
```

Cependant, les méthodes du framework telles que `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez
une erreur si vous essayez de le faire.