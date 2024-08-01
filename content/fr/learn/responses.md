# Réponses

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous avez le contrôle sur ce que vous renvoyez à l'utilisateur. Parfois, vous pouvez accéder directement à l'objet `Response`, mais la plupart du temps, vous utiliserez l'instance de `Flight` pour envoyer une réponse.

## Envoi d'une réponse de base

Flight utilise ob_start() pour mettre en tampon la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur, et Flight le capturera et le renverra à l'utilisateur avec les en-têtes appropriés.

```php

// Cela enverra "Bonjour, le monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	echo "Bonjour, le monde !";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Bonjour, le monde !
```

En alternative, vous pouvez appeler la méthode `write()` pour ajouter du contenu au corps également.

```php

// Cela enverra "Bonjour, le monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais fait parfois le travail quand vous en avez besoin
	Flight::response()->write("Bonjour, le monde !");

	// si vous voulez récupérer le corps que vous avez défini à ce stade
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
		echo "Bonjour, le monde !";
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

## Définition du corps de la réponse

Vous pouvez définir le corps de la réponse en utilisant la méthode `write`, cependant, si vous faites un `echo` ou un `print`, 
cela sera capturé et envoyé comme corps de réponse via le tampon de sortie.

```php
Flight::route('/', function() {
	Flight::response()->write("Bonjour, le monde !");
});

// identique à

Flight::route('/', function() {
	echo "Bonjour, le monde !";
});
```

### Effacer le corps de la réponse

Si vous voulez effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody` :

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Bonjour, le monde !");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Exécution d'un rappel sur le corps de la réponse

Vous pouvez exécuter un rappel sur le corps de la réponse en utilisant la méthode `addResponseBodyCallback` :

```php
Flight::route('/utilisateurs', function() {
	$db = Flight::db();
	$utilisateurs = $db->fetchAll("SELECT * FROM utilisateurs");
	Flight::render('tableau_utilisateurs', ['utilisateurs' => $utilisateurs]);
});

// Cela compressera toutes les réponses pour n'importe quelle route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Vous pouvez ajouter plusieurs rappels et ils seront exécutés dans l'ordre où ils ont été ajoutés. Étant donné que cela peut accepter n'importe quelle [callable](https://www.php.net/manual/en/language.types.callable.php), cela peut accepter un tableau de classe `[ $class, 'method' ]`, une fermeture `$strReplace = function($body) { str_replace('salut', 'bonjour', $body); };`, ou un nom de fonction `'minifier'` si vous aviez une fonction pour minifier votre code HTML par exemple.

**Remarque :** Les rappels de route ne fonctionneront pas si vous utilisez l'option de configuration `flight.v2.output_buffering`.

### Rappel de route spécifique

Si vous voulez que cela s'applique uniquement à une route spécifique, vous pouvez ajouter le rappel dans la route elle-même :

```php
Flight::route('/utilisateurs', function() {
	$db = Flight::db();
	$utilisateurs = $db->fetchAll("SELECT * FROM utilisateurs");
	Flight::render('tableau_utilisateurs', ['utilisateurs' => $utilisateurs]);

	// Cela compressera uniquement la réponse pour cette route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Option Middleware

Vous pouvez également utiliser un middleware pour appliquer le rappel à toutes les routes via le middleware :

```php
// Middleware de Minification.php
class MiddlewareMinification {
	public function before() {
		// Appliquer le rappel ici sur l'objet response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minifier le corps d'une manière ou d'une autre
		return $body;
	}
}

// index.php
Flight::group('/utilisateurs', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MiddlewareMinification() ]);
```

## Définition d'un en-tête de réponse

Vous pouvez définir un en-tête tel que le type de contenu de la réponse en utilisant la méthode `header` :

```php

// Cela enverra "Bonjour, le monde !" au navigateur de l'utilisateur en texte brut
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Bonjour, le monde !";
});
```

## JSON

Flight prend en charge l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON
vous transmettez des données à encoder en JSON :

```php
Flight::json(['id' => 123]);
```

### JSON avec code d'état

Vous pouvez également transmettre un code d'état en tant que deuxième argument :

```php
Flight::json(['id' => 123], 201);
```

### JSON avec formatage agréable

Vous pouvez également transmettre un argument à la dernière position pour activer le formatage agréable :

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si vous modifiez les options transmises à `Flight::json()` et que vous voulez une syntaxe plus simple, vous pouvez
simplement remapper la méthode JSON :

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Et maintenant cela peut être utilisé comme ceci
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON et Arrêt de l'exécution (v3.10.0)

Si vous voulez envoyer une réponse JSON et arrêter l'exécution, vous pouvez utiliser la méthode `jsonHalt`.
Ceci est utile pour les cas où vous vérifiez peut-être un type d'autorisation et si
l'utilisateur n'est pas autorisé, vous pouvez envoyer immédiatement une réponse JSON, effacer le contenu du corps existant
et arrêter l'exécution.

```php
Flight::route('/utilisateurs', function() {
	$autorise = certaineVerificationDautorisation();
	// Vérifier si l'utilisateur est autorisé
	if($autorise === false) {
		Flight::jsonHalt(['erreur' => 'Non autorisé'], 401);
	}

	// Continuer avec le reste de la route
});
```

Avant v3.10.0, vous auriez dû faire quelque chose comme ceci :

```php
Flight::route('/utilisateurs', function() {
	$autorise = certaineVerificationDautorisation();
	// Vérifier si l'utilisateur est autorisé
	if($autorise === false) {
		Flight::halt(401, json_encode(['error' => 'Non autorisé']));
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

Ainsi, lors d'une requête GET en utilisant `?q=ma_fonction`, vous devriez recevoir la sortie :

```javascript
ma_fonction({"id":123});
```

Si vous ne transmettez pas de nom de paramètre de requête, il sera par défaut à `jsonp`.

## Rediriger vers une autre URL

Vous pouvez rediriger la demande actuelle en utilisant la méthode `redirect()` et en transmettant
une nouvelle URL :

```php
Flight::redirect('/nouvel/emplacement');
```

Par défaut, Flight envoie un code d'état HTTP 303 ("See Other"). Vous pouvez éventuellement définir un
code personnalisé :

```php
Flight::redirect('/nouvel/emplacement', 401);
```

## Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` facultatif et un message :

```php
Flight::halt(200, 'Reviens tout de suite...');
```

Appeler `halt` va supprimer tout le contenu de réponse jusqu'à ce point. Si vous voulez arrêter
le framework et envoyer le contenu de réponse actuel, utilisez la méthode `stop` :

```php
Flight::stop();
```

## Effacer les données de réponse

Vous pouvez effacer le corps de la réponse et les en-têtes en utilisant la méthode `clear()`. Cela effacera
tous les en-têtes assignés à la réponse, effacera le corps de la réponse, et définira le code d'état à `200`.

```php
Flight::response()->clear();
```

### Effacer uniquement le corps de la réponse

Si vous voulez uniquement effacer le corps de la réponse, vous pouvez utiliser la méthode `clearBody()` :

```php
// Cela conservera toujours tous les en-têtes définis sur l'objet response().
Flight::response()->clearBody();
```

## Mise en cache HTTP

Flight offre une prise en charge intégrée de la mise en cache au niveau HTTP. Si la condition de mise en cache
est remplie, Flight renverra une réponse HTTP `304 Not Modified`. La prochaine fois que le
client demandera la même ressource, il sera invité à utiliser sa version mise en cache localement.

### Mise en cache au niveau de la route

Si vous voulez mettre en cache toute votre réponse, vous pouvez utiliser la méthode `cache()` et transmettre le temps à mettre en cache.

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

### Modifié pour la dernière fois

Vous pouvez utiliser la méthode `lastModified` et transmettre un horodatage UNIX pour définir la date
et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera d'utiliser sa mise en cache jusqu'à ce que
la valeur de la dernière modification soit modifiée.

```php
Flight::route('/actualites', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

### ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant
que vous souhaitez pour la ressource :

```php
Flight::route('/actualites', function () {
  Flight::etag('mon-identifiant-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Gardez à l'esprit que l'appel à `lastModified` ou `etag` va à la fois définir et vérifier la
valeur de la mise en cache. Si la valeur de mise en cache est la même entre les requêtes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.

### Télécharger un fichier

Il y a une méthode d'aide pour télécharger un fichier. Vous pouvez utiliser la méthode `download` et transmettre le chemin.

```php
Flight::route('/telechargement', function () {
  Flight::download('/chemin/vers/fichier.txt');
});
```