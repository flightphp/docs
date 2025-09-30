# Réponses

## Aperçu

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous conservez la plupart des contrôles sur ce que vous renvoyez à l'utilisateur. La plupart du temps, vous accéderez directement à l'objet `response()`, mais Flight dispose de quelques méthodes d'assistance pour définir certains en-têtes de réponse pour vous.

## Compréhension

Après que l'utilisateur a envoyé sa [requête](/learn/requests) à votre application, vous devez générer une réponse appropriée pour lui. Ils vous ont envoyé des informations comme la langue qu'ils préfèrent, s'ils peuvent gérer certains types de compression, leur agent utilisateur, etc., et après avoir traité tout cela, il est temps de leur renvoyer une réponse appropriée. Cela peut consister à définir des en-têtes, à sortir un corps de HTML ou de JSON pour eux, ou à les rediriger vers une page.

## Utilisation de base

### Envoi d'un corps de réponse

Flight utilise `ob_start()` pour tamponner la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur et Flight la capturera et la renverra à l'utilisateur avec les en-têtes appropriés.

```php
// Ceci enverra "Hello, World!" au navigateur de l'utilisateur
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
// Ceci enverra "Hello, World!" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais fait le travail parfois quand vous en avez besoin
	Flight::response()->write("Hello, World!");

	// si vous voulez récupérer le corps que vous avez défini à ce moment
	// vous pouvez le faire comme ceci
	$body = Flight::response()->getBody();
});
```

### JSON

Flight fournit un support pour l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON, vous
passez des données à encoder en JSON :

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// d'une manière ou d'une autre, extrayez vos utilisateurs d'une base de données par exemple
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* plus d'utilisateurs */ ]
```

> **Note :** Par défaut, Flight enverra un en-tête `Content-Type: application/json` avec la réponse. Il utilisera également les drapeaux `JSON_THROW_ON_ERROR` et `JSON_UNESCAPED_SLASHES` lors de l'encodage du JSON.

#### JSON avec code de statut

Vous pouvez également passer un code de statut en tant que deuxième argument :

```php
Flight::json(['id' => 123], 201);
```

#### JSON avec impression jolie

Vous pouvez également passer un argument à la dernière position pour activer l'impression jolie :

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Changement de l'ordre des arguments JSON

`Flight::json()` est une méthode très ancienne, mais l'objectif de Flight est de maintenir la compatibilité arrière
pour les projets. C'est en fait très simple si vous voulez refaire l'ordre des arguments pour utiliser une syntaxe plus simple,
vous pouvez simplement remapper la méthode JSON [comme n'importe quelle autre méthode Flight](/learn/extending) :

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// maintenant vous n'avez pas à utiliser `true, 'utf-8'` lors de l'utilisation de la méthode json() !
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Et maintenant elle peut être utilisée comme ceci
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON et arrêt de l'exécution

_v3.10.0_

Si vous voulez envoyer une réponse JSON et arrêter l'exécution, vous pouvez utiliser la méthode `jsonHalt()`.
C'est utile pour les cas où vous vérifiez peut-être un type d'autorisation et si
l'utilisateur n'est pas autorisé, vous pouvez envoyer une réponse JSON immédiatement, effacer le contenu du corps existant
et arrêter l'exécution.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifiez si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// pas de exit; nécessaire ici.
	}

	// Continuer avec le reste de la route
});
```

Avant v3.10.0, vous deviez faire quelque chose comme ceci :

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Vérifiez si l'utilisateur est autorisé
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continuer avec le reste de la route
});
```

### Effacement d'un corps de réponse

Si vous voulez effacer le corps de réponse, vous pouvez utiliser la méthode `clearBody` :

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

Le cas d'utilisation ci-dessus n'est probablement pas courant, cependant il pourrait être plus courant si cela était utilisé dans un [middleware](/learn/middleware).

### Exécution d'un rappel sur le corps de réponse

Vous pouvez exécuter un rappel sur le corps de réponse en utilisant la méthode `addResponseBodyCallback` :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Ceci gzippira toutes les réponses pour n'importe quelle route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Vous pouvez ajouter plusieurs rappels et ils seront exécutés dans l'ordre où ils ont été ajoutés. Comme cela peut accepter n'importe quel [appelable](https://www.php.net/manual/en/language.types.callable.php), il peut accepter un tableau de classe `[ $class, 'method' ]`, une closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou un nom de fonction `'minify'` si vous aviez une fonction pour minifier votre code html par exemple.

**Note :** Les rappels de route ne fonctionneront pas si vous utilisez l'option de configuration `flight.v2.output_buffering`.

#### Rappel de route spécifique

Si vous vouliez que cela ne s'applique qu'à une route spécifique, vous pourriez ajouter le rappel dans la route elle-même :

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Ceci gzippira seulement la réponse pour cette route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Option Middleware

Vous pouvez également utiliser [middleware](/learn/middleware) pour appliquer le rappel à toutes les routes via middleware :

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
		// minifiez le corps d'une manière ou d'une autre
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### Codes de statut

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

Si vous voulez obtenir le code de statut actuel, vous pouvez utiliser la méthode `status` sans aucun argument :

```php
Flight::response()->status(); // 200
```

### Définition d'un en-tête de réponse

Vous pouvez définir un en-tête tel que le type de contenu de la réponse en utilisant la méthode `header` :

```php
// Ceci enverra "Hello, World!" au navigateur de l'utilisateur en texte brut
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirection

Vous pouvez rediriger la requête actuelle en utilisant la méthode `redirect()` et en passant
une nouvelle URL :

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // ceci est nécessaire pour que la fonctionnalité ci-dessous ne s'exécute pas
	}

	// ajoutez le nouvel utilisateur...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Note :** Par défaut, Flight envoie un code de statut HTTP 303 ("See Other"). Vous pouvez optionnellement définir un
code personnalisé :

```php
Flight::redirect('/new/location', 301); // permanent
```

### Arrêt de l'exécution de la route

Vous pouvez arrêter le framework et sortir immédiatement à n'importe quel point en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code de statut `HTTP` et un message optionnels :

```php
Flight::halt(200, 'Be right back...');
```

L'appel à `halt` rejettera tout contenu de réponse jusqu'à ce point et arrêtera toute exécution.
Si vous voulez arrêter le framework et sortir la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop($httpStatusCode = null);
```

> **Note :** `Flight::stop()` a un comportement étrange tel qu'il sortira la réponse mais continuera à exécuter votre script ce qui pourrait ne pas être ce que vous voulez. Vous pouvez utiliser `exit` ou `return` après avoir appelé `Flight::stop()` pour empêcher une exécution supplémentaire, mais il est généralement recommandé d'utiliser `Flight::halt()`.

Ceci sauvegardera la clé et la valeur de l'en-tête dans l'objet de réponse. À la fin du cycle de vie de la requête,
il construira les en-têtes et enverra une réponse.

## Utilisation avancée

### Envoi d'un en-tête immédiatement

Il peut y avoir des moments où vous devez faire quelque chose de personnalisé avec l'en-tête et vous devez envoyer l'en-tête
sur cette ligne de code même avec laquelle vous travaillez. Si vous définissez une [route streamée](/learn/routing),
c'est ce dont vous auriez besoin. Cela est réalisable via `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

Pour les requêtes JSONP, vous pouvez optionnellement passer le nom du paramètre de requête que vous
utilisez pour définir votre fonction de rappel :

```php
Flight::jsonp(['id' => 123], 'q');
```

Donc, lors d'une requête GET en utilisant `?q=my_func`, vous devriez recevoir la sortie :

```javascript
my_func({"id":123});
```

Si vous ne passez pas de nom de paramètre de requête, il utilisera par défaut `jsonp`.

> **Note :** Si vous utilisez encore des requêtes JSONP en 2025 et au-delà, rejoignez le chat et dites-nous pourquoi ! Nous aimons entendre de bonnes histoires de bataille/horreur !

### Effacement des données de réponse

Vous pouvez effacer le corps de réponse et les en-têtes en utilisant la méthode `clear()`. Cela effacera
tout en-tête assigné à la réponse, effacera le corps de réponse, et définira le code de statut à `200`.

```php
Flight::response()->clear();
```

#### Effacement uniquement du corps de réponse

Si vous voulez seulement effacer le corps de réponse, vous pouvez utiliser la méthode `clearBody()` :

```php
// Ceci gardera toujours les en-têtes définis sur l'objet response().
// Ceci gardera toujours les en-têtes définis sur l'objet response().
Flight::response()->clearBody();
```

### Mise en cache HTTP

Flight fournit un support intégré pour la mise en cache au niveau HTTP. Si la condition de mise en cache
est remplie, Flight renverra une réponse HTTP `304 Not Modified`. La prochaine fois que le
client demandera la même ressource, il sera invité à utiliser sa version mise en cache localement.

#### Mise en cache au niveau de la route

Si vous voulez mettre en cache toute votre réponse, vous pouvez utiliser la méthode `cache()` et passer le temps de mise en cache.

```php
// Ceci mettra en cache la réponse pendant 5 minutes
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

Vous pouvez utiliser la méthode `lastModified` et passer un timestamp UNIX pour définir la date
et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera à utiliser son cache jusqu'à
ce que la valeur de dernière modification soit changée.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

La mise en cache `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant que vous
voulez pour la ressource :

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Gardez à l'esprit que l'appel à `lastModified` ou `etag` définira et vérifiera tous les deux
la valeur de cache. Si la valeur de cache est la même entre les requêtes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.

### Téléchargement d'un fichier

_v3.12.0_

Il y a une méthode d'assistance pour streamer un fichier vers l'utilisateur final. Vous pouvez utiliser la méthode `download` et passer le chemin.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## Voir aussi
- [Routing](/learn/routing) - Comment mapper les routes vers les contrôleurs et rendre les vues.
- [Requests](/learn/requests) - Comprendre comment gérer les requêtes entrantes.
- [Middleware](/learn/middleware) - Utiliser le middleware avec les routes pour l'authentification, la journalisation, etc.
- [Why a Framework?](/learn/why-frameworks) - Comprendre les avantages d'utiliser un framework comme Flight.
- [Extending](/learn/extending) - Comment étendre Flight avec votre propre fonctionnalité.

## Dépannage
- Si vous avez des problèmes avec les redirections qui ne fonctionnent pas, assurez-vous d'ajouter un `return;` à la méthode.
- `stop()` et `halt()` ne sont pas la même chose. `halt()` arrêtera l'exécution immédiatement, tandis que `stop()` permettra à l'exécution de continuer.

## Journal des modifications
- v3.12.0 - Ajout de la méthode d'assistance downloadFile.
- v3.10.0 - Ajout de `jsonHalt`.
- v1.0 - Version initiale.