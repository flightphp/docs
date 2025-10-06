# Requêtes

## Aperçu

Flight encapsule la requête HTTP dans un seul objet, qui peut être
accédé en faisant :

```php
$request = Flight::request();
```

## Compréhension

Les requêtes HTTP sont l'un des aspects fondamentaux à comprendre concernant le cycle de vie HTTP. Un utilisateur effectue une action dans un navigateur web ou un client HTTP, et ils envoient une série d'en-têtes, de corps, d'URL, etc. vers votre projet. Vous pouvez capturer ces en-têtes (le langage du navigateur, le type de compression qu'ils peuvent gérer, l'agent utilisateur, etc.) et capturer le corps et l'URL qui sont envoyés à votre application Flight. Ces requêtes sont essentielles pour que votre application comprenne quoi faire ensuite.

## Utilisation de base

PHP possède plusieurs super globales incluant `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, et `$_COOKIE`. Flight abstrait ces éléments en [Collections](/learn/collections) pratiques. Vous pouvez accéder aux propriétés `query`, `data`, `cookies`, et `files` en tant qu'arrays ou objets.

> **Note :** Il est **FORTEMENT** déconseillé d'utiliser ces super globales dans votre projet et elles doivent être référencées via l'objet `request()`.

> **Note :** Il n'y a pas d'abstraction disponible pour `$_ENV`.

### `$_GET`

Vous pouvez accéder à l'array `$_GET` via la propriété `query` :

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// ou
	$keyword = Flight::request()->query->keyword;
	echo "Vous recherchez : $keyword";
	// interroger une base de données ou autre chose avec le $keyword
});
```

### `$_POST`

Vous pouvez accéder à l'array `$_POST` via la propriété `data` :

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// ou
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Vous avez soumis : $name, $email";
	// sauvegarder dans une base de données ou autre chose avec le $name et $email
});
```

### `$_COOKIE`

Vous pouvez accéder à l'array `$_COOKIE` via la propriété `cookies` :

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// ou
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// vérifier s'il est vraiment sauvegardé ou non et si oui, les connecter automatiquement
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Pour de l'aide sur la définition de nouvelles valeurs de cookies, voir [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Il existe un raccourci disponible pour accéder à l'array `$_SERVER` via la méthode `getVar()` :

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Vous pouvez accéder aux fichiers téléchargés via la propriété `files` :

```php
// accès brut à la propriété $_FILES. Voir ci-dessous pour l'approche recommandée
$uploadedFile = Flight::request()->files['myFile']; 
// ou
$uploadedFile = Flight::request()->files->myFile;
```

Voir [Uploaded File Handler](/learn/uploaded-file) pour plus d'informations.

#### Traitement des téléchargements de fichiers

_v3.12.0_

Vous pouvez traiter les téléchargements de fichiers en utilisant le framework avec quelques méthodes d'aide. Cela se résume essentiellement à extraire les données de fichier de la requête et à les déplacer vers un nouvel emplacement.

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si vous avez plusieurs fichiers téléchargés, vous pouvez les parcourir en boucle :

```php
Flight::route('POST /upload', function(){
	// Si vous aviez un champ d'entrée comme <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Note de sécurité :** Validez et nettoyez toujours les entrées utilisateur, surtout lors du traitement des téléchargements de fichiers. Validez toujours le type d'extensions que vous autorisez à être téléchargées, mais vous devriez également valider les "octets magiques" du fichier pour vous assurer qu'il s'agit réellement du type de fichier que l'utilisateur prétend. Il existe des [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [et](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliothèques](https://github.com/RikudouSage/MimeTypeDetector) disponibles pour vous aider avec cela.

### Corps de la requête

Pour obtenir le corps brut de la requête HTTP, par exemple lors du traitement de requêtes POST/PUT,
vous pouvez faire :

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// faire quelque chose avec le XML qui a été envoyé.
});
```

### Corps JSON

Si vous recevez une requête avec le type de contenu `application/json` et les données d'exemple `{"id": 123}`
elle sera disponible via la propriété `data` :

```php
$id = Flight::request()->data->id;
```

### En-têtes de la requête

Vous pouvez accéder aux en-têtes de la requête en utilisant la méthode `getHeader()` ou `getHeaders()` :

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

### Méthode de la requête

Vous pouvez accéder à la méthode de la requête en utilisant la propriété `method` ou la méthode `getMethod()` :

```php
$method = Flight::request()->method; // en réalité peuplé par getMethod()
$method = Flight::request()->getMethod();
```

**Note :** La méthode `getMethod()` récupère d'abord la méthode depuis `$_SERVER['REQUEST_METHOD']`, puis elle peut être écrasée 
par `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si elle existe ou `$_REQUEST['_method']` si elle existe.

## Propriétés de l'objet requête

L'objet requête fournit les propriétés suivantes :

- **body** - Le corps brut de la requête HTTP
- **url** - L'URL demandée
- **base** - Le sous-répertoire parent de l'URL
- **method** - La méthode de la requête (GET, POST, PUT, DELETE)
- **referrer** - L'URL de référence
- **ip** - L'adresse IP du client
- **ajax** - Si la requête est une requête AJAX
- **scheme** - Le protocole du serveur (http, https)
- **user_agent** - Les informations du navigateur
- **type** - Le type de contenu
- **length** - La longueur du contenu
- **query** - Les paramètres de la chaîne de requête
- **data** - Les données POST ou les données JSON
- **cookies** - Les données des cookies
- **files** - Les fichiers téléchargés
- **secure** - Si la connexion est sécurisée
- **accept** - Les paramètres HTTP accept
- **proxy_ip** - L'adresse IP proxy du client. Parcourt l'array `$_SERVER` pour `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` dans cet ordre.
- **host** - Le nom d'hôte de la requête
- **servername** - Le SERVER_NAME depuis `$_SERVER`

## Méthodes d'aide

Il existe quelques méthodes d'aide pour assembler des parties d'une URL, ou gérer certains en-têtes.

### URL complète

Vous pouvez accéder à l'URL complète de la requête en utilisant la méthode `getFullUrl()` :

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL de base

Vous pouvez accéder à l'URL de base en utilisant la méthode `getBaseUrl()` :

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Notez, pas de barre oblique finale.
```

## Analyse de requête

Vous pouvez passer une URL à la méthode `parseQuery()` pour analyser la chaîne de requête en un array associatif :

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Négociation des types de contenu acceptés

_v3.17.2_

Vous pouvez utiliser la méthode `negotiateContentType()` pour déterminer le meilleur type de contenu à répondre en fonction de l'en-tête `Accept` envoyé par le client.

```php

// Exemple d'en-tête Accept : text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// Le code ci-dessous définit ce que vous supportez.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Servir une réponse JSON
} elseif ($typeToServe === 'application/xml') {
	// Servir une réponse XML
} else {
	// Par défaut, utiliser quelque chose d'autre ou lever une erreur
}
```

> **Note :** Si aucun des types disponibles n'est trouvé dans l'en-tête `Accept`, la méthode renverra `null`. Si aucun en-tête `Accept` n'est défini, la méthode renverra le premier type dans le array `$availableTypes`.

## Voir aussi
- [Routing](/learn/routing) - Voir comment mapper les routes aux contrôleurs et rendre les vues.
- [Responses](/learn/responses) - Comment personnaliser les réponses HTTP.
- [Why a Framework?](/learn/why-frameworks) - Comment les requêtes s'intègrent dans le tableau global.
- [Collections](/learn/collections) - Travailler avec des collections de données.
- [Uploaded File Handler](/learn/uploaded-file) - Gérer les téléchargements de fichiers.

## Dépannage
- `request()->ip` et `request()->proxy_ip` peuvent être différents si votre serveur web est derrière un proxy, un équilibreur de charge, etc. 

## Journal des modifications
- v3.17.2 - Ajout de negotiateContentType()
- v3.12.0 - Ajout de la capacité à gérer les téléchargements de fichiers via l'objet requête.
- v1.0 - Version initiale.