# Méthodes de l'API du cadre

Flight est conçu pour être facile à utiliser et à comprendre. Ce qui suit est l'ensemble complet
des méthodes pour le cadre. Il se compose de méthodes de base, qui sont des méthodes statiques régulières,
et de méthodes extensibles, qui sont des méthodes mappées qui peuvent être filtrées
ou remplacées.

## Méthodes de base

Ces méthodes sont fondamentales pour le cadre et ne peuvent pas être remplacées.

```php
Flight::map(string $nom, callable $rappel, bool $pass_route = false) // Crée une méthode de cadre personnalisée.
Flight::register(string $nom, string $classe, array $params = [], ?callable $rappel = null) // Enregistre une classe dans une méthode de cadre.
Flight::unregister(string $nom) // Désenregistre une classe d'une méthode de cadre.
Flight::before(string $nom, callable $rappel) // Ajoute un filtre avant une méthode de cadre.
Flight::after(string $nom, callable $rappel) // Ajoute un filtre après une méthode de cadre.
Flight::path(string $chemin) // Ajoute un chemin pour le chargement automatique des classes.
Flight::get(string $clé) // Obtient une variable.
Flight::set(string $clé, mixed $valeur) // Définit une variable.
Flight::has(string $clé) // Vérifie si une variable est définie.
Flight::clear(array|string $clé = []) // Efface une variable.
Flight::init() // Initialise le cadre avec ses paramètres par défaut.
Flight::app() // Obtient l'instance de l'objet application
Flight::request() // Obtient l'instance de l'objet requête
Flight::response() // Obtient l'instance de l'objet réponse
Flight::router() // Obtient l'instance de l'objet routeur
Flight::view() // Obtient l'instance de l'objet vue
```

## Méthodes extensibles

```php
Flight::start() // Démarre le cadre.
Flight::stop() // Arrête le cadre et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le cadre avec un code d'état et un message facultatifs.
Flight::route(string $motif, callable $rappel, bool $pass_route = false, string $alias = '') // Associe un motif URL à un rappel.
Flight::post(string $motif, callable $rappel, bool $pass_route = false, string $alias = '') // Associe un motif URL de requête POST à un rappel.
Flight::put(string $motif, callable $rappel, bool $pass_route = false, string $alias = '') // Associe un motif URL de requête PUT à un rappel.
Flight::patch(string $motif, callable $rappel, bool $pass_route = false, string $alias = '') // Associe un motif URL de requête PATCH à un rappel.
Flight::delete(string $motif, callable $rappel, bool $pass_route = false, string $alias = '') // Associe un motif URL de requête DELETE à un rappel.
Flight::group(string $motif, callable $rappel) // Crée un regroupement pour les URL, le motif doit être une chaîne.
Flight::getUrl(string $nom, array $params = []) // Génère une URL basée sur un alias de route.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::render(string $fichier, array $données, ?string $clé = null) // Rend un fichier de modèle.
Flight::error(Throwable $erreur) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'chaîne') // Effectue la mise en cache HTTP ETag.
Flight::lastModified(int $temps) // Effectue la mise en cache HTTP de dernière modification.
Flight::json(mixed $donnees, int $code = 200, bool $encoder = true, string $jeu_caractères = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $donnees, string $param = 'jsonp', int $code = 200, bool $encoder = true, string $jeu_caractères = 'utf8', int $option) // Envoie une réponse JSONP.
```

Toutes les méthodes personnalisées ajoutées avec `map` et `register` peuvent également être filtrées.