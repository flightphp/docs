# Requêtes

Flight encapsule la requête HTTP dans un seul objet, qui peut être accédé en faisant :

```php
$request = Flight::request();
```

L'objet de requête fournit les propriétés suivantes :

- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de requête (GET, POST, PUT, DELETE)
- **referrer** - L'URL de référence
- **ip** - Adresse IP du client
- **ajax** - Si la requête est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Informations sur le navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Paramètres de la chaîne de requête
- **data** - Données de publication ou données JSON
- **cookies** - Données de cookie
- **files** - Fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres d'acceptation HTTP
- **proxy_ip** - Adresse IP du proxy du client
- **host** - Le nom d'hôte de la requête

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files`
sous forme de tableaux ou d'objets.

Ainsi, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

```php
$id = Flight::request()->query->id;
```

## Corps de la requête BRUT

Pour obtenir le corps brut de la requête HTTP, par exemple lors de la gestion des requêtes PUT,
vous pouvez faire :

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une requête avec le type `application/json` et les données `{"id": 123}`,
elles seront disponibles à partir de la propriété `data` :

```php
$id = Flight::request()->data->id;
```