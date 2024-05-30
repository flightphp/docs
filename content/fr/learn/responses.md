# Réponses

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous avez le contrôle sur ce que vous renvoyez à l'utilisateur. Parfois, vous pouvez accéder directement à l'objet `Response`, mais la plupart du temps, vous utiliserez l'instance `Flight` pour renvoyer une réponse.

## Envoi d'une réponse de base

Flight utilise ob_start() pour mettre en mémoire tampon la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur et Flight le capturera et le renverra à l'utilisateur avec les en-têtes appropriés.

```php

// Cela enverra "Bonjour, monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	echo "Bonjour, monde !";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Bonjour, monde !
```

En alternative, vous pouvez appeler la méthode `write()` pour ajouter au corps également.

```php

// Cela enverra "Bonjour, monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais fait le travail parfois lorsque vous en avez besoin
	Flight::response()->write("Bonjour, monde!");

	// si vous voulez récupérer le corps que vous avez défini à ce stade
	// vous pouvez le faire comme ceci
	$body = Flight::response()->getBody();
});
```

## Codes d'état

Vous pouvez définir le code d'état de la réponse en utilisant la méthode `status` :

```php
Flight::route('/@id', function($id) {
	if ($id == 123) {
		Flight::response()->status(200);
		echo "Bonjour, monde !";
	} else {
		Flight::response()->status(403);
		echo "Interdit";
	}
});
```

Si vous voulez obtenir le code d'état actuel, vous pouvez utiliser la méthode `status` sans arguments :

```php
Flight::response()->status(); // 200
```

## Exécution d'un rappel sur le corps de la réponse

Vous pouvez exécuter un rappel sur le corps de la réponse en utilisant la méthode `addResponseBodyCallback` :

```php
Flight::route('/utilisateurs', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Cela comprimera toutes les réponses pour n'importe quelle route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Vous pouvez ajouter plusieurs rappels et ils seront exécutés dans l'ordre où ils ont été ajoutés. Puisque cela peut accepter n'importe [callable](https://www.php.net/manual/en/language.types.callable.php), cela peut accepter un tableau de classe `[ $class, 'méthode' ]`, une clôture `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou un nom de fonction `'minify'` si vous aviez une fonction pour minifier votre code html par exemple.

**Remarque :** Les rappels de route ne fonctionneront pas si vous utilisez l'option de configuration `flight.v2.output_buffering`.

### Rappel de Route Spécifique

Si vous voulez que cela s'applique uniquement à une route spécifique, vous pouvez ajouter le rappel dans la route elle-même :

```php
Flight::route('/utilisateurs', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Cela comprimera uniquement la réponse pour cette route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Option de Middleware

Vous pouvez également utiliser un middleware pour appliquer le rappel à toutes les routes via le middleware :

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// Il s'agit d'un exemple
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minifier le corps
		return $body;
	}
}

// index.php
Flight::group('/utilisateurs', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Définition d'un en-tête de réponse

Vous pouvez définir un en-tête tel que le type de contenu de la réponse en utilisant la méthode `header` :

```php

// Cela enverra "Bonjour, monde !" au navigateur de l'utilisateur en texte brut
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Bonjour, monde !";
});
```

## JSON

Flight fournit un support pour l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON vous
transmettez des données à encoder en JSON :

```php
Flight::json(['id' => 123]);
```

### JSON avec Code d'État

Vous pouvez également transmettre un code d'état en tant que deuxième argument :

```php
Flight::json(['id' => 123], 201);
```

### JSON avec Joli Affichage

Vous pouvez également transmettre un argument à la dernière position pour activer un joli affichage :

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si vous modifiez les options transmises à `Flight::json()` et que vous souhaitez une syntaxe plus simple, vous pouvez
simplement remapper la méthode JSON :

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Et maintenant cela peut être utilisé comme ceci
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON et Arrêt de l'Exécution

Si vous voulez envoyer une réponse JSON et arrêter l'exécution, vous pouvez utiliser la méthode `jsonHalt`.
Cela est utile pour les cas où vous vérifiez peut-être un type d'autorisation et si
l'utilisateur n'est pas autorisé, vous pouvez envoyer une réponse JSON immédiatement, effacer le contenu actuel du corps
et arrêter l'exécution.

```php
Flight::route('/utilisateurs', function() {
	$authorized = someAuthorizationCheck();
	// Vérifier si l'utilisateur est autorisé
	if ($authorized === false) {
		Flight::jsonHalt(['error' => 'Non autorisé'], 401);
	}

	// Continuer avec le reste de la route
});
```

### JSONP

Pour les requêtes JSONP, vous pouvez éventuellement transmettre le nom du paramètre de requête que vous utilisez
pour définir votre fonction de rappel :

```php
Flight::jsonp(['id' => 123], 'q');
```

Donc, en faisant une requête GET en utilisant `?q=my_func`, vous devriez recevoir la sortie :

```javascript
my_func({"id":123});
```

Si vous ne transmettez pas de nom de paramètre de requête, il sera par défaut `jsonp`.

## Redirection vers une autre URL

Vous pouvez rediriger la requête actuelle en utilisant la méthode `redirect()` et en transmettant
une nouvelle URL :

```php
Flight::redirect('/nouvel/emplacement');
```

Par défaut, Flight envoie un code d'état HTTP 303 ("Voir Autre"). Vous pouvez également définir facultativement un
code personnalisé :

```php
Flight::redirect('/nouvel/emplacement', 401);
```

## Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` optionnel et un message :

```php
Flight::halt(200, 'Je reviens dans un instant...');
```

Appeler `halt` effacera tout le contenu de la réponse jusqu'à ce point. Si vous voulez arrêter
le framework et afficher la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop();
```

## Mise en cache HTTP

Flight offre une prise en charge intégrée pour la mise en cache au niveau HTTP. Si la condition de
mise en cache est remplie, Flight renverra une réponse `304 Non modifiée` HTTP. La prochaine fois que le
client demandera la même ressource, celui-ci sera invité à utiliser sa version mise en cache localement.

### Mise en cache au Niveau de la Route

Si vous voulez mettre en cache toute votre réponse, vous pouvez utiliser la méthode `cache()` et lui transmettre un temps de mise en cache.

```php

// Cela mettra en cache la réponse pendant 5 minutes
Flight::route('/actualites', function () {
  Flight::response()->cache(time() + 300);
  echo 'Ce contenu sera mis en cache.';
});

// Alternativement, vous pouvez utiliser une chaîne que vous transmettriez
// à la méthode strtotime()
Flight::route('/actualites', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Ce contenu sera mis en cache.';
});
```

### Last-Modified

Vous pouvez utiliser la méthode `lastModified` et lui transmettre un horodatage UNIX pour définir la date
et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera à utiliser sa mise en cache jusqu'à
ce que la valeur de la dernière modification soit modifiée.

```php
Flight::route('/actualites', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

### ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant
vous voulez pour la ressource :

```php
Flight::route('/actualites', function () {
  Flight::etag('mon-identifiant-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit qu'appeler `lastModified` ou `etag` définira et vérifiera
la valeur de mise en cache. Si la valeur de mise en cache est la même entre les requêtes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.