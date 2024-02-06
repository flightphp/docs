# Redirection

Vous pouvez rediriger la requête actuelle en utilisant la méthode `redirect` et en passant
une nouvelle URL :

```php
Flight::redirect('/nouvel/emplacement');
```

Par défaut, Flight envoie un code d'état HTTP 303. Vous pouvez éventuellement définir un
code personnalisé :

```php
Flight::redirect('/nouvel/emplacement', 401);
```