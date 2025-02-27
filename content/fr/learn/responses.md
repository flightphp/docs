# Réponses

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous gardez le contrôle sur ce que vous renvoyez à l'utilisateur. Parfois, vous pouvez accéder directement à l'objet `Response`, mais la plupart du temps, vous utiliserez l'instance `Flight` pour envoyer une réponse.

## Envoi d'une réponse de base

Flight utilise ob_start() pour mettre en mémoire tampon la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur et Flight la capturera et la renverra avec les en-têtes appropriés.

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

En alternative, vous pouvez appeler la méthode `write()` pour ajouter au corps également.

```php

// Cela enverra "Hello, World!" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais cela fait souvent le travail lorsque vous en avez besoin
	Flight::response()->write("Hello, World!");

	// si vous souhaitez récupérer le corps que vous avez défini à ce stade
	// vous pouvez le faire comme ceci
	$body = Flight::response()->getBody();
});
```

## Codes d'état

Vous pouvez définir le code d'état de la réponse en utilisant la méthode `status` :

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Interdit";
	}
});
```

Si vous souhaitez obtenir le code d'état actuel, vous pouvez utiliser la méthode `status` sans aucun argument :

```php
Flight::response()->status(); // 200
```

## Définir un corps de réponse

Vous pouvez définir le corps de la réponse en utilisant la méthode `write`, cependant, si vous echo ou print quelque chose, 
il sera capturé et envoyé comme corps de réponse via la mise en mémoire tampon de sortie.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// même que

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Effacer un corps de réponse

Si vous souhaitez effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody` :

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Exécution d'un rappel sur le corps de réponse

Vous pouvez exécuter un rappel sur le corps de réponse en utilisant la méthode `addResponseBodyCallback` :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Cela gérera toutes les réponses pour n'importe quelle route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Vous pouvez ajouter plusieurs rappels et ils seront exécutés dans l'ordre dans lequel ils ont été ajoutés. Étant donné que cela peut accepter tout [callable](https://www.php.net/manual/en/language.types.callable.php), cela peut accepter un tableau de classe `[ $class, 'method' ]`, une closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou un nom de fonction `'minify'` si vous aviez une fonction pour minifier votre code html par exemple.

**Remarque :** Les rappels de route ne fonctionneront pas si vous utilisez l'option de configuration `flight.v2.output_buffering`.

### Rappel de route spécifique

Si vous souhaitez que cela s'applique uniquement à une route spécifique, vous pouvez ajouter le rappel dans la route elle-même :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Cela gérera uniquement la réponse pour cette route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Option Middleware

Vous pouvez également utiliser un middleware pour appliquer le rappel à toutes les routes via middleware :

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Appliquez le rappel ici sur l'objet response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minifiez le corps d'une certaine manière
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

Vous pouvez définir un en-tête comme le type de contenu de la réponse en utilisant la méthode `header` :

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

Flight fournit un support pour l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON, vous
transmettez des données à encoder en JSON :

```php
Flight::json(['id' => 123]);
```

> **Remarque :** Par défaut, Flight enverra un en-tête `Content-Type: application/json` avec la réponse. Il utilisera également les constantes `JSON_THROW_ON_ERROR` et `JSON_UNESCAPED_SLASHES` lors de l'encodage du JSON.

### JSON avec code d'état

Vous pouvez également passer un code d'état en tant que deuxième argument :

```php
Flight::json(['id' => 123], 201);
```

### JSON avec formatage

Vous pouvez également passer un argument à la dernière position pour activer le formatage :

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si vous modifiez les options passées dans `Flight::json()` et souhaitez une syntaxe plus simple, vous pouvez
simplement remapper la méthode JSON :

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Et maintenant cela peut être utilisé comme ceci
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON et arrêter l'exécution (v3.10.0)

Si vous souhaitez envoyer une réponse JSON et arrêter l'exécution, vous pouvez utiliser la méthode `jsonHalt`.
Ceci est utile pour les cas où vous vérifiez peut-être un certain type d'autorisation et si
l'utilisateur n'est pas autorisé, vous pouvez envoyer une réponse JSON immédiatement, effacer le contenu du corps existant
et arrêter l'exécution.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifiez si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Non autorisé'], 401);
	}

	// Continuez avec le reste de la route
});
```

Avant v3.10.0, vous auriez dû faire quelque chose comme ceci :

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifiez si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Non autorisé']));
	}

	// Continuez avec le reste de la route
});
```

### JSONP

Pour les requêtes JSONP, vous pouvez facultativement passer le nom du paramètre de requête que vous utilisez pour définir votre fonction de rappel :

```php
Flight::jsonp(['id' => 123], 'q');
```

Ainsi, lorsque vous effectuez une requête GET en utilisant `?q=my_func`, vous devriez recevoir la sortie :

```javascript
my_func({"id":123});
```

Si vous ne passez pas de nom de paramètre de requête, il sera par défaut `jsonp`.

## Rediriger vers une autre URL

Vous pouvez rediriger la demande actuelle en utilisant la méthode `redirect()` et en passant
une nouvelle URL :

```php
Flight::redirect('/new/location');
```

Par défaut, Flight envoie un code d'état HTTP 303 ("Voir autre"). Vous pouvez éventuellement définir un
code personnalisé :

```php
Flight::redirect('/new/location', 401);
```

## Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code de statut `HTTP` et un message optionnels :

```php
Flight::halt(200, 'Soyez de retour bientôt...');
```

Appeler `halt` annulera tout contenu de réponse jusqu'à ce point. Si vous souhaitez arrêter
le framework et afficher la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop();
```

## Effacer les données de réponse

Vous pouvez effacer le corps et les en-têtes de réponse en utilisant la méthode `clear()`. Cela effacera
tous les en-têtes assignés à la réponse, effacera le corps de la réponse et définira le code d'état sur `200`.

```php
Flight::response()->clear();
```

### Effacer uniquement le corps de réponse

Si vous voulez uniquement effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody()` :

```php
// Cela conservera toujours tous les en-têtes définis sur l'objet response().
Flight::response()->clearBody();
```

## Mise en cache HTTP

Flight fournit un support intégré pour la mise en cache au niveau HTTP. Si la condition de mise en cache
est remplie, Flight retournera une réponse HTTP `304 Non modifié`. La prochaine fois que le
client demandera la même ressource, il sera invité à utiliser sa version mise en cache localement.

### Mise en cache au niveau de la route

Si vous souhaitez mettre en cache l'ensemble de votre réponse, vous pouvez utiliser la méthode `cache()` et passer le temps à mettre en cache.

```php

// Cela mettra en cache la réponse pendant 5 minutes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Ce contenu sera mis en cache.';
});

// Alternativement, vous pouvez utiliser une chaîne que vous passeriez
// à la méthode strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Ce contenu sera mis en cache.';
});
```

### Dernière modification

Vous pouvez utiliser la méthode `lastModified` et passer un horodatage UNIX pour définir la date
et l'heure de la dernière modification d'une page. Le client continuera à utiliser son cache jusqu'à
ce que la valeur de dernière modification soit changée.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

### ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant que
vous souhaitez pour la ressource :

```php
Flight::route('/news', function () {
  Flight::etag('mon-id-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit que l'appel de `lastModified` ou `etag` définira et vérifiera
la valeur de cache. Si la valeur de cache est la même entre les requêtes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.

## Télécharger un fichier (v3.12.0)

Il existe une méthode d'assistance pour télécharger un fichier. Vous pouvez utiliser la méthode `download` et passer le chemin.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```