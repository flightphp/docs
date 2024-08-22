# Requêtes

Flight encapsule la requête HTTP dans un seul objet, qui peut être
accédé en faisant :

```php
$request = Flight::request();
```

## Cas d'utilisation typiques

Lorsque vous travaillez avec une requête dans une application web, vous voudrez généralement
extraire un en-tête, ou un paramètre `$_GET` ou `$_POST`, ou peut-être même le corps brut de la requête. Flight fournit une interface simple pour faire toutes ces choses.

Voici un exemple d'obtention d'un paramètre de chaîne de requête :

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Vous recherchez : $keyword";
	// interroger une base de données ou autre chose avec le $keyword
});
```

Voici un exemple d'un formulaire avec une méthode POST :

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Vous avez soumis : $name, $email";
	// enregistrer dans une base de données ou autre chose avec le $name et $email
});
```

## Propriétés de l'objet de requête

L'objet de requête fournit les propriétés suivantes :

- **body** - Le corps brut de la requête HTTP
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
- **query** - Paramètres de chaîne de requête
- **data** - Données POST ou données JSON
- **cookies** - Données de cookie
- **files** - Fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Paramètres d'acceptation HTTP
- **proxy_ip** - Adresse IP proxy du client. Scanne le tableau `$_SERVER` pour `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dans cet ordre.
- **host** - Le nom d'hôte de la requête

Vous pouvez accéder aux propriétés `query`, `data`, `cookies` et `files` comme des tableaux ou des objets.

Donc, pour obtenir un paramètre de chaîne de requête, vous pouvez faire :

```php
$id = Flight::request()->query['id'];
```

Ou vous pouvez faire :

```php
$id = Flight::request()->query->id;
```

## CORPS DE LA REQUÊTE BRUTE

Pour obtenir le corps brut de la requête HTTP, par exemple lors du traitement des requêtes PUT,
vous pouvez faire :

```php
$body = Flight::request()->getBody();
```

## Entrée JSON

Si vous envoyez une requête avec le type `application/json` et les données `{"id": 123}`
elles seront disponibles via la propriété `data` :

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
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Il existe un raccourci disponible pour accéder au tableau `$_SERVER` via la méthode `getVar()` :

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accéder aux fichiers téléchargés via `$_FILES`

Vous pouvez accéder aux fichiers téléchargés via la propriété `files` :

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Traitement des téléchargements de fichiers

Vous pouvez traiter les téléchargements de fichiers en utilisant le framework avec quelques méthodes d'aide. Cela revient essentiellement à obtenir les données du fichier de la requête et à les déplacer vers un nouvel emplacement.

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si vous avez plusieurs fichiers téléchargés, vous pouvez les parcourir :

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Note de sécurité :** Validez toujours et assainissez les entrées de l'utilisateur, en particulier lors du traitement des téléchargements de fichiers. Validez toujours le type d'extensions que vous autoriserez à être téléchargées, mais vous devriez également valider les "octets magiques" du fichier pour vous assurer qu'il s'agit effectivement du type de fichier que l'utilisateur prétend avoir. Il existe des [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [et](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliothèques](https://github.com/RikudouSage/MimeTypeDetector) disponibles pour vous aider avec cela.

## En-têtes de requête

Vous pouvez accéder aux en-têtes de la requête en utilisant la méthode `getHeader()` ou `getHeaders()` :

```php

// Peut-être avez-vous besoin de l'en-tête d'Authorization
$host = Flight::request()->getHeader('Authorization');
// ou
$host = Flight::request()->header('Authorization');

// Si vous devez récupérer tous les en-têtes
$headers = Flight::request()->getHeaders();
// ou
$headers = Flight::request()->headers();
```

## Corps de la requête

Vous pouvez accéder au corps brut de la requête en utilisant la méthode `getBody()` :

```php
$body = Flight::request()->getBody();
```

## Méthode de requête

Vous pouvez accéder à la méthode de requête en utilisant la propriété `method` ou la méthode `getMethod()` :

```php
$method = Flight::request()->method; // appelle en réalité getMethod()
$method = Flight::request()->getMethod();
```

**Remarque :** La méthode `getMethod()` récupère d'abord la méthode à partir de `$_SERVER['REQUEST_METHOD']`, puis elle peut être écrasée 
par `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si elle existe ou `$_REQUEST['_method']` si elle existe.

## URLs de requête

Il existe quelques méthodes d'aide pour assembler des parties d'une URL pour votre commodité.

### URL complète

Vous pouvez accéder à l'URL de requête complète en utilisant la méthode `getFullUrl()` :

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL de base

Vous pouvez accéder à l'URL de base en utilisant la méthode `getBaseUrl()` :

```php
$url = Flight::request()->getBaseUrl();
// Remarque, pas de barre oblique de fin.
// https://example.com
```

## Analyse des requêtes

Vous pouvez passer une URL à la méthode `parseQuery()` pour analyser la chaîne de requête en un tableau associatif :

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```