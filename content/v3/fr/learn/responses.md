# Réponses

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous conservez la plupart du contrôle sur ce que vous renvoyez à l'utilisateur. Parfois, vous pouvez accéder directement à l'objet `Response`, mais la plupart du temps, vous utiliserez l'instance `Flight` pour envoyer une réponse.

## Envoyer une réponse basique

Flight utilise `ob_start()` pour tamponner la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur et que Flight la capturera et l'enverra avec les en-têtes appropriés.

```php
// Cela enverra "Hello, World!" au navigateur de l'utilisateur
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

En alternative, vous pouvez appeler la méthode `write()` pour ajouter au corps de la réponse.

```php
// Cela enverra "Hello, World!" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais utile dans certains cas lorsque vous en avez besoin
	Flight::response()->write("Hello, World!");

	// si vous voulez récupérer le corps que vous avez défini à ce point
	// vous pouvez le faire comme ceci
	$body = Flight::response()->getBody();
});
```

## Codes de statut

Vous pouvez définir le code de statut de la réponse en utilisant la méthode `status` :

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Si vous voulez obtenir le code de statut actuel, vous pouvez utiliser la méthode `status` sans arguments :

```php
Flight::response()->status(); // 200
```

## Définir un corps de réponse

Vous pouvez définir le corps de la réponse en utilisant la méthode `write`, cependant, si vous utilisez `echo` ou `print`, cela sera capturé et envoyé comme corps de la réponse via le tampon de sortie.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// équivalent à

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Effacer un corps de réponse

Si vous voulez effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody` :

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Exécuter un rappel sur le corps de la réponse

Vous pouvez exécuter un rappel sur le corps de la réponse en utilisant la méthode `addResponseBodyCallback` :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Cela comprendra toutes les réponses pour n'importe quelle route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Vous pouvez ajouter plusieurs rappels et ils seront exécutés dans l'ordre dans lequel ils ont été ajoutés. Comme cela peut accepter n'importe quel [appelable](https://www.php.net/manual/en/language.types.callable.php), il peut accepter un tableau de classe `[ $class, 'method' ]`, une fermeture `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou un nom de fonction `'minify'` si vous aviez une fonction pour minimiser votre code HTML par exemple.

**Note :** Les rappels de route ne fonctionneront pas si vous utilisez l'option de configuration `flight.v2.output_buffering`.

### Rappel pour une route spécifique

Si vous voulez que cela s'applique uniquement à une route spécifique, vous pouvez ajouter le rappel dans la route elle-même :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Cela comprendra uniquement la réponse pour cette route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Option de middleware

Vous pouvez également utiliser un middleware pour appliquer le rappel à toutes les routes via un middleware :

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Appliquer le rappel ici sur l'objet response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minimiser le corps d'une certaine manière
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Définir un en-tête de réponse

Vous pouvez définir un en-tête tel que le type de contenu de la réponse en utilisant la méthode `header` :

```php
// Cela enverra "Hello, World!" au navigateur de l'utilisateur en texte brut
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight fournit un support pour envoyer des réponses JSON et JSONP. Pour envoyer une réponse JSON, vous passez des données à encoder en JSON :

```php
Flight::json(['id' => 123]);
```

> **Note :** Par défaut, Flight enverra un en-tête `Content-Type: application/json` avec la réponse. Il utilisera également les constantes `JSON_THROW_ON_ERROR` et `JSON_UNESCAPED_SLASHES` lors de l'encodage du JSON.

### JSON avec code de statut

Vous pouvez également passer un code de statut en tant que deuxième argument :

```php
Flight::json(['id' => 123], 201);
```

### JSON avec impression formatée

Vous pouvez également passer un argument à la dernière position pour activer l'impression formatée :

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si vous modifiez les options passées à `Flight::json()` et que vous voulez une syntaxe plus simple, vous pouvez remapper la méthode JSON :

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Et maintenant, elle peut être utilisée comme ceci
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON et arrêt de l'exécution (v3.10.0)

Si vous voulez envoyer une réponse JSON et arrêter l'exécution, vous pouvez utiliser la méthode `jsonHalt()`. Cela est utile pour les cas où vous vérifiez peut-être un type d'autorisation et si l'utilisateur n'est pas autorisé, vous pouvez envoyer une réponse JSON immédiatement, effacer le contenu du corps existant et arrêter l'exécution.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifier si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Continuer avec le reste de la route
});
```

Avant v3.10.0, vous deviez faire quelque chose comme ceci :

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifier si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continuer avec le reste de la route
});
```

### JSONP

Pour les demandes JSONP, vous pouvez optionnellement passer le nom du paramètre de requête que vous utilisez pour définir votre fonction de rappel :

```php
Flight::jsonp(['id' => 123], 'q');
```

Donc, lors d'une demande GET en utilisant `?q=my_func`, vous devriez recevoir la sortie :

```javascript
my_func({"id":123});
```

Si vous ne passez pas de nom de paramètre de requête, il passera par défaut à `jsonp`.

## Rediriger vers une autre URL

Vous pouvez rediriger la demande actuelle en utilisant la méthode `redirect()` et en passant une nouvelle URL :

```php
Flight::redirect('/new/location');
```

Par défaut, Flight envoie un code de statut HTTP 303 ("See Other"). Vous pouvez optionnellement définir un code personnalisé :

```php
Flight::redirect('/new/location', 401);
```

## Arrêter

Vous pouvez arrêter le framework à n'importe quel moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code de statut HTTP et un message optionnels :

```php
Flight::halt(200, 'Be right back...');
```

L'appel à `halt` supprimera tout contenu de réponse jusqu'à ce point. Si vous voulez arrêter le framework et sortir la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop($httpStatusCode = null);
```

> **Note :** `Flight::stop()` a un comportement inhabituel, tel qu'il sortira la réponse mais continuera à exécuter votre script. Vous pouvez utiliser `exit` ou `return` après avoir appelé `Flight::stop()` pour empêcher une exécution ultérieure, mais il est généralement recommandé d'utiliser `Flight::halt()`.

## Effacer les données de réponse

Vous pouvez effacer le corps et les en-têtes de la réponse en utilisant la méthode `clear()`. Cela effacera tous les en-têtes assignés à la réponse, effacera le corps de la réponse et définira le code de statut sur `200`.

```php
Flight::response()->clear();
```

### Effacer uniquement le corps de la réponse

Si vous voulez uniquement effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody()` :

```php
// Cela conservera tous les en-têtes définis sur l'objet response().
Flight::response()->clearBody();
```

## Mise en cache HTTP

Flight fournit un support intégré pour la mise en cache au niveau HTTP. Si la condition de mise en cache est satisfaite, Flight renverra une réponse HTTP `304 Not Modified`. La prochaine fois que le client demande la même ressource, il sera invité à utiliser sa version mise en cache localement.

### Mise en cache au niveau de la route

Si vous voulez mettre en cache votre réponse entière, vous pouvez utiliser la méthode `cache()` et passer un temps de mise en cache.

```php
// Cela mettra en cache la réponse pour 5 minutes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// En alternative, vous pouvez utiliser une chaîne que vous passeriez
// à la méthode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Vous pouvez utiliser la méthode `lastModified` et passer un horodatage UNIX pour définir la date et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera à utiliser son cache jusqu'à ce que la valeur de dernière modification soit modifiée.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

La mise en cache ETag est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant pour la ressource :

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

N'oubliez pas que l'appel à `lastModified` ou `etag` définira et vérifiera la valeur de cache. Si la valeur de cache est la même entre les demandes, Flight enverra immédiatement une réponse `HTTP 304` et arrêtera le traitement.

## Télécharger un fichier (v3.12.0)

Il y a une méthode d'aide pour télécharger un fichier. Vous pouvez utiliser la méthode `download` et passer le chemin.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```