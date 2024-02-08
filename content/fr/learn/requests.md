# Requêtes

Flight encapsule la requête HTTP dans un seul objet, auquel on peut accéder en faisant :

```php
$request = Flight::request();
```

L'objet de requête fournit les propriétés suivantes :

- **body** - Le corps brut de la requête HTTP
- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de requête (GET, POST, PUT, DELETE)
- **referrer** - L'URL de référence
- **ip** - L'adresse IP du client
- **ajax** - Si la requête est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Informations sur le navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Paramètres de chaîne de requête
- **data** - Données de publication ou données JSON
- **cookies** - Données de cookie
- **files** - Fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres d'acceptation HTTP
- **proxy_ip** - Adresse IP du proxy du client
- **host** - Le nom d'hôte de la demande

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files` en tant que tableaux ou objets.

Ainsi, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

```php
$id = Flight::request()->query->id;
```

## Corps brut de la Requête

Pour obtenir le corps brut de la requête HTTP, par exemple lors de la gestion de requêtes PUT, vous pouvez faire :

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une requête avec le type `application/json` et les données `{"id": 123}`, elles seront disponibles à partir de la propriété `data` :

```php
$id = Flight::request()->data->id;
```

## Accès à `$_SERVER`

Il existe un raccourci disponible pour accéder au tableau `$_SERVER` via la méthode `getVar()` :

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accès aux En-têtes de Requête

Vous pouvez accéder aux en-têtes de demande en utilisant la méthode `getHeader()` ou `getHeaders()` :

```php

// Peut-être avez-vous besoin de l'en-tête Authorization
$host = Flight::request()->getHeader('Authorization');

// Si vous avez besoin de récupérer tous les en-têtes
$headers = Flight::request()->getHeaders();
```