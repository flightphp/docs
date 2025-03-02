# Mise en cache HTTP

Flight offre une prise en charge intégrée pour la mise en cache au niveau HTTP. Si la condition de mise en cache
est remplie, Flight renverra une réponse HTTP `304 Non modifié`. La prochaine fois que  
le client demande la même ressource, il sera invité à utiliser sa version mise en cache localement.

## Dernière modification

Vous pouvez utiliser la méthode `lastModified` et transmettre un horodatage UNIX pour définir la date
et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera d'utiliser sa mise en cache jusqu'à ce que
que la valeur de la dernière modification soit modifiée.

```php
Flight::route('/actualites', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

## ETag

La mise en cache `ETag` est similaire à `Dernière modification`, sauf que vous pouvez spécifier n'importe quel identifiant
que vous souhaitez pour la ressource :

```php
Flight::route('/actualites', function () {
  Flight::etag('mon-identifiant-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit que l'appel à `lastModified` ou `etag` définira et vérifiera à la fois
la valeur de la mise en cache. Si la valeur de mise en cache est la même entre les demandes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.