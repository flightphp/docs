# Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` optionnel et un message :

```php
Flight::halt(200, 'De retour bientôt...');
```

Appeler `halt` supprimera tout contenu de réponse jusqu'à ce point. Si vous souhaitez arrêter
le framework et afficher la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop();
```