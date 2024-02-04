# Gestion des erreurs

## Erreurs et exceptions

Toutes les erreurs et exceptions sont capturées par Flight et transmises à la méthode `error`.
Le comportement par défaut est d'envoyer une réponse générique `HTTP 500 Erreur interne du serveur`
avec des informations sur l'erreur.

Vous pouvez remplacer ce comportement selon vos besoins :

```php
Flight::map('error', function (Throwable $error) {
  // Gérer l'erreur
  echo $error->getTraceAsString();
});
```

Par défaut, les erreurs ne sont pas consignées dans le serveur web. Vous pouvez activer cela en
modifiant la configuration :

```php
Flight::set('flight.log_errors', true);
```

## Introuvable

Lorsqu'une URL est introuvable, Flight appelle la méthode `notFound`. Le comportement par défaut
est d'envoyer une réponse `HTTP 404 Non trouvé` avec un message simple.

Vous pouvez remplacer ce comportement selon vos besoins :

```php
Flight::map('notFound', function () {
  // Gérer l'introuvable
});
```