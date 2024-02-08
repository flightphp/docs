# Réponses

Flight aide à générer une partie des en-têtes de réponse pour vous, mais vous avez la plupart du contrôle sur ce que vous renvoyez à l'utilisateur. Parfois, vous pouvez accéder directement à l'objet `Response`, mais la plupart du temps, vous utiliserez l'instance `Flight` pour envoyer une réponse.

## Envoi d'une réponse de base

Flight utilise ob_start() pour mettre en tampon la sortie. Cela signifie que vous pouvez utiliser `echo` ou `print` pour envoyer une réponse à l'utilisateur et Flight le capturera et le renverra à l'utilisateur avec les en-têtes appropriés.

```php

// Cela enverra "Bonjour monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	echo "Bonjour monde !";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Bonjour monde !
```

En alternative, vous pouvez appeler la méthode `write()` pour ajouter au corps également.

```php

// Cela enverra "Bonjour monde !" au navigateur de l'utilisateur
Flight::route('/', function() {
	// verbeux, mais fait parfois le travail quand vous en avez besoin
	Flight::response()->write("Bonjour monde !");

	// si vous voulez récupérer le corps que vous avez défini à ce point
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
		echo "Bonjour monde !";
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

## Définition d'un en-tête de réponse

Vous pouvez définir un en-tête tel que le type de contenu de la réponse en utilisant la méthode `header` :

```php

// Cela enverra "Bonjour monde !" au navigateur de l'utilisateur sous forme de texte brut
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Bonjour monde !";
});
```



## JSON

Flight prend en charge l'envoi de réponses JSON et JSONP. Pour envoyer une réponse JSON, vous transmettez des données à encoder en JSON :

```php
Flight::json(['id' => 123]);
```

### JSONP

Pour les requêtes JSONP, vous pouvez éventuellement transmettre le nom du paramètre de requête que vous utilisez pour définir votre fonction de rappel :

```php
Flight::jsonp(['id' => 123], 'q');
```

Ainsi, en effectuant une requête GET en utilisant `?q=my_func`, vous devriez recevoir la sortie :

```javascript
my_func({"id":123});
```

Si vous ne transmettez pas un nom de paramètre de requête, il sera par défaut `jsonp`.

## Rediriger vers une autre URL

Vous pouvez rediriger la requête actuelle en utilisant la méthode `redirect()` et en transmettant une nouvelle URL :

```php
Flight::redirect('/nouvel/emplacement');
```

Par défaut, Flight envoie un code d'état HTTP 303 ("Voir autre"). Vous pouvez éventuellement définir un code personnalisé :

```php
Flight::redirect('/nouvel/emplacement', 401);
```

## Arrêt

Vous pouvez arrêter le framework à tout moment en appelant la méthode `halt` :

```php
Flight::halt();
```

Vous pouvez également spécifier un code d'état `HTTP` et un message facultatif :

```php
Flight::halt(200, 'De retour bientôt...');
```

Appeler `halt` supprimera tout contenu de réponse jusqu'à ce point. Si vous souhaitez arrêter
le framework et afficher la réponse actuelle, utilisez la méthode `stop` :

```php
Flight::stop();
```

## Mise en cache HTTP

Flight fournit une prise en charge intégrée pour la mise en cache au niveau HTTP. Si la condition de mise en cache
est satisfaite, Flight renverra une réponse `304 Non modifié` HTTP. La prochaine fois que le
client demande la même ressource, il lui sera demandé d'utiliser sa version en cache localement.

### Mise en cache au niveau de la route

Si vous souhaitez mettre en cache toute votre réponse, vous pouvez utiliser la méthode `cache()` et transmettre le temps de mise en cache.

```php

// Cela mettra en cache la réponse pendant 5 minutes
Flight::route('/nouvelles', function () {
  Flight::cache(time() + 300);
  echo 'Ce contenu sera mis en cache.';
});

// Ou, vous pouvez utiliser une chaîne que vous transmettriez
// à la méthode strtotime()
Flight::route('/nouvelles', function () {
  Flight::cache('+5 minutes');
  echo 'Ce contenu sera mis en cache.';
});
```

### Dernière modification

Vous pouvez utiliser la méthode `lastModified` et transmettre un horodatage UNIX pour définir la date
et l'heure à laquelle une page a été modifiée pour la dernière fois. Le client continuera d'utiliser son cache jusqu'à
ce que la valeur de la dernière modification soit modifiée.

```php
Flight::route('/nouvelles', function () {
  Flight::lastModified(1234567890);
  echo 'Ce contenu sera mis en cache.';
});
```

### ETag

La mise en cache via `ETag` est similaire à `Last-Modified`, sauf que vous pouvez spécifier n'importe quel identifiant
que vous souhaitez pour la ressource :

```php
Flight::route('/nouvelles', function () {
  Flight::etag('mon-id-unique');
  echo 'Ce contenu sera mis en cache.';
});
```

Notez qu'appeler `lastModified` ou `etag` définira et vérifiera à la fois la
valeur de mise en cache. Si la valeur de mise en cache est la même entre les requêtes, Flight enverra immédiatement
une réponse `HTTP 304` et arrêtera le traitement.