# Weiterleitungen

Sie können die aktuelle Anfrage umleiten, indem Sie die `redirect` Methode verwenden und eine neue URL übergeben:

```php
Flight::redirect('/neuer/standort');
```

Standardmäßig sendet Flight einen HTTP-Statuscode 303. Sie können optional einen benutzerdefinierten Code setzen:

```php
Flight::redirect('/neuer/standort', 401);
```