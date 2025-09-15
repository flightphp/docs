# Demandes

Flight encapsule la requête HTTP dans un seul objet, que vous pouvez accéder en faisant :

```php
$request = Flight::request();
```

## Cas d'utilisation typiques

Lorsque vous travaillez avec une requête dans une application web, vous voudrez généralement extraire un en-tête, ou un paramètre `$_GET` ou `$_POST`, ou peut-être même le corps brut de la requête. Flight fournit une interface simple pour faire tout cela.

Voici un exemple pour obtenir un paramètre de chaîne de requête :

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// interrogez une base de données ou autre chose avec $keyword
});
```

Voici un exemple pour peut-être un formulaire avec une méthode POST :

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// enregistrez dans une base de données ou autre chose avec $name et $email
});
```

## Propriétés de l'objet de requête

L'objet de requête fournit les propriétés suivantes :

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
- **query** - Paramètres de la chaîne de requête
- **data** - Données POST ou JSON
- **cookies** - Données de cookies
- **files** - Fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres HTTP accept
- **proxy_ip** - Adresse IP proxy du client. Analyse le tableau `$_SERVER` pour `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dans cet ordre.
- **host** - Le nom d'hôte de la requête
- **servername** - Le SERVER_NAME à partir de `$_SERVER`

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files` en tant que tableaux ou objets.

Donc, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

```php
$id = Flight::request()->query->id;
```

## Corps de requête brut

Pour obtenir le corps brut de la requête HTTP, par exemple lors de requêtes PUT, vous pouvez faire :

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une requête avec le type `application/json` et les données `{"id": 123}`, elle sera disponible à partir de la propriété `data` :

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Vous pouvez accéder au tableau `$_GET` via la propriété `query` :

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Vous pouvez accéder au tableau `$_POST` via la propriété `data` :

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Vous pouvez accéder au tableau `$_COOKIE` via la propriété `cookies` :

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Il y a un raccourci disponible pour accéder au tableau `$_SERVER` via la méthode `getVar()` :

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Accès aux fichiers téléchargés via `$_FILES`

Vous pouvez accéder aux fichiers téléchargés via la propriété `files` :

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Traitement des téléchargements de fichiers (v3.12.0)

Vous pouvez traiter les téléchargements de fichiers en utilisant le framework avec quelques méthodes d'aide. Cela revient essentiellement à extraire les données de fichier de la requête et à les déplacer vers un nouvel emplacement.

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si vous avez plusieurs fichiers téléchargés, vous pouvez les parcourir :

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Note de sécurité :** Toujours valider et assainir les entrées utilisateur, surtout lors du traitement des téléchargements de fichiers. Toujours valider les types d'extensions que vous autorisez à être téléchargées, mais vous devriez également valider les "octets magiques" du fichier pour vous assurer qu'il s'agit réellement du type de fichier que l'utilisateur prétend. Il y a [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) disponibles pour aider avec cela.

## En-têtes de requête

Vous pouvez accéder aux en-têtes de requête en utilisant la méthode `getHeader()` ou `getHeaders()` :

```php
// Peut-être que vous avez besoin de l'en-tête Authorization
$host = Flight::request()->getHeader('Authorization');
// ou
$host = Flight::request()->header('Authorization');

// Si vous devez récupérer tous les en-têtes
$headers = Flight::request()->getHeaders();
// ou
$headers = Flight::request()->headers();
```

## Corps de la requête

Vous pouvez accéder au corps brut de la requête en utilisant la méthode `getBody()` :

```php
$body = Flight::request()->getBody();
```

## Méthode de requête

Vous pouvez accéder à la méthode de requête en utilisant la propriété `method` ou la méthode `getMethod()` :

```php
$method = Flight::request()->method; // appelle en fait getMethod()
$method = Flight::request()->getMethod();
```

**Note :** La méthode `getMethod()` récupère d'abord la méthode à partir de `$_SERVER['REQUEST_METHOD']`, puis elle peut être écrasée par `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si elle existe ou `$_REQUEST['_method']` si elle existe.

## URLs de requête

Il y a quelques méthodes d'aide pour assembler des parties d'une URL pour votre commodité.

### URL complète

Vous pouvez accéder à l'URL de requête complète en utilisant la méthode `getFullUrl()` :

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL de base

Vous pouvez accéder à l'URL de base en utilisant la méthode `getBaseUrl()` :

```php
$url = Flight::request()->getBaseUrl();
// Remarquez, pas de slash final.
// https://example.com
```

## Analyse de requête

Vous pouvez passer une URL à la méthode `parseQuery()` pour analyser la chaîne de requête en un tableau associatif :

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```