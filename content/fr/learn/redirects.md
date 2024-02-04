# Redirections

Vous pouvez rediriger la demande actuelle en utilisant la méthode `redirect` et en passant
dans une nouvelle URL:

```php
Flight::redirect('/nouvel/emplacement');
```

Par défaut, Flight envoie un code de statut HTTP 303. Vous pouvez éventuellement définir un
code personnalisé:

```php
Flight::redirect('/nouvel/emplacement', 401);
```