# Umleitungen

Sie können die aktuelle Anfrage umleiten, indem Sie die Methode `redirect` verwenden und eine neue URL übergeben:

```php
Flight::redirect('/neuer/standort');
```

Standardmäßig sendet Flight einen HTTP-Statuscode 303. Sie können optional einen benutzerdefinierten Code festlegen:

```php
Flight::redirect('/neuer/standort', 401);
```