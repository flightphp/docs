## Arrêt

Vous pouvez arrêter le cadre à tout moment en appelant la méthode `halt`:

```php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` et un message facultatif:

```php
Flight::halt(200, 'Je reviens bientôt...');
```

Appeler `halt` permettra de supprimer tout contenu de réponse jusqu'à ce point. Si vous souhaitez arrêter
le cadre et afficher la réponse actuelle, utilisez la méthode `stop`:

```php
Flight::stop();
```