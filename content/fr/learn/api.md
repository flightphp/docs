# Méthodes de l'API du Framework

Flight est conçu pour être facile à utiliser et à comprendre. Ce qui suit est l'ensemble complet des méthodes pour le framework. Il se compose de méthodes de base, qui sont des méthodes statiques régulières, et de méthodes extensibles, qui sont des méthodes mappées qui peuvent être filtrées ou remplacées.

## Méthodes de Base

Ces méthodes sont essentielles au framework et ne peuvent pas être remplacées.

```php
Flight::map(string $nom, callable $callback, bool $pass_route = false) // Crée une méthode personnalisée pour le framework.
Flight::register(string $nom, string $class, array $params = [], ?callable $callback = null) // Enregistre une classe pour une méthode du framework.
Flight::unregister(string $nom) // Désenregistre une classe pour une méthode du framework.
Flight::before(string $nom, callable $callback) // Ajoute un filtre avant une méthode du framework.
Flight::after(string $nom, callable $callback) // Ajoute un filtre après une méthode du framework.
Flight::path(string $chemin) // Ajoute un chemin pour le chargement automatique des classes.
Flight::get(string $clé) // Récupère une variable.
Flight::set(string $clé, mixed $valeur) // Définit une variable.
Flight::has(string $clé) // Vérifie si une variable est définie.
Flight::clear(array|string $clé = []) // Efface une variable.
Flight::init() // Initialise le framework avec ses paramètres par défaut.
Flight::app() // Récupère une instance de l'objet application
Flight::request() // Récupère une instance de l'objet requête
Flight::response() // Récupère une instance de l'objet réponse
Flight::router() // Récupère une instance de l'objet routeur
Flight::view() // Récupère une instance de l'objet vue
```

## Méthodes Extensibles

```php
Flight::start() // Lance le framework.
Flight::stop() // Arrête le framework et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le framework avec un code d'état et un message optionnel.
Flight::route(string $motif, callable $retroaction, bool $pass_route = false, string $alias = '') // Assigne un motif d'URL à une rétroaction.
Flight::post(string $motif, callable $retroaction, bool $pass_route = false, string $alias = '') // Assigne un motif d'URL de requête POST à une rétroaction.
Flight::put(string $motif, callable $retroaction, bool $pass_route = false, string $alias = '') // Assigne un motif d'URL de requête PUT à une rétroaction.
Flight::patch(string $motif, callable $retroaction, bool $pass_route = false, string $alias = '') // Assigne un motif d'URL de requête PATCH à une rétroaction.
Flight::delete(string $motif, callable $retroaction, bool $pass_route = false, string $alias = '') // Assigne un motif d'URL de requête DELETE à une rétroaction.
Flight::group(string $motif, callable $retroaction) // Crée un regroupement pour les URL, le motif doit être une chaîne.
Flight::getUrl(string $nom, array $params = []) // Génère une URL basée sur un alias de route.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::render(string $fichier, array $données, ?string $cle = null) // Rend un fichier de modèle.
Flight::error(Throwable $erreur) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'string') // Effectue la mise en cache HTTP ETag.
Flight::lastModified(int $temps) // Effectue la mise en cache HTTP de la dernière modification.
Flight::json(mixed $données, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $données, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSONP.
```

Toutes les méthodes personnalisées ajoutées avec `map` et `register` peuvent également être filtrées.