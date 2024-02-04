# Mise en cache HTTP

Flight fournit une prise en charge intégrée de la mise en cache au niveau HTTP. Si la condition de mise en cache est remplie, Flight renverra une réponse `304 Non modifié`. La prochaine fois que le client demandera la même ressource, il lui sera demandé d'utiliser sa version mise en cache localement.

## Dernière modification

Vous pouvez utiliser la méthode `lastModified` et transmettre un horodatage UNIX pour définir la date et l'heure de la dernière modification d'une page. Le client continuera à utiliser son cache jusqu'à ce que la valeur de dernière modification soit modifiée.

```php
Flight::route('/actualites', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

## ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant que vous voulez pour la ressource:

```php
Flight::route('/actualites', function () {
  Flight::etag('mon-identifiant-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit qu'appeler `lastModified` ou `etag` définira et vérifiera à la fois la valeur du cache. Si la valeur du cache est la même entre les demandes, Flight enverra immédiatement une réponse `HTTP 304` et arrêtera le traitement.