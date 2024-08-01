# Demandes

Flight encapsule la requête HTTP dans un seul objet, qui peut être
accédé en faisant :

```php
$request = Flight::request();
```

## Cas d'utilisation typiques

Lorsque vous travaillez avec une requête dans une application web, vous voudrez
généralement extraire un en-tête, un paramètre `$_GET` ou `$_POST`, ou peut-être
même le corps brut de la requête. Flight fournit une interface simple pour faire tout cela.

Voici un exemple d'obtention d'un paramètre de chaîne de requête :

```php
Flight::route('/recherche', function(){
	$motClé = Flight::request()->query['motClé'];
	echo "Vous recherchez : $motClé";
	// interroger une base de données ou autre chose avec le $motClé
});
```

Voici un exemple peut-être d'un formulaire avec une méthode POST :

```php
Flight::route('POST /envoyer', function(){
	$nom = Flight::request()->data['nom'];
	$email = Flight::request()->data['email'];
	echo "Vous avez envoyé : $nom, $email";
	// enregistrer dans une base de données ou autre chose avec le $nom et $email
});
```

## Propriétés de l'objet de requête

L'objet de requête fournit les propriétés suivantes:

- **body** - Le corps brut de la requête HTTP
- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de requête (GET, POST, PUT, DELETE)
- **referrer** - L'URL du référent
- **ip** - Adresse IP du client
- **ajax** - Si la requête est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Informations sur le navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Paramètres de la chaîne de requête
- **data** - Données POST ou JSON
- **cookies** - Données des cookies
- **files** - Fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres d'acceptation HTTP
- **proxy_ip** - Adresse IP du proxy du client. Analyse le tableau `$_SERVER` pour `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dans cet ordre.
- **host** - Le nom d'hôte de la demande

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files`
sous forme de tableaux ou d'objets.

Donc, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

```php
$id = Flight::request()->query->id;
```

## Corps brut de la requête

Pour obtenir le corps brut de la requête HTTP, par exemple lors de la manipulation de demandes PUT,
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

## `$_GET`

Vous pouvez accéder au tableau `$_GET` via la propriété `query` :

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Vous pouvez accéder au tableau `$_POST` via la propriété `data` :

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Vous pouvez accéder au tableau `$_COOKIE` via la propriété `cookies` :

```php
$maValeurDeCookie = Flight::request()->cookies['monNomCookie'];
```

## `$_SERVER`

Il y a un raccourci disponible pour accéder au tableau `$_SERVER` via la méthode `getVar()` :

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Fichiers téléchargés via `$_FILES`

Vous pouvez accéder aux fichiers téléchargés via la propriété `files` :

```php
$fichierTéléchargé = Flight::request()->files['monFichier'];
```

## En-têtes de requête

Vous pouvez accéder aux en-têtes de requête en utilisant la méthode `getHeader()` ou `getHeaders()` :

```php

// Peut-être avez-vous besoin de l'en-tête Authorization
$hôte = Flight::request()->getHeader('Authorization');
// ou
$hôte = Flight::request()->header('Authorization');

// Si vous devez récupérer tous les en-têtes
$enTêtes = Flight::request()->getHeaders();
// ou
$enTêtes = Flight::request()->headers();
```

## Corps de la requête

Vous pouvez accéder au corps brut de la requête en utilisant la méthode `getBody()` :

```php
$body = Flight::request()->getBody();
```

## Méthode de requête

Vous pouvez accéder à la méthode de requête en utilisant la propriété `method` ou la méthode `getMethod()` :

```php
$méthode = Flight::request()->method; // appelle réellement getMethod()
$méthode = Flight::request()->getMethod();
```

**Remarque :** La méthode `getMethod()` tire d'abord la méthode de `$_SERVER['REQUEST_METHOD']`, puis elle peut être écrasée par `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` s'il existe ou par `$_REQUEST['_method']` s'il existe.

## URLs de requête

Il existe quelques méthodes d'aide pour assembler des parties d'une URL pour votre commodité.

### URL complète

Vous pouvez accéder à l'URL complète de la requête en utilisant la méthode `getFullUrl()` :

```php
$url = Flight::request()->getFullUrl();
// https://exemple.com/quelque/chemin?foo=bar
```
### URL de base

Vous pouvez accéder à l'URL de base en utilisant la méthode `getBaseUrl()` :

```php
$url = Flight::request()->getBaseUrl();
// Remarque, pas de slash final.
// https://exemple.com
```

## Analyse de la requête

Vous pouvez passer une URL à la méthode `parseQuery()` pour analyser la chaîne de requête en tableau associatif :

```php
$query = Flight::request()->parseQuery('https://exemple.com/quelque/chemin?foo=bar');
// ['foo' => 'bar']
```