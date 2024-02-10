# Requêtes

Flight encapsule la demande HTTP dans un objet unique, qui peut être
accédé en faisant:

```php
$request = Flight::request();
```

L'objet de demande fournit les propriétés suivantes:

- **body** - Le corps brut de la demande HTTP
- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de demande (GET, POST, PUT, DELETE)
- **referrer** - L'URL de référence
- **ip** - L'adresse IP du client
- **ajax** - Si la demande est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Informations sur le navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Paramètres de chaîne de requête
- **data** - Données de publication ou données JSON
- **cookies** - Données des cookies
- **files** - Fichiers téléversés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres acceptés par HTTP
- **proxy_ip** - Adresse IP proxy du client
- **host** - Le nom d'hôte de la demande

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

## Corps brut de la requête

Pour obtenir le corps brut de la demande HTTP, par exemple lors du traitement de demandes PUT,
vous pouvez faire:

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une demande avec le type `application/json` et les données `{"id": 123}`
ils seront disponibles à partir de la propriété `data`:

```php
$id = Flight::request()->data->id;
```

## Accès à `$_SERVEUR`

Il existe un raccourci disponible pour accéder au tableau `$_SERVEUR` via la méthode `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accès aux en-têtes de la demande

Vous pouvez accéder aux en-têtes de la demande en utilisant la méthode `getHeader()` ou `getHeaders()`:

```php

// Peut-être avez-vous besoin de l'en-tête Authorization
$host = Flight::request()->getHeader('Authorization');

// Si vous devez récupérer tous les en-têtes
$headers = Flight::request()->getHeaders();
```