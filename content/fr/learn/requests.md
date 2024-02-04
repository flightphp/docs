# Requêtes

Flight encapsule la requête HTTP dans un seul objet, qui peut être accédé en faisant:

```php
$request = Flight::request();
```

L'objet de requête fournit les propriétés suivantes:

- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de requête (GET, POST, PUT, DELETE)
- **referrer** - L'URL du référent
- **ip** - Adresse IP du client
- **ajax** - Indique si la requête est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Informations sur le navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Paramètres de la chaîne de requêtes (query string)
- **data** - Données Post ou données JSON
- **cookies** - Données des cookies
- **files** - Fichiers téléchargés
- **secure** - Indique si la connexion est sécurisée
- **accept** - Paramètres accept HTTP
- **proxy_ip** - Adresse IP du proxy du client
- **host** - Le nom de l'hôte de la requête

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files`
sous forme de tableaux ou d'objets.

Ainsi, pour obtenir un paramètre de chaîne de requête, vous pouvez faire:

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire:

```php
$id = Flight::request()->query->id;
```

## Corps de Requête RAW

Pour obtenir le corps brut de la requête HTTP, par exemple lors de la manipulation de requêtes PUT,
vous pouvez faire:

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une requête avec le type `application/json` et les données `{"id": 123}`,
elles seront disponibles à partir de la propriété `data`:

```php
$id = Flight::request()->data->id;
```