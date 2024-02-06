# JSON

Flight fournit un support pour l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON, vous transmettez des données à encoder en JSON:

```php
Flight::json(['id' => 123]);
```

Pour les requêtes JSONP, vous pouvez éventuellement spécifier le nom du paramètre de requête que vous utilisez pour définir votre fonction de rappel:

```php
Flight::jsonp(['id' => 123], 'q');
```

Ainsi, lors de l'envoi d'une requête GET en utilisant `?q=my_func`, vous devriez recevoir la sortie:

```javascript
my_func({"id":123});
```

Si vous ne spécifiez pas de nom de paramètre de requête, il sera par défaut `jsonp`.