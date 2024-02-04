```fr
# Méthodes du Cadre

Flight est conçu pour être facile à utiliser et à comprendre. Ce qui suit est l'ensemble complet
des méthodes pour le cadre. Il se compose de méthodes de base, qui sont des méthodes statiques régulières,
et de méthodes extensibles, qui sont des méthodes mappées qui peuvent être filtrées
ou remplacées.

## Méthodes de Base

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crée une méthode de cadre personnalisée.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Enregistre une classe à une méthode de cadre.
Flight::before(string $name, callable $callback) // Ajoute un filtre avant une méthode de cadre.
Flight::after(string $name, callable $callback) // Ajoute un filtre après une méthode de cadre.
Flight::path(string $path) // Ajoute un chemin pour le chargement automatique des classes.
Flight::get(string $key) // Obtient une variable.
Flight::set(string $key, mixed $value) // Définit une variable.
Flight::has(string $key) // Vérifie si une variable est définie.
Flight::clear(array|string $key = []) // Efface une variable.
Flight::init() // Initialise le cadre à ses paramètres par défaut.
Flight::app() // Obtient l'instance de l'objet d'application
```

## Méthodes Extensibles

```php
Flight::start() // Lance le cadre.
Flight::stop() // Arrête le cadre et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le cadre avec un code d'état et un message facultatifs.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Associe un motif d'URL à un rappel.
Flight::group(string $pattern, callable $callback) // Crée des groupes pour les URL, le motif doit être une chaîne.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::render(string $file, array $data, ?string $key = null) // Rend un fichier de modèle.
Flight::error(Throwable $error) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'string') // Effectue une mise en cache HTTP ETag.
Flight::lastModified(int $time) // Effectue une mise en cache HTTP de dernière modification.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSONP.
```

Toutes les méthodes personnalisées ajoutées avec `map` et `register` peuvent également être filtrées.
```