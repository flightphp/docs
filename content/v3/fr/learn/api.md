# Méthodes API du Framework

Flight est conçu pour être facile à utiliser et à comprendre. Voici l'ensemble complet des méthodes du framework. Il se compose de méthodes principales, qui sont des méthodes statiques régulières, et de méthodes extensibles, qui sont des méthodes mappées pouvant être filtrées ou remplacées.

## Méthodes Principales

Ces méthodes sont essentielles au framework et ne peuvent pas être écrasées.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crée une méthode personnalisée pour le framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Enregistre une classe à une méthode du framework.
Flight::unregister(string $name) // Désenregistre une classe d'une méthode du framework.
Flight::before(string $name, callable $callback) // Ajoute un filtre avant une méthode du framework.
Flight::after(string $name, callable $callback) // Ajoute un filtre après une méthode du framework.
Flight::path(string $path) // Ajoute un chemin pour le chargement automatique des classes.
Flight::get(string $key) // Obtiens une variable définie par Flight::set().
Flight::set(string $key, mixed $value) // Définit une variable dans le moteur de Flight.
Flight::has(string $key) // Vérifie si une variable est définie.
Flight::clear(array|string $key = []) // Efface une variable.
Flight::init() // Initialise le framework avec ses paramètres par défaut.
Flight::app() // Obtient l'instance de l'objet d'application
Flight::request() // Obtient l'instance de l'objet de requête
Flight::response() // Obtient l'instance de l'objet de réponse
Flight::router() // Obtient l'instance de l'objet de routage
Flight::view() // Obtient l'instance de l'objet de vue
```

## Méthodes Extensibles

```php
Flight::start() // Démarre le framework.
Flight::stop() // Arrête le framework et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le framework avec un code d'état facultatif et un message.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Associe un schéma d'URL à un rappel.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Associe un schéma d'URL de requête POST à un rappel.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Associe un schéma d'URL de requête PUT à un rappel.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Associe un schéma d'URL de requête PATCH à un rappel.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Associe un schéma d'URL de requête DELETE à un rappel.
Flight::group(string $pattern, callable $callback) // Crée un regroupement pour les URLs, le schéma doit être une chaîne de caractères.
Flight::getUrl(string $name, array $params = []) // Génère une URL basée sur un alias de route.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::download(string $filePath) // Télécharge un fichier.
Flight::render(string $file, array $data, ?string $key = null) // Rend un fichier de modèle.
Flight::error(Throwable $error) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'string') // Effectue la mise en cache HTTP ETag.
Flight::lastModified(int $time) // Effectue la mise en cache HTTP de la dernière modification.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON et arrête le framework.
```

Toutes les méthodes personnalisées ajoutées avec `map` et `register` peuvent également être filtrées. Pour des exemples sur la façon de mapper ces méthodes, consultez le guide [Étendre Flight](/learn/extending).